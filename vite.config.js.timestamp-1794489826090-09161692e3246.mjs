// vite.config.js
import laravel from "file:///C:/xampp/htdocs/iampos/node_modules/laravel-vite-plugin/dist/index.js";
import { fileURLToPath } from "node:url";
import vue from "file:///C:/xampp/htdocs/iampos/node_modules/@vitejs/plugin-vue/dist/index.mjs";
import vueJsx from "file:///C:/xampp/htdocs/iampos/node_modules/@vitejs/plugin-vue-jsx/dist/index.mjs";
import AutoImport from "file:///C:/xampp/htdocs/iampos/node_modules/unplugin-auto-import/dist/vite.js";
import Components from "file:///C:/xampp/htdocs/iampos/node_modules/unplugin-vue-components/dist/vite.js";
import { defineConfig } from "file:///C:/xampp/htdocs/iampos/node_modules/vite/dist/node/index.js";
import vuetify from "file:///C:/xampp/htdocs/iampos/node_modules/vite-plugin-vuetify/dist/index.mjs";
import svgLoader from "file:///C:/xampp/htdocs/iampos/node_modules/vite-svg-loader/index.js";
var __vite_injected_original_import_meta_url = "file:///C:/xampp/htdocs/iampos/vite.config.js";
var vite_config_default = defineConfig({
  plugins: [
    vue({
      template: {
        transformAssetUrls: {
          base: null,
          includeAbsolute: false
        }
      }
    }),
    vueJsx(),
    laravel({
      input: ["resources/js/main.js"],
      refresh: true
    }),
    // Docs: https://github.com/vuetifyjs/vuetify-loader/tree/master/packages/vite-plugin
    vuetify({
      styles: {
        configFile: "resources/styles/variables/_vuetify.scss"
      }
    }),
    Components({
      dirs: ["resources/js/@core/components", "resources/js/components"],
      dts: true
    }),
    // Docs: https://github.com/antfu/unplugin-auto-import#unplugin-auto-import
    AutoImport({
      imports: ["vue", "vue-router", "@vueuse/core", "@vueuse/math", "pinia"],
      vueTemplate: true,
      // ℹ️ Disabled to avoid confusion & accidental usage
      ignore: ["useCookies", "useStorage"],
      eslintrc: {
        enabled: true,
        filepath: "./.eslintrc-auto-import.json"
      }
    }),
    svgLoader()
  ],
  define: { "process.env": {} },
  resolve: {
    alias: {
      "@core-scss": fileURLToPath(new URL("./resources/styles/@core", __vite_injected_original_import_meta_url)),
      "@": fileURLToPath(new URL("./resources/js", __vite_injected_original_import_meta_url)),
      "@core": fileURLToPath(new URL("./resources/js/@core", __vite_injected_original_import_meta_url)),
      "@layouts": fileURLToPath(new URL("./resources/js/@layouts", __vite_injected_original_import_meta_url)),
      "@images": fileURLToPath(new URL("./resources/images/", __vite_injected_original_import_meta_url)),
      "@styles": fileURLToPath(new URL("./resources/styles/", __vite_injected_original_import_meta_url)),
      "@configured-variables": fileURLToPath(new URL("./resources/styles/variables/_template.scss", __vite_injected_original_import_meta_url))
    }
  },
  build: {
    chunkSizeWarningLimit: 5e3,
    outDir: "public/build"
    // Asegúrate de que los archivos generados se guarden en la carpeta pública de Laravel
    //minify: 'terser',  // Minimizar los archivos para producción
  },
  server: {
    mimeTypes: {
      "application/javascript": [".js"]
    }
  },
  base: "/",
  optimizeDeps: {
    exclude: ["vuetify"],
    entries: [
      "./resources/js/**/*.vue"
    ]
  }
});
export {
  vite_config_default as default
};
//# sourceMappingURL=data:application/json;base64,ewogICJ2ZXJzaW9uIjogMywKICAic291cmNlcyI6IFsidml0ZS5jb25maWcuanMiXSwKICAic291cmNlc0NvbnRlbnQiOiBbImNvbnN0IF9fdml0ZV9pbmplY3RlZF9vcmlnaW5hbF9kaXJuYW1lID0gXCJDOlxcXFx4YW1wcFxcXFxodGRvY3NcXFxcaWFtcG9zXCI7Y29uc3QgX192aXRlX2luamVjdGVkX29yaWdpbmFsX2ZpbGVuYW1lID0gXCJDOlxcXFx4YW1wcFxcXFxodGRvY3NcXFxcaWFtcG9zXFxcXHZpdGUuY29uZmlnLmpzXCI7Y29uc3QgX192aXRlX2luamVjdGVkX29yaWdpbmFsX2ltcG9ydF9tZXRhX3VybCA9IFwiZmlsZTovLy9DOi94YW1wcC9odGRvY3MvaWFtcG9zL3ZpdGUuY29uZmlnLmpzXCI7aW1wb3J0IGxhcmF2ZWwgZnJvbSAnbGFyYXZlbC12aXRlLXBsdWdpbidcbmltcG9ydCB7IGZpbGVVUkxUb1BhdGggfSBmcm9tICdub2RlOnVybCdcbmltcG9ydCB2dWUgZnJvbSAnQHZpdGVqcy9wbHVnaW4tdnVlJ1xuaW1wb3J0IHZ1ZUpzeCBmcm9tICdAdml0ZWpzL3BsdWdpbi12dWUtanN4J1xuaW1wb3J0IEF1dG9JbXBvcnQgZnJvbSAndW5wbHVnaW4tYXV0by1pbXBvcnQvdml0ZSdcbmltcG9ydCBDb21wb25lbnRzIGZyb20gJ3VucGx1Z2luLXZ1ZS1jb21wb25lbnRzL3ZpdGUnXG5pbXBvcnQgeyBkZWZpbmVDb25maWcgfSBmcm9tICd2aXRlJ1xuaW1wb3J0IHZ1ZXRpZnkgZnJvbSAndml0ZS1wbHVnaW4tdnVldGlmeSdcbmltcG9ydCBzdmdMb2FkZXIgZnJvbSAndml0ZS1zdmctbG9hZGVyJ1xuXG4vLyBodHRwczovL3ZpdGVqcy5kZXYvY29uZmlnL1xuZXhwb3J0IGRlZmF1bHQgZGVmaW5lQ29uZmlnKHtcbiAgcGx1Z2luczogW3Z1ZSh7XG4gICAgdGVtcGxhdGU6IHtcbiAgICAgIHRyYW5zZm9ybUFzc2V0VXJsczoge1xuICAgICAgICBiYXNlOiBudWxsLFxuICAgICAgICBpbmNsdWRlQWJzb2x1dGU6IGZhbHNlLFxuICAgICAgfSxcbiAgICB9LFxuICB9KSxcbiAgdnVlSnN4KCksXG4gIGxhcmF2ZWwoe1xuICAgIGlucHV0OiBbJ3Jlc291cmNlcy9qcy9tYWluLmpzJ10sXG4gICAgcmVmcmVzaDogdHJ1ZSxcbiAgfSksIC8vIERvY3M6IGh0dHBzOi8vZ2l0aHViLmNvbS92dWV0aWZ5anMvdnVldGlmeS1sb2FkZXIvdHJlZS9tYXN0ZXIvcGFja2FnZXMvdml0ZS1wbHVnaW5cbiAgdnVldGlmeSh7XG4gICAgc3R5bGVzOiB7XG4gICAgICBjb25maWdGaWxlOiAncmVzb3VyY2VzL3N0eWxlcy92YXJpYWJsZXMvX3Z1ZXRpZnkuc2NzcycsXG4gICAgfSxcbiAgfSksXG4gIENvbXBvbmVudHMoe1xuICAgIGRpcnM6IFsncmVzb3VyY2VzL2pzL0Bjb3JlL2NvbXBvbmVudHMnLCAncmVzb3VyY2VzL2pzL2NvbXBvbmVudHMnXSxcbiAgICBkdHM6IHRydWUsXG4gIH0pLCAvLyBEb2NzOiBodHRwczovL2dpdGh1Yi5jb20vYW50ZnUvdW5wbHVnaW4tYXV0by1pbXBvcnQjdW5wbHVnaW4tYXV0by1pbXBvcnRcbiAgQXV0b0ltcG9ydCh7XG4gICAgaW1wb3J0czogWyd2dWUnLCAndnVlLXJvdXRlcicsICdAdnVldXNlL2NvcmUnLCAnQHZ1ZXVzZS9tYXRoJywgJ3BpbmlhJ10sXG4gICAgdnVlVGVtcGxhdGU6IHRydWUsXG5cbiAgICAvLyBcdTIxMzlcdUZFMEYgRGlzYWJsZWQgdG8gYXZvaWQgY29uZnVzaW9uICYgYWNjaWRlbnRhbCB1c2FnZVxuICAgIGlnbm9yZTogWyd1c2VDb29raWVzJywgJ3VzZVN0b3JhZ2UnXSxcbiAgICBlc2xpbnRyYzoge1xuICAgICAgZW5hYmxlZDogdHJ1ZSxcbiAgICAgIGZpbGVwYXRoOiAnLi8uZXNsaW50cmMtYXV0by1pbXBvcnQuanNvbicsXG4gICAgfSxcbiAgfSksXG4gIHN2Z0xvYWRlcigpXSxcbiAgZGVmaW5lOiB7ICdwcm9jZXNzLmVudic6IHt9IH0sXG4gIHJlc29sdmU6IHtcbiAgICBhbGlhczoge1xuICAgICAgJ0Bjb3JlLXNjc3MnOiBmaWxlVVJMVG9QYXRoKG5ldyBVUkwoJy4vcmVzb3VyY2VzL3N0eWxlcy9AY29yZScsIGltcG9ydC5tZXRhLnVybCkpLFxuICAgICAgJ0AnOiBmaWxlVVJMVG9QYXRoKG5ldyBVUkwoJy4vcmVzb3VyY2VzL2pzJywgaW1wb3J0Lm1ldGEudXJsKSksXG4gICAgICAnQGNvcmUnOiBmaWxlVVJMVG9QYXRoKG5ldyBVUkwoJy4vcmVzb3VyY2VzL2pzL0Bjb3JlJywgaW1wb3J0Lm1ldGEudXJsKSksXG4gICAgICAnQGxheW91dHMnOiBmaWxlVVJMVG9QYXRoKG5ldyBVUkwoJy4vcmVzb3VyY2VzL2pzL0BsYXlvdXRzJywgaW1wb3J0Lm1ldGEudXJsKSksXG4gICAgICAnQGltYWdlcyc6IGZpbGVVUkxUb1BhdGgobmV3IFVSTCgnLi9yZXNvdXJjZXMvaW1hZ2VzLycsIGltcG9ydC5tZXRhLnVybCkpLFxuICAgICAgJ0BzdHlsZXMnOiBmaWxlVVJMVG9QYXRoKG5ldyBVUkwoJy4vcmVzb3VyY2VzL3N0eWxlcy8nLCBpbXBvcnQubWV0YS51cmwpKSxcbiAgICAgICdAY29uZmlndXJlZC12YXJpYWJsZXMnOiBmaWxlVVJMVG9QYXRoKG5ldyBVUkwoJy4vcmVzb3VyY2VzL3N0eWxlcy92YXJpYWJsZXMvX3RlbXBsYXRlLnNjc3MnLCBpbXBvcnQubWV0YS51cmwpKSxcbiAgICB9LFxuICB9LFxuICBidWlsZDoge1xuICAgIGNodW5rU2l6ZVdhcm5pbmdMaW1pdDogNTAwMCxcbiAgICBvdXREaXI6ICdwdWJsaWMvYnVpbGQnLCAgLy8gQXNlZ1x1MDBGQXJhdGUgZGUgcXVlIGxvcyBhcmNoaXZvcyBnZW5lcmFkb3Mgc2UgZ3VhcmRlbiBlbiBsYSBjYXJwZXRhIHBcdTAwRkFibGljYSBkZSBMYXJhdmVsXG4gICAgLy9taW5pZnk6ICd0ZXJzZXInLCAgLy8gTWluaW1pemFyIGxvcyBhcmNoaXZvcyBwYXJhIHByb2R1Y2NpXHUwMEYzblxuICB9LFxuICBzZXJ2ZXI6IHtcbiAgICBtaW1lVHlwZXM6IHtcbiAgICAgICdhcHBsaWNhdGlvbi9qYXZhc2NyaXB0JzogWycuanMnXSxcbiAgICB9LFxuICB9LFxuICBiYXNlOiAnLycsXG4gIG9wdGltaXplRGVwczoge1xuICAgIGV4Y2x1ZGU6IFsndnVldGlmeSddLFxuICAgIGVudHJpZXM6IFtcbiAgICAgICcuL3Jlc291cmNlcy9qcy8qKi8qLnZ1ZScsXG4gICAgXSxcbiAgfSxcbn0pIl0sCiAgIm1hcHBpbmdzIjogIjtBQUE0UCxPQUFPLGFBQWE7QUFDaFIsU0FBUyxxQkFBcUI7QUFDOUIsT0FBTyxTQUFTO0FBQ2hCLE9BQU8sWUFBWTtBQUNuQixPQUFPLGdCQUFnQjtBQUN2QixPQUFPLGdCQUFnQjtBQUN2QixTQUFTLG9CQUFvQjtBQUM3QixPQUFPLGFBQWE7QUFDcEIsT0FBTyxlQUFlO0FBUnFJLElBQU0sMkNBQTJDO0FBVzVNLElBQU8sc0JBQVEsYUFBYTtBQUFBLEVBQzFCLFNBQVM7QUFBQSxJQUFDLElBQUk7QUFBQSxNQUNaLFVBQVU7QUFBQSxRQUNSLG9CQUFvQjtBQUFBLFVBQ2xCLE1BQU07QUFBQSxVQUNOLGlCQUFpQjtBQUFBLFFBQ25CO0FBQUEsTUFDRjtBQUFBLElBQ0YsQ0FBQztBQUFBLElBQ0QsT0FBTztBQUFBLElBQ1AsUUFBUTtBQUFBLE1BQ04sT0FBTyxDQUFDLHNCQUFzQjtBQUFBLE1BQzlCLFNBQVM7QUFBQSxJQUNYLENBQUM7QUFBQTtBQUFBLElBQ0QsUUFBUTtBQUFBLE1BQ04sUUFBUTtBQUFBLFFBQ04sWUFBWTtBQUFBLE1BQ2Q7QUFBQSxJQUNGLENBQUM7QUFBQSxJQUNELFdBQVc7QUFBQSxNQUNULE1BQU0sQ0FBQyxpQ0FBaUMseUJBQXlCO0FBQUEsTUFDakUsS0FBSztBQUFBLElBQ1AsQ0FBQztBQUFBO0FBQUEsSUFDRCxXQUFXO0FBQUEsTUFDVCxTQUFTLENBQUMsT0FBTyxjQUFjLGdCQUFnQixnQkFBZ0IsT0FBTztBQUFBLE1BQ3RFLGFBQWE7QUFBQTtBQUFBLE1BR2IsUUFBUSxDQUFDLGNBQWMsWUFBWTtBQUFBLE1BQ25DLFVBQVU7QUFBQSxRQUNSLFNBQVM7QUFBQSxRQUNULFVBQVU7QUFBQSxNQUNaO0FBQUEsSUFDRixDQUFDO0FBQUEsSUFDRCxVQUFVO0FBQUEsRUFBQztBQUFBLEVBQ1gsUUFBUSxFQUFFLGVBQWUsQ0FBQyxFQUFFO0FBQUEsRUFDNUIsU0FBUztBQUFBLElBQ1AsT0FBTztBQUFBLE1BQ0wsY0FBYyxjQUFjLElBQUksSUFBSSw0QkFBNEIsd0NBQWUsQ0FBQztBQUFBLE1BQ2hGLEtBQUssY0FBYyxJQUFJLElBQUksa0JBQWtCLHdDQUFlLENBQUM7QUFBQSxNQUM3RCxTQUFTLGNBQWMsSUFBSSxJQUFJLHdCQUF3Qix3Q0FBZSxDQUFDO0FBQUEsTUFDdkUsWUFBWSxjQUFjLElBQUksSUFBSSwyQkFBMkIsd0NBQWUsQ0FBQztBQUFBLE1BQzdFLFdBQVcsY0FBYyxJQUFJLElBQUksdUJBQXVCLHdDQUFlLENBQUM7QUFBQSxNQUN4RSxXQUFXLGNBQWMsSUFBSSxJQUFJLHVCQUF1Qix3Q0FBZSxDQUFDO0FBQUEsTUFDeEUseUJBQXlCLGNBQWMsSUFBSSxJQUFJLCtDQUErQyx3Q0FBZSxDQUFDO0FBQUEsSUFDaEg7QUFBQSxFQUNGO0FBQUEsRUFDQSxPQUFPO0FBQUEsSUFDTCx1QkFBdUI7QUFBQSxJQUN2QixRQUFRO0FBQUE7QUFBQTtBQUFBLEVBRVY7QUFBQSxFQUNBLFFBQVE7QUFBQSxJQUNOLFdBQVc7QUFBQSxNQUNULDBCQUEwQixDQUFDLEtBQUs7QUFBQSxJQUNsQztBQUFBLEVBQ0Y7QUFBQSxFQUNBLE1BQU07QUFBQSxFQUNOLGNBQWM7QUFBQSxJQUNaLFNBQVMsQ0FBQyxTQUFTO0FBQUEsSUFDbkIsU0FBUztBQUFBLE1BQ1A7QUFBQSxJQUNGO0FBQUEsRUFDRjtBQUNGLENBQUM7IiwKICAibmFtZXMiOiBbXQp9Cg==
