import { expect, test } from '@playwright/test'

const userPayload = {
  id: 1,
  name: 'Camila Demo',
  email: 'student@demo.local',
  email_verified: true,
  active_organization_id: 10
}

const tenantPayload = {
  id: 10,
  name: 'Ritmo Demo Academy',
  slug: 'ritmo-demo-academy',
  primary_color: '#e45b3d',
  secondary_color: '#17211b',
  membership: { status: 'active', access_expires_at: null, joined_at: null },
  is_active: true
}

async function mockAuthenticatedApi(page: import('@playwright/test').Page) {
  await page.route('**/api/v1/csrf-token', (route) => route.fulfill({
    contentType: 'application/json',
    headers: { 'Cache-Control': 'no-store, private' },
    body: JSON.stringify({ csrf_token: 'token' })
  }))
  await page.route('**/api/v1/me', (route) => route.fulfill({
    contentType: 'application/json',
    headers: { 'Cache-Control': 'no-store, private' },
    body: JSON.stringify({ data: userPayload })
  }))
  await page.route('**/api/v1/tenants', (route) => route.fulfill({
    contentType: 'application/json',
    headers: { 'Cache-Control': 'no-store, private' },
    body: JSON.stringify({ data: [tenantPayload] })
  }))
  await page.route('**/api/v1/auth/logout', (route) => route.fulfill({
    status: 204,
    headers: { 'Cache-Control': 'no-store, private' }
  }))
}

async function activateAppServiceWorker(page: import('@playwright/test').Page) {
  await page.goto('/')
  await page.evaluate(() => navigator.serviceWorker.ready)
  await page.reload()
  await page.waitForFunction(() => Boolean(navigator.serviceWorker.controller))
}

test('abre el shell y navega a Explorar', async ({ page }) => {
  await mockAuthenticatedApi(page)
  await page.goto('/')
  await expect(page.getByRole('heading', { name: 'Buenas tardes, Camila' })).toBeVisible()
  await page.getByRole('button', { name: 'Explorar' }).first().click()
  await expect(page.getByRole('heading', { name: 'Explorar', level: 1 })).toBeVisible()
})

test('expone un manifiesto PWA en español', async ({ page, request }) => {
  await mockAuthenticatedApi(page)
  await page.goto('/')
  const manifestHref = await page.locator('link[rel="manifest"]').getAttribute('href')
  expect(manifestHref).toBeTruthy()
  const response = await request.get(manifestHref!)
  expect(response.ok()).toBeTruthy()
  const manifest = await response.json()
  expect(manifest.lang).toBe('es')
  expect(manifest.short_name).toBe('Danza')
})

test('mantiene el shell disponible sin conexión', async ({ page, context }) => {
  await mockAuthenticatedApi(page)
  await activateAppServiceWorker(page)

  await context.setOffline(true)
  await page.reload()

  await expect(page.getByRole('heading', { name: 'Buenas tardes, Camila' })).toBeVisible()
})

test('mantiene el deep link después de refrescar', async ({ page }) => {
  await mockAuthenticatedApi(page)
  await page.goto('/explorar')
  await expect(page.getByRole('heading', { name: 'Explorar', level: 1 })).toBeVisible()

  await page.reload()

  await expect(page).toHaveURL(/\/explorar$/)
  await expect(page.getByRole('heading', { name: 'Explorar', level: 1 })).toBeVisible()
})

test('nunca cachea API ni respuestas privadas y limpia cachés sensibles', async ({ page, context }) => {
  await mockAuthenticatedApi(page)
  await context.route('**/api/private-profile', (route) => route.fulfill({
    contentType: 'application/json',
    headers: { 'Cache-Control': 'private, no-store', 'Set-Cookie': 'session=secret; HttpOnly' },
    body: JSON.stringify({ name: 'Privado' })
  }))
  await context.route('**/assets/private-profile.png', (route) => route.fulfill({
    contentType: 'image/png',
    headers: { 'Cache-Control': 'private, no-store', 'Set-Cookie': 'media=secret; HttpOnly' },
    body: Buffer.from('not-a-real-image')
  }))
  await activateAppServiceWorker(page)

  await page.evaluate(async () => {
    await fetch('/api/private-profile')
    await fetch('/assets/private-profile.png')
  })

  const cachedSensitiveResponse = await page.evaluate(async () => {
    const keys = await caches.keys()
    const matches = await Promise.all(keys.flatMap((key) => [
      caches.open(key).then((cache) => cache.match('/api/private-profile')),
      caches.open(key).then((cache) => cache.match('/assets/private-profile.png'))
    ]))
    return matches.some(Boolean)
  })
  expect(cachedSensitiveResponse).toBe(false)

  await page.evaluate(async () => {
    const cache = await caches.open('dance-private-e2e')
    await cache.put('/secret', new Response('sensitive'))
    window.dispatchEvent(new Event('dance:logout'))
  })
  await expect.poll(() => page.evaluate(async () => !(await caches.keys()).includes('dance-private-e2e'))).toBe(true)
})

test('detecta una actualización y activa el worker solo tras confirmación', async ({ page }) => {
  await mockAuthenticatedApi(page)
  await activateAppServiceWorker(page)

  await page.evaluate(async () => {
    const registration = await navigator.serviceWorker.register('/sw-update-fixture.js', { scope: '/' })
    if (registration.waiting) return
    const worker = registration.installing
    if (!worker) throw new Error('La actualización no inició la instalación')
    await new Promise<void>((resolve, reject) => {
      worker.addEventListener('statechange', () => {
        if (worker.state === 'installed') resolve()
        if (worker.state === 'redundant') reject(new Error('El worker de actualización quedó redundante'))
      })
    })
  })

  await expect(page.getByText('Hay una nueva versión disponible.')).toBeVisible()
  const navigation = page.waitForEvent('framenavigated')
  await page.getByRole('button', { name: 'Actualizar' }).click()
  await navigation

  await expect(page.getByRole('heading', { name: 'Buenas tardes, Camila' })).toBeVisible()
})
