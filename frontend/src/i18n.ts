import i18n from 'i18next'
import { initReactI18next } from 'react-i18next'

const resources = {
  es: {
    translation: {
      nav: { home: 'Inicio', groups: 'Grupos', explore: 'Explorar', classes: 'Clases', profile: 'Perfil' },
      brand: { eyebrow: 'Archivo vivo', location: 'Bogotá · Colombia' },
      actions: { search: 'Buscar', openCollection: 'Abrir colección' },
      home: {
        date: 'Lunes, 7 de septiembre',
        greeting: 'Buenas tardes, Camila',
        title: 'El cuerpo también guarda memoria.',
        intro: 'Tu próxima clase y nuevas historias de la escena local.',
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
        profile: { eyebrow: 'Cuenta', title: 'Perfil', description: 'Tus guardados, reservas y preferencias.', first: 'Próximas reservas', second: 'Colecciones guardadas', third: 'Ajustes' }
      },
      status: { offlineReady: 'La app ya funciona sin conexión.', updateReady: 'Hay una nueva versión disponible.', update: 'Actualizar', close: 'Cerrar' }
    }
  }
} as const

void i18n.use(initReactI18next).init({ resources, lng: 'es', fallbackLng: 'es', interpolation: { escapeValue: false } })

export default i18n
