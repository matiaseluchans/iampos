<script setup>
import logo from '@images/logos/logo.png'
import { ref, onMounted } from 'vue'
import axios from 'axios'
 

const version = ref('')

const fetchVersion = async () => {
  try {
    const response = await axios.get('/api/version')
    version.value = response.data ;
  } catch (error) {
    console.error('Error fetching version:', error)
  }
}

const cleanAuthor = (raw) => {
  if (!raw) return ''
  const match = raw.match(/^([^<]+)/)
  return match ? match[1].trim() : raw
}

onMounted(() => {
  fetchVersion()
})
</script>
<template>
<div>
  <div class="h-100 d-flex align-center justify-space-between  border-t-sm	pt-3">            
      <!-- 👉 Footer: left content -->
       <VRow no-gutters class="">
        <VCol
            cols="12"
            sm="10"> 
            
          <span class="mt-0 pt-0 align-center">
                  <small> 
                  <strong>TAG:</strong> {{ version.tag}} - <strong>MSG:</strong>{{ version.mensaje_tag}} - <strong>AUTOR:</strong>{{ cleanAuthor(version.tag_autor)}} <br>
                  <strong>COMMIT:</strong> {{ version.commit?.slice(-7) }} - <strong>MSG:</strong>{{ version.mensaje_commit }} - <strong>AUTOR:</strong>{{ cleanAuthor(version.commit_autor)}}</small>
          
                </span>
          </VCol>
          <VCol
            cols="12"
            sm="2"> 
          
           <VImg  
                   max-height="25px"  
                   :src="logo"
                   
                   ></VImg> 
                  </VCol>
       </VRow>
       
      
      
      
    </div>
     
  </div>
  
  
</template>
