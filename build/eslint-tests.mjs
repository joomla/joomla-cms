import { defineConfig } from 'eslint/config';
import stylistic from '@stylistic/eslint-plugin';

export default defineConfig([
  stylistic.configs.customize({
    arrowParens: true,
    braceStyle: '1tbs',
    quoteProps: 'as-needed',
    quotes: 'single',
    semi: true,
  }),
  {
    rules: {
      '@stylistic/quotes': ['error', 'single', { avoidEscape: true }],
    },
  },
]);
