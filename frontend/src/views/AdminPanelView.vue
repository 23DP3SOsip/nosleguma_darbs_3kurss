<script setup>
import { computed, onMounted, ref } from 'vue'
import api from '../services/api'
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()

const activeTab = ref('users')
const roleLabel = computed(() => {
  return (
    {
      vadiba: 'Vadība',
      admin: 'Administrators',
      user: 'Lietotājs',
    }[authStore.user?.role] || authStore.user?.role || ''
  )
})

const users = ref([])
const loadingUsers = ref(false)
const savingUser = ref(false)
const deletingId = ref(null)
const userErrorMessage = ref('')
const userSuccessMessage = ref('')
const editingUserId = ref(null)
const editingUser = ref({ name: '', email: '', role: '', password: '' })
const editingUserOriginalRole = ref('')
const editingUserIsVadiba = ref(false)
const savingEditedUser = ref(false)
// Users search & sort
const usersSearchQuery = ref('')
const usersSortKey = ref('id')
const usersSortDir = ref('asc')

const cars = ref([])
const loadingCars = ref(false)
const savingCar = ref(false)
const deletingCarId = ref(null)
const carErrorMessage = ref('')
const carSuccessMessage = ref('')
const editingCarId = ref(null)
// Cars search & sort
const carsSearchQuery = ref('')
const carsSortKey = ref('id')
const carsSortDir = ref('asc')

const maintenanceLogs = ref([])
const loadingMaintenance = ref(false)
const savingMaintenance = ref(false)
const deletingMaintenanceId = ref(null)
const maintenanceErrorMessage = ref('')
const maintenanceSuccessMessage = ref('')
const maintenanceTypes = [
  'Tehniskā apkope',
  'Plānotā apkope',
  'Remonts',
  'Diagnostika',
  'Eļļas maiņa',
  'Riepu maiņa',
  'Bremžu pārbaude',
  'Elektronikas remonts',
  'Cits',
]

// Maintenance search & sort
const maintenanceSearchQuery = ref('')
const maintenanceSortKey = ref('id')
const maintenanceSortDir = ref('asc')
// Maintenance export period
const maintenanceExportPeriod = ref('all')

const filteredSortedMaintenance = computed(() => {
  let list = maintenanceLogs.value.slice()

  const q = maintenanceSearchQuery.value.trim().toLowerCase()
  if (q) {
    list = list.filter((m) => {
      return (
        String(m.id).includes(q) ||
        (m.maintenance_type || '').toLowerCase().includes(q) ||
        (m.description || '').toLowerCase().includes(q) ||
        (m.user?.name || '').toLowerCase().includes(q) ||
        (m.car?.brand || '').toLowerCase().includes(q) ||
        (m.car?.model || '').toLowerCase().includes(q) ||
        (m.car?.plate_number || '').toLowerCase().includes(q)
      )
    })
  }

  const key = maintenanceSortKey.value
  const dir = maintenanceSortDir.value === 'asc' ? 1 : -1

  list.sort((a, b) => {
    const va = a[key]
    const vb = b[key]
    if (va == null && vb == null) return 0
    if (va == null) return -1 * dir
    if (vb == null) return 1 * dir
    if (typeof va === 'number' && typeof vb === 'number') return (va - vb) * dir
    return String(va).localeCompare(String(vb)) * dir
  })

  return list
})

const maintenanceForm = ref({
  car_id: '',
  maintenance_type: '',
  description: '',
  performed_at: '',
  mileage: '',
  cost: '',
})

const performedAtField = ref(null)

function openPerformedAtPicker() {
  // Try component ref -> native input -> call focus+click or showPicker
  const comp = performedAtField.value
  const input = comp?.$el?.querySelector('input') || comp?.$el || document.querySelector('input[type="datetime-local"][name=performed_at]')
  if (input) {
    try {
      if (typeof input.showPicker === 'function') {
        input.showPicker()
        return
      }
    } catch (e) {
      // ignore
    }
    input.focus()
    input.click()
  }
}

const reservationLogs = ref([])
const loadingLogs = ref(false)
const logsErrorMessage = ref('')

// Logs search & sort
const logsSearchQuery = ref('')
const logsSortKey = ref('id')
const logsSortDir = ref('asc')
// Logs export period
const logsExportPeriod = ref('all')

const filteredSortedReservationLogs = computed(() => {
  let list = reservationLogs.value.slice()

  const q = logsSearchQuery.value.trim().toLowerCase()
  if (q) {
    list = list.filter((r) => {
      return (
        String(r.id).includes(q) ||
        (r.user?.name || '').toLowerCase().includes(q) ||
        (r.car?.brand || '').toLowerCase().includes(q) ||
        (r.car?.model || '').toLowerCase().includes(q) ||
        (r.car?.plate_number || '').toLowerCase().includes(q)
      )
    })
  }

  const key = logsSortKey.value
  const dir = logsSortDir.value === 'asc' ? 1 : -1
  const resolve = (obj, k) => (k && k.includes('.') ? k.split('.').reduce((o, x) => (o ? o[x] : undefined), obj) : obj?.[k])

  list.sort((a, b) => {
    const va = resolve(a, key)
    const vb = resolve(b, key)
    if (va == null && vb == null) return 0
    if (va == null) return -1 * dir
    if (vb == null) return 1 * dir
    if (typeof va === 'number' && typeof vb === 'number') return (va - vb) * dir
    return String(va).localeCompare(String(vb)) * dir
  })

  return list
})

const form = ref({
  name: '',
  email: '',
  password: '',
  role: '',
})

const carForm = ref({
  brand: '',
  model: '',
  plate_number: '',
  transmission_type: '',
  image_url: '',
  status: 'available',
})

const transmissionTypes = ['Automātiskā', 'Manuālā']
const carStatuses = [
  { title: 'Pieejama', value: 'available' },
  { title: 'Apkalpošanā', value: 'maintenance' },
]

const exportPeriods = [
  { title: 'Visi', value: 'all' },
  { title: 'Šomēnes', value: 'month' },
  { title: 'Pēdējie 3 mēneši', value: '3m' },
  { title: 'Pēdējie 6 mēneši', value: '6m' },
  { title: 'Pēdējais gads', value: '12m' },
]

const carOptions = computed(() => {
  return cars.value.map((car) => ({
    title: `${car.brand} ${car.model} (${car.plate_number})`,
    value: car.id,
  }))
})

const filteredSortedCars = computed(() => {
  let list = cars.value.slice()

  const q = carsSearchQuery.value.trim().toLowerCase()
  if (q) {
    list = list.filter((c) => {
      return (
        String(c.id).includes(q) ||
        (c.brand || '').toLowerCase().includes(q) ||
        (c.model || '').toLowerCase().includes(q) ||
        (c.plate_number || '').toLowerCase().includes(q) ||
        (c.transmission_type || '').toLowerCase().includes(q)
      )
    })
  }

  const key = carsSortKey.value
  const dir = carsSortDir.value === 'asc' ? 1 : -1

  list.sort((a, b) => {
    const va = a[key]
    const vb = b[key]
    if (va == null && vb == null) return 0
    if (va == null) return -1 * dir
    if (vb == null) return 1 * dir
    if (typeof va === 'number' && typeof vb === 'number') return (va - vb) * dir
    return String(va).localeCompare(String(vb)) * dir
  })

  return list
})

const allowedRoles = computed(() => {
  if (authStore.role === 'vadiba') {
    return ['admin', 'user']
  }

  if (authStore.role === 'admin') {
    return ['user']
  }

  return []
})

const filteredSortedUsers = computed(() => {
  let list = users.value.slice()

  const q = usersSearchQuery.value.trim().toLowerCase()
  if (q) {
    list = list.filter((u) => {
      return (
        String(u.id).includes(q) ||
        (u.name || '').toLowerCase().includes(q) ||
        (u.email || '').toLowerCase().includes(q) ||
        (u.role || '').toLowerCase().includes(q)
      )
    })
  }

  const key = usersSortKey.value
  const dir = usersSortDir.value === 'asc' ? 1 : -1

  list.sort((a, b) => {
    const va = a[key]
    const vb = b[key]

    if (va == null && vb == null) return 0
    if (va == null) return -1 * dir
    if (vb == null) return 1 * dir

    if (typeof va === 'number' && typeof vb === 'number') {
      return (va - vb) * dir
    }

    return String(va).localeCompare(String(vb)) * dir
  })

  return list
})

const loadUsers = async () => {
  loadingUsers.value = true
  userErrorMessage.value = ''

  try {
    const { data } = await api.get('/api/admin/users')
    users.value = data.users
  } catch (error) {
    userErrorMessage.value = error.response?.data?.message || 'Neizdevās ielādēt lietotājus.'
  } finally {
    loadingUsers.value = false
  }
}

const createUser = async () => {
  savingUser.value = true
  userErrorMessage.value = ''
  userSuccessMessage.value = ''

  try {
    await api.post('/api/admin/users', form.value)
    userSuccessMessage.value = 'Lietotājs veiksmīgi izveidots.'
    form.value = { name: '', email: '', password: '', role: '' }
    await loadUsers()
  } catch (error) {
    userErrorMessage.value = error.response?.data?.message || 'Neizdevās izveidot lietotāju.'
  } finally {
    savingUser.value = false
  }
}

const canDelete = (targetRole) => {
  if (authStore.role === 'vadiba') {
    return ['admin', 'user'].includes(targetRole)
  }

  if (authStore.role === 'admin') {
    return targetRole === 'user'
  }

  return false
}

const deleteUser = async (user) => {
  if (!window.confirm('Vai tiešām dzēst šo lietotāju?')) {
    return
  }

  deletingId.value = user.id
  userErrorMessage.value = ''
  userSuccessMessage.value = ''

  try {
    await api.delete(`/api/admin/users/${user.id}`)
    userSuccessMessage.value = 'Lietotājs veiksmīgi dzēsts.'
    await loadUsers()
  } catch (error) {
    userErrorMessage.value = error.response?.data?.message || 'Neizdevās dzēst lietotāju.'
  } finally {
    deletingId.value = null
  }
}

const fillEditUserForm = (user) => {
  editingUserId.value = user.id
  editingUser.value = {
    name: user.name || '',
    email: user.email || '',
    role: user.role || '',
    password: '',
  }
  editingUserIsVadiba.value = user.role === 'vadiba'
  editingUserOriginalRole.value = user.role || ''
}

const resetEditUserForm = () => {
  editingUserId.value = null
  editingUser.value = { name: '', email: '', role: '', password: '' }
}

const saveEditedUser = async () => {
  if (!editingUserId.value) return
  savingEditedUser.value = true
  userErrorMessage.value = ''
  userSuccessMessage.value = ''

  try {
    // determine final role to send
    const finalRole = (() => {
      // never allow changing vadiba
      if (editingUserIsVadiba.value) return 'vadiba'
      // if current user is admin and original role was admin or vadiba, preserve original
      if (authStore.role === 'admin' && (editingUserOriginalRole.value === 'admin' || editingUserOriginalRole.value === 'vadiba')) {
        return editingUserOriginalRole.value
      }
      return editingUser.value.role
    })()

    const payload = {
      name: editingUser.value.name,
      email: editingUser.value.email,
      role: finalRole,
    }

    if (editingUser.value.password) payload.password = editingUser.value.password

    await api.put(`/api/admin/users/${editingUserId.value}`, payload)
    userSuccessMessage.value = 'Lietotāja dati veiksmīgi atjaunināti.'
    resetEditUserForm()
    await loadUsers()
  } catch (error) {
    userErrorMessage.value = error.response?.data?.message || 'Neizdevās saglabāt lietotāju.'
  } finally {
    savingEditedUser.value = false
  }
}

const editingRoleEditable = computed(() => {
  if (editingUserIsVadiba.value) return false
  if (authStore.role === 'admin' && (editingUserOriginalRole.value === 'admin' || editingUserOriginalRole.value === 'vadiba')) return false
  return true
})

const loadCars = async () => {
  loadingCars.value = true
  carErrorMessage.value = ''

  try {
    const { data } = await api.get('/api/admin/cars')
    cars.value = data.cars
  } catch (error) {
    carErrorMessage.value = error.response?.data?.message || 'Neizdevās ielādēt automašīnas.'
  } finally {
    loadingCars.value = false
  }
}

const loadReservationLogs = async () => {
  loadingLogs.value = true
  logsErrorMessage.value = ''

  try {
    const { data } = await api.get('/api/admin/reservations')
    reservationLogs.value = data.reservations
  } catch (error) {
    logsErrorMessage.value = error.response?.data?.message || 'Neizdevās ielādēt braucienu žurnālu.'
  } finally {
    loadingLogs.value = false
  }
}

const loadMaintenanceLogs = async () => {
  loadingMaintenance.value = true
  maintenanceErrorMessage.value = ''

  try {
    const { data } = await api.get('/api/admin/maintenance')
    maintenanceLogs.value = data.logs
  } catch (error) {
    maintenanceErrorMessage.value = error.response?.data?.message || 'Neizdevās ielādēt apkopes žurnālu.'
  } finally {
    loadingMaintenance.value = false
  }
}

const resetCarForm = () => {
  editingCarId.value = null
  carForm.value = {
    brand: '',
    model: '',
    plate_number: '',
    transmission_type: '',
    image_url: '',
    status: 'available',
  }
}

const fillCarFormForEdit = (car) => {
  editingCarId.value = car.id
  carForm.value = {
    brand: car.brand,
    model: car.model,
    plate_number: car.plate_number,
    transmission_type: car.transmission_type,
    image_url: car.image_url || '',
    status: car.status || 'available',
  }
}

const saveCar = async () => {
  savingCar.value = true
  carErrorMessage.value = ''
  carSuccessMessage.value = ''

  try {
    const payload = {
      ...carForm.value,
      image_url: carForm.value.image_url || null,
    }

    if (editingCarId.value) {
      await api.put(`/api/admin/cars/${editingCarId.value}`, payload)
      carSuccessMessage.value = 'Automašīna veiksmīgi atjaunota.'
    } else {
      await api.post('/api/admin/cars', payload)
      carSuccessMessage.value = 'Automašīna veiksmīgi izveidota.'
    }

    resetCarForm()
    await loadCars()
  } catch (error) {
    carErrorMessage.value = error.response?.data?.message || 'Neizdevās saglabāt automašīnu.'
  } finally {
    savingCar.value = false
  }
}

const deleteCar = async (car) => {
  if (!window.confirm('Vai tiešām dzēst šo automašīnu?')) {
    return
  }

  deletingCarId.value = car.id
  carErrorMessage.value = ''
  carSuccessMessage.value = ''

  try {
    await api.delete(`/api/admin/cars/${car.id}`)
    carSuccessMessage.value = 'Automašīna veiksmīgi dzēsta.'

    if (editingCarId.value === car.id) {
      resetCarForm()
    }

    await loadCars()
  } catch (error) {
    carErrorMessage.value = error.response?.data?.message || 'Neizdevās dzēst automašīnu.'
  } finally {
    deletingCarId.value = null
  }
}

const resetMaintenanceForm = () => {
  maintenanceForm.value = {
    car_id: '',
    maintenance_type: '',
    description: '',
    performed_at: '',
    mileage: '',
    cost: '',
  }
}

const saveMaintenance = async () => {
  savingMaintenance.value = true
  maintenanceErrorMessage.value = ''
  maintenanceSuccessMessage.value = ''

  try {
    const payload = {
      ...maintenanceForm.value,
      mileage: maintenanceForm.value.mileage === '' ? null : maintenanceForm.value.mileage,
      cost: maintenanceForm.value.cost === '' ? null : maintenanceForm.value.cost,
    }

    await api.post('/api/admin/maintenance', payload)
    maintenanceSuccessMessage.value = 'Apkopes ieraksts veiksmīgi pievienots.'
    resetMaintenanceForm()
    await loadMaintenanceLogs()
  } catch (error) {
    maintenanceErrorMessage.value = error.response?.data?.message || 'Neizdevās saglabāt apkopes ierakstu.'
  } finally {
    savingMaintenance.value = false
  }
}

