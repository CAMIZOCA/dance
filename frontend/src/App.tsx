import { FormEvent, useEffect, useState, type ComponentType } from 'react'
import { AnimatePresence, motion } from 'framer-motion'
import { useTranslation } from 'react-i18next'
import { usePwaLifecycle } from './usePwaLifecycle'
import {
  ArrowRight,
  CalendarDays,
  Check,
  ChevronRight,
  CircleUserRound,
  Compass,
  DoorOpen,
  House,
  MapPin,
  RefreshCw,
  Search,
  ShieldCheck,
  Sparkles,
  UsersRound,
  X
} from 'lucide-react'

type Screen = 'home' | 'groups' | 'explore' | 'classes' | 'profile'
type AuthMode = 'login' | 'forgot'

type ApiUser = {
  id: number
  name: string
  email: string
  email_verified: boolean
  active_organization_id: number | null
}

type Tenant = {
  id: number
  name: string
  slug: string
  primary_color: string
  secondary_color: string
  membership: {
    status: string
    access_expires_at: string | null
    joined_at: string | null
  }
  is_active: boolean
}

type ApiEnvelope<T> = {
  data: T
}

type AuthState =
  | { status: 'loading' }
  | { status: 'guest' }
  | { status: 'selecting-tenant'; user: ApiUser; tenants: Tenant[] }
  | { status: 'authenticated'; user: ApiUser; tenants: Tenant[]; activeTenant: Tenant }

const screenPaths: Record<Screen, string> = {
  home: '/',
  groups: '/grupos',
  explore: '/explorar',
  classes: '/clases',
  profile: '/perfil'
}

function screenFromPath(pathname: string): Screen {
  return (Object.entries(screenPaths).find(([, path]) => path === pathname)?.[0] as Screen | undefined) ?? 'home'
}

type NavItem = {
  id: Screen
  icon: ComponentType<{ size?: number; strokeWidth?: number }>
  labelKey: string
}

const navItems: NavItem[] = [
  { id: 'home', icon: House, labelKey: 'nav.home' },
  { id: 'groups', icon: UsersRound, labelKey: 'nav.groups' },
  { id: 'explore', icon: Compass, labelKey: 'nav.explore' },
  { id: 'classes', icon: CalendarDays, labelKey: 'nav.classes' },
  { id: 'profile', icon: CircleUserRound, labelKey: 'nav.profile' }
]

const entrance = {
  initial: { opacity: 0, y: 14 },
  animate: { opacity: 1, y: 0 },
  transition: { duration: 0.46, ease: [0.22, 1, 0.36, 1] as const }
}

async function apiRequest<T>(path: string, options: RequestInit = {}): Promise<T> {
  const headers = new Headers(options.headers)
  headers.set('Accept', 'application/json')

  if (options.body && !headers.has('Content-Type')) {
    headers.set('Content-Type', 'application/json')
  }

  const response = await fetch(path, {
    credentials: 'include',
    ...options,
    headers
  })

  if (!response.ok) {
    const payload = await response.json().catch(() => ({})) as { message?: string; errors?: Record<string, string[]> }
    const message = payload.errors ? Object.values(payload.errors).flat()[0] : payload.message
    throw new Error(message ?? 'No fue posible completar la solicitud.')
  }

  if (response.status === 204) {
    return undefined as T
  }

  return response.json() as Promise<T>
}

async function prepareCsrf(): Promise<void> {
  await apiRequest<{ csrf_token: string }>('/api/v1/csrf-token')
}

async function loadSession(): Promise<AuthState> {
  try {
    const [userEnvelope, tenantsEnvelope] = await Promise.all([
      apiRequest<ApiEnvelope<ApiUser>>('/api/v1/me'),
      apiRequest<ApiEnvelope<Tenant[]>>('/api/v1/tenants')
    ])
    const activeTenant = tenantsEnvelope.data.find((tenant) => tenant.id === userEnvelope.data.active_organization_id)

    if (!activeTenant) {
      return { status: 'selecting-tenant', user: userEnvelope.data, tenants: tenantsEnvelope.data }
    }

    return { status: 'authenticated', user: userEnvelope.data, tenants: tenantsEnvelope.data, activeTenant }
  } catch {
    return { status: 'guest' }
  }
}

function Brand() {
  const { t } = useTranslation()
  return (
    <div className="brand-lockup" aria-label="Danza, archivo vivo">
      <span className="brand-word">DANZA</span>
      <span className="brand-meta">{t('brand.eyebrow')}<br />{t('brand.location')}</span>
    </div>
  )
}

