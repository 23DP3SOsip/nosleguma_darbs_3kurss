<script setup>
import { computed, onMounted, ref } from 'vue'
import api from '../services/api'
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()

const cars = ref([])
const loading = ref(false)
const actionCarId = ref(null)
const errorMessage = ref('')
const successMessage = ref('')

const summary = computed(() => ({
  total: cars.value.length,
  free: cars.value.filter((car) => car.is_available).length,
  reserved: cars.value.filter((car) => !car.is_available).length,
}))

const myActiveReservation = computed(() => {
  return cars.value.find((car) => car.active_reservation?.user?.id === authStore.user?.id) || null
})

const loadCars = async () => {
  loading.value = true
  errorMessage.value = ''

  try {
    const { data } = await api.get('/api/cars')
    cars.value = data.cars
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Neizdevās ielādēt automašīnas.'
  } finally {
    loading.value = false
  }
}

const executeAction = async (car, action) => {
  actionCarId.value = car.id
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const { data } = await api.post(`/api/cars/${car.id}/${action}`)
    successMessage.value = data.message || 'Darbība izpildīta.'
    await loadCars()
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Neizdevās izpildīt darbību.'
  } finally {
    actionCarId.value = null
  }
}

const reserveCar = (car) => executeAction(car, 'reserve')
const completeReservation = (car) => executeAction(car, 'complete')

onMounted(loadCars)
</script>

<template>
  <div class="home-page">
    <v-sheet class="hero-shell pa-6 pa-md-8 mb-6" rounded="xl" elevation="0">
      <div class="hero-grid">
        <div>
          <p class="hero-kicker mb-2">Autoparka rezervācija</p>
          <h1 class="hero-title mb-4">Rezervējiet automašīnu tieši no galvenās lapas</h1>
          <p class="hero-text mb-0">
            Redziet visas automašīnas, to statusu, kurš tās izmanto un kad sākta pašreizējā rezervācija.
            Lietotāji ar lomu <strong>User</strong> var turēt tikai vienu aktīvu rezervāciju, bet <strong>Admin</strong> un <strong>Vadiba</strong> var rezervēt neierobežoti.
          </p>
        </div>

        <div class="hero-summary">
          <v-card class="summary-card" rounded="xl" elevation="0">
            <div class="summary-value">{{ summary.total }}</div>
            <div class="summary-label">Auto kopā</div>
          </v-card>
          <v-card class="summary-card success" rounded="xl" elevation="0">
            <div class="summary-value">{{ summary.free }}</div>
            <div class="summary-label">Brīvas</div>
          </v-card>
          <v-card class="summary-card danger" rounded="xl" elevation="0">
            <div class="summary-value">{{ summary.reserved }}</div>
            <div class="summary-label">Rezervētas</div>
          </v-card>
        </div>
      </div>
    </v-sheet>

    <v-alert v-if="errorMessage" type="error" variant="tonal" class="mb-4">{{ errorMessage }}</v-alert>
    <v-alert v-if="successMessage" type="success" variant="tonal" class="mb-4">{{ successMessage }}</v-alert>

    <v-alert v-if="myActiveReservation" type="info" variant="tonal" class="mb-6">
      Jums šobrīd ir aktīva rezervācija: {{ myActiveReservation.display_name }} ({{ myActiveReservation.plate_number }}).
    </v-alert>

    <v-progress-linear v-if="loading" indeterminate color="primary" class="mb-6" />

    <v-row v-if="cars.length" dense>
      <v-col v-for="car in cars" :key="car.id" cols="12" md="6" lg="4" class="d-flex">
        <v-card class="car-card d-flex flex-column w-100" rounded="xl" elevation="10">
          <v-img :src="car.image_url" height="230" cover class="car-image">
            <div class="car-image-overlay">
              <v-chip :color="car.is_available ? 'success' : 'error'" variant="flat" size="small">
                {{ car.status_label }}
              </v-chip>
            </div>
          </v-img>

          <v-card-title class="pb-1">{{ car.display_name }}</v-card-title>
          <v-card-subtitle class="pt-0">{{ car.plate_number }}</v-card-subtitle>

          <v-card-text class="pt-3 pb-2">
            <div class="info-row">
              <span>Brends</span>
              <strong>{{ car.brand }}</strong>
            </div>
            <div class="info-row">
              <span>Modelis</span>
              <strong>{{ car.model }}</strong>
            </div>
            <div class="info-row">
              <span>KPP</span>
              <strong>{{ car.transmission_type }}</strong>
            </div>

            <v-divider class="my-4" />

            <div v-if="car.active_reservation" class="reservation-box">
              <div class="info-row">
                <span>Rezervēja</span>
                <strong>{{ car.active_reservation.user?.name }}</strong>
              </div>
              <div class="info-row">
                <span>Rezervēts no</span>
                <strong>{{ new Date(car.active_reservation.started_at).toLocaleString('lv-LV') }}</strong>
              </div>
            </div>

            <div v-else class="text-body-2 text-medium-emphasis">
              Šis automobilis šobrīd ir brīvs un pieejams rezervācijai.
            </div>
          </v-card-text>

          <v-spacer />

          <v-card-actions class="px-4 pb-4 pt-0">
            <v-btn
              v-if="car.is_available"
              color="primary"
              variant="flat"
              block
              :disabled="!car.can_reserve"
              :loading="actionCarId === car.id"
              @click="reserveCar(car)"
            >
              Rezervēt
            </v-btn>

            <template v-else>
              <v-btn
                v-if="car.can_complete"
                color="success"
                variant="flat"
                :loading="actionCarId === car.id"
                @click="completeReservation(car)"
              >
                Pabeigt
              </v-btn>
            </template>
          </v-card-actions>

          <v-card-actions v-if="car.is_available && !car.can_reserve" class="px-4 pb-4 pt-0">
            <v-alert type="warning" variant="tonal" density="compact" class="w-100 mb-0">
              Jums jau ir aktīva rezervācija.
            </v-alert>
          </v-card-actions>
        </v-card>
      </v-col>
    </v-row>

    <v-card v-else-if="!loading" class="pa-8 text-center" rounded="xl" elevation="4">
      <v-card-title class="justify-center">Nav pievienotu automašīnu</v-card-title>
      <v-card-text>Pievienojiet automašīnas datubāzei, lai tās parādītos rezervācijas katalogā.</v-card-text>
    </v-card>
  </div>
