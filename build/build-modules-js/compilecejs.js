const babelify = require("babelify");
const browserify = require("browserify");
const Chalk = require('chalk');
const fs = require('fs');
const fsExtra = require('fs-extra');
const Path = require('path');
const Promise = require('bluebird');
const UglifyJS = require('uglify-es');

// const compileCEscss = require('./compilecescss.js');

// Various variables
const rootPath = __dirname.replace('/build/build-modules-js', '').replace('\\build\\build-modules-js', '');

compileCejs = (options) => {
	// Make sure that the dist paths exist
	if (!fs.existsSync(rootPath + '/media/system/webcomponents')) {
		fsExtra.mkdirSync(rootPath + '/media/system/webcomponents');
	}
	if (!fs.existsSync(rootPath + '/media/system/webcomponents/js')) {
		fsExtra.mkdirSync(rootPath + '/media/system/webcomponents/js');
	}

	options.settings.elements.forEach((element) => {
		const b = browserify();
		const c = browserify();

		// Copy the ES6 file
		let es6File = fs.readFileSync(rootPath + '/build/webcomponents/js/' + element + '/' + element + '.js', "utf8");

		// Check if there is a css file
		if (fs.existsSync(Path.join(rootPath, '/build/webcomponents/css/' + element + '.css'))) {
			const cssContent = fs.readFileSync(Path.join(rootPath, '/build/webcomponents/css/' + element + '.css'), "utf8");

			if (cssContent) {
				es6File = es6File.replace('{{CSS_CONTENTS_AUTOMATICALLY_INSERTED_HERE}}', cssContent);
			}
		}

		fs.writeFileSync(rootPath + '/media/system/webcomponents/js/joomla-' + element + '.js', es6File, { encoding: "utf8" });

		// And the minified version
		fs.writeFileSync(rootPath + '/media/system/webcomponents/js/joomla-' + element + '.min.js', UglifyJS.minify(es6File).code, { encoding: "utf8" });

		// Transpile a copy for ES5
		fs.writeFileSync(rootPath + '/media/system/webcomponents/js/joomla-' + element + '-es5.js', '');
		const bundleFs = fs.createWriteStream(rootPath + '/media/system/webcomponents/js/joomla-' + element + '-es5.js');
		const bundleFsMin = fs.createWriteStream(rootPath + '/media/system/webcomponents/js/joomla-' + element + '-es5.min.js');

		b.add(rootPath + '/build/webcomponents/js/' + element + '/' + element + '.js');
		c.add(rootPath + '/build/webcomponents/js/' + element + '/' + element + '.js');
		b.transform(babelify, { presets: ["babel-preset-es2015"] }).bundle().pipe(bundleFs);
		c.transform(babelify, { presets: ["babel-preset-es2015", "babel-preset-minify"] }).bundle().pipe(bundleFsMin);

		console.log(Chalk.yellow('Custom Element: joomla-' + element + ' was packaged.'));
	});
};

compileCEjs = (options, path) => {
	Promise.resolve()
		// First the css
		.then(() => compileSass(options, path))
		// Then the js
		.then(() => compileCejs(options, path))

		// Do some cleanup
		.then(() => {
			// Clean up the css files
			fsExtra.emptyDir(rootPath + '/build/webcomponents/css');
		})

		// Handle errors
		.catch((err) => {
			console.error(Chalk.red(err));
			process.exit(-1);
		});
};

module.exports.compileCEjs = compileCEjs;
