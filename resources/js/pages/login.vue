<script setup>
import { useTheme } from "vuetify";
import AuthProvider from "@/views/pages/authentication/AuthProvider.vue";

import logo from "@images/logos/logo.png";
import illustration from "@images/pages/auth-v2-register-illustration-light.png";

import authV1MaskDark from "@images/pages/auth-v1-mask-dark.png";
import authV1MaskLight from "@images/pages/auth-v1-mask-light.png";
import authV1Tree2 from "@images/pages/auth-v1-tree-2.png";
import authV1Tree from "@images/pages/auth-v1-tree.png";

import { login } from "../api/auth";
import { useRouter } from "vue-router";

const router = useRouter();

const isLoading = ref(false);

const form = ref({
  email: "",
  password: "",
  remember: false,
});

async function handleLogin() {
  isLoading.value = true;
  try {
    await login(form.value.email, form.value.password);
    router.push("/dashboard");
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Error de inicio de sesión",
      text: "Revisá tu email y contraseña",
    });
  } finally {
    isLoading.value = false;
  }
}

const vuetifyTheme = useTheme();

const authThemeMask = computed(() => {
  return vuetifyTheme.global.name.value === "light" ? authV1MaskLight : authV1MaskDark;
});

const isPasswordVisible = ref(false);

const logoColor = computed(() => {
  return vuetifyTheme.global.name.value === "light" ? "#ff0000" : "#00ff00";
});

// Modern gradient colors
const gradientColors = computed(() => {
  return vuetifyTheme.global.name.value === "light"
    ? "linear-gradient(135deg, #9090E0 0%, #042990 100%)"
    : "linear-gradient(135deg, #9E95F5 0%, #B8B1FF 100%)";
});

// Color personalizado - azul corporativo #042990
const primaryColor = ref("#042990");

// Estilo para el logo
const logoStyle = computed(() => ({
  color: primaryColor.value,
}));

// Estilo para el botón
const buttonStyle = computed(() => ({
  background: primaryColor.value,
  "--hover-color": "#1a43b5", // Color más claro para hover
}));
</script>

