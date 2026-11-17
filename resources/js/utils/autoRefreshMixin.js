// Mixin: ejecuta `refreshFn(vm)` cada INTERVAL ms solo si no hubo interacción humana
// (mouse, teclado, scroll, touch) durante ese tiempo. Se pausa si la pestaña está oculta,
// si hay un diálogo/alerta abierto o si la pantalla ya está cargando.
import LastUpdated from "@/components/LastUpdated.vue";

const IDLE_MS = 2 * 60 * 1000;
const CHECK_MS = 15 * 1000;
const EVENTS = ["mousemove", "mousedown", "keydown", "wheel", "touchstart", "scroll"];

// options.watch: propiedad de data que guarda los datos mostrados; cada vez que se reemplaza
// se considera "actualizado" (lastUpdatedAt). Sin watch, usar this.markUpdated() a mano.
export default function autoRefreshMixin(refreshFn, { watch, idleMs = IDLE_MS } = {}) {
  return {
    components: { LastUpdated },

    data() {
      return { lastUpdatedAt: null };
    },

    created() {
      if (watch) this.$watch(watch, () => this.markUpdated());
    },

    methods: {
      markUpdated() {
        this.lastUpdatedAt = Date.now();
      },
    },

    mounted() {
      this._lastActivity = Date.now();
      this._onActivity = () => {
        this._lastActivity = Date.now();
      };
      EVENTS.forEach((e) =>
        window.addEventListener(e, this._onActivity, { passive: true, capture: true })
      );

      this._autoRefreshTimer = setInterval(async () => {
        if (document.hidden) return;
        if (Date.now() - this._lastActivity < idleMs) return;
        if (this.loading || this.refreshing || this.refreshingCatalog) return;
        if (document.querySelector(".v-dialog.v-overlay--active, .swal2-container")) return;

        // Se reinicia el contador: la próxima actualización será tras otros idleMs sin actividad
        this._lastActivity = Date.now();
        try {
          await refreshFn(this);
        } catch (e) {
          console.error("Auto-actualización fallida:", e);
        }
      }, CHECK_MS);
    },

    beforeUnmount() {
      clearInterval(this._autoRefreshTimer);
      EVENTS.forEach((e) =>
        window.removeEventListener(e, this._onActivity, { capture: true })
      );
    },
  };
}
