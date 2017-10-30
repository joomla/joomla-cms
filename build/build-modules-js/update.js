const Promise = require('bluebird');
const fs = require('fs');
const fsExtra = require('fs-extra');
const Path = require('path');
const Chalk = require('chalk');

// Various variables
const rootPath = __dirname.replace('/build/build-modules-js', '');
const xmlVersionStr = /(<version>)(\d+.\d+.\d+)(<\/version>)/;

// rm -rf media/vendor
cleanVendors = () => {
	// Let's keep some tinyMCE folders
	fsExtra.copySync(Path.join(rootPath, 'media/vendor/tinymce/langs'), Path.join(rootPath, 'build/tiny_langs'));
	fsExtra.copySync(Path.join(rootPath, 'media/vendor/tinymce/templates'), Path.join(rootPath, 'build/tiny_templates'));

	fsExtra.removeSync(Path.join(rootPath, 'media/vendor'));
	fsExtra.removeSync(Path.join(rootPath, 'media/system/js/polyfills'));

	// Restore and erase the tmp folders
	fsExtra.copySync(Path.join(rootPath, 'build/tiny_langs'), Path.join(rootPath, 'media/vendor/tinymce/langs'));
	fsExtra.copySync(Path.join(rootPath, 'build/tiny_templates'), Path.join(rootPath, 'media/vendor/tinymce/templates'));
	fsExtra.removeSync(Path.join(rootPath, 'build/tiny_langs'));
	fsExtra.removeSync(Path.join(rootPath, 'build/tiny_templates'));

	console.log(Chalk.blue('/media/vendor has been removed.'));
};

