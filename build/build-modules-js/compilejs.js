const Promise = require('bluebird');
const fs = require('fs');
const fsExtra = require('fs-extra');
const Recurs = require("recursive-readdir");
const Chalk = require('chalk');
const UglifyJS = require('uglify-es');

// Various variables
const rootPath = __dirname.replace('/build/build-modules-js', '');

uglifyJs = (options) => {
	const folders = [
		rootPath + '/' + 'media',
		rootPath + '/' + 'administrator/templates/atum/js',
		rootPath + '/' + 'templates/cassiopeia/js'
	];


	// Loop to get some text for the packgage.json
	folders.forEach((folder) => {
		Recurs(folder, ['*.min.js', '*.map', '*.css', '*.svg', '*.png', '*.swf']).then(
			(files) => {
				files.forEach((file) => {
					if (file.match(/.js/)) {
						// Write the file
						fs.writeFileSync(file.replace('.js', '.min.js'), UglifyJS.minify(fs.readFileSync(file, "utf8")).code, {encoding: "utf8"});
					}

					},
					(error) => {
						console.error("something exploded", error);
					}
				);
			});
	});
};


ujs = (options) => {
	Promise.resolve()
		// Compile the scss files
		.then(() => uglifyJs(options))

		// Handle errors
		.catch((err) => {
			console.error(Chalk.red(err));
			process.exit(-1);
		});
};

module.exports.js = ujs;
