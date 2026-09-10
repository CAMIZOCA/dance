import i18n from 'i18next'
import { initReactI18next } from 'react-i18next'

const resources = {
  es: {
    translation: {
      nav: { home: 'Inicio', groups: 'Grupos', explore: 'Explorar', classes: 'Clases', profile: 'Perfil' },
      brand: { eyebrow: 'Archivo vivo', location: 'Bogotá · Colombia' },
      actions: { search: 'Buscar', openCollection: 'Abrir colección' },
      search: {
        title: 'Buscar en Danza',
        placeholder: 'Clases, grupos o colecciones',
        inputLabel: 'Buscar clases, grupos o colecciones',
        empty: 'No encontramos resultados con ese nombre.'
      },
      auth: {
        eyebrow: 'Acceso privado',
        title: 'La memoria de tu academia, cuidada por sesión.',
        subtitle: 'Entra con tu cuenta para elegir academia y continuar desde tu archivo activo.',
        loginEyebrow: 'Cuenta',
        loginTitle: 'Iniciar sesión',
        forgotEyebrow: 'Recuperación',
        forgotTitle: 'Restablecer contraseña',
        email: 'Correo',
        password: 'Contraseña',
        signIn: 'Entrar',
        sendReset: 'Enviar instrucciones',
        forgotLink: 'Olvidé mi contraseña',
        backToLogin: 'Volver al inicio de sesión',
        genericError: 'No fue posible completar la solicitud.'
      },
      tenant: {
        eyebrow: 'Academia activa',
        title: 'Hola, {{name}}. Elige tu academia.',
        subtitle: 'Solo aparecen membresías vigentes y academias activas.',
        active: 'Academia'
      },
      home: {
        date: 'Lunes, 7 de septiembre',
        greeting: 'Buenas tardes, {{name}}',
        title: 'El cuerpo también guarda memoria.',
        intro: '{{tenant}} tiene nuevas clases e historias para ti.',
        next: 'Tu próxima clase',
        featured: 'En el archivo',
        seeAll: 'Ver agenda completa',
        week: 'Esta semana'
      },
      featuredClass: { type: 'Contemporáneo · Nivel abierto', title: 'Laboratorio de piso', place: 'La Fábrica · Chapinero' },
      agenda: {
        first: { weekday: 'MIÉ', title: 'Improvisación y escucha', meta: '20:00 · Casa Kilele' },
        second: { weekday: 'SÁB', title: 'Danza afro del Pacífico', meta: '10:30 · La Ventana' }
      },
      archive: { collection: 'COLECCIÓN 06', title: 'Huellas de ciudad', meta: '12 registros · Bogotá' },
      sync: { label: 'Sincronizado', time: 'hace 2 minutos' },
      screens: {
        groups: { eyebrow: 'Tu comunidad', title: 'Grupos', description: 'Compañías, colectivos y procesos que sigues.', first: 'Colectivo Carretel', second: 'La Ventana', third: 'Movimiento en Red' },
        explore: { eyebrow: 'Archivo abierto', title: 'Explorar', description: 'Historias, cuerpos y lugares de la escena local.', first: 'Memoria oral', second: 'Espacios independientes', third: 'Creación joven' },
        classes: { eyebrow: 'Agenda', title: 'Clases', description: 'Encuentra prácticas abiertas cerca de ti.', first: 'Hoy · 18:30', second: 'Miércoles · 20:00', third: 'Sábado · 10:30' },
        profile: { eyebrow: 'Cuenta', title: 'Perfil', description: 'Sesión, academia activa y datos básicos.', first: 'Próximas reservas', second: 'Colecciones guardadas', third: 'Ajustes' }
      },
      profile: {
        settings: 'Ajustes del perfil',
        name: 'Nombre',
        email: 'Correo',
        save: 'Guardar cambios',
        saving: 'Guardando…',
        saved: 'Perfil actualizado.',
        verified: 'correo verificado',
        unverified: 'correo pendiente de verificación',
        logout: 'Cerrar sesión'
      },
      records: {
        title: 'Últimos registros',
        empty: 'Aún no hay acciones guardadas en esta academia.',
        saved: 'Registro guardado en la academia activa.',
        now: 'Ahora',
        types: {
          class: 'Clase',
          agenda: 'Agenda',
          archive: 'Archivo',
          group: 'Grupo',
          explore: 'Explorar',
          profile: 'Perfil',
          tenant: 'Academia',
          action: 'Acción'
        }
      },
      status: { loading: 'Preparando tu sesión...', offlineReady: 'La app ya funciona sin conexión.', updateReady: 'Hay una nueva versión disponible.', update: 'Actualizar', close: 'Cerrar' }
    }
  }
} as const

void i18n.use(initReactI18next).init({ resources, lng: 'es', fallbackLng: 'es', interpolation: { escapeValue: false } })

export default i18n
