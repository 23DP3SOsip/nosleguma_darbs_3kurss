<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  email: '',
  password: '',
})

const errorMessage = ref('')

const submit = async () => {
  errorMessage.value = ''

  try {
    await authStore.login(form.value)
    await router.push({ name: 'home' })
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Neizdevās autorizēties.'
  }
}
</script>

<template>
  <v-container class="fill-height d-flex align-center justify-center">
    <v-card class="pa-6" max-width="420" width="100%" elevation="6">
      <v-card-title class="text-h5 font-weight-bold pa-0 mb-2">Pieslēgšanās</v-card-title>
      <v-card-subtitle class="pa-0 mb-6">Publiska reģistrācija nav pieejama.</v-card-subtitle>

      <v-alert v-if="errorMessage" type="error" variant="tonal" class="mb-4">
        {{ errorMessage }}
      </v-alert>

      <v-form @submit.prevent="submit">
        <v-text-field
          v-model="form.email"
          label="E-pasts"
          type="email"
          variant="outlined"
          density="comfortable"
          required
          class="mb-2"
        />

        <v-text-field
          v-model="form.password"
          label="Parole"
          type="password"
          variant="outlined"
          density="comfortable"
          required
          class="mb-4"
        />

        <v-btn
          type="submit"
          color="primary"
          block
          size="large"
          :loading="authStore.loading"
          :disabled="authStore.loading"
        >
          Ieiet sistēmā
        </v-btn>
      </v-form>
    </v-card>
  </v-container>
</template>
