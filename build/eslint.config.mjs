// eslint.config.js
import { defineConfig } from 'eslint/config';

export default defineConfig([
	// matches all files ending with .js
	{
		files: [
			'build/**/*.js',
			'build/**/*.mjs',
			'build/**/*.es6',
			'build/**/*.vue',
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