const deleteMaintenanceLog = async (log) => {
  if (!window.confirm('Vai tiešām dzēst šo apkopes ierakstu?')) {
    return
  }

  deletingMaintenanceId.value = log.id
  maintenanceErrorMessage.value = ''
  maintenanceSuccessMessage.value = ''

  try {
    await api.delete(`/api/admin/maintenance/${log.id}`)
    maintenanceSuccessMessage.value = 'Apkopes ieraksts dzēsts.'
    await loadMaintenanceLogs()
  } catch (error) {
    maintenanceErrorMessage.value = error.response?.data?.message || 'Neizdevās dzēst apkopes ierakstu.'
  } finally {
    deletingMaintenanceId.value = null
  }
}

const exportHtmlTable = (title, headers, rows, summaryLines = []) => {
  const w = window.open('', '_blank')
  const style = `
    body{font-family: Arial, Helvetica, sans-serif; padding:20px}
    h1{font-size:18px}
    table{width:100%;border-collapse:collapse;margin-top:10px}
    th,td{border:1px solid #ddd;padding:8px;text-align:left}
    .summary{margin-top:10px;font-weight:600}
  `

  const thead = `<tr>${headers.map((h) => `<th>${h}</th>`).join('')}</tr>`
  const tbody = rows
    .map((r) => `<tr>${r.map((c) => `<td>${c ?? ''}</td>`).join('')}</tr>`)
    .join('')

  const summaryHtml = summaryLines && summaryLines.length ? `<div class="summary">${summaryLines.map((s) => `<div>${s}</div>`).join('')}</div>` : ''

  w.document.write(`<!doctype html><html><head><title>${title}</title><style>${style}</style></head><body><h1>${title}</h1>${summaryHtml}<table><thead>${thead}</thead><tbody>${tbody}</tbody></table></body></html>`)
  w.document.close()
  w.focus()
  setTimeout(() => {
    w.print()
  }, 500)
}

const getCutoffDate = (period) => {
  if (!period || period === 'all') return null
  const now = new Date()
  if (period === 'month') return new Date(now.getFullYear(), now.getMonth(), 1)
  if (period === '3m') {
    const d = new Date(now)
    d.setMonth(d.getMonth() - 3)
    return d
  }
  if (period === '6m') {
    const d = new Date(now)
    d.setMonth(d.getMonth() - 6)
    return d
  }
  if (period === '12m') {
    const d = new Date(now)
    d.setFullYear(d.getFullYear() - 1)
    return d
  }
  return null
}

const filterByPeriod = (list, dateKey, period) => {
  const cutoff = getCutoffDate(period)
  if (!cutoff) return list
  const resolve = (obj, k) => (k && k.includes('.') ? k.split('.').reduce((o, x) => (o ? o[x] : undefined), obj) : obj?.[k])
  return list.filter((item) => {
    const v = resolve(item, dateKey)
    if (!v) return false
    const dt = new Date(v)
    return dt >= cutoff
  })
}

const exportLogsPdf = () => {
  const base = filteredSortedReservationLogs.value
  const filtered = filterByPeriod(base, 'started_at', logsExportPeriod.value)
  const rows = filtered.map((r) => [
    r.id,
    `${r.car?.brand || ''} ${r.car?.model || ''} (${r.car?.plate_number || ''})`,
    r.user?.name || '',
    r.status_label || r.status || '',
    r.started_at ? new Date(r.started_at).toLocaleString('lv-LV') : '-',
    r.ended_at ? new Date(r.ended_at).toLocaleString('lv-LV') : '-',
  ])

  const totalReservations = filtered.length
  const summary = [`Kopā rezervācijas: ${totalReservations}`]

  exportHtmlTable('Žurnāla pārskats', ['ID', 'Automašīna', 'Lietotājs', 'Statuss', 'Sākts', 'Pabeigts'], rows, summary)
}

const exportMaintenancePdf = () => {
  const baseM = filteredSortedMaintenance.value
  const filteredM = filterByPeriod(baseM, 'performed_at', maintenanceExportPeriod.value)
  const rows = filteredM.map((m) => [
    m.id,
    `${m.car?.brand || ''} ${m.car?.model || ''} (${m.car?.plate_number || ''})`,
    m.maintenance_type || '',
    m.description || '',
    m.performed_at ? new Date(m.performed_at).toLocaleString('lv-LV') : '-',
    m.mileage ?? '-',
    m.cost ?? '-',
  ])

  // compute total cost (sum numeric costs)
  const total = filteredM.reduce((acc, it) => {
    const v = parseFloat(it.cost)
    return acc + (isNaN(v) ? 0 : v)
  }, 0)

  const summary = [`Kopējā summa: ${total.toFixed(2)} €`]

  exportHtmlTable('Apkopes pārskats', ['ID', 'Automašīna', 'Veids', 'Apraksts', 'Datums', 'Nobraukums', 'Cena'], rows, summary)
}

onMounted(async () => {
  await Promise.all([loadUsers(), loadCars(), loadReservationLogs(), loadMaintenanceLogs()])
})
</script>

