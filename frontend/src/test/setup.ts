import '@testing-library/jest-dom/vitest'
import '../i18n'

Object.defineProperty(window, 'scrollTo', { value: vi.fn(), writable: true })
