<script setup>
import {computed } from 'vue'  
import avatar1 from "@images/avatars/avatar-1.png";
import { logout } from "@/api/auth"; 
 
import { useStore } from 'vuex'

const router = useRouter();

const store = useStore()

// Acceder a los getters
const isAuthenticated = computed(() => store.getters.isAuthenticated)
const currentUser = computed(() => store.getters.currentUser)
const userPermissions = computed(() => store.getters.userPermissions)


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
function getImageUrl(imagePath) {
  if (!imagePath) return ''
  if (imagePath.startsWith('http')) return imagePath
  return `${process.env.VUE_APP_API_URL || ''}/storage/users/${imagePath}`
}

const avatarSrc = computed(() => {
  const image = currentUser.value?.data?.image
  return image ? getImageUrl(image) : avatar1
})
 

</script>

<template>
  <VBadge dot location="bottom right" offset-x="3" offset-y="3" color="success" bordered>
    <VAvatar  color="primary" variant="tonal" max-height="50"
    max-width="50" contain >
      <VImg :src="avatarSrc" 
      cover
      class="rounded-circle"
      @error="e => e.target.src = avatar1" />
    

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
                    
                    :image="avatarSrc" 
                  >
               
                  </VAvatar>
                </VBadge>
              </VListItemAction>
            </template>

            <VListItemTitle v-if="currentUser" class="font-weight-semibold">
            {{ currentUser.data.name }}  
            </VListItemTitle>
            <VListItemSubtitle>{{ currentUser.data.tenant.name }}</VListItemSubtitle>
            <VListItemSubtitle>{{ currentUser.data.roles[0] }}</VListItemSubtitle>
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
