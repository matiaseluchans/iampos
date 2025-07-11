<script setup>
import { computed } from 'vue' // Importa ref

import NavItems from "@/layouts/components/NavItems.vue";
import VerticalNavLayout from "@layouts/components/VerticalNavLayout.vue";
import Footer from "@/layouts/components/Footer.vue";
import NavbarThemeSwitcher from "@/layouts/components/NavbarThemeSwitcher.vue";
import UserProfile from "@/layouts/components/UserProfile.vue";
import logo from "@images/logos/logo.png";

//import { getUser } from "../../api/auth";
import { useStore } from 'vuex'
const store = useStore()

// Acceder a los getters
const isAuthenticated = computed(() => store.getters.isAuthenticated)
const currentUser = computed(() => store.getters.currentUser)
const userPermissions = computed(() => store.getters.userPermissions)



</script>

<template>
  <VerticalNavLayout>
    <!-- 👉 navbar -->
    <template #navbar="{ toggleVerticalOverlayNavActive }">
      <div class="d-flex h-100 align-center">
        <!-- 👉 Vertical nav toggle in overlay mode -->
        
        <IconBtn class="ms-n3 d-lg-none" @click="toggleVerticalOverlayNavActive(true)">
          <VIcon icon="ri-menu-line" />
        </IconBtn>

        <!-- 👉 Search -->
        <!--
        <div
          class="d-flex align-center cursor-pointer"
          style="user-select: none;"
        >
          
          <IconBtn>
            <VIcon icon="ri-search-line" />
          </IconBtn>

          <span class="d-none d-md-flex align-center text-disabled">
            <span class="me-3">Search</span>
            <span class="meta-key">&#8984;K</span>
          </span>
        </div>
-->
        <VSpacer />
        <!--
        <IconBtn
          class="me-2"
          href="https://github.com/themeselection/materio-vuetify-vuejs-laravel-admin-template-free"
          target="_blank"
          rel="noopener noreferrer"
        >
          <VIcon icon="ri-github-fill" />
        </IconBtn>
        -->
        
        <div v-if="currentUser" class="user-info text-end pr-4">
          {{ currentUser.data.email }} <br> 
          <VChip
            class="ma-0"
            style="height: 20px;"
            color="secondary"
          >
            <span class="text-caption">{{ currentUser.data.tenant.name }} | {{ currentUser.data.roles[0] }}</span>
          </VChip>
        </div>
        <!--<IconBtn class="me-2">
          <VIcon icon="ri-notification-line" />
        </IconBtn>-->

        <NavbarThemeSwitcher class="me-2" />
       
        <UserProfile />
      </div>
    </template>

    <template #vertical-nav-header="{ toggleIsOverlayNavActive }">
      <div class="d-flex border-b-md" />
      <VRow no-gutters class="border-b-md pb-4">
        <VCol cols="12" xs="12"> <VImg max-height="145px" :src="logo"></VImg></VCol>
      </VRow>

      <RouterLink to="/" class="app-logo app-title-wrapper">
        <!--
        <div
          class="d-flex"
          v-html="logo"
        />-->

        <!--<h1 class="font-weight-medium leading-normal text-xl text-uppercase">
        &nbsp;&nbsp;IAMPOS
        </h1>-->
      </RouterLink>

      <IconBtn class="d-block d-lg-none" @click="toggleIsOverlayNavActive(false)">
        <VIcon icon="ri-close-line" />
      </IconBtn>
    </template>

    <template #vertical-nav-content>
      <NavItems />
    </template>

    <!-- 👉 Pages -->
    <slot />

    <!-- 👉 Footer -->
    <template #footer>
      <Footer />
    </template>
  </VerticalNavLayout>
</template>
 
<style lang="scss" scoped>
.meta-key {
  border: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
  border-radius: 6px;
  block-size: 1.5625rem;
  line-height: 1.3125rem;
  padding-block: 0.125rem;
  padding-inline: 0.25rem;
}

.app-logo {
  display: flex;
  align-items: center;
  column-gap: 0.75rem;

  .app-logo-title {
    font-size: 1.25rem;
    font-weight: 500;
    line-height: 1.75rem;
    text-transform: uppercase;
  }
}
</style>
