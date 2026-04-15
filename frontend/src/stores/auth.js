import { defineStore } from 'pinia'
import api from '../services/api'

const TOKEN_KEY = 'auth_token'
const USER_KEY = 'auth_user'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem(TOKEN_KEY),
    user: JSON.parse(localStorage.getItem(USER_KEY) || 'null'),
    loading: false,
  }),

  getters: {
    isAuthenticated: (state) => Boolean(state.token),
    role: (state) => state.user?.role || null,
    canOpenAdminPanel: (state) => ['admin', 'vadiba'].includes(state.user?.role || ''),
  },

  actions: {
    persistAuth(token, user) {
      this.token = token
      this.user = user
      localStorage.setItem(TOKEN_KEY, token)
      localStorage.setItem(USER_KEY, JSON.stringify(user))
    },

    clearAuth() {
      this.token = null
      this.user = null
      localStorage.removeItem(TOKEN_KEY)
      localStorage.removeItem(USER_KEY)
    },

    async login(payload) {
      this.loading = true
      try {
        const { data } = await api.post('/api/auth/login', payload)
        this.persistAuth(data.token, data.user)
        return data.user
      } finally {
        this.loading = false
      }
    },

    async fetchCurrentUser() {
      if (!this.token) {
        return null
      }

      try {
        const { data } = await api.get('/api/auth/me')
        this.user = data.user
        localStorage.setItem(USER_KEY, JSON.stringify(data.user))
        return data.user
      } catch {
        this.clearAuth()
        return null
      }
    },

    async logout() {
      try {
        if (this.token) {
          await api.post('/api/auth/logout')
        }
      } finally {
        this.clearAuth()
      }
    },
  },
})
