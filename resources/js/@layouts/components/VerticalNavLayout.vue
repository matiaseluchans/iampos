<script>
import { useDisplay } from 'vuetify'
import VerticalNav from '@layouts/components/VerticalNav.vue'

export default defineComponent({
  setup(props, { slots }) {
    const isOverlayNavActive = ref(false)
    const isLayoutOverlayVisible = ref(false)
    const isCollapsed = ref(true) // Inicialmente colapsado
    const isMenuPinned = ref(false) // Nuevo estado para controlar si el menú está fijado

    const toggleIsOverlayNavActive = useToggle(isOverlayNavActive)
    const route = useRoute()
    const { mdAndDown, lgAndUp } = useDisplay()

    // Función para alternar el estado de fijado del menú
    const toggleMenuPin = () => {
      isMenuPinned.value = !isMenuPinned.value
      if(isMenuPinned.value)
      {
        isCollapsed.value = 0
      }
      else
      {
        isCollapsed.value = 1
      }
    }

    // Colapsar automáticamente en pantallas grandes
    onMounted(() => {
      isCollapsed.value = lgAndUp.value && !isMenuPinned.value
    })

    // Observar cambios en el tamaño de pantalla
    watch(lgAndUp, (newVal) => {
      if (!isMenuPinned.value) {
        isCollapsed.value = newVal
      }
      if (!newVal) {
        isOverlayNavActive.value = false
      }
    })
    watch(isOverlayNavActive, (newVal) => {
      if (mdAndDown.value) {
        isLayoutOverlayVisible.value = newVal
      }
    })

    syncRef(isOverlayNavActive, isLayoutOverlayVisible)
    
    
    return () => {
      // Prepara todas las props que necesitan los slots
      const navbarSlotProps = {
        toggleVerticalOverlayNavActive: toggleIsOverlayNavActive,
        isMenuPinned: isMenuPinned.value, // Pasar el valor, no la ref
        toggleMenuPin,
      };

      const navHeaderSlotProps = {
        toggleIsOverlayNavActive,
        isMenuPinned: isMenuPinned.value,
        toggleMenuPin,
      };

      const verticalNav = h(VerticalNav, { 
        isOverlayNavActive: isOverlayNavActive.value,
        toggleIsOverlayNavActive,
        isCollapsed: isCollapsed.value,
        isMenuPinned: isMenuPinned.value,
        toggleMenuPin,
        onMouseenter: () => !isMenuPinned.value && (isCollapsed.value = false),
        onMouseleave: () => !isMenuPinned.value && (isCollapsed.value = lgAndUp.value)
      }, {
        'nav-header': () => slots['vertical-nav-header']?.(navHeaderSlotProps),
        'before-nav-items': () => slots['before-vertical-nav-items']?.(),
        'default': () => slots['vertical-nav-content']?.(),
        'after-nav-items': () => slots['after-vertical-nav-items']?.(),
      });

      return h('div', {
        class: [
          'layout-wrapper layout-nav-type-vertical layout-navbar-static layout-footer-static layout-content-width-fluid',
          mdAndDown.value && 'layout-overlay-nav',
          isCollapsed.value && 'layout-vertical-nav-collapsed',
          route.meta.layoutWrapperClasses,
        ],
      }, [
        verticalNav,
        h('div', { class: 'layout-content-wrapper' }, [
          h('header', { class: ['layout-navbar navbar-blur'] }, [
            h('div', { class: 'navbar-content-container' }, slots.navbar?.(navbarSlotProps)),
          ]),
          h('main', { class: 'layout-page-content' }, h('div', { class: 'page-content-container' }, slots.default?.())),
          h('footer', { class: 'layout-footer' }, [
            h('div', { class: 'footer-content-container' }, slots.footer?.()),
          ]),
        ]),
        h('div', {
          class: ['layout-overlay', { visible: isLayoutOverlayVisible.value }],
          onClick: () => { isLayoutOverlayVisible.value = !isLayoutOverlayVisible.value },
        }),
      ]);
    };
  },
});
</script>

<style lang="scss">
@use "@configured-variables" as variables;
@use "@layouts/styles/placeholders";
@use "@layouts/styles/mixins";

.layout-wrapper.layout-nav-type-vertical {
  block-size: 100%;

  // Estilos para el menú colapsado
  &.layout-vertical-nav-collapsed {
    .vertical-nav {
      width: variables.$layout-vertical-nav-collapsed-width !important;
      overflow: hidden;
      transition: width 0.2s ease-in-out;
      
      &:hover {
        width: variables.$layout-vertical-nav-width !important;
        
        .nav-item-title,
        .nav-item-badge,
        .app-logo-title {
          opacity: 1;
          transition: opacity 0.2s ease-in-out;
        }
        
        .nav-link {
          justify-content: flex-start !important;
        }
      }
      
      .nav-item-title,
      .nav-item-badge,
      .app-logo-title {
        opacity: 0;
        width: 0;
        height: 0;
        overflow: hidden;
        position: absolute;
        transition: opacity 0.2s ease-in-out;
      }
      
      .nav-link {
        justify-content: center !important;
        padding-inline: 0.5rem !important;
      }
      
      .vertical-nav-header {
        padding-inline: 0.5rem !important;
        justify-content: center !important;
      }
    }
    
    .layout-content-wrapper {
      padding-inline-start: variables.$layout-vertical-nav-collapsed-width !important;
      transition: padding-inline-start 0.2s ease-in-out;
    }
    
    &:hover {
      .layout-content-wrapper {
       /* padding-inline-start: variables.$layout-vertical-nav-width !important;*/
      }
    }
  }

  // Estilos base del content wrapper
  .layout-content-wrapper {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    min-block-size: 100dvh;
    transition: padding-inline-start 0.2s ease-in-out;
    will-change: padding-inline-start;

    @media screen and (min-width: 1280px) {
      padding-inline-start: variables.$layout-vertical-nav-width;
    }
  }

  // Resto de tus estilos existentes...
  .layout-navbar {
    z-index: variables.$layout-vertical-nav-layout-navbar-z-index;

    .navbar-content-container {
      block-size: variables.$layout-vertical-nav-navbar-height;
    }
  }

  // Estilos para móvil
  @media (max-width: 1279px) {
    &.layout-overlay-nav {
      .vertical-nav {
        position: fixed;
        z-index: variables.$layout-vertical-nav-z-index;
        inset-block: 0;
        inset-inline-start: 0;
        transform: translateX(-100%);
        transition: transform 0.25s ease-in-out;
        
        &.active {
          transform: translateX(0);
        }
      }
      
      .layout-overlay {
        position: fixed;
        z-index: variables.$layout-overlay-z-index;
        background-color: rgb(0 0 0 / 60%);
        cursor: pointer;
        inset: 0;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.25s ease-in-out;
        
        &.visible {
          opacity: 1;
          pointer-events: auto;
        }
      }
    }
  }

  // Estilos base del content wrapper
  .layout-content-wrapper {
    display: flex;
    flex-direction: column;
    flex-grow: 1;
    min-block-size: 100dvh;
    transition: padding-inline-start 0.2s ease-in-out;
    will-change: padding-inline-start;

    @media (min-width: 1280px) {
      padding-inline-start: variables.$layout-vertical-nav-width;
    }
  }

  // Resto de tus estilos existentes...
  .layout-navbar {
    z-index: variables.$layout-vertical-nav-layout-navbar-z-index;

    .navbar-content-container {
      block-size: variables.$layout-vertical-nav-navbar-height;
    }
  }
}
</style>