<template>
  <!-- eslint-disable vue/no-v-html -->
  <!--
  <div class="auth-wrapper d-flex align-center justify-center pa-4">
    <VCard class="auth-card pa-4 pt-7" max-width="448">
      <VCardItem class="justify-center">
        <template #prepend>
          <div class="d-flex" >
            <div v-html="logo" :style="{ color: logoColor }"/>
          </div>
        </template>

        <VCardTitle class="font-weight-semibold text-2xl text-uppercase">
          IAMPOS
        </VCardTitle>
      </VCardItem>

      <VCardText class="pt-2">
        <h5 class="text-h5 font-weight-semibold mb-1">Bienvenido! 👋🏻</h5>
        <p class="mb-0">Por favor inicie sesión para comenzar la aventura</p>
      </VCardText>

      <VCardText>
        <VForm @submit.prevent="handleLogin">
          <VRow>
           
            <VCol cols="12">
              <VTextField v-model="form.email" label="Email" type="email" />
            </VCol>

 
            <VCol cols="12">
              <VTextField
                v-model="form.password"
                label="Password"
                placeholder="············"
                :type="isPasswordVisible ? 'text' : 'password'"
                :append-inner-icon="isPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
                @click:append-inner="isPasswordVisible = !isPasswordVisible"
              />

    
              <div class="d-flex align-center justify-space-between flex-wrap mt-1 mb-4">
                <VCheckbox v-model="form.remember" label="Remember me" />

                <a class="ms-2 mb-1" href="javascript:void(0)"> Forgot Password? </a>
              </div> 
 
              <br>
              <VBtn block type="submit"  :loading="isLoading" > Login </VBtn>
            </VCol>

             <VCol cols="12" class="text-center text-base">
              <span>New on our platform?</span>
              <RouterLink class="text-primary ms-2" to="/register">
                Create an account
              </RouterLink>
            </VCol>

            <VCol cols="12" class="d-flex align-center">
              <VDivider />
              <span class="mx-4">or</span>
              <VDivider />
            </VCol>

 
            <VCol cols="12" class="text-center">
              <AuthProvider />
            </VCol> 
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
<!--
    <VImg
      class="auth-footer-start-tree d-none d-md-block"
      :src="authV1Tree"
      :width="250"
    />

    <VImg
      :src="authV1Tree2"
      class="auth-footer-end-tree d-none d-md-block"
      :width="350"
    />
   
    <VImg class="auth-footer-mask d-none d-md-block" :src="authThemeMask" />
  </div> 
  -->
  <div class="auth-wrapper">
    <VCard class="auth-card">
      <VCardItem class="justify-center logo-container">
        <div class="d-flex align-center">
                

          <!--<div class="logo-wrapper" v-html="logo" :style="{ color: logoColor }"></div>-->
          
          <img class="logo-wrapper"  :src="logo"> </img>
          
          <!--<VCardTitle class="font-weight-semibold text-2xl text-uppercase ml-3">
            IAMPOS
          </VCardTitle>-->
        </div>
        
      </VCardItem>

      <VCardText class="text-center pt-2">
        <h5 class="text-h5 font-weight-semibold mb-1">Bienvenido! 👋🏻</h5>
        <p class="mb-0">Por favor inicie sesión para comenzar</p>
      </VCardText>

      <VCardText>
        <VForm @submit.prevent="handleLogin">
          <VRow>
            <!-- email -->

            <VCol cols="12">
              <VTextField
                v-model="form.email"
                label="Email"
                type="email"
                prepend-inner-icon="ri-mail-line"
                variant="outlined"
                color="primary"
                rounded="lg"
              />
            </VCol>

            <!-- password -->
            <VCol cols="12">
              <VTextField
                v-model="form.password"
                label="Contraseña"
                placeholder="············"
                :type="isPasswordVisible ? 'text' : 'password'"
                prepend-inner-icon="ri-lock-line"
                :append-inner-icon="isPasswordVisible ? 'ri-eye-off-line' : 'ri-eye-line'"
                @click:append-inner="isPasswordVisible = !isPasswordVisible"
                variant="outlined"
                color="primary"
                rounded="lg"
              />

              <!--<div class="d-flex justify-end mt-2">
                <a class="text-caption" href="#" style="color: inherit;">¿Olvidaste tu contraseña?</a>
              </div>-->

              <!-- login button -->
              <VBtn
                block
                type="submit"
                :loading="isLoading"
                rounded="lg"
                size="large"
                class="mt-6"
                :style="{ background: gradientColors }"
              >
                Iniciar Sesión
              </VBtn>
            </VCol>

            <!--<VCol cols="12" class="text-center mt-2">
              <span class="text-caption">¿No tienes una cuenta?</span>
              <RouterLink class="text-caption font-weight-bold ml-2" to="/register" :style="{ color: logoColor }">
                Regístrate
              </RouterLink>
            </VCol>-->
          </VRow>
        </VForm>
      </VCardText>
    </VCard>
    <div key=""></div>
    <!--<div class="auth-background" :style="{ background: gradientColors }"></div>-->
    <div class="auth-background login-bg" ></div>
  </div>
</template>

<style lang="scss">
/*@use "@core-scss/pages/page-auth.scss";*/
.auth-wrapper {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  padding: 2rem;

  .auth-background {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 50%;
    z-index: 0;
    clip-path: ellipse(60% 60% at 50% 70%);
  }

  .auth-card {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 450px;
    border-radius: 16px !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    overflow: hidden;

    .logo-container {
      padding-top: 2rem;

      .logo-wrapper {
        width: 200px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;

        :deep(svg) {
          width: 100%;
          height: 100%;
        }
      }
      .logo-illustration {
        width: 200px;
        height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;

        :deep(svg) {
          width: 100%;
          height: 100%;
        }
      }
    }
  }

  :deep(.v-field--outlined) {
    fieldset {
      border-color: rgba(0, 0, 0, 0.1);
    }

    &:hover fieldset {
      border-color: rgba(0, 0, 0, 0.3);
    }
  }

  :deep(.v-btn) {
    text-transform: none;
    letter-spacing: normal;
    box-shadow: 0 4px 12px #042990;
    transition: all 0.3s ease;

    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px #042990;
    }
  }
}

.login-bg {
  min-height: 50vh;
  display: flex;
  justify-content: center;
  align-items: center;

 
  /* Fondo degradado */
  background: linear-gradient(135deg, rgb(144, 144, 224) -1%, rgb(4, 41, 144) 53%);
  background-size: 400% 400%;
  animation: gradientShift 12s ease infinite;
}


@keyframes gradientShift {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}
</style>
