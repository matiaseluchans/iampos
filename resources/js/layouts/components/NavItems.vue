<script setup>
import VerticalNavSectionTitle from '@/@layouts/components/VerticalNavSectionTitle.vue'
import VerticalNavGroup from '@layouts/components/VerticalNavGroup.vue'
import VerticalNavLink from '@layouts/components/VerticalNavLink.vue'
</script>

<template>
<div>
  <!-- 👉 Dashboards -->
  <VerticalNavLink
    :item="{
      title: 'Dashboard',      
      icon: 'ri-home-smile-line',
       to: '/dashboard',
    }"
  />  

<!--
  <VerticalNavSectionTitle
    :item="{
      heading: 'Administracion General',
    }"
  />-->
  <VerticalNavGroup
    :item="{
      title: 'Administración',
      icon: 'ri-tools-line',      
    }"
  >
  <!--
  <VerticalNavLink
      :item="{
        title: 'Companias',
        to: '/companies',        
      }"
    />
    -->
    <VerticalNavLink
      :item="{
        title: 'Clientes',
        to: '/customers',        
        icon: 'ri-shopping-bag-2-fill', 
      }"
    />
    <VerticalNavLink
      :item="{
        title: 'Tenants',
        to: '/tenants',        
        icon: 'ri-shopping-bag-2-fill', 
      }"
    />
    

    
    <VerticalNavSectionTitle
    :item="{
      heading: 'Productos',
    }"
  />
    <VerticalNavLink
      :item="{
        title: 'Productos',
        to: '/products',        
      }"
    />
    <VerticalNavLink
      :item="{
        title: 'Categorias',
        to: '/categories',        
      }"
    />
    <VerticalNavLink
      :item="{
        title: 'Marcas',
        to: '/brands',        
      }"
    />
    
  </VerticalNavGroup>

 <!--
   <VerticalNavSectionTitle
    :item="{
      heading: 'Psa Partes',
    }"
  />
  <VerticalNavGroup
    :item="{
      title: 'PSA - Partes',
      icon: 'ri-file-copy-line'      
    }"
  >
    <VerticalNavLink
      :item="{
        title: 'Nuevo Parte',
        to: '/partes'              
      }"
    />
    <VerticalNavLink
      :item="{
        title: 'Recibidos',
        to: '/partes_status',        
      }"
    />
    <VerticalNavLink
      :item="{
        title: 'Enviados',
        href: '',        
      }"
    />
    <VerticalNavLink
      :item="{
        title: 'Finalizados',
        href: '',        
      }"
    />
    <VerticalNavLink
      :item="{
        title: 'Eliminados',
        href: '',        
      }"
    />


    <VerticalNavLink
      :item="{
        title: 'Clasificaciones',
        to: '/clasificaciones',        
      }"
    />
    <VerticalNavLink
      :item="{
        title: 'Estados',
        to: '/estados',        
      }"
    />
    <VerticalNavLink
      :item="{
        title: 'Funciones',
        to: '/funciones',        
      }"
    /> 
 
  </VerticalNavGroup>

  <VerticalNavSectionTitle
    :item="{
      heading: 'Búsquedas y Consultas',
    }"
  />
  <VerticalNavGroup
    :item="{
      title: 'Consultas',
      icon: 'ri-file-search-line',      
    }"
  >
    <VerticalNavLink
      :item="{
        title: 'Partes',
        href: '',        
      }"
    />    
  </VerticalNavGroup>
