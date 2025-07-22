<script setup>
import { computed } from 'vue' // Importa ref

import NavItems from "@/layouts/components/NavItems.vue";
import VerticalNavLayout from "@layouts/components/VerticalNavLayout.vue";
import Footer from "@/layouts/components/Footer.vue";
import NavbarThemeSwitcher from "@/layouts/components/NavbarThemeSwitcher.vue";
import UserProfile from "@/layouts/components/UserProfile.vue";
import logo from "@images/logos/logo.png";
import logo2 from "@images/logo.svg?raw";

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
      <div class="d-flex h-100 align-center ml-6">
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
      

      <!--<VRow no-gutters class="border-b-md pb-4">
        <VCol cols="12" xs="12"> 
          <RouterLink to="/" class="app-logo app-title-wrapper" style="column-gap:5px; display: flex; align-items: center;">
            <VImg style="height:25px" :src="logo"></VImg>
          </RouterLink>
        </VCol>
      </VRow>-->
      <RouterLink to="/" class="app-logo app-title-wrapper" style="column-gap:5px">
      
       
        <!--
        <div
          class="d-flex"
          v-html="logo"
        />-->

        <!--<h1 class="font-weight-medium leading-normal text-xl text-uppercase">
        &nbsp;&nbsp;IAMPOS
        </h1>-->
        
        <svg width="30" viewBox="0 0 30 24" xmlns="http://www.w3.org/2000/svg">
          <g fill="none" fill-rule="evenodd">
            <path d="M30 21.392a2 2 0 0 1-3.027 1.716l-3.258-1.95a2 2 0 0 1-.973-1.716l-.001-6.7L15
                17.178l-7.742-4.434v6.7a2 2 0 0 1-.973 1.715l-3.258 1.95A2 2 0 0 1 0 21.392V3.585l.005-.15L0 3.572a2 2 0 0
                1 3.045-1.706L15 9.194l11.955-7.328A2 2 0 0 1 30 3.572Z" fill="var(--initial-loader-color)" />
            <path fill="var(--initial-loader-color)" opacity=".078" d="m0 8.589 7.258 4.162v4.08z" />
            <path fill="var(--initial-loader-color)" opacity=".078" d=" m0 8.589 7.258 4.056v2.492zm30 0-7.258
                4.153v4.212z" />
            <path fill="var(--initial-loader-color)" opacity=".078" d="m30 8.589-7.258 4.052v2.62z" />
            <path d="M3.045 1.866 15 9.194v7.983L0 8.587V3.571a2 2 0 0 1 3.045-1.706Z" fill-opacity=".15" fill="
                #FFF" />
            <path d="M26.955 1.866 15 9.194v7.983l15-8.59V3.571a2 2 0 0 0-3.045-1.706Z" fill-opacity=".35"
              fill="#FFF" />
          </g>
        </svg>
      
        <h1 class="ml-8 font-weight-medium leading-normal text-h5 text-uppercase">
        IA 
        </h1>
        <svg width="20" viewBox="0 0 30 24" xmlns="http://www.w3.org/2000/svg">
          <g fill="none" fill-rule="evenodd">
            <path d="M30 21.392a2 2 0 0 1-3.027 1.716l-3.258-1.95a2 2 0 0 1-.973-1.716l-.001-6.7L15
                17.178l-7.742-4.434v6.7a2 2 0 0 1-.973 1.715l-3.258 1.95A2 2 0 0 1 0 21.392V3.585l.005-.15L0 3.572a2 2 0 0
                1 3.045-1.706L15 9.194l11.955-7.328A2 2 0 0 1 30 3.572Z" fill="var(--initial-loader-color)" />
            <path fill="var(--initial-loader-color)" opacity=".078" d="m0 8.589 7.258 4.162v4.08z" />
            <path fill="var(--initial-loader-color)" opacity=".078" d=" m0 8.589 7.258 4.056v2.492zm30 0-7.258
                4.153v4.212z" />
            <path fill="var(--initial-loader-color)" opacity=".078" d="m30 8.589-7.258 4.052v2.62z" />
            <path d="M3.045 1.866 15 9.194v7.983L0 8.587V3.571a2 2 0 0 1 3.045-1.706Z" fill-opacity=".15" fill="
                #FFF" />
            <path d="M26.955 1.866 15 9.194v7.983l15-8.59V3.571a2 2 0 0 0-3.045-1.706Z" fill-opacity=".35"
              fill="#FFF" />
          </g>
        </svg>
        <h1 class="font-weight-medium leading-normal text-h5 text-uppercase">
        POS 
        </h1> 
        
      </RouterLink>

      <IconBtn class="d-block d-lg-none" @click="toggleIsOverlayNavActive(false)">
        <VIcon icon="ri-close-line" />
      </IconBtn>
      
    </template>

    <template #vertical-nav-content >
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
