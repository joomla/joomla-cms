/**
 * Command line helper
 *
 * For maintainers, please run:
 * node build.js --installer
 * node build.js --update
 * node build.js --compilejs
 * node build.js --compilecss
 * Before making any PRs or building any package!
 *
 */
const Program = require('commander');
const Chalk = require('chalk');

// Joomla Build modules
const installer = require('./build/build-modules-js/installation.js');
const update = require('./build/build-modules-js/update.js');
const css = require('./build/build-modules-js/compilescss.js');
const Js = require('./build/build-modules-js/compilejs.js');
const CEscss = require('./build/build-modules-js/compilecescss.js');
const CEjs = require('./build/build-modules-js/compilecejs.js');

// The settings
const options = require('./package.json');

// Initialize CLI
Program
	.version(options.version)
	.option('--update', 'Updates the vendor scripts')
	.option('--compilejs, --compilejs path', 'Compiles ES6 to ES5 scripts')
	.option('--compilecss, --compilecss path', 'Compiles all the scss files to css')
	.option('--compilecejs, --compilecejs path', 'Compiles/traspiles all the custom elements files')
	.option('--compilecescss, --compilecescss path', 'Compiles/traspiles all the custom elements files')
	.option('--watch, --watch path', 'Watch file changes and re-compile (Only work for compilecss and compilejs now).')
	.option('--installer', 'Creates the language file for installer error page')
	.on('--help', () => {
		console.log(Chalk.cyan('\n  Version %s\n'), options.version);
		process.exit(0);
	})
	.parse(process.argv);


// Show help by default
if (!process.argv.slice(2).length) {
	Program.outputHelp();
	process.exit(1);
}

// Update the vendor folder
if (Program.update) {
	Promise.resolve()
		.then(update.update(options))

		// Exit with success
		.then(() => process.exit(0))

		// Handle errors
		.catch((err) => {
			console.error(Chalk.red(err));
			process.exit(-1);
		});
}

// Create the languages file for the error page on the installer
if (Program.installer) {
	installer.installation()
}

// Convert scss to css
if (Program['compilecss']) {
	if (Program['watch']) {
		css.watch(options, null, true);
	} else {
		css.css(options, Program.args[0])
	}
}

// Compress/transpile the javascript files
if (Program['compilejs']) {
	if (Program['watch']) {
		Js.watch(options, null, false);
	} else {
		Js.js(options, Program.args[0])
	}
}

// Compress/transpile the Custom Elements files
if (Program['compilecescss']) {
	CEscss.compileCEscss(options, Program.args[0])
}

// Compress/transpile the Custom Elements files
if (Program['compilecejs']) {
	CEjs.compileCEjs(options, Program.args[0])
}
