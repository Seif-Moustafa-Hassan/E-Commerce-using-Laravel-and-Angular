<script setup>
import Navbar from './components/Navbar.vue';
</script>

<template class="bg-primary w-100 p-4">
  <Navbar />
  <router-view />
</template>

<style scoped>
/* remove the Vite default styles */
</style>
