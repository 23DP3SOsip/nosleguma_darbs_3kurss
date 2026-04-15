<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { RouterView } from 'vue-router'
import { useAuthStore } from './stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const isAuthenticated = computed(() => authStore.isAuthenticated)
const canOpenAdmin = computed(() => authStore.canOpenAdminPanel)

const logout = async () => {
  await authStore.logout()
  await router.push({ name: 'login' })
}
</script>

<template>
  <v-app>
    <v-app-bar v-if="isAuthenticated" flat color="white">
      <v-container class="d-flex align-center ga-2">
        <v-btn variant="text" :to="{ name: 'home' }">Sākums</v-btn>
        <v-btn v-if="canOpenAdmin" variant="text" :to="{ name: 'admin' }">Admin panelis</v-btn>
        <v-spacer />
        <div class="text-body-2 text-medium-emphasis mr-3">{{ authStore.user?.name }} ({{ authStore.user?.role }})</div>
        <v-btn color="error" variant="flat" @click="logout">Iziet</v-btn>
      </v-container>
    </v-app-bar>

    <v-main>
      <v-container class="py-8">
        <RouterView />
      </v-container>
    </v-main>
  </v-app>
</template>