-->
 
  <!-- 👉 Apps & Pages -->
  <VerticalNavSectionTitle
    :item="{
      heading: 'Apps & Pages',
    }"
  />
  <VerticalNavLink
    :item="{
      title: 'Permissions',
      icon: 'ri-lock-2-line',
      href: 'https://demos.themeselection.com/materio-vuetify-vuejs-laravel-admin-template/demo-1/apps/permissions',
      target: '_blank',
      badgeContent: 'Pro',
      badgeClass: 'bg-light-primary text-primary',
    }"
  />
  <VerticalNavLink
    :item="{
      title: 'Email',
      icon: 'ri-mail-line',
      href: 'https://demos.themeselection.com/materio-vuetify-vuejs-laravel-admin-template/demo-1/apps/email',
      target: '_blank',
      badgeContent: 'Pro',
      badgeClass: 'bg-light-primary text-primary',
    }"
  />
  <VerticalNavLink
    :item="{
      title: 'Chat',
      icon: 'ri-wechat-line',
      href: 'https://demos.themeselection.com/materio-vuetify-vuejs-laravel-admin-template/demo-1/apps/chat',
      target: '_blank',
      badgeContent: 'Pro',
      badgeClass: 'bg-light-primary text-primary',
    }"
  />
  <VerticalNavLink
    :item="{
      title: 'Calendar',
      icon: 'ri-calendar-line',
      href: 'https://demos.themeselection.com/materio-vuetify-vuejs-laravel-admin-template/demo-1/apps/calendar',
      target: '_blank',
      badgeContent: 'Pro',
      badgeClass: 'bg-light-primary text-primary',
    }"
  />

  <VerticalNavLink
    :item="{
      title: 'Account Settings',
      icon: 'ri-user-settings-line',
      to: '/account-settings',
    }"
  />

  <VerticalNavLink
    :item="{
      title: 'Login',
      icon: 'ri-login-box-line',
      to: '/login',
    }"
  />
  <VerticalNavLink
    :item="{
      title: 'Register',
      icon: 'ri-user-add-line',
      to: '/register',
    }"
  />
  <VerticalNavLink
    :item="{
      title: 'Error',
      icon: 'ri-information-line',
      to: '/no-existence',
    }"
  />

  <!-- 👉 User Interface -->
  <VerticalNavSectionTitle
    :item="{
      heading: 'User Interface',
    }"
  />
  <VerticalNavLink
    :item="{
      title: 'Typography',
      icon: 'ri-text',
      to: '/typography',
    }"
  />
  <VerticalNavLink
    :item="{
      title: 'Icons',
      icon: 'ri-remixicon-line',
      to: '/icons',
    }"
  />
  <VerticalNavLink
    :item="{
      title: 'Cards',
      icon: 'ri-bar-chart-box-line',
      to: '/cards',
    }"
  />

  <!-- 👉 Forms & Tables -->
  <VerticalNavSectionTitle
    :item="{
      heading: 'Forms & Tables',
    }"
  />
  <VerticalNavLink
    :item="{
      title: 'Form Layouts',
      icon: 'ri-layout-4-line',
      to: '/form-layouts',
    }"
  />
  <VerticalNavLink
    :item="{
      title: 'Form Validation',
      icon: 'ri-checkbox-multiple-line',
      href: 'https://demos.themeselection.com/materio-vuetify-vuejs-laravel-admin-template/demo-1/forms/form-validation',
      target: '_blank',
      badgeContent: 'Pro',
      badgeClass: 'bg-light-primary text-primary',
    }"
  />
  <VerticalNavLink
    :item="{
      title: 'Form Wizard',
      icon: 'ri-git-commit-line',
      href: 'https://demos.themeselection.com/materio-vuetify-vuejs-laravel-admin-template/demo-1/forms/form-wizard-numbered',
      target: '_blank',
      badgeContent: 'Pro',
      badgeClass: 'bg-light-primary text-primary',
    }"
  />
  <VerticalNavLink
    :item="{
      title: 'Tables',
      icon: 'ri-table-alt-line',
      to: '/tables',
    }"
  />
  <VerticalNavLink
    :item="{
      title: 'DataTables',
      icon: 'ri-table-alt-line',
      to: '/datatables',
    }"
  />

  <!-- 👉 Others -->
  <VerticalNavSectionTitle
    :item="{
      heading: 'Others',
    }"
  />
  <VerticalNavLink
    :item="{
      title: 'Access Control',
      icon: 'ri-shield-line',
      href: 'https://demos.themeselection.com/materio-vuetify-vuejs-laravel-admin-template/demo-1/access-control',
      target: '_blank',
      badgeContent: 'Pro',
      badgeClass: 'bg-light-primary text-primary',
    }"
  />
  <VerticalNavLink
    :item="{
      title: 'Documentation',
      icon: 'ri-article-line',
      href: 'https://demos.themeselection.com/materio-vuetify-vuejs-admin-template/documentation/guide/laravel-integration/folder-structure.html',
      target: '_blank',
    }"
  />
  <VerticalNavLink
    :item="{
      title: 'Raise Support',
      href: 'https://github.com/themeselection/materio-vuetify-vuejs-laravel-admin-template-free/issues',
      icon: 'ri-lifebuoy-line',
      target: '_blank',
    }"
  />


  </div>

</template>
