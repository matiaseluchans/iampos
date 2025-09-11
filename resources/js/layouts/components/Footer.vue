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
            
          <span class="mt-0 pt-0  align-center">
                  <small > 
                  <strong class="ml-2">TAG:</strong> {{ version.tag}} - <strong>MSG:</strong>{{ version.mensaje_tag}} - <strong>AUTOR:</strong>{{ cleanAuthor(version.tag_autor)}} <br>
                  <strong class="ml-2">COMMIT:</strong> {{ version.commit?.slice(-7) }} - <strong>MSG:</strong>{{ version.mensaje_commit }} - <strong>AUTOR:</strong>{{ cleanAuthor(version.commit_autor)}}</small>
          
                </span>
          </VCol>
          <VCol
            cols="12"
            sm="2" class="text-end pr-2"> 
          
           <!--<VImg  
                   max-height="25px"  
                   :src="logo"
                   
                   ></VImg> -->
              <h1 class="font-weight-black leading-normal text-h5 text-uppercase logo-color-text" style="display:inline">
               IA 
              </h1>
              <svg width="18" viewBox="0 0 30 24" xmlns="http://www.w3.org/2000/svg "  style="display:inline">
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
              <h1 class="font-weight-black leading-normal text-h5 text-uppercase logo-color-text " style="display:inline">
              POS 
              </h1> 
          </VCol>
       </VRow>
       
      
      
      
    </div>
     
  </div>
  
  
</template>
