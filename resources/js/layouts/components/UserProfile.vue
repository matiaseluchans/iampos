<script setup>
import { ref, onMounted } from 'vue' // Importa ref
import avatar1 from "@images/avatars/avatar-1.png";
import { logout } from "@/api/auth";
import { getUser } from "@/api/auth";

const router = useRouter();

async function handleLogout() {
  try {
    await logout();

    Swal.fire({
      icon: "success",
      title: "Sesión cerrada",
      timer: 1500,
      showConfirmButton: false,
    });
    router.push("/login");
  } catch (e) {
    console.error("Error al cerrar sesión:", e);
  }
}

const userInfo1 = ref(null);

onMounted(async () => {
  try {
    
    const response = await getUser();
    userInfo1.value = response;  
    console.log("Datos del usuario:", response);

  } catch (error) {
    console.error('Error al obtener información del usuario:', error);
  }
});
</script>

<template>
  <VBadge dot location="bottom right" offset-x="3" offset-y="3" color="success" bordered>
    <VAvatar class="cursor-pointer" color="primary" variant="tonal">
      <VImg :src="avatar1" />

      <!-- SECTION Menu -->
      <VMenu activator="parent" width="230" location="bottom end" offset="14px">
        <VList>

          
         
          <VListItem>
            <template #prepend>
              <VListItemAction start>
                <VBadge
                  dot
                  location="bottom right"
                  offset-x="3"
                  offset-y="3"
                  color="success"
                >
                  <VAvatar
                    color="primary"
                    variant="tonal"
                  >
                    <VImg :src="avatar1" />
                  </VAvatar>
                </VBadge>
              </VListItemAction>
            </template>

            <VListItemTitle v-if="userInfo1" class="font-weight-semibold">
              {{ userInfo1.name }} 
            </VListItemTitle>
            <VListItemSubtitle>{{ userInfo1.email }}</VListItemSubtitle>
          </VListItem>

         
          <VDivider class="my-2" />
<!--
     
          <VListItem link>
            <template #prepend>
              <VIcon
                class="me-2"
                icon="ri-user-line"
                size="22"
              />
            </template>

            <VListItemTitle>Profile</VListItemTitle>
          </VListItem>

 
          <VListItem link>
            <template #prepend>
              <VIcon
                class="me-2"
                icon="ri-settings-4-line"
                size="22"
              />
            </template>

            <VListItemTitle>Settings</VListItemTitle>
          </VListItem>

   
          <VListItem link>
            <template #prepend>
              <VIcon
                class="me-2"
                icon="ri-money-dollar-circle-line"
                size="22"
              />
            </template>

            <VListItemTitle>Pricing</VListItemTitle>
          </VListItem>

      
          <VListItem link>
            <template #prepend>
              <VIcon
                class="me-2"
                icon="ri-question-line"
                size="22"
              />
            </template>

            <VListItemTitle>FAQ</VListItemTitle>
          </VListItem>

 
          <VDivider class="my-2" />

           -->
          <VListItem @click="handleLogout">
            <template #prepend>
              <VIcon class="me-2" icon="ri-logout-box-r-line" size="22" />
            </template>
           
            <VListItemTitle>Logout</VListItemTitle>
          </VListItem>
        </VList>
      </VMenu>
      <!-- !SECTION -->
    </VAvatar>
  </VBadge>
</template>
