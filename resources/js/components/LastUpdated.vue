<script setup>
// Indicador discreto: "hace X s / min" desde la última actualización de datos.
// El tick por segundo vive acá para no re-renderizar toda la pantalla.
import { computed, onBeforeUnmount, onMounted, ref } from "vue";

const props = defineProps({
  timestamp: { type: Number, default: null },
  inline: { type: Boolean, default: false },
});

const now = ref(Date.now());
let timer = null;

onMounted(() => {
  timer = setInterval(() => {
    now.value = Date.now();
  }, 1000);
});
onBeforeUnmount(() => clearInterval(timer));

const label = computed(() => {
  if (!props.timestamp) return "Sin datos";
  const s = Math.max(0, Math.floor((now.value - props.timestamp) / 1000));
  if (s < 60) return `hace ${s} s`;
  const m = Math.floor(s / 60);
  if (m < 60) return `hace ${m} min ${String(s % 60).padStart(2, "0")} s`;
  return `hace ${Math.floor(m / 60)} h ${m % 60} min`;
});

const exact = computed(() =>
  props.timestamp
    ? "Última actualización: " + new Date(props.timestamp).toLocaleTimeString("es-AR")
    : ""
);
</script>

<template>
  <span
    class="last-updated text-caption text-disabled d-inline-flex align-center"
    :class="{ 'last-updated--floating': !inline }" 
    :title="exact"
  >
    <VIcon icon="ri-time-line" size="14" class="mr-1" />
    {{ label }}
  </span>
</template>

<style scoped>
.last-updated {
  font-variant-numeric: tabular-nums;
  white-space: nowrap;
  user-select: none;
  color: #800804 !important;
  
}
.last-updated--floating {
  position: absolute;
  top: 8px;
  right: 16px;
  z-index: 1;
  
}
</style>
