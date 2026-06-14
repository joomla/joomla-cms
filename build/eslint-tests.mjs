import { defineConfig } from 'eslint/config';
import cypressPlugin from 'eslint-plugin-cypress';
import stylistic from '@stylistic/eslint-plugin';

export default defineConfig([
  stylistic.configs.customize({
    arrowParens: true,
    braceStyle: '1tbs',
    quoteProps: 'as-needed',
    quotes: 'single',
    semi: true,
  }),
  cypressPlugin.configs.recommended,
  {
    rules: {
      '@stylistic/quotes': ['error', 'single', { avoidEscape: true }],
      'cypress/unsafe-to-chain-command': 'off',
    },
  },
]);
