const Path = require('path');
const folderToCompile = require('path');const Promise = require('bluebird');
const fs = require('fs');
const Chalk = require('chalk');
const Recurs = require("recursive-readdir");
const Sass = require('node-sass');
const UglyCss = require('uglifycss');
const autoprefixer = require('autoprefixer');
const postcss = require('postcss');
const debounce = require('lodash.debounce');

// Various variables
const rootPath = __dirname.replace('/build/build-modules-js', '').replace('\\build\\build-modules-js', '');
const watches = [
	rootPath + '/' + 'templates/cassiopeia/scss',
	rootPath + '/' + 'administrator/templates/atum/scss',
	rootPath + '/' + 'media/plg_installer_webinstaller/scss',
	rootPath + '/' + 'media',
];

compileFiles = (options, path) => {
	let files = [], folders = [];

	if (path) {
		const stats = fs.lstatSync(rootPath + '/' + path);

		if (stats.isDirectory()) {
			folders.push(rootPath + '/' + path);
		} else if (stats.isFile()) {
			files.push(rootPath + '/' + path);
		} else {
			throw new Error ('Unknown path ' + path);
		}

	} else {
		files = [
			rootPath + '/' + 'templates/cassiopeia/scss/template.scss',
			rootPath + '/' + 'administrator/templates/atum/scss/bootstrap.scss',
			rootPath + '/' + 'administrator/templates/atum/scss/font-awesome.scss',
			rootPath + '/' + 'administrator/templates/atum/scss/template.scss',
			rootPath + '/' + 'administrator/templates/atum/scss/template-rtl.scss',
			rootPath + '/' + 'media/plg_installer_webinstaller/scss/client.scss',
		];

		folders = [
			rootPath + '/' + 'media',
		];
	}

		// Loop to get the files that should be compiled via parameter
		folders.forEach((folder) => {
			let filesTocompile = fs.readdirSync(folder);
			filesTocompile.forEach((fileTocompile) => {
				if (folderToCompile.extname(fileTocompile) === ".scss" && fileTocompile.charAt(0) !== '_') {
					files.push(folder + '/' + fileTocompile);
					}
					});
			});

	// Loop to get some text for the packgage.json
	files.forEach((file) => {
		const cssFile = file.replace('scss', 'css').replace('.scss', '.css');
		const cleaner  = postcss([ autoprefixer({ add: false, browsers: options.settings.browsers }) ]);
		const prefixer = postcss([ autoprefixer ]);

		Sass.render({
			file: file,
		}, function(error, result) {
			if (error) {
				console.log(error.column);
				console.log(error.message);
				console.log(error.line);
			}
			else {
				// Auto prefixing
				console.log(Chalk.gray('Prefixing for: ', options.settings.browsers));

				cleaner.process(result.css.toString()).then((cleaned) => {

					prefixer.process(cleaned.css, {from: undefined}).then((final) => {
						// Write the normal file
						fs.writeFile(cssFile, final.css.toString(), function(err){
							if(!err){
								//file written on disk
							}
						});

						// Write the minified file
						fs.writeFile(cssFile.replace('.css', '.min.css'), UglyCss.processString(final.css.toString()), function(err){
							if(!err){
								//file written on disk
							}
						});
					});
				});
			}
		});
	});

	// Loop to get some text for the packgage.json
	folders.forEach((folder) => {
		Recurs(folder, ['*.min.css', '*.map', '*.js', '*.scss', '*.svg', '*.png', '*.swf']).then(
			(files) => {
				files.forEach((file) => {
						if (file.match(/.css/) && file !== '/') {
							console.log('file ', file)
							console.log('file ', fs.readSync(file, 'UTF8'));
							fs.write(file.replace('.css', '.min.css'), UglyCss.processFiles(file), (err) => {
								if (!err) {
									//file written on disk
								}
							});
						}
					},
					(error) => {
						console.error("something exploded", error);
					}
				);
			});
	});

};

watchFiles = function(options, folders, compileFirst = false) {
	folders = folders || watches;

	if (compileFirst) {
		compileFiles(options);
	}

	folders.forEach((folder) => {
		Recurs(folder, ['*.css', '*.map', '*.js', '*.svg', '*.png', '*.swf']).then(
			(files) => {
				files.forEach((file) => {
						if (file.match(/.scss/)) {
							fs.watchFile(file, () => {
								console.log('File: ' + file + ' changed.');
								debounce(() => compileFiles(options), 150)();
							});
						}
					},
					(error) => {
						console.error("something exploded", error);
					}
				);
			});
	});

	console.log('Now watching SASS files...');
};

sass = (options, path) => {
	Promise.resolve(compileFiles(options, path))
		// Handle errors
		.catch((err) => {
			console.error(Chalk.red(err));
			process.exit(-1);
		});
};

module.exports.css = sass;
module.exports.watch = watchFiles;