// Copies all the files from a directory
copyAll = (dirName, name, type) => {
	const folderName = dirName === '/' ? '/' : '/' + dirName;
	fsExtra.copySync(Path.join(rootPath, 'node_modules/' + name + '/' + folderName),
		Path.join(rootPath, 'media/vendor/' + name.replace(/.+\//, '') + '/' + type));
};

// Copies an array of files from a directory
copyArrayFiles = (dirName, files, name, type) => {
	files.forEach((file) => {
		const folderName = dirName === '/' ? '/' : '/' + dirName + '/';
		if (fsExtra.existsSync('node_modules/' + name + folderName + file)) {
			fsExtra.copySync('node_modules/' + name + folderName + file, 'media/vendor/' + name.replace(/.+\//, '') + (type ? '/' + type : '') + '/' + file);
		}
	});
};

// Concatenate some files
concatFiles = (files, output) => {
	let tempMem = '';
	files.forEach((file) => {
		if (fsExtra.existsSync(rootPath + '/' + file)) {
			tempMem += fs.readFileSync(rootPath + '/' +  file);
		}
	});

	fs.writeFileSync(rootPath + '/' + output, tempMem);
};

copyFiles = (options) => {
	if (!fsExtra.existsSync(Path.join(rootPath, 'media/vendor'))) {
		fsExtra.mkdirSync(Path.join(rootPath, 'media/vendor'));
	}

	// Loop to get some text for the packgage.json
	for (let name in options.settings.vendors) {
		if (['codemirror', 'tinymce'].indexOf(name) === -1) {
			// Create the directory stracture
			if (!fsExtra.existsSync(Path.join(rootPath, 'media/vendor/' + name.replace(/.+\//, '')))) {
				fsExtra.mkdirSync(Path.join(rootPath, 'media/vendor/' + name.replace(/.+\//, '')));

				if (options.settings.vendors[name]['srcjs']) {
					fsExtra.mkdirSync(Path.join(rootPath, 'media/vendor/' + name.replace(/.+\//, '') + '/js'));
				}

				if (options.settings.vendors[name]['srccss']) {
					fsExtra.mkdirSync(Path.join(rootPath, 'media/vendor/' + name.replace(/.+\//, '') + '/css'));
				}

				if (options.settings.vendors[name]['srcscss']) {
					fsExtra.mkdirSync(Path.join(rootPath, 'media/vendor/' + name.replace(/.+\//, '') + '/scss'));
				}

				if (options.settings.vendors[name]['license']) {
					fsExtra.copySync(Path.join(rootPath, 'node_modules/' + name + '/' + options.settings.vendors[name]['license']), Path.join(rootPath, 'media/vendor/' + name.replace(/.+\//, '') + '/license.txt'));
				}
			}

			// Copy any js files
			if (options.settings.vendors[name]['srcjs']) {
				if (options.settings.vendors[name]['filesjs'] && options.settings.vendors[name]['filesjs'] === '*') {
					copyAll(options.settings.vendors[name]['srcjs'], name, 'js')
				} else if (options.settings.vendors[name]['filesjs'] && Array.isArray(options.settings.vendors[name]['filesjs'])) {
					copyArrayFiles(options.settings.vendors[name]['srcjs'], options.settings.vendors[name]['filesjs'], name, 'js')
				}
			}

			// // Copy any css files
			if (options.settings.vendors[name]['srccss']) {
				if (options.settings.vendors[name]['filescss'] && options.settings.vendors[name]['filescss'] === '*') {
					copyAll(options.settings.vendors[name]['srccss'], name, 'css')
				} else if (options.settings.vendors[name]['filescss'] && Array.isArray(options.settings.vendors[name]['filescss'])) {
					copyArrayFiles(options.settings.vendors[name]['srccss'], options.settings.vendors[name]['filescss'], name, 'css')
				}
			}

			// Copy any scss files
			if (options.settings.vendors[name]['srcscss']) {
				if (options.settings.vendors[name]['filesscss'] && options.settings.vendors[name]['filesscss'] === '*') {
					copyAll(options.settings.vendors[name]['srcscss'], name, 'scss')
				} else if (options.settings.vendors[name]['filesscss'] && Array.isArray(options.settings.vendors[name]['filesscss'])) {
					copyArrayFiles(options.settings.vendors[name]['srcscss'], options.settings.vendors[name]['filesscss'], name, 'scss')
				}
			}

			// Copy any font files
			if (options.settings.vendors[name]['srcfonts']) {
				if (options.settings.vendors[name]['filesfonts'] && options.settings.vendors[name]['filesfonts'] === '*') {
					copyAll(options.settings.vendors[name]['srcfonts'], name, 'fonts')
				} else if (options.settings.vendors[name]['filesfonts'] && Array.isArray(options.settings.vendors[name]['filesfonts'])) {
					copyArrayFiles(options.settings.vendors[name]['srcfonts'], options.settings.vendors[name]['filesfonts'], name, 'fonts')
				}
			}
		} else if ('codemirror' === name) {
			if (!fsExtra.existsSync(Path.join(rootPath, 'media/vendor/' + name.replace(/.+\//, '')))) {
				fsExtra.mkdirSync(Path.join(rootPath, 'media/vendor/' + name.replace(/.+\//, '')));
				fsExtra.mkdirSync(Path.join(rootPath, 'media/vendor/' + name.replace(/.+\//, '') + '/addon'));
				fsExtra.mkdirSync(Path.join(rootPath, 'media/vendor/' + name.replace(/.+\//, '') + '/lib'));
				fsExtra.mkdirSync(Path.join(rootPath, 'media/vendor/' + name.replace(/.+\//, '') + '/mode'));
				fsExtra.mkdirSync(Path.join(rootPath, 'media/vendor/' + name.replace(/.+\//, '') + '/keymap'));
				fsExtra.mkdirSync(Path.join(rootPath, 'media/vendor/' + name.replace(/.+\//, '') + '/theme'));
			}

			copyAll('addon', 'codemirror', 'addon');
			copyAll('lib', 'codemirror', 'lib');
			copyAll('mode', 'codemirror', 'mode');
			copyAll('keymap', 'codemirror', 'keymap');
			copyAll('theme', 'codemirror', 'theme');

			concatFiles(
				[
				"media/vendor/codemirror/addon/display/fullscreen.js",
				"media/vendor/codemirror/addon/display/panel.js",
				"media/vendor/codemirror/addon/edit/closebrackets.js",
				"media/vendor/codemirror/addon/edit/closetag.js",
				"media/vendor/codemirror/addon/edit/matchbrackets.js",
				"media/vendor/codemirror/addon/edit/matchtags.js",
				"media/vendor/codemirror/addon/fold/brace-fold.js",
				"media/vendor/codemirror/addon/fold/foldcode.js",
				"media/vendor/codemirror/addon/fold/foldgutter.js",
				"media/vendor/codemirror/addon/fold/xml-fold.js",
				"media/vendor/codemirror/addon/mode/loadmode.js",
				"media/vendor/codemirror/addon/mode/multiplex.js",
				"media/vendor/codemirror/addon/scroll/simplescrollbars.js",
				"media/vendor/codemirror/addon/selection/active-line.js",
				"media/vendor/codemirror/keymap/vim.js"
				],
				'media/vendor/codemirror/lib/addons.js');

			concatFiles([
				"media/vendor/codemirror/addon/display/fullscreen.css",
				"media/vendor/codemirror/addon/fold/foldgutter.css",
				"media/vendor/codemirror/addon/scroll/simplescrollbars.css"
			], 'media/vendor/codemirror/lib/addons.css');

			// Update the XML file for Codemirror
			let codemirrorXml = fs.readFileSync(rootPath + '/plugins/editors/codemirror/codemirror.xml', {encoding: 'UTF-8'});
			codemirrorXml = codemirrorXml.replace(xmlVersionStr, "$1" + options.dependencies.codemirror + "$3");
			fs.writeFileSync(rootPath + '/plugins/editors/codemirror/codemirror.xml', codemirrorXml, {encoding: 'UTF-8'});

		} else if ('tinymce' === name) {
			if (!fsExtra.existsSync(Path.join(rootPath, 'media/vendor/' + name.replace(/.+\//, '')))) {
				fsExtra.mkdirSync(Path.join(rootPath, 'media/vendor/' + name.replace(/.+\//, '')));
				fsExtra.mkdirSync(Path.join(rootPath, 'media/vendor/' + name.replace(/.+\//, '') + '/plugins'));
				fsExtra.mkdirSync(Path.join(rootPath, 'media/vendor/' + name.replace(/.+\//, '') + '/langs'));
				fsExtra.mkdirSync(Path.join(rootPath, 'media/vendor/' + name.replace(/.+\//, '') + '/skins'));
				fsExtra.mkdirSync(Path.join(rootPath, 'media/vendor/' + name.replace(/.+\//, '') + '/themes'));
				fsExtra.mkdirSync(Path.join(rootPath, 'media/vendor/' + name.replace(/.+\//, '') + '/templates'));
			}

			copyAll('plugins', 'tinymce', 'plugins');
			copyAll('skins', 'tinymce', 'skins');
			copyAll('themes', 'tinymce', 'themes');

			copyArrayFiles('', ['tinymce.js', 'tinymce.min.js', 'changelog.txt', 'license.txt'], 'tinymce', '');

			// Update the XML file for tinyMCE
			let tinyXml = fs.readFileSync(rootPath + '/plugins/editors/tinymce/tinymce.xml', {encoding: 'UTF-8'});
			tinyXml = tinyXml.replace(xmlVersionStr, "$1" + options.dependencies.tinymce + "$3");
			fs.writeFileSync(rootPath + '/plugins/editors/tinymce/tinymce.xml', tinyXml, {encoding: 'UTF-8'});
		}

		console.log(Chalk.green(name + ' was updated.'));
	}
};

copyPolyfills = () => {

	if (!fsExtra.existsSync(Path.join(rootPath, 'media/system/js/polyfills/webcomponents'))) {
		fsExtra.mkdirSync(Path.join(rootPath, 'media/system/js/polyfills'));
		fsExtra.mkdirSync(Path.join(rootPath, 'media/system/js/polyfills/webcomponents'));
	}

	const polyfills = [
		rootPath + '/node_modules/@webcomponents/webcomponentsjs/webcomponents-hi-ce.js',
		rootPath + '/node_modules/@webcomponents/webcomponentsjs/webcomponents-hi-sd-ce.js',
		rootPath + '/node_modules/@webcomponents/webcomponentsjs/webcomponents-hi.js',
		rootPath + '/node_modules/@webcomponents/webcomponentsjs/webcomponents-lite.js',
		rootPath + '/node_modules/@webcomponents/webcomponentsjs/webcomponents-sd-ce.js',
	];

	polyfills.forEach((file) => {
		fs.copyFileSync(file, rootPath + '/media/system/js/polyfills/webcomponents/' + file.replace(/.+\//, ''));
		fs.copyFileSync(file.replace('.js', '.js.map'), rootPath + '/media/system/js/polyfills/webcomponents/' + file.replace(/.+\//, '').replace('.js', '.js.map'));
	});

	// Special case for plain custom element polyfill
	fs.copyFileSync(rootPath + '/node_modules/@webcomponents/custom-elements/custom-elements.min.js', rootPath + '/media/system/js/polyfills/webcomponents/webcomponents-ce.js');
	fs.copyFileSync(rootPath + '/node_modules/@webcomponents/custom-elements/custom-elements.min.js.map', rootPath + '/media/system/js/polyfills/webcomponents/webcomponents-ce.js.map');

	// We NEED the webcomponents.ready event in the polyfill!!!
	if (fsExtra.existsSync('media/system/js/polyfills/webcomponents-ce.js')) {
		let ce = fs.readFileSync('media/system/js/polyfills/webcomponents-ce.js');
		ce = ce.replace('//# sourceMappingURL=custom-elements.min.js.map', `
(function(){
	window.WebComponents = window.WebComponents || {};
	requestAnimationFrame(function() {
		window.WebComponents.ready= true;
		document.dispatchEvent(new CustomEvent("WebComponentsReady", { bubbles:true }) );
	})
})();
//# sourceMappingURL=custom-elements.js.map`);

		fs.writeFileSync('media/system/js/polyfills/webcomponents-ce.js', ce, {encoding: 'UTF-8'});
	}
};

update = (options) => {
	Promise.resolve()
		// Copy a fresh version of the files
		.then(cleanVendors())

		// Copy a fresh version of the files
		.then(copyFiles(options))

		// Copy all the polyfills
		.then(copyPolyfills())

		// Handle errors
		.catch((err) => {
			console.error(Chalk.red(err));
			process.exit(-1);
		});
};

module.exports.update = update;
