import { beforeEach } from 'vitest'
import { render, screen } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import App from './App'

describe('App shell', () => {
  beforeEach(() => window.history.replaceState({}, '', '/'))

  it('muestra el inicio y su próxima clase', () => {
    render(<App />)
    expect(screen.getByRole('heading', { name: 'Buenas tardes, Camila' })).toBeInTheDocument()
    expect(screen.getByText('Laboratorio de piso')).toBeInTheDocument()
  })

  it('permite navegar a la agenda de clases', async () => {
    const user = userEvent.setup()
    render(<App />)
    const classLinks = screen.getAllByRole('button', { name: /Clases/i })
    await user.click(classLinks[0])
    expect(screen.getByRole('heading', { name: 'Clases', level: 1 })).toBeInTheDocument()
    expect(screen.getByText('Encuentra prácticas abiertas cerca de ti.')).toBeInTheDocument()
    expect(window.location.pathname).toBe('/clases')
  })
})
