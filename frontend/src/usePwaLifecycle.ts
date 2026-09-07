import { useCallback, useEffect, useRef, useState } from 'react'

export async function clearPrivateCaches() {
  if (!('serviceWorker' in navigator)) return
  const registrations = await navigator.serviceWorker.getRegistrations()
  registrations.forEach((registration) => {
    registration.active?.postMessage({ type: 'CLEAR_PRIVATE_CACHES' })
    registration.waiting?.postMessage({ type: 'CLEAR_PRIVATE_CACHES' })
  })
}

export function usePwaLifecycle() {
  const [offlineReady, setOfflineReady] = useState(false)
  const [needRefresh, setNeedRefresh] = useState(false)
  const registrationRef = useRef<ServiceWorkerRegistration | null>(null)
  const refreshingRef = useRef(false)

  useEffect(() => {
    if (!('serviceWorker' in navigator) || import.meta.env.DEV) return

    let mounted = true
    navigator.serviceWorker.register('/sw.js').then((registration) => {
      if (!mounted) return
      registrationRef.current = registration

      if (registration.waiting) setNeedRefresh(true)

      registration.addEventListener('updatefound', () => {
        const worker = registration.installing
        worker?.addEventListener('statechange', () => {
          if (worker.state !== 'installed' || !mounted) return
          if (navigator.serviceWorker.controller) setNeedRefresh(true)
          else setOfflineReady(true)
        })
      })
    }).catch((error: unknown) => console.error('No se pudo registrar el service worker', error))

    const reloadOnActivation = () => {
      if (refreshingRef.current) window.location.reload()
    }
    navigator.serviceWorker.addEventListener('controllerchange', reloadOnActivation)
    const clearSensitiveState = () => { void clearPrivateCaches() }
    window.addEventListener('dance:logout', clearSensitiveState)
    window.addEventListener('dance:tenant-change', clearSensitiveState)

    return () => {
      mounted = false
      navigator.serviceWorker.removeEventListener('controllerchange', reloadOnActivation)
      window.removeEventListener('dance:logout', clearSensitiveState)
      window.removeEventListener('dance:tenant-change', clearSensitiveState)
    }
  }, [])

  const updateServiceWorker = useCallback(() => {
    refreshingRef.current = true
    registrationRef.current?.waiting?.postMessage({ type: 'SKIP_WAITING' })
  }, [])

  return {
    offlineReady: [offlineReady, setOfflineReady] as const,
    needRefresh: [needRefresh, setNeedRefresh] as const,
    updateServiceWorker
  }
}
