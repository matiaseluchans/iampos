<script setup>
const props = defineProps({
  item: {
    type: null,
    required: true,
  },
})
</script>

<template>
  <li
    class="nav-link"
    :class="{ disabled: item.disable }"
  >
    <Component
      :is="item.to ? 'RouterLink' : 'a'"
      :to="item.to"
      :href="item.href"
      :target="item.target"
      :class="item.class|| ''"
    >
    
      <VIcon
        :icon="item.icon || 'ri-checkbox-blank-circle-line'"
        class="nav-item-icon"
      />
      <!-- 👉 Title -->
      <span class="nav-item-title">
        {{ item.title }}
      </span>
      <span
        class="nav-item-badge"
        :class="item.badgeClass"
      >
        {{ item.badgeContent }}
      </span>
    </Component>
  </li>
</template>

<style lang="scss" scoped>
.layout-vertical-nav {
  .nav-link a {
    display: flex;
    align-items: center;
    cursor: pointer;
  }
}
.classOrderCreate {
    background: linear-gradient(-72.47deg, 
                rgba(var(--v-theme-primary), 0.6) 12.16%, 
                rgba(var(--v-theme-primary), 0.0) 76.47%) !important;
    /*box-shadow: 0 3px 6px 0 rgba(var(--v-shadow-key-umbra-color), var(--v-shadow-sm-opacity)), 
                0 0 transparent, 
                0 0 transparent !important;*/
    color: black !important;
    animation: blink 1.5s infinite alternate;
}

/* Definición de la animación de parpadeo */
@keyframes blink {
    0%, 100% {
        opacity: 1;
        background: linear-gradient(22.47deg, 
                    rgba(var(--v-theme-primary), 0.2) 12.16%, 
                    rgba(var(--v-theme-primary), 0.2) 76.47%) !important;
    }
    50% {
        opacity: 0.5;
        background: linear-gradient(-72.47deg, 
                    rgba(var(--v-theme-primary), 0.4) 12.16%, 
                    rgba(var(--v-theme-primary), 0.4) 76.47%) !important;
       /* box-shadow: 0 0 10px rgba(var(--v-theme-primary), 0.5) !important;*/
    }
}
</style>
