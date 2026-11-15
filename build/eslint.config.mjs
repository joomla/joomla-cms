import { defineConfig } from 'eslint/config';
import stylistic from '@stylistic/eslint-plugin';
import vue from 'eslint-plugin-vue';

export default defineConfig([
  stylistic.configs.customize({
    arrowParens: true,
    braceStyle: '1tbs',
    quoteProps: 'as-needed',
    quotes: 'single',
    semi: true,
  }),
  ...vue.configs['flat/recommended'],
  {
    ignores: [
      '**/*.es5.js',
    ],
  },
  {
    rules: {
      '@stylistic/quotes': ['error', 'single', { avoidEscape: true }],
      '@stylistic/max-statements-per-line': 'off',
    },
  },
]);
