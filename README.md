## Initialize Plugin Folder with Vite

1. npm init -y
2. npm install --save-dev typescript
3. npm install --save-dev @types/node
4. (optional) npx tsc --init

## Install Vite and Laravel Vite

1. npm install -D vite
2. npm install -D laravel-vite-plugin

## Setup Laravel Vite config file

```
import laravel from "laravel-vite-plugin";

import { defineConfig } from "vite";

export default defineConfig(() => {
  return {
    base: "/",
    plugins: [
      laravel({
        input: ["src/css/app.css", "src/css/editor.css"],
        refresh: ["**/*.php"],
      }),
    ],
    build: {
      outDir: "dist",
    },
  };
});

```

## Create /scr folder with all the css/js files

## Initialize Plugin Folder with Composer

1. composer init
