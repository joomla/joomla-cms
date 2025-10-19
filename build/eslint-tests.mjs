// eslint.config.js
import { defineConfig } from 'eslint/config';

export default defineConfig([
	// matches all files ending with .js
	{
		files: [
			'tests/**/*.js',
			'tests/**/*.mjs',
			'tests/**/*.es6',
			'tests/**/*.vue',
		],
		rules: {
			'no-restricted-globals': 'error',
		},
		languageOptions: {
			globals: {
				Joomla: true,
				MediaManager: true,
				bootstrap: true,
			},
		},
	},
]);

