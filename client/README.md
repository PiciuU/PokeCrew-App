# PokeCrew Application

PokeCrew website utilizes Vue as a Single Page Application (SPA) for its frontend, communicating seamlessly with the API. Configuration options in the frontend include:

### Configuration

Apart from the default settings, the following additional configurations are defined in `env.development` and `env.production` files:

- **VITE_APP_API_URL**: The address where the API is accessible.

If you encounter issues with CORS during development, it is recommended to use the [Allow CORS: Access-Control-Allow-Origin](https://chromewebstore.google.com/detail/allow-cors-access-control/lhobafahddgcelffkeicbaginigeejlf) browser extension.

## Customize configuration

See [Vite Configuration Reference](https://vitejs.dev/config/).

## Project Setup

```sh
npm install
```

### Compile and Hot-Reload for Development

```sh
npm run dev
```

### Compile and Minify for Production

```sh
npm run build
```

### Lint with [ESLint](https://eslint.org/)

```sh
npm run lint
```