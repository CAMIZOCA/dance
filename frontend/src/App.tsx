import { useEffect, useState, type ComponentType } from 'react'
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
  House,
  MapPin,
  Search,
  Sparkles,
  UsersRound,
  X
} from 'lucide-react'

type Screen = 'home' | 'groups' | 'explore' | 'classes' | 'profile'

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

function HomeScreen({ onNavigate }: { onNavigate: (screen: Screen) => void }) {
  const { t } = useTranslation()
  return (
    <motion.div {...entrance} className="screen home-screen">
      <header className="mobile-header">
        <Brand />
        <button className="icon-button" aria-label={t('actions.search')}><Search size={20} /></button>
      </header>

      <section className="intro-block">
        <p className="eyebrow">{t('home.date')}</p>
        <h1>{t('home.greeting')}</h1>
        <p>{t('home.intro')}</p>
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

function SecondaryScreen({ screen }: { screen: Exclude<Screen, 'home'> }) {
  const { t } = useTranslation()
  const key = `screens.${screen}`
  const items = ['first', 'second', 'third'] as const
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

  return (
    <div className="app-shell">
      <aside className="desktop-sidebar">
        <Brand />
        <Navigation active={active} onNavigate={navigate} />
        <div className="sidebar-note"><span className="live-dot" /> {t('sync.label')}<br /><small>{t('sync.time')}</small></div>
      </aside>
      <main>
        <AnimatePresence mode="wait">
          {active === 'home' ? <HomeScreen key="home" onNavigate={navigate} /> : <SecondaryScreen key={active} screen={active} />}
        </AnimatePresence>
      </main>
      <div className="mobile-navigation"><Navigation active={active} onNavigate={navigate} /></div>
      <PwaNotice />
    </div>
  )
}
