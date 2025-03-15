<script setup>
import logoMinSeg from '@images/logos/min_seg_logo.jpg'
</script>
<template>
<div>
  <div class="h-100 d-flex align-center justify-space-between">            
      <!-- 👉 Footer: left content -->
      <span class="d-flex align-center">
        &copy;
        {{ new Date().getFullYear() }}
        SiCoPol v2.0.0|
        <!--
        <VIcon
          icon="ri-heart-line"
          color="error"
          size="1.25rem"
          class="mx-1"
        />-->
        <a
          href="https://www.argentina.gob.ar/psa"
          target="_blank"
          rel="noopener noreferrer"
          class="text-primary ms-1"
        >Policía de Seguridad Aeroportuaria</a>
      </span>
      <!-- 👉 Footer: right content -->      
      <v-img  class="gap-x-4 text-primary ml-auto"
                    max-height="200px" 
                    max-width="200px"
                    :src="logoMinSeg"
                    
                    ></v-img>
      
      
      
    </div>
    <div >
    
    </div>
  </div>
  
  
</template>
