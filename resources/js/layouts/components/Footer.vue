<script setup>
import logo from '@images/logos/logo.png'
</script>
<template>
<div>
  <div class="h-100 d-flex align-center justify-space-between  border-t-sm	pt-4">            
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
            sm="2"><VRow no-gutters class=" ">
          <VCol
            cols="12"
            sm="8"> 
            <h3 class=" text-end">IA</h3>

          </VCol>
          <VCol
            cols="12"
            sm="2"> <VImg  
                   max-height="55px" 
                   max-width="55px"
                   :src="logo"
                   
                   ></VImg></VCol>
          <VCol
            cols="12"
            sm="1">                  
            <h3 class=" ">POS</h3>
</VCol>
        </VRow></VCol>
       </VRow>
       
      
      
      
    </div>
     
  </div>
  
  
</template>
