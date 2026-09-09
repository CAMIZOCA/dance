const CACHE_NAME = 'dance-shell-v0.2.2'
const PRIVATE_CACHE_PREFIX = 'dance-private-'
const APP_SHELL = ['/', '/index.html', '/manifest.webmanifest', '/dance-mark.svg']
const MEDIA_EXTENSIONS = /\.(?:mp4|webm|mov|m4v|avi|mp3|wav|ogg)(?:\?.*)?$/i
const PUBLIC_ASSET_EXTENSIONS = /\.(?:js|css|png|jpe?g|gif|webp|avif|svg|ico|woff2?)(?:\?.*)?$/i

function isPublicAsset(url) {
  if (url.pathname.startsWith('/api/')) return false
  if (url.pathname.startsWith('/assets/') && PUBLIC_ASSET_EXTENSIONS.test(url.pathname)) return true
  if (APP_SHELL.includes(url.pathname)) return true
  return false
}

function responseCanBeCached(response) {
  const cacheControl = response.headers.get('Cache-Control')?.toLowerCase() ?? ''
  const hasSetCookie = response.headers.has('Set-Cookie')
  return response.ok && !cacheControl.includes('private') && !cacheControl.includes('no-store') && !hasSetCookie
}

self.addEventListener('install', (event) => {
  event.waitUntil(caches.open(CACHE_NAME).then((cache) => cache.addAll(APP_SHELL)))
})

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(keys
        .filter((key) => (key.startsWith('dance-shell-') && key !== CACHE_NAME) || key.startsWith(PRIVATE_CACHE_PREFIX))
        .map((key) => caches.delete(key))))
      .then(() => self.clients.claim())
  )
})

self.addEventListener('message', (event) => {
  if (event.data?.type === 'SKIP_WAITING') {
    self.skipWaiting()
    return
  }

  if (event.data?.type === 'CLEAR_PRIVATE_CACHES') {
    event.waitUntil(
      caches.keys().then((keys) => Promise.all(
        keys.filter((key) => key.startsWith(PRIVATE_CACHE_PREFIX)).map((key) => caches.delete(key))
      ))
    )
  }
})

self.addEventListener('fetch', (event) => {
  const { request } = event
  const url = new URL(request.url)

  if (
    request.method !== 'GET' ||
    url.origin !== self.location.origin ||
    url.pathname.startsWith('/api/') ||
    request.destination === 'video' ||
    request.destination === 'audio' ||
    MEDIA_EXTENSIONS.test(url.pathname)
  ) return

  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request)
        .catch(() => caches.match('/index.html'))
    )
    return
  }

  if (!isPublicAsset(url)) return

  event.respondWith(
    caches.match(request).then((cached) => cached || fetch(request).then((response) => {
      if (responseCanBeCached(response)) {
        const copy = response.clone()
        void caches.open(CACHE_NAME).then((cache) => cache.put(request, copy))
      }
      return response
    }))
  )
})
