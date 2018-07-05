const Promise = require('bluebird');
const fs = require('fs');
const fsExtra = require('fs-extra');
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

    // Loop to get some text for the packgage.json
    files.forEach((file) => {
        const cssFile = file.replace('scss', 'css').replace('.scss', '.css');

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

                const cleaner  = postcss([ autoprefixer({ add: false, browsers: options.settings.browsers }) ]);
                const prefixer = postcss([ autoprefixer ]);

                cleaner.process(result.css.toString()).then(function (cleaned) {
                    return prefixer.process(cleaned.css)
                }).then(function (result) {
                  const cssContent = result.css.toString();
                  fs.writeFileSync(cssFile, cssContent, {encoding: 'UTF-8'});

                  // Uglify it now
                  fs.writeFileSync(cssFile.replace('.css', '.min.css'), UglyCss.processString(cssContent, {expandVars: false }), {encoding: 'UTF-8'});
                });

                console.log(Chalk.bgYellow(cssFile.replace(/.+\//, '') + ' was updated.'));
            }
        });
    });

    // Loop to get some text for the packgage.json
    folders.forEach((folder) => {
        Recurs(folder, ['*.min.css', '*.map', '*.js', '*.scss', '*.svg', '*.png', '*.swf']).then(
            (files) => {
                files.forEach((file) => {
                        if (file.match(/.css/) && !file.toLowerCase().match(/license/)) {
                            // Write the file
                            fs.writeFileSync(file.replace('.css', '.min.css'), UglyCss.processFiles([file], {expandVars: false }), {encoding: "utf8"});
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
    Promise.resolve()
    // Compile the scss files
        .then(() => compileFiles(options, path))

        // Handle errors
        .catch((err) => {
            console.error(Chalk.red(err));
            process.exit(-1);
        });
};

module.exports.css = sass;
module.exports.watch = watchFiles;
