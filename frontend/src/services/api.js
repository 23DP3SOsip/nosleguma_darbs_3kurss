import axios from 'axios'

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

console.log('API_URL configured as:', API_URL)

const api = axios.create({
  baseURL: API_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true,
})

// Добавляем CSRF токен и auth токен если он есть
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('auth_token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  console.log('Request:', config.method?.toUpperCase(), config.url)
  return config
})

// Обработка ошибок
api.interceptors.response.use(
  (response) => {
    console.log('Response:', response.status, response.data)
    return response
  },
  (error) => {
    console.error('Error:', error.message, error.response?.status, error.response?.data)
    if (error.response?.status === 401) {
      // Если 401 - нужно перенаправить на логин
      localStorage.removeItem('auth_token')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api