function Navigation({ active, onNavigate }: { active: Screen; onNavigate: (screen: Screen) => void }) {
  const { t } = useTranslation()
  return (
    <nav className="primary-nav" aria-label="Navegación principal">
      {navItems.map(({ id, icon: Icon, labelKey }) => (
        <button
          className={`nav-item ${active === id ? 'is-active' : ''}`}
          key={id}
          type="button"
          aria-current={active === id ? 'page' : undefined}
          onClick={() => onNavigate(id)}
        >
          {active === id && <motion.span layoutId="nav-marker" className="nav-marker" />}
          <Icon size={20} strokeWidth={1.7} />
          <span>{t(labelKey)}</span>
        </button>
      ))}
    </nav>
  )
}

function HomeScreen({ onNavigate, user, tenant }: { onNavigate: (screen: Screen) => void; user: ApiUser; tenant: Tenant }) {
  const { t } = useTranslation()
  return (
    <motion.div {...entrance} className="screen home-screen">
      <header className="mobile-header">
        <Brand />
        <button className="icon-button" aria-label={t('actions.search')}><Search size={20} /></button>
      </header>

      <section className="intro-block">
        <p className="eyebrow">{t('home.date')}</p>
        <h1>{t('home.greeting', { name: user.name.split(' ')[0] })}</h1>
        <p>{t('home.intro', { tenant: tenant.name })}</p>
      </section>

      <section aria-labelledby="next-class-title">
        <div className="section-heading">
          <h2 id="next-class-title">{t('home.next')}</h2>
          <span>18:30</span>
        </div>
        <motion.button className="class-feature" whileTap={{ scale: 0.99 }} type="button">
          <div className="movement-figure" aria-hidden="true">
            <span className="figure-head" />
            <span className="figure-body" />
            <span className="figure-arm arm-one" />
            <span className="figure-arm arm-two" />
            <span className="figure-leg leg-one" />
            <span className="figure-leg leg-two" />
          </div>
          <div className="class-copy">
            <div>
              <span className="class-tag">{t('featuredClass.type')}</span>
              <h3>{t('featuredClass.title')}</h3>
            </div>
            <div className="class-place"><MapPin size={16} /> {t('featuredClass.place')}</div>
          </div>
          <span className="feature-index">01 / 04</span>
          <ArrowRight className="feature-arrow" size={24} />
        </motion.button>
      </section>

      <section className="agenda-section" aria-labelledby="agenda-title">
        <div className="section-heading">
          <h2 id="agenda-title">{t('home.week')}</h2>
          <button type="button" onClick={() => onNavigate('classes')}>{t('home.seeAll')}</button>
        </div>
        <div className="agenda-list">
          <AgendaRow day="09" weekday={t('agenda.first.weekday')} title={t('agenda.first.title')} meta={t('agenda.first.meta')} tone="dark" />
          <AgendaRow day="12" weekday={t('agenda.second.weekday')} title={t('agenda.second.title')} meta={t('agenda.second.meta')} tone="coral" />
        </div>
      </section>

      <section className="archive-section" aria-labelledby="archive-title">
        <div className="archive-statement">
          <Sparkles size={18} />
          <p className="eyebrow">{t('home.featured')}</p>
          <h2>{t('home.title')}</h2>
        </div>
        <div className="archive-strip" aria-label={`${t('archive.collection')}: ${t('archive.title')}`}>
          <div className="archive-art archive-art-one"><span>1987</span></div>
          <div className="archive-copy"><p>{t('archive.collection')}</p><h3>{t('archive.title')}</h3><p>{t('archive.meta')}</p><button type="button">{t('actions.openCollection')} <ArrowRight size={16} /></button></div>
          <div className="archive-art archive-art-two"><span>2004</span></div>
        </div>
      </section>
    </motion.div>
  )
}

function AgendaRow({ day, weekday, title, meta, tone }: { day: string; weekday: string; title: string; meta: string; tone: 'dark' | 'coral' }) {
  return (
    <button className="agenda-row" type="button">
      <span className={`date-block ${tone}`}><strong>{day}</strong><small>{weekday}</small></span>
      <span className="agenda-copy"><strong>{title}</strong><small>{meta}</small></span>
      <ChevronRight size={18} />
    </button>
  )
}

