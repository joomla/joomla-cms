import { defineConfig } from 'eslint/config';

export default defineConfig([
  {
    files: [
      'tests/**/*.js',
      'tests/**/*.mjs',
      'tests/**/*.es6',
      'tests/**/*.vue',
    ],
  },
]);
