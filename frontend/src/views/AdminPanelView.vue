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

const cars = ref([])
const loadingCars = ref(false)
const savingCar = ref(false)
const deletingCarId = ref(null)
const carErrorMessage = ref('')
const carSuccessMessage = ref('')
const editingCarId = ref(null)

const reservationLogs = ref([])
const loadingLogs = ref(false)
const logsErrorMessage = ref('')

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
})

const transmissionTypes = ['Automātiskā', 'Manuālā']

const allowedRoles = computed(() => {
  if (authStore.role === 'vadiba') {
    return ['admin', 'user']
  }

  if (authStore.role === 'admin') {
    return ['user']
  }

  return []
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

const resetCarForm = () => {
  editingCarId.value = null
  carForm.value = {
    brand: '',
    model: '',
    plate_number: '',
    transmission_type: '',
    image_url: '',
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

onMounted(async () => {
  await Promise.all([loadUsers(), loadCars(), loadReservationLogs()])
})
</script>

<template>
  <v-card elevation="4" class="pa-4 admin-shell">
    <div class="d-flex flex-column flex-md-row align-md-center justify-space-between ga-4 mb-4">
      <div>
        <v-card-title class="px-0 pb-1">Administrācijas panelis</v-card-title>
        <v-card-subtitle class="px-0">
          Lietotāju un citu administratīvo sadaļu pārvaldība vienuviet.
        </v-card-subtitle>
      </div>

      <v-chip color="primary" variant="flat" label>
        {{ roleLabel }}
      </v-chip>
    </div>

    <v-tabs v-model="activeTab" color="primary" class="mb-4" align-tabs="start">
      <v-tab value="users">Lietotāji</v-tab>
      <v-tab value="cars">Automašīnas</v-tab>
      <v-tab value="logs">Žurnāli</v-tab>
    </v-tabs>

    <v-window v-model="activeTab">
      <v-window-item value="users">
        <v-row>
          <v-col cols="12" md="4">
            <v-card elevation="2" class="pa-4 h-100">
              <v-card-title class="px-0">Jauna lietotāja izveide</v-card-title>
              <v-card-subtitle class="px-0 pb-4">
                <span v-if="authStore.role === 'vadiba'">Jūs varat izveidot admin un user kontus.</span>
                <span v-else-if="authStore.role === 'admin'">Jūs varat izveidot tikai user kontus.</span>
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

              <v-alert v-if="userErrorMessage" type="error" variant="tonal" class="mx-4 my-2">{{ userErrorMessage }}</v-alert>
              <v-alert v-if="userSuccessMessage" type="success" variant="tonal" class="mx-4 my-2">{{ userSuccessMessage }}</v-alert>

              <v-progress-linear v-if="loadingUsers" indeterminate color="primary" class="mb-2" />

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
                  <tr v-for="user in users" :key="user.id">
                    <td>{{ user.id }}</td>
                    <td>{{ user.name }}</td>
                    <td>{{ user.email }}</td>
                    <td>{{ user.role }}</td>
                    <td>{{ user.created_by_name || '-' }}</td>
                    <td>{{ user.created_at ? new Date(user.created_at).toLocaleString('lv-LV') : '-' }}</td>
                    <td>
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
                    </td>
                  </tr>
                </tbody>
              </v-table>
            </v-card>
          </v-col>
        </v-row>
      </v-window-item>

      <v-window-item value="cars">
        <v-row>
          <v-col cols="12" md="4">
            <v-card elevation="2" class="pa-4 h-100">
              <v-card-title class="px-0">{{ editingCarId ? 'Automašīnas rediģēšana' : 'Jauna automašīna' }}</v-card-title>
              <v-card-subtitle class="px-0 pb-4">
                Aizpildiet automašīnas datus, lai tie tiktu parādīti rezervāciju lapā.
              </v-card-subtitle>

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

              <v-alert v-if="carErrorMessage" type="error" variant="tonal" class="mx-4 my-2">{{ carErrorMessage }}</v-alert>
              <v-alert v-if="carSuccessMessage" type="success" variant="tonal" class="mx-4 my-2">{{ carSuccessMessage }}</v-alert>

              <v-progress-linear v-if="loadingCars" indeterminate color="primary" class="mb-2" />

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
                  <tr v-for="car in cars" :key="car.id">
                    <td>{{ car.id }}</td>
                    <td>
                      <div class="font-weight-medium">{{ car.brand }} {{ car.model }}</div>
                      <div class="text-caption text-medium-emphasis">{{ car.image_url || 'Nav attēla URL' }}</div>
                    </td>
                    <td>{{ car.plate_number }}</td>
                    <td>{{ car.transmission_type }}</td>
                    <td>
                      <v-chip size="small" :color="car.is_reserved ? 'error' : 'success'" variant="tonal">
                        {{ car.is_reserved ? `Rezervēta (${car.reserved_by || 'nezināms'})` : 'Brīva' }}
                      </v-chip>
                    </td>
                    <td>
                      <div class="d-flex ga-2">
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
            </v-card>
          </v-col>
        </v-row>
      </v-window-item>

      <v-window-item value="logs">
        <v-card elevation="2" class="pa-2 pa-md-4 h-100">
          <v-card-title>Žurnāls par braucieniem</v-card-title>
          <v-card-subtitle class="px-4 pb-2">
            Šeit redzami visi rezervācijas ieraksti: kas rezervēja, kuru auto, kad sāka un kāds ir statuss.
          </v-card-subtitle>

          <v-alert v-if="logsErrorMessage" type="error" variant="tonal" class="mx-4 my-2">{{ logsErrorMessage }}</v-alert>

          <v-progress-linear v-if="loadingLogs" indeterminate color="primary" class="mb-2" />

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
              <tr v-for="log in reservationLogs" :key="log.id">
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
                  <v-chip size="small" :color="log.status === 'active' ? 'info' : log.status === 'completed' ? 'success' : 'error'" variant="tonal">
                    {{ log.status_label }}
                  </v-chip>
                </td>
                <td>{{ log.started_at ? new Date(log.started_at).toLocaleString('lv-LV') : '-' }}</td>
                <td>{{ log.ended_at ? new Date(log.ended_at).toLocaleString('lv-LV') : '-' }}</td>
              </tr>
            </tbody>
          </v-table>

          <v-card-text v-if="!loadingLogs && !reservationLogs.length" class="text-medium-emphasis">
            Žurnālā pagaidām nav ierakstu.
          </v-card-text>
        </v-card>
      </v-window-item>
    </v-window>
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
  flex: 1 1 0;
  min-width: 0;
}

@media (max-width: 600px) {
  .car-form-actions {
    flex-direction: column;
  }
}
</style>
