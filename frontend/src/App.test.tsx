import { beforeEach, vi } from 'vitest'
import { render, screen, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import App from './App'

const userPayload = {
  id: 1,
  name: 'Camila Demo',
  email: 'student@demo.local',
  email_verified: true,
  active_organization_id: null
}

const tenantPayload = {
  id: 10,
  name: 'Ritmo Demo Academy',
  slug: 'ritmo-demo-academy',
  primary_color: '#e45b3d',
  secondary_color: '#17211b',
  membership: { status: 'active', access_expires_at: null, joined_at: null },
  is_active: false
}

const recordPayload = {
  id: 99,
  type: 'class',
  label: 'Laboratorio de piso',
  metadata: { screen: 'home' },
  created_at: '2026-09-08T23:28:53.000000Z'
}

function jsonResponse(body: unknown, init: ResponseInit = {}) {
  return Promise.resolve(new Response(JSON.stringify(body), {
    status: 200,
    headers: { 'Content-Type': 'application/json' },
    ...init
  }))
}

describe('App shell', () => {
  beforeEach(() => {
    window.history.replaceState({}, '', '/')
    vi.restoreAllMocks()
  })

  it('muestra el inicio de sesión cuando no hay sesión activa', async () => {
    vi.spyOn(globalThis, 'fetch').mockResolvedValue(new Response(null, { status: 401 }))

    render(<App />)

    expect(await screen.findByRole('heading', { name: 'Iniciar sesión' })).toBeInTheDocument()
    expect(screen.getByRole('button', { name: 'Entrar' })).toBeInTheDocument()
  })

  it('permite iniciar sesión, seleccionar academia y navegar a clases', async () => {
    const user = userEvent.setup()
    const fetchMock = vi.spyOn(globalThis, 'fetch')
      .mockResolvedValueOnce(new Response(null, { status: 401 }))
      .mockResolvedValueOnce(new Response(null, { status: 401 }))
      .mockImplementation((input) => {
        const url = input.toString()

        if (url.endsWith('/csrf-token')) {
          return jsonResponse({ csrf_token: 'token' })
        }

        if (url.endsWith('/auth/login')) {
          return jsonResponse({ data: userPayload })
        }

        if (url.endsWith('/me')) {
          return jsonResponse({ data: userPayload })
        }

        if (url.endsWith('/tenants')) {
          return jsonResponse({ data: [tenantPayload] })
        }

        if (url.endsWith('/tenant')) {
          return jsonResponse({ data: { ...tenantPayload, is_active: true } })
        }

        if (url.endsWith('/records')) {
          return jsonResponse({ data: [] })
        }

        return Promise.resolve(new Response(null, { status: 404 }))
      })

    render(<App />)

    await user.click(await screen.findByRole('button', { name: 'Entrar' }))
    await user.click(await screen.findByRole('button', { name: /Ritmo Demo Academy/i }))
    expect(await screen.findByRole('heading', { name: 'Buenas tardes, Camila' })).toBeInTheDocument()
    expect(screen.getByText('Laboratorio de piso')).toBeInTheDocument()

    const classLinks = await screen.findAllByRole('button', { name: /Clases/i })
    await user.click(classLinks[0])
    expect(screen.getByRole('heading', { name: 'Clases', level: 1 })).toBeInTheDocument()
    expect(screen.getByText('Encuentra prácticas abiertas cerca de ti.')).toBeInTheDocument()
    expect(window.location.pathname).toBe('/clases')
    expect(fetchMock).toHaveBeenCalled()
  })

  it('guarda cambios de perfil desde la sesión autenticada', async () => {
    const user = userEvent.setup()
    vi.spyOn(globalThis, 'fetch').mockImplementation((input, init) => {
      const url = input.toString()

      if (url.endsWith('/me') && init?.method === 'PATCH') {
        return jsonResponse({ data: { ...userPayload, name: 'Camila Archivo', active_organization_id: 10 } })
      }

      if (url.endsWith('/me')) {
        return jsonResponse({ data: { ...userPayload, active_organization_id: 10 } })
      }

      if (url.endsWith('/tenants')) {
        return jsonResponse({ data: [{ ...tenantPayload, is_active: true }] })
      }

      if (url.endsWith('/records')) {
        return jsonResponse({ data: [] })
      }

      if (url.endsWith('/csrf-token')) {
        return jsonResponse({ csrf_token: 'token' })
      }

      return Promise.resolve(new Response(null, { status: 404 }))
    })

    render(<App />)

    await waitFor(() => expect(screen.getByRole('heading', { name: 'Buenas tardes, Camila' })).toBeInTheDocument())
    await user.click(screen.getAllByRole('button', { name: /Perfil/i })[0])
    await user.clear(screen.getByLabelText('Nombre'))
    await user.type(screen.getByLabelText('Nombre'), 'Camila Archivo')
    await user.click(screen.getByRole('button', { name: 'Guardar cambios' }))

    expect(await screen.findByText('Perfil actualizado.')).toBeInTheDocument()
  })

  it('guarda una acción de pantalla como registro de academia', async () => {
    const user = userEvent.setup()
    const fetchMock = vi.spyOn(globalThis, 'fetch').mockImplementation((input, init) => {
      const url = input.toString()

      if (url.endsWith('/me')) {
        return jsonResponse({ data: { ...userPayload, active_organization_id: 10 } })
      }

      if (url.endsWith('/tenants')) {
        return jsonResponse({ data: [{ ...tenantPayload, is_active: true }] })
      }

      if (url.endsWith('/records') && init?.method === 'POST') {
        return jsonResponse({ data: recordPayload }, { status: 201 })
      }

      if (url.endsWith('/records')) {
        return jsonResponse({ data: [] })
      }

      if (url.endsWith('/csrf-token')) {
        return jsonResponse({ csrf_token: 'token' })
      }

      return Promise.resolve(new Response(null, { status: 404 }))
    })

    render(<App />)

    await user.click(await screen.findByRole('button', { name: /Laboratorio de piso/i }))

    await waitFor(() => expect(fetchMock).toHaveBeenCalledWith(
      '/api/v1/records',
      expect.objectContaining({
        method: 'POST'
      })
    ))
    expect(await screen.findByText('Registro guardado en la academia activa.')).toBeInTheDocument()
  })
})