function SecondaryScreen({
  screen,
  user,
  tenant,
  tenants,
  onTenantChange,
  onLogout,
  onUserChange
}: {
  screen: Exclude<Screen, 'home'>
  user: ApiUser
  tenant: Tenant
  tenants: Tenant[]
  onTenantChange: (tenant: Tenant, user: ApiUser) => void
  onLogout: () => void
  onUserChange: (user: ApiUser) => void
}) {
  const { t } = useTranslation()
  const key = `screens.${screen}`
  const items = ['first', 'second', 'third'] as const

  if (screen === 'profile') {
    return (
      <ProfileScreen
        user={user}
        tenant={tenant}
        tenants={tenants}
        onTenantChange={onTenantChange}
        onLogout={onLogout}
        onUserChange={onUserChange}
      />
    )
  }

  return (
    <motion.div key={screen} {...entrance} className="screen secondary-screen">
      <header className="mobile-header"><Brand /><button className="icon-button" aria-label={t('actions.search')}><Search size={20} /></button></header>
      <section className="secondary-intro">
        <p className="eyebrow">{t(`${key}.eyebrow`)}</p>
        <h1>{t(`${key}.title`)}</h1>
        <p>{t(`${key}.description`)}</p>
      </section>
      <div className="ruled-list">
        {items.map((item, index) => (
          <button type="button" key={item}>
            <span className="list-number">0{index + 1}</span>
            <span>{t(`${key}.${item}`)}</span>
            <ArrowRight size={19} />
          </button>
        ))}
      </div>
    </motion.div>
  )
}

function AuthScreen({ onAuthenticated }: { onAuthenticated: (state: AuthState) => void }) {
  const { t } = useTranslation()
  const [mode, setMode] = useState<AuthMode>('login')
  const [email, setEmail] = useState('student@demo.local')
  const [password, setPassword] = useState('DanceDemo2026!')
  const [status, setStatus] = useState<'idle' | 'submitting'>('idle')
  const [message, setMessage] = useState<string | null>(null)

  const submit = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault()
    setStatus('submitting')
    setMessage(null)

    try {
      await prepareCsrf()

      if (mode === 'forgot') {
        const response = await apiRequest<{ message: string }>('/api/v1/auth/forgot-password', {
          method: 'POST',
          body: JSON.stringify({ email })
        })
        setMessage(response.message)
        return
      }

      await apiRequest<ApiEnvelope<ApiUser>>('/api/v1/auth/login', {
        method: 'POST',
        body: JSON.stringify({ email, password })
      })
      onAuthenticated(await loadSession())
    } catch (error) {
      setMessage(error instanceof Error ? error.message : t('auth.genericError'))
    } finally {
      setStatus('idle')
    }
  }

  return (
    <main className="auth-shell">
      <motion.section className="auth-visual" initial={{ opacity: 0 }} animate={{ opacity: 1 }} transition={{ duration: 0.5 }}>
        <Brand />
        <div className="auth-statement">
          <p className="eyebrow">{t('auth.eyebrow')}</p>
          <h1>{t('auth.title')}</h1>
          <p>{t('auth.subtitle')}</p>
        </div>
      </motion.section>
      <motion.section className="auth-panel" {...entrance} aria-labelledby="auth-title">
        <div>
          <ShieldCheck size={22} />
          <p className="eyebrow">{mode === 'login' ? t('auth.loginEyebrow') : t('auth.forgotEyebrow')}</p>
          <h2 id="auth-title">{mode === 'login' ? t('auth.loginTitle') : t('auth.forgotTitle')}</h2>
        </div>
        <form className="auth-form" onSubmit={submit}>
          <label>
            <span>{t('auth.email')}</span>
            <input value={email} onChange={(event) => setEmail(event.target.value)} type="email" autoComplete="email" required />
          </label>
          {mode === 'login' && (
            <label>
              <span>{t('auth.password')}</span>
              <input value={password} onChange={(event) => setPassword(event.target.value)} type="password" autoComplete="current-password" required />
            </label>
          )}
          {message && <p className="form-message" role="status">{message}</p>}
          <button className="primary-action" type="submit" disabled={status === 'submitting'}>
            {status === 'submitting' && <RefreshCw size={17} className="spin" />}
            {mode === 'login' ? t('auth.signIn') : t('auth.sendReset')}
          </button>
        </form>
        <button className="text-action" type="button" onClick={() => { setMode(mode === 'login' ? 'forgot' : 'login'); setMessage(null) }}>
          {mode === 'login' ? t('auth.forgotLink') : t('auth.backToLogin')}
        </button>
      </motion.section>
    </main>
  )
}

