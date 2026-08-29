import { defineConfig } from 'eslint/config';
import vue from 'eslint-plugin-vue';

export default defineConfig([
  ...vue.configs['flat/recommended'],
  {
    files: [
      'build/**/*.js',
      'build/**/*.mjs',
      'build/**/*.es6',
      'build/**/*.vue',
    ],
  },
]);
