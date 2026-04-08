<script setup>
import { RouterView } from 'vue-router'
</script>

<template>
  <div id="app">


    <main>
      <RouterView />
    </main>
  </div>
</template>

<style scoped>
#app {
  min-height: 100vh;
}


h1 {
  margin: 0;
  font-size: 28px;
}

main {
  padding: 20px;
  max-width: 1200px;
  margin: 0 auto;
}
</style>