function TenantSelectionScreen({ state, onSelected, onLogout }: { state: Extract<AuthState, { status: 'selecting-tenant' }>; onSelected: (tenant: Tenant, user: ApiUser) => void; onLogout: () => void }) {
  const { t } = useTranslation()
  const [message, setMessage] = useState<string | null>(null)

  const selectTenant = async (tenant: Tenant) => {
    setMessage(null)

    try {
      await prepareCsrf()
      const selected = await apiRequest<ApiEnvelope<Tenant>>('/api/v1/tenant', {
        method: 'PUT',
        body: JSON.stringify({ organization_id: tenant.id })
      })
      const session = await apiRequest<ApiEnvelope<ApiUser>>('/api/v1/me')
      onSelected(selected.data, session.data)
    } catch (error) {
      setMessage(error instanceof Error ? error.message : t('auth.genericError'))
    }
  }

  return (
    <main className="tenant-shell">
      <header className="session-header">
        <Brand />
        <button className="text-action" type="button" onClick={onLogout}><DoorOpen size={17} />{t('profile.logout')}</button>
      </header>
      <motion.section className="tenant-selection" {...entrance}>
        <p className="eyebrow">{t('tenant.eyebrow')}</p>
        <h1>{t('tenant.title', { name: state.user.name.split(' ')[0] })}</h1>
        <p>{t('tenant.subtitle')}</p>
        <div className="tenant-list">
          {state.tenants.map((tenant) => (
            <button key={tenant.id} type="button" onClick={() => void selectTenant(tenant)}>
              <span className="tenant-swatch" style={{ background: tenant.primary_color }} />
              <span><strong>{tenant.name}</strong><small>{tenant.slug}</small></span>
              <ArrowRight size={19} />
            </button>
          ))}
        </div>
        {message && <p className="form-message" role="status">{message}</p>}
      </motion.section>
    </main>
  )
}

function ProfileScreen({
  user,
  tenant,
  tenants,
  onTenantChange,
  onLogout,
  onUserChange
}: {
  user: ApiUser
  tenant: Tenant
  tenants: Tenant[]
  onTenantChange: (tenant: Tenant, user: ApiUser) => void
  onLogout: () => void
  onUserChange: (user: ApiUser) => void
}) {
  const { t } = useTranslation()
  const [name, setName] = useState(user.name)
  const [email, setEmail] = useState(user.email)
  const [message, setMessage] = useState<string | null>(null)

  useEffect(() => {
    setName(user.name)
    setEmail(user.email)
  }, [user.email, user.name])

  const saveProfile = async (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault()
    setMessage(null)

    try {
      await prepareCsrf()
      const response = await apiRequest<ApiEnvelope<ApiUser>>('/api/v1/me', {
        method: 'PATCH',
        body: JSON.stringify({ name, email })
      })
      onUserChange(response.data)
      setMessage(t('profile.saved'))
    } catch (error) {
      setMessage(error instanceof Error ? error.message : t('auth.genericError'))
    }
  }

  const selectTenant = async (tenantId: number) => {
    const selectedTenant = tenants.find((candidate) => candidate.id === tenantId)
    if (!selectedTenant) return

    await prepareCsrf()
    const response = await apiRequest<ApiEnvelope<Tenant>>('/api/v1/tenant', {
      method: 'PUT',
      body: JSON.stringify({ organization_id: tenantId })
    })
    const session = await apiRequest<ApiEnvelope<ApiUser>>('/api/v1/me')
    onTenantChange(response.data, session.data)
  }

  return (
    <motion.div key="profile" {...entrance} className="screen secondary-screen profile-screen">
      <header className="mobile-header"><Brand /><button className="icon-button" aria-label={t('profile.logout')} onClick={onLogout}><DoorOpen size={20} /></button></header>
      <section className="secondary-intro profile-intro">
        <p className="eyebrow">{t('screens.profile.eyebrow')}</p>
        <h1>{t('screens.profile.title')}</h1>
        <p>{tenant.name} · {user.email_verified ? t('profile.verified') : t('profile.unverified')}</p>
      </section>
      <section className="profile-layout" aria-label={t('profile.settings')}>
        <form className="auth-form profile-form" onSubmit={saveProfile}>
          <label>
            <span>{t('profile.name')}</span>
            <input value={name} onChange={(event) => setName(event.target.value)} required />
          </label>
          <label>
            <span>{t('profile.email')}</span>
            <input value={email} onChange={(event) => setEmail(event.target.value)} type="email" required />
          </label>
          <button className="primary-action" type="submit">{t('profile.save')}</button>
          {message && <p className="form-message" role="status">{message}</p>}
        </form>
        <div className="tenant-switcher">
          <label htmlFor="tenant-select">{t('tenant.active')}</label>
          <select id="tenant-select" value={tenant.id} onChange={(event) => void selectTenant(Number(event.target.value))}>
            {tenants.map((candidate) => <option key={candidate.id} value={candidate.id}>{candidate.name}</option>)}
          </select>
          <button className="text-action logout-line" type="button" onClick={onLogout}><DoorOpen size={17} />{t('profile.logout')}</button>
        </div>
      </section>
    </motion.div>
  )
}

