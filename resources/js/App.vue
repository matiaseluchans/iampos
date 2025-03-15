<script setup>
import UpgradeToPro from '@/components/UpgradeToPro.vue'
</script>

<template>
  <VApp>
    <RouterView />
    <!-- <UpgradeToPro /> -->
  </VApp>
</template>
<style>
/* Ajustar z-index de SweetAlert2 */
.my-swal-container {
  z-index: 99999 !important;
}
</style>
