import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import '@mdi/font/css/materialdesignicons.css'

export default createVuetify({
  components,
  directives,
  theme: {
    defaultTheme: 'dark',
    themes: {
      dark: {
        colors: {
          background: '#0b0b0b',
          surface: '#141414',
          'surface-bright': '#1f1f1f',
          primary: '#FFC107',
          secondary: '#B8860B',
          accent: '#FFD54F',
          error: '#CF6679',
          info: '#90CAF9',
          success: '#81C784',
          warning: '#FFB300',
          // on-surface / text color (light gray)
          'on-surface': '#d0d0d0',
          'on-surface-variant': '#cfcfcf',
        },
      },
    },
  },
})