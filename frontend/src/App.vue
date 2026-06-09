<script setup>
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { RouterView } from 'vue-router'
import { useDisplay } from 'vuetify'
import { useAuthStore } from './stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const { smAndDown } = useDisplay()
const mobileNavOpen = ref(false)

const isAuthenticated = computed(() => authStore.isAuthenticated)
const canOpenAdmin = computed(() => authStore.canOpenAdminPanel)
const currentYear = new Date().getFullYear()
const mobileNavItems = computed(() => {
  const items = [{ title: 'Sākums', name: 'home' }]

  if (canOpenAdmin.value) {
    items.push({ title: 'Admin panelis', name: 'admin' })
  }

  return items
})
const roleLabel = computed(() => {
  return (
    {
      vadiba: 'Vadība',
      admin: 'Administrators',
      user: 'Lietotājs',
    }[authStore.user?.role] || authStore.user?.role || ''
  )
})

const logout = async () => {
  await authStore.logout()
  await router.push({ name: 'login' })
}

const goToRoute = async (name) => {
  mobileNavOpen.value = false
  await router.push({ name })
}
</script>

<template>
  <v-app>
    <v-navigation-drawer
      v-if="isAuthenticated && smAndDown"
      v-model="mobileNavOpen"
      temporary
      location="left"
      width="268"
      class="mobile-nav-drawer"
    >
      <v-list nav density="comfortable">
        <v-list-subheader>Izvēlne</v-list-subheader>
        <v-list-item
          v-for="item in mobileNavItems"
          :key="item.name"
          :title="item.title"
          prepend-icon="mdi-chevron-right"
          class="mobile-nav-item"
          :class="{ 'mobile-nav-item--active': route.name === item.name }"
          @click="goToRoute(item.name)"
        />
      </v-list>
    </v-navigation-drawer>

    <v-app-bar v-if="isAuthenticated" flat color="surface" class="app-header">
      <v-container class="d-flex align-center ga-2 app-header-inner">
        <template v-if="smAndDown">
          <v-btn icon variant="text" aria-label="Atvērt izvēlni" @click="mobileNavOpen = true">
            <v-icon>mdi-menu</v-icon>
          </v-btn>

          <v-spacer />

          <v-menu location="bottom end" :offset="8">
            <template #activator="{ props }">
              <v-btn
                v-bind="props"
                variant="text"
                class="text-body-2 text-medium-emphasis text-none px-2 app-header-user"
              >
                <span class="text-truncate">{{ authStore.user?.name }}</span>
              </v-btn>
            </template>
            <v-card class="pa-2" min-width="180">
              <div class="text-body-2 text-medium-emphasis px-2 pb-2 text-truncate">
                {{ authStore.user?.name }} ({{ roleLabel }})
              </div>
              <v-btn color="error" variant="flat" block @click="logout">Iziet</v-btn>
            </v-card>
          </v-menu>
        </template>

        <template v-else>
          <v-btn variant="text" :to="{ name: 'home' }">Sākums</v-btn>
          <v-btn v-if="canOpenAdmin" variant="text" :to="{ name: 'admin' }">Admin panelis</v-btn>
          <v-spacer />
          <div class="text-body-2 text-medium-emphasis mr-3">
            {{ authStore.user?.name }} ({{ roleLabel }})
          </div>
          <v-btn color="error" variant="flat" @click="logout">Iziet</v-btn>
        </template>
      </v-container>
    </v-app-bar>

    <v-main>
      <v-container class="py-4 py-md-8">
        <RouterView />
      </v-container>
    </v-main>

    <v-footer class="app-footer py-4" color="surface" border>
      <v-container class="d-flex flex-column flex-md-row align-center justify-space-between ga-3">
        <div class="footer-copy text-body-2">
          © {{ currentYear }} Autoparka rezervācijas sistēma. Visas tiesības aizsargātas.
        </div>

        <div class="footer-links text-body-2 text-medium-emphasis">Atbalsts: A230309SO@rvt.lv</div>
      </v-container>
    </v-footer>
  </v-app>
</template>