<template>
  <v-card elevation="4" class="pa-4 admin-shell">
    <div class="d-flex flex-column flex-md-row align-md-center justify-space-between ga-4 mb-4">
      <div>
        <v-card-title class="px-0 pb-1">Administrācijas panelis</v-card-title>
      </div>

      <v-chip color="primary" variant="flat" label>
        {{ roleLabel }}
      </v-chip>
    </div>

    <v-tabs v-model="activeTab" color="primary" class="mb-4" align-tabs="start">
      <v-tab value="users">Lietotāji</v-tab>
      <v-tab value="cars">Automašīnas</v-tab>
      <v-tab value="maintenance">Apkope</v-tab>
      <v-tab value="logs">Žurnāli</v-tab>
    </v-tabs>

    <div class="admin-tab-panels">
      <section v-show="activeTab === 'users'">
        <v-row>
          <v-col cols="12" md="4">
            <v-card elevation="2" class="pa-4 h-100">
              <v-card-title class="px-0">Jauna lietotāja izveide</v-card-title>
              <v-card-subtitle class="px-0 pb-4">
                <span v-if="authStore.role === 'admin'">Jūs varat izveidot tikai user kontus.</span>
              </v-card-subtitle>

              <v-form @submit.prevent="createUser">
                <v-text-field v-model="form.name" label="Vārds" variant="outlined" density="comfortable" required class="mb-2" />
                <v-text-field v-model="form.email" label="E-pasts" type="email" variant="outlined" density="comfortable" required class="mb-2" />
                <v-text-field v-model="form.password" label="Parole" type="password" variant="outlined" density="comfortable" required class="mb-2" />

                <v-select
                  v-model="form.role"
                  :items="allowedRoles"
                  label="Loma"
                  variant="outlined"
                  density="comfortable"
                  required
                  class="mb-4"
                />

                <v-btn type="submit" color="primary" block :loading="savingUser" :disabled="savingUser || !allowedRoles.length">
                  Izveidot lietotāju
                </v-btn>
              </v-form>
            </v-card>
          </v-col>

          <v-col cols="12" md="8">
            <v-card elevation="2" class="pa-2 pa-md-4 h-100">
              <v-card-title>Visi lietotāji</v-card-title>

              <v-row class="mb-3" align="center">
                <v-col cols="12" md="6">
                  <v-text-field
                    v-model="usersSearchQuery"
                    label="Meklēt pēc ID, vārda vai e-pasta"
                    clearable
                  />
                </v-col>

                <v-col cols="12" md="4">
                  <v-select
                    v-model="usersSortKey"
                    :items="[{title:'ID', value:'id'},{title:'Vārds', value:'name'},{title:'E-pasts', value:'email'},{title:'Loma', value:'role'},{title:'Izveidots', value:'created_at'}]"
                    item-title="title"
                    item-value="value"
                    label="Kārtot pēc"
                    dense
                  />
                </v-col>

                <v-col cols="12" md="2" class="d-flex align-center">
                  <v-btn class="sort-dir-btn" icon @click="usersSortDir = usersSortDir === 'asc' ? 'desc' : 'asc'" :title="usersSortDir === 'asc' ? 'Asc' : 'Desc'">
                    <v-icon>{{ usersSortDir === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}</v-icon>
                  </v-btn>
                </v-col>
              </v-row>

              <v-alert v-if="userErrorMessage" type="error" variant="tonal" class="mx-4 my-2">{{ userErrorMessage }}</v-alert>
              <v-alert v-if="userSuccessMessage" type="success" variant="tonal" class="mx-4 my-2">{{ userSuccessMessage }}</v-alert>

              <v-progress-linear v-if="loadingUsers" indeterminate color="primary" class="mb-2" />

              <div class="table-scroll">
                <v-table>
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Vārds</th>
                      <th>E-pasts</th>
                      <th>Loma</th>
                      <th>Izveidoja</th>
                      <th>Izveidots</th>
                      <th>Darbības</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="user in filteredSortedUsers" :key="user.id">
                      <td>{{ user.id }}</td>
                      <td>{{ user.name }}</td>
                      <td>{{ user.email }}</td>
                      <td>{{ user.role }}</td>
                      <td>{{ user.created_by_name || '-' }}</td>
                      <td>{{ user.created_at ? new Date(user.created_at).toLocaleString('lv-LV') : '-' }}</td>
                      <td>
                        <div class="d-flex ga-2 table-actions">
                          <v-btn
                            v-if="!(authStore.role === 'admin' && (user.role === 'admin' || user.role === 'vadiba'))"
                            size="small"
                            color="primary"
                            variant="flat"
                            @click="fillEditUserForm(user)"
                          >
                            Rediģēt
                          </v-btn>

                        <v-btn
                          v-if="canDelete(user.role)"
                          color="error"
                          size="small"
                          variant="flat"
                          :loading="deletingId === user.id"
                          :disabled="deletingId === user.id"
                          @click="deleteUser(user)"
                        >
                          Dzēst
                        </v-btn>
                        <span v-else class="text-medium-emphasis">Nav atļauts</span>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </v-table>
              </div>
            </v-card>
          </v-col>
        </v-row>
      </section>

      <!-- Edit user dialog -->
      <v-dialog v-model="editingUserId" persistent max-width="560">
        <v-card>
          <v-card-title>Rediģēt lietotāju</v-card-title>
          <v-card-text>
            <v-form @submit.prevent="saveEditedUser">
              <v-text-field v-model="editingUser.name" label="Vārds" required class="mb-2" />
              <v-text-field v-model="editingUser.email" label="E-pasts" type="email" required class="mb-2" />

              <v-select
                v-model="editingUser.role"
                :items="allowedRoles"
                label="Loma"
                required
                class="mb-2"
                :disabled="!editingRoleEditable"
              />
              <div v-if="!editingRoleEditable && editingUserIsVadiba" class="text-caption text-medium-emphasis mb-2">Lomas rediģēšana nav atļauta vadībai.</div>
              <div v-else-if="!editingRoleEditable && authStore.role === 'admin'" class="text-caption text-medium-emphasis mb-2">Kā admin, jūs nevarat mainīt lietotājus ar lomām admin vai vadiba.</div>

              <v-text-field v-model="editingUser.password" label="Parole (atstāt tukšu, ja nemainīt)" type="password" class="mb-4" />

              <div class="d-flex justify-end ga-2">
                <v-btn color="secondary" variant="tonal" @click="resetEditUserForm">Atcelt</v-btn>
                <v-btn color="primary" :loading="savingEditedUser" type="submit">Saglabāt</v-btn>
              </div>
            </v-form>
          </v-card-text>
        </v-card>
      </v-dialog>

      <section v-show="activeTab === 'cars'">
        <v-row>
          <v-col cols="12" md="4">
            <v-card elevation="2" class="pa-4 h-100">
              <v-card-title class="px-0">{{ editingCarId ? 'Automašīnas rediģēšana' : 'Jauna automašīna' }}</v-card-title>
              

              <v-form @submit.prevent="saveCar">
                <v-text-field v-model="carForm.brand" label="Zīmols" variant="outlined" density="comfortable" required class="mb-2" />
                <v-text-field v-model="carForm.model" label="Modelis" variant="outlined" density="comfortable" required class="mb-2" />
                <v-text-field v-model="carForm.plate_number" label="Numurzīme" variant="outlined" density="comfortable" required class="mb-2" />

                <v-select
                  v-model="carForm.transmission_type"
                  :items="transmissionTypes"
                  label="Ātrumkārba"
                  variant="outlined"
                  density="comfortable"
                  required
                  class="mb-2"
                />

                <v-text-field
                  v-model="carForm.image_url"
                  label="Attēla URL"
                  variant="outlined"
                  density="comfortable"
                  placeholder="https://..."
                  class="mb-4"
                />

                <v-select
                  v-if="editingCarId"
                  v-model="carForm.status"
                  :items="carStatuses"
                  item-title="title"
                  item-value="value"
                  label="Statuss"
                  variant="outlined"
                  density="comfortable"
                  required
                  class="mb-4"
                />

                <div class="d-flex ga-2 car-form-actions">
                  <v-btn
                    type="submit"
                    color="primary"
                    :loading="savingCar"
                    :disabled="savingCar"
                    class="car-form-action-btn"
                  >
                    {{ editingCarId ? 'Saglabāt izmaiņas' : 'Pievienot automašīnu' }}
                  </v-btn>
                  <v-btn
                    v-if="editingCarId"
                    color="secondary"
                    variant="tonal"
                    class="car-form-action-btn"
                    @click="resetCarForm"
                  >
                    Atcelt
                  </v-btn>
                </div>
              </v-form>
            </v-card>
          </v-col>

          <v-col cols="12" md="8">
            <v-card elevation="2" class="pa-2 pa-md-4 h-100">
              <v-card-title>Visas automašīnas</v-card-title>

              <v-row class="mb-3" align="center">
                <v-col cols="12" md="6">
                  <v-text-field v-model="carsSearchQuery" label="Meklēt pēc ID, zīmola, modeļa vai numura" clearable />
                </v-col>

                <v-col cols="12" md="4">
                  <v-select
                    v-model="carsSortKey"
                    :items="[
                      { title: 'ID', value: 'id' },
                      { title: 'Zīmols', value: 'brand' },
                      { title: 'Numurzīme', value: 'plate_number' },
                      { title: 'Ātrumkārba', value: 'transmission_type' },
                      { title: 'Statuss', value: 'status' },
                    ]"
                    item-title="title"
                    item-value="value"
                    label="Kārtot pēc"
                    dense
                  />
                </v-col>

                <v-col cols="12" md="2" class="d-flex align-center">
                  <v-btn class="sort-dir-btn" icon @click="carsSortDir = carsSortDir === 'asc' ? 'desc' : 'asc'" :title="carsSortDir === 'asc' ? 'Asc' : 'Desc'">
                    <v-icon>{{ carsSortDir === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}</v-icon>
                  </v-btn>
                </v-col>
              </v-row>

              <v-alert v-if="carErrorMessage" type="error" variant="tonal" class="mx-4 my-2">{{ carErrorMessage }}</v-alert>
              <v-alert v-if="carSuccessMessage" type="success" variant="tonal" class="mx-4 my-2">{{ carSuccessMessage }}</v-alert>

              <v-progress-linear v-if="loadingCars" indeterminate color="primary" class="mb-2" />

              <div class="table-scroll">
                <v-table>
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Automašīna</th>
                      <th>Numurzīme</th>
                      <th>Ātrumkārba</th>
                      <th>Statuss</th>
                      <th>Darbības</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="car in filteredSortedCars" :key="car.id">
                      <td>{{ car.id }}</td>
                      <td>
                        <div class="font-weight-medium">{{ car.brand }} {{ car.model }}</div>
                        <div class="text-caption text-medium-emphasis">{{ car.image_url || 'Nav attēla URL' }}</div>
                      </td>
                      <td>{{ car.plate_number }}</td>
                      <td>{{ car.transmission_type }}</td>
                      <td>
                        <v-chip size="small" :color="car.status === 'maintenance' ? 'warning' : car.is_reserved ? 'error' : 'success'" variant="tonal">
                          {{ car.status === 'maintenance' ? 'Apkalpošanā' : car.is_reserved ? `Rezervēta (${car.reserved_by || 'nezināms'})` : 'Brīva' }}
                        </v-chip>
                      </td>
                      <td>
                        <div class="d-flex ga-2 table-actions">
                          <v-btn size="small" color="primary" variant="flat" @click="fillCarFormForEdit(car)">
                            Rediģēt
                          </v-btn>
                          <v-btn
                            size="small"
                            color="error"
                            variant="flat"
                            :loading="deletingCarId === car.id"
                            :disabled="deletingCarId === car.id"
                            @click="deleteCar(car)"
                          >
                            Dzēst
                          </v-btn>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </v-table>
              </div>
            </v-card>
          </v-col>
        </v-row>
      </section>

      <section v-show="activeTab === 'logs'">
        <v-card elevation="2" class="pa-2 pa-md-4 h-100">
          <v-card-title>Žurnāls par braucieniem</v-card-title>

          <v-row class="mb-3" align="center">
            <v-col cols="12" md="6">
              <v-text-field v-model="logsSearchQuery" label="Meklēt pēc ID, auto, lietotāja vai numura" clearable />
            </v-col>

            <v-col cols="12" md="4">
              <v-select
                v-model="logsSortKey"
                :items="[
                  { title: 'ID', value: 'id' },
                  { title: 'Automašīna', value: 'car.brand' },
                  { title: 'Lietotājs', value: 'user.name' },
                  { title: 'Statuss', value: 'status' },
                  { title: 'Sākts', value: 'started_at' },
                  { title: 'Pabeigts', value: 'ended_at' },
                ]"
                item-title="title"
                item-value="value"
                label="Kārtot pēc"
                dense
              />
            </v-col>

            <v-col cols="12" md="2" class="d-flex align-center">
              <v-btn class="sort-dir-btn" icon @click="logsSortDir = logsSortDir === 'asc' ? 'desc' : 'asc'" :title="logsSortDir === 'asc' ? 'Asc' : 'Desc'">
                <v-icon>{{ logsSortDir === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}</v-icon>
              </v-btn>
            </v-col>

            <v-col cols="12" md="3" class="d-flex flex-column align-start">
              <v-select v-model="logsExportPeriod" :items="exportPeriods" item-title="title" item-value="value" label="Periods" dense hide-details style="width:100%; max-width:240px" />
              <v-btn small class="mt-2" color="secondary" variant="tonal" @click="exportLogsPdf">Eksportēt PDF</v-btn>
            </v-col>
          </v-row>

          <v-alert v-if="logsErrorMessage" type="error" variant="tonal" class="mx-4 my-2">{{ logsErrorMessage }}</v-alert>

          <v-progress-linear v-if="loadingLogs" indeterminate color="primary" class="mb-2" />

          <div class="table-scroll">
            <v-table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Automašīna</th>
                  <th>Lietotājs</th>
                  <th>Statuss</th>
                  <th>Sākts</th>
                  <th>Pabeigts</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="log in filteredSortedReservationLogs" :key="log.id">
                  <td>{{ log.id }}</td>
                  <td>
                    <div class="font-weight-medium">{{ log.car.brand }} {{ log.car.model }}</div>
                    <div class="text-caption text-medium-emphasis">{{ log.car.plate_number }}</div>
                  </td>
                  <td>
                    <div class="font-weight-medium">{{ log.user.name }}</div>
                    <div class="text-caption text-medium-emphasis">{{ log.user.role }}</div>
                  </td>
                  <td>
                    <v-chip size="small" :color="log.status === 'active' ? 'info' : 'success'" variant="tonal">
                      {{ log.status_label }}
                    </v-chip>
                  </td>
                  <td>{{ log.started_at ? new Date(log.started_at).toLocaleString('lv-LV') : '-' }}</td>
                  <td>{{ log.ended_at ? new Date(log.ended_at).toLocaleString('lv-LV') : '-' }}</td>
                </tr>
              </tbody>
            </v-table>
          </div>

          <v-card-text v-if="!loadingLogs && !reservationLogs.length" class="text-medium-emphasis">
            Žurnālā pagaidām nav ierakstu.
          </v-card-text>
        </v-card>
      </section>

      <section v-show="activeTab === 'maintenance'">
        <v-row>
          <v-col cols="12" md="4">
            <v-card elevation="2" class="pa-4 h-100">
              <v-card-title class="px-0">Jauns apkopes ieraksts</v-card-title>
              

              <v-form @submit.prevent="saveMaintenance">
                <v-select
                  v-model="maintenanceForm.car_id"
                  :items="carOptions"
                  item-title="title"
                  item-value="value"
                  label="Automašīna"
                  variant="outlined"
                  density="comfortable"
                  required
                  class="mb-2"
                />

                <v-select
                  v-model="maintenanceForm.maintenance_type"
                  :items="maintenanceTypes"
                  label="Apkopes veids"
                  variant="outlined"
                  density="comfortable"
                  required
                  class="mb-2"
                />

                <v-textarea
                  v-model="maintenanceForm.description"
                  label="Apraksts"
                  variant="outlined"
                  density="comfortable"
                  rows="3"
                  auto-grow
                  required
                  class="mb-2"
                />

                <v-text-field
                  v-model="maintenanceForm.performed_at"
                  label="Datums"
                  type="datetime-local"
                  variant="outlined"
                  density="comfortable"
                  required
                  class="mb-2"
                  append-inner-icon="mdi-calendar"
                  ref="performedAtField"
                  @click:append-inner="openPerformedAtPicker"
                />

                <v-text-field
                  v-model="maintenanceForm.mileage"
                  label="Nobraukums"
                  type="text"
                  inputmode="numeric"
                  variant="outlined"
                  density="comfortable"
                  class="mb-2"
                />

                <v-text-field
                  v-model="maintenanceForm.cost"
                  label="Cena"
                  type="text"
                  inputmode="decimal"
                  variant="outlined"
                  density="comfortable"
                  class="mb-4"
                />

                <v-btn type="submit" color="primary" block :loading="savingMaintenance" :disabled="savingMaintenance || !carOptions.length">
                  Pievienot ierakstu
                </v-btn>
              </v-form>
            </v-card>
          </v-col>

          <v-col cols="12" md="8">
            <v-card elevation="2" class="pa-2 pa-md-4 h-100">
              <v-card-title>Apkopes ieraksti</v-card-title>

              <v-row class="mb-3" align="center">
                <v-col cols="12" md="6">
                  <v-text-field v-model="maintenanceSearchQuery" label="Meklēt pēc ID, auto, veida vai lietotāja" clearable />
                </v-col>

                <v-col cols="12" md="4">
                  <v-select
                    v-model="maintenanceSortKey"
                    :items="[
                      { title: 'ID', value: 'id' },
                      { title: 'Automašīna', value: 'car.brand' },
                      { title: 'Veids', value: 'maintenance_type' },
                      { title: 'Datums', value: 'performed_at' },
                      { title: 'Nobraukums', value: 'mileage' },
                    ]"
                    item-title="title"
                    item-value="value"
                    label="Kārtot pēc"
                    dense
                  />
                </v-col>

                <v-col cols="12" md="2" class="d-flex align-center">
                  <v-btn class="sort-dir-btn" icon @click="maintenanceSortDir = maintenanceSortDir === 'asc' ? 'desc' : 'asc'" :title="maintenanceSortDir === 'asc' ? 'Asc' : 'Desc'">
                    <v-icon>{{ maintenanceSortDir === 'asc' ? 'mdi-arrow-up' : 'mdi-arrow-down' }}</v-icon>
                  </v-btn>
                </v-col>

                <v-col cols="12" md="3" class="d-flex flex-column align-start">
                  <v-select v-model="maintenanceExportPeriod" :items="exportPeriods" item-title="title" item-value="value" label="Periods" dense hide-details style="width:100%; max-width:240px" />
                  <v-btn small class="mt-2" color="secondary" variant="tonal" @click="exportMaintenancePdf">Eksportēt PDF</v-btn>
                </v-col>
              </v-row>

              <v-alert v-if="maintenanceErrorMessage" type="error" variant="tonal" class="mx-4 my-2">{{ maintenanceErrorMessage }}</v-alert>
              <v-alert v-if="maintenanceSuccessMessage" type="success" variant="tonal" class="mx-4 my-2">{{ maintenanceSuccessMessage }}</v-alert>

              <v-progress-linear v-if="loadingMaintenance" indeterminate color="primary" class="mb-2" />

              <div class="table-scroll">
                <v-table>
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Automašīna</th>
                      <th>Veids</th>
                      <th>Apraksts</th>
                      <th>Datums</th>
                      <th>Nobraukums</th>
                      <th>Cena</th>
                      <th>Darbības</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="log in filteredSortedMaintenance" :key="log.id">
                      <td>{{ log.id }}</td>
                      <td>
                        <div class="font-weight-medium">{{ log.car.brand }} {{ log.car.model }}</div>
                        <div class="text-caption text-medium-emphasis">{{ log.car.plate_number }}</div>
                      </td>
                      <td>{{ log.maintenance_type }}</td>
                      <td>
                        <div>{{ log.description }}</div>
                        <div class="text-caption text-medium-emphasis">{{ log.user.name }}</div>
                      </td>
                      <td>{{ log.performed_at ? new Date(log.performed_at).toLocaleString('lv-LV') : '-' }}</td>
                      <td>{{ log.mileage ?? '-' }}</td>
                      <td>{{ log.cost ?? '-' }}</td>
                      <td>
                        <v-btn
                          color="error"
                          size="small"
                          variant="flat"
                          :loading="deletingMaintenanceId === log.id"
                          :disabled="deletingMaintenanceId === log.id"
                          @click="deleteMaintenanceLog(log)"
                        >
                          Dzēst
                        </v-btn>
                      </td>
                    </tr>
                  </tbody>
                </v-table>
              </div>

              <v-card-text v-if="!loadingMaintenance && !maintenanceLogs.length" class="text-medium-emphasis">
                Apkopes ierakstu pagaidām nav.
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </section>
    </div>
  </v-card>
</template>

<style scoped>
.admin-shell {
  overflow: hidden;
}

.car-form-actions {
  width: 100%;
}

.car-form-action-btn {
  flex: 1 1 auto;
  min-width: 0;
}

.table-scroll {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch;
}

.table-actions {
  flex-wrap: wrap;
}

.sort-dir-btn {
  margin-top: -23px;
}

@media (max-width: 600px) {
  .car-form-actions {
    flex-direction: column;
  }

  .table-scroll :deep(table) {
    min-width: 760px;
  }

  .sort-dir-btn {
    margin-top: 0;
  }
}

/* Hide native browser date/datetime picker icon so only mdi-calendar remains */
::v-deep input[type="date"]::-webkit-calendar-picker-indicator,
::v-deep input[type="datetime-local"]::-webkit-calendar-picker-indicator {
  display: none !important;
}
::v-deep input[type="date"],
::v-deep input[type="datetime-local"] {
  -webkit-appearance: none;
  appearance: none;
}
</style>