</template>

<style scoped>
.home-page {
  display: grid;
  gap: 24px;
}

.hero-shell {
  background:
    radial-gradient(circle at top right, rgba(210, 25, 34, 0.18), transparent 30%),
    linear-gradient(135deg, rgba(17, 24, 39, 0.92), rgba(31, 41, 55, 0.9));
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.hero-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.5fr) minmax(280px, 0.9fr);
  gap: 24px;
  align-items: stretch;
}

.hero-kicker {
  letter-spacing: 0.16em;
  text-transform: uppercase;
  font-size: 0.78rem;
  color: rgba(255, 255, 255, 0.72);
}

.hero-title {
  font-size: clamp(2rem, 4vw, 3.5rem);
  line-height: 1.05;
  max-width: 12ch;
}

.hero-text {
  max-width: 68ch;
  color: rgba(255, 255, 255, 0.82);
}

.hero-summary {
  display: grid;
  gap: 14px;
}

.summary-card {
  background: rgba(255, 255, 255, 0.08);
  color: white;
  padding: 20px;
  border: 1px solid rgba(255, 255, 255, 0.08);
}

.summary-card.success {
  background: rgba(76, 175, 80, 0.16);
}

.summary-card.danger {
  background: rgba(210, 25, 34, 0.16);
}

.summary-value {
  font-size: 2rem;
  font-weight: 800;
  line-height: 1;
}

.summary-label {
  margin-top: 6px;
  color: rgba(255, 255, 255, 0.78);
}

.car-card {
  overflow: hidden;
  background: linear-gradient(180deg, #ffffff, #fbfbfd);
}

.car-image {
  position: relative;
}

.car-image-overlay {
  position: absolute;
  inset: 16px 16px auto auto;
}

.info-row {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 16px;
  padding: 5px 0;
  color: rgba(0, 0, 0, 0.72);
}

.info-row strong {
  color: rgba(0, 0, 0, 0.9);
  text-align: right;
}

.reservation-box {
  padding: 14px;
  border-radius: 16px;
  background: rgba(210, 25, 34, 0.06);
  border: 1px solid rgba(210, 25, 34, 0.12);
}

@media (max-width: 960px) {
  .hero-grid {
    grid-template-columns: 1fr;
  }
}
</style>
