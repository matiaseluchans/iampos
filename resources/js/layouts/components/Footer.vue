<script setup>
import logo from '@images/logos/logo.png'
</script>
<template>
<div>
  <div class="h-100 d-flex align-center justify-space-between  border-t-sm	pt-3">            
      <!-- 👉 Footer: left content -->
       <VRow no-gutters class="">
        <VCol
            cols="12"
            sm="10"> 
            
          <span class="d-flex align-center">
                  &copy;
                  {{ new Date().getFullYear() }}
                  IAMPOS v2.0.0| 
                </span>
          </VCol>
          <VCol
            cols="12"
            sm="2"> 
          
           <VImg  
                   max-height="35px"  
                   :src="logo"
                   
                   ></VImg> 
                  </VCol>
       </VRow>
       
      
      
      
    </div>
     
  </div>
  
  
</template>