function PwaNotice() {
  const { t } = useTranslation()
  const {
    offlineReady: [offlineReady, setOfflineReady],
    needRefresh: [needRefresh, setNeedRefresh],
    updateServiceWorker
  } = usePwaLifecycle()

  const visible = offlineReady || needRefresh
  const close = () => { setOfflineReady(false); setNeedRefresh(false) }

  return (
    <AnimatePresence>
      {visible && (
        <motion.aside className="pwa-notice" role="status" initial={{ opacity: 0, y: 30 }} animate={{ opacity: 1, y: 0 }} exit={{ opacity: 0, y: 20 }}>
          <Check size={18} />
          <p>{needRefresh ? t('status.updateReady') : t('status.offlineReady')}</p>
          {needRefresh && <button className="update-button" type="button" onClick={updateServiceWorker}>{t('status.update')}</button>}
          <button className="close-button" aria-label={t('status.close')} type="button" onClick={close}><X size={18} /></button>
        </motion.aside>
      )}
    </AnimatePresence>
  )
}

export default function App() {
  const { t } = useTranslation()
  const [active, setActive] = useState<Screen>(() => screenFromPath(window.location.pathname))
  const [authState, setAuthState] = useState<AuthState>({ status: 'loading' })

  useEffect(() => {
    void loadSession().then(setAuthState)
  }, [])

  useEffect(() => {
    const onPopState = () => setActive(screenFromPath(window.location.pathname))
    window.addEventListener('popstate', onPopState)
    return () => window.removeEventListener('popstate', onPopState)
  }, [])

  const navigate = (screen: Screen) => {
    const path = screenPaths[screen]
    if (window.location.pathname !== path) window.history.pushState({ screen }, '', path)
    setActive(screen)
  }

  useEffect(() => {
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }, [active])

  const logout = async () => {
    try {
      await prepareCsrf()
      await apiRequest<void>('/api/v1/auth/logout', { method: 'POST' })
    } finally {
      window.dispatchEvent(new Event('dance:logout'))
      setAuthState({ status: 'guest' })
      navigate('home')
    }
  }

  if (authState.status === 'loading') {
    return <main className="loading-shell"><Brand /><p>{t('status.loading')}</p></main>
  }

  if (authState.status === 'guest') {
    return <><AuthScreen onAuthenticated={setAuthState} /><PwaNotice /></>
  }

  if (authState.status === 'selecting-tenant') {
    return (
      <>
        <TenantSelectionScreen
          state={authState}
          onSelected={(tenant, user) => setAuthState({ status: 'authenticated', user, tenants: authState.tenants.map((candidate) => ({ ...candidate, is_active: candidate.id === tenant.id })), activeTenant: tenant })}
          onLogout={() => void logout()}
        />
        <PwaNotice />
      </>
    )
  }

  const session = authState

  return (
    <div className="app-shell">
      <aside className="desktop-sidebar">
        <Brand />
        <Navigation active={active} onNavigate={navigate} />
        <div className="sidebar-note"><span className="live-dot" /> {t('sync.label')}<br /><small>{t('sync.time')}</small></div>
      </aside>
      <main>
        <AnimatePresence mode="wait">
          {active === 'home' ? (
            <HomeScreen key="home" onNavigate={navigate} user={session.user} tenant={session.activeTenant} />
          ) : (
            <SecondaryScreen
              key={active}
              screen={active}
              user={session.user}
              tenant={session.activeTenant}
              tenants={session.tenants}
              onLogout={() => void logout()}
              onUserChange={(user) => setAuthState({ ...session, user })}
              onTenantChange={(tenant, user) => setAuthState({
                status: 'authenticated',
                user,
                activeTenant: tenant,
                tenants: session.tenants.map((candidate) => ({ ...candidate, is_active: candidate.id === tenant.id }))
              })}
            />
          )}
        </AnimatePresence>
      </main>
      <div className="mobile-navigation"><Navigation active={active} onNavigate={navigate} /></div>
      <PwaNotice />
    </div>
  )
}
