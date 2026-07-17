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
    },

    plugins: [
      laravel({
        input: ["src/css/app.css", "src/js/app.js"],
        refresh: ["**/*.php"],
      }),

      react(),
    ],

    build: {
      sourcemap: true,
    },
  };
});
