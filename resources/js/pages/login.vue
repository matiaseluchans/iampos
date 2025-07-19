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

// Efecto de partículas
const particles = ref([]);
const maxParticles = 230;

// Generar partículas aleatorias
const generateParticles = () => {
  particles.value = [];
  for (let i = 0; i < maxParticles; i++) {
    particles.value.push({
      id: i,
      x: Math.random() * 100,
      y: Math.random() * 100,
      size: Math.random() * 50 + 2,
      speed: Math.random() * 0.5 + 0.1,
      opacity: Math.random() * 0.5 + 0.3,
      delay: Math.random() * 5
    });
  }
};

onMounted(() => {
  generateParticles();
});

// Color corporativo
const primaryColor = ref("#042990");
const secondaryColor = ref("#9090E0");
</script>

<template>
  <div class="auth-wrapper">
    <!-- Partículas de fondo animadas -->
    <div class="particles-container">
      <div 
        v-for="particle in particles" 
        :key="particle.id"
        class="particle"
        :style="{
          left: `${particle.x}%`,
          top: `${particle.y}%`,
          width: `${particle.size}px`,
          height: `${particle.size}px`,
          opacity: particle.opacity,
          animation: `float ${particle.speed * 10}s ease-in-out infinite ${particle.delay}s`,
          background: `radial-gradient(circle, ${secondaryColor} 0%, ${primaryColor} 100%)`
        }"
      ></div>
    </div>

    <!-- Tarjeta de login con efecto hover 3D -->
    <div class="card-container">
      <VCard class="auth-card">
        <!-- Logo con efecto de flotación -->
        <VCardItem class="justify-center logo-container">
          <div class="logo-animation">
            <img class="logo-wrapper" :src="logo" alt="Logo">
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
                  class="input-field"
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
                  class="input-field"
                />

                <!-- Botón con efecto de onda al hacer hover -->
                <VBtn
                  block
                  type="submit"
                  :loading="isLoading"
                  rounded="lg"
                  size="large"
                  class="mt-6 login-btn"
                >
                  <span>Iniciar Sesión</span>
                  <div class="wave"></div>
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </div>

    <!-- Fondo degradado animado -->
    <div class="auth-background"></div>
  </div>
</template>

<style lang="scss" scoped>
.auth-wrapper {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  padding: 2rem;
  overflow: hidden;
  background-color: #f5f7ff;

  .particles-container {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
    pointer-events: none;
    
    .particle {
      position: absolute;
      border-radius: 50%;
      filter: blur(1px);
    }
  }

  .card-container {
    perspective: 1000px;
    z-index: 2;
  }

  .auth-card {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 450px;
    border-radius: 16px !important;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    transition: transform 0.5s ease, box-shadow 0.5s ease;
    transform-style: preserve-3d;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    
    &:hover {
      //transform: translateY(-5px) rotateX(2deg) rotateY(2deg);
      box-shadow: 0 20px 50px rgba(4, 41, 144, 0.2);
    }

    .logo-container {
      padding-top: 2rem;

      .logo-animation {
        //animation: float 6s ease-in-out infinite;
        
        .logo-wrapper {
          width: 200px;
          height: auto;
          //filter: drop-shadow(0 5px 15px rgba(4, 41, 144, 0.2));
        }
      }
    }

    .input-field {
      transition: all 0.3s ease;
      
      &:focus-within {
        transform: translateY(-2px);
        
        :deep(.v-field__outline) {
          color: v-bind(primaryColor) !important;
        }
      }
    }

    .login-btn {
      position: relative;
      overflow: hidden;
      background: linear-gradient(135deg, v-bind(secondaryColor) 0%, v-bind(primaryColor) 100%);
      box-shadow: 0 4px 15px rgba(4, 41, 144, 0.3);
      transition: all 0.3s ease;
      
      span {
        position: relative;
        z-index: 2;
      }
      
      .wave {
        position: absolute;
        top: -100%;
        left: 0;
        width: 200%;
        height: 200%;
        background: rgba(255, 255, 255, 0.2);
        transform: rotate(45deg);
        transition: all 0.5s ease;
        z-index: 1;
      }
      
      &:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(4, 41, 144, 0.4);
        
        .wave {
          top: -50%;
        }
      }
      
      &:active {
        transform: translateY(1px);
      }
    }
  }

  .auth-background {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 50%;
    z-index: 0;
    clip-path: ellipse(70% 60% at 50% 80%);
    background: linear-gradient(135deg, v-bind(secondaryColor) 0%, v-bind(primaryColor) 100%);
    background-size: 200% 200%;
    animation: gradientShift 12s ease infinite, pulse 15s ease infinite alternate;
  }
}

@keyframes float {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}

@keyframes gradientShift {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}

@keyframes float {
  0% {
    transform: translateY(0) translateX(0);
  }
  25% {
    transform: translateY(-5px) translateX(5px);
  }
  50% {
    transform: translateY(0) translateX(0);
  }
  75% {
    transform: translateY(-3px) translateX(-3px);
  }
  100% {
    transform: translateY(0) translateX(0);
  }
}
</style>
