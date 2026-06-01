import react from "@vitejs/plugin-react";
import laravel from "laravel-vite-plugin";
import { defineConfig } from "vite";

export default defineConfig(() => {
  return {
    base: "/",

    server: {
      host: "localhost",
      hmr: {
        protocol: "ws",
        host: "localhost",
      },
      watch: {
        usePolling: true,
      },
    },

    plugins: [
      laravel({
        input: [
          "src/css/app.css",
          "src/js/app.js",
          "src/upholstery-previz/upholstery-previz.tsx",
        ],
        refresh: ["**/*.php"],
      }),

      react(),
    ],

    build: {
      sourcemap: true,
    },
  };
});
