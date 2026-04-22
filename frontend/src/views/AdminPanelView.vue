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
const loading = ref(false)
const saving = ref(false)
const deletingId = ref(null)
const errorMessage = ref('')
const successMessage = ref('')

const form = ref({
  name: '',
  email: '',
  password: '',
  role: '',
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

const loadUsers = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const { data } = await api.get('/api/admin/users')
    users.value = data.users
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Neizdevās ielādēt lietotājus.'
  } finally {
    loading.value = false
  }
}

const createUser = async () => {
  saving.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    await api.post('/api/admin/users', form.value)
    successMessage.value = 'Lietotājs veiksmīgi izveidots.'
    form.value = { name: '', email: '', password: '', role: '' }
    await loadUsers()
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Neizdevās izveidot lietotāju.'
  } finally {
    saving.value = false
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
  errorMessage.value = ''
  successMessage.value = ''

  try {
    await api.delete(`/api/admin/users/${user.id}`)
    successMessage.value = 'Lietotājs veiksmīgi dzēsts.'
    await loadUsers()
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Neizdevās dzēst lietotāju.'
  } finally {
    deletingId.value = null
  }
}

onMounted(loadUsers)
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

                <v-btn type="submit" color="primary" block :loading="saving" :disabled="saving || !allowedRoles.length">
                  Izveidot lietotāju
                </v-btn>
              </v-form>
            </v-card>
          </v-col>

          <v-col cols="12" md="8">
            <v-card elevation="2" class="pa-2 pa-md-4 h-100">
              <v-card-title>Visi lietotāji</v-card-title>

              <v-alert v-if="errorMessage" type="error" variant="tonal" class="mx-4 my-2">{{ errorMessage }}</v-alert>
              <v-alert v-if="successMessage" type="success" variant="tonal" class="mx-4 my-2">{{ successMessage }}</v-alert>

              <v-progress-linear v-if="loading" indeterminate color="primary" class="mb-2" />

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
        <v-card elevation="2" class="pa-6">
          <v-card-title class="px-0">Automašīnas</v-card-title>
          <v-card-text class="px-0 pb-0">
            Šī sadaļa šobrīd ir sagatavota kā navigācijas cilne. Šeit vēlāk var pievienot automašīnu pārvaldību, foto un parametrus.
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
</style>
