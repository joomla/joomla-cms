const Autoprefixer = require('autoprefixer');
const CssNano = require('cssnano');
const Debounce = require('lodash.debounce');
const Fs = require('fs');
const MakeDir = require('./make-dir.js');
const Path = require('path');
const Postcss = require('postcss');
const Promise = require('bluebird');
const Recurs = require('recursive-readdir');
const RootPath = require('./rootpath.js')._();
const Sass = require('node-sass');

/**
 * A collection of folders to be watched
 * @type {string[]}
 */
const watches = [
  `${RootPath}/templates/cassiopeia/scss`,
  `${RootPath}/administrator/templates/atum/scss`,
  `${RootPath}/media/plg_installer_webinstaller/scss`,
  `${RootPath}/build/media_src`,
  `${RootPath}/installation/template/scss/template.scss`,
  `${RootPath}/installation/template/scss/template-rtl.scss`,
];

/**
 *
 * @param {object} options  the options
 * @param {string} path     the folder that needs to be compiled, optional
 */
const compileCSSFiles = (options, path) => {
  let files = [];
  let folders = [];

  if (path) {
    const stats = Fs.lstatSync(`${RootPath}/${path}`);

    if (stats.isDirectory()) {
      folders.push(`${RootPath}/${path}`);
    } else if (stats.isFile()) {
      files.push(`${RootPath}/${path}`);
    } else {
        // eslint-disable-next-line no-console
        console.error(`Unknown path ${path}`);
        process.exit(1);
    }
  } else {
    files = [
      `${RootPath}/templates/cassiopeia/scss/offline.scss`,
      `${RootPath}/templates/cassiopeia/scss/template.scss`,
      `${RootPath}/templates/cassiopeia/scss/template-rtl.scss`,
      `${RootPath}/administrator/templates/atum/scss/bootstrap.scss`,
      `${RootPath}/administrator/templates/atum/scss/font-awesome.scss`,
      `${RootPath}/administrator/templates/atum/scss/template.scss`,
      `${RootPath}/administrator/templates/atum/scss/template-rtl.scss`,
      `${RootPath}/build/media_src/plg_installer_webinstaller/scss/client.scss`,
      `${RootPath}/installation/template/scss/template.scss`,
      `${RootPath}/installation/template/scss/template-rtl.scss`,
    ];

    folders = [
      `${RootPath}/build/media_src`,
    ];
  }

  // Loop to get the files that should be compiled via parameter
  folders.forEach((folder) => {
    let filesTocompile = Fs.readdirSync(folder);
    filesTocompile.forEach((fileTocompile) => {
      if (Path.extname(fileTocompile) === ".scss" && fileTocompile.charAt(0) !== '_') {
        files.push(folder + '/' + fileTocompile);
      }
    });
  });

  // Loop to get some text for the packgage.json
  files.forEach((file) => {
    const cssFile = file.replace('/scss/', '/css/').replace('.scss', '.css').replace('/build/media_src/', '/media/');

    Sass.render({
      file,
    }, (error, result) => {
      if (error) {
        // eslint-disable-next-line no-console
        console.error(`something exploded ${error.column}`, error.message, error.line);
        process.exit(1);
      } else {
        // Auto prefixing
        // eslint-disable-next-line no-console
        console.log(`Prefixing for: ${options.settings.browsers}`);

        const cleaner = Postcss(
          [
            Autoprefixer({
              browsers: options.settings.browsers,
            }),
          ],
        );

        cleaner.process(result.css.toString(), {from: undefined})
          .then((res) => {
            // Ensure the folder exists or create it
            MakeDir.run(Path.dirname(cssFile));

            Fs.writeFileSync(
              cssFile,
              res.css.toString(),
              { encoding: 'UTF-8' },
            );

              Postcss([CssNano]).process(res.css.toString(), {from: undefined}).then(cssMin => {
                  // Ensure the folder exists or create it
                  MakeDir.run(Path.dirname(cssFile.replace('.css', '.min.css')));
                  Fs.writeFileSync(
                      cssFile.replace('.css', '.min.css'),
                      cssMin.css.toString(),
                      { encoding: 'UTF-8' },
                  );

                  // eslint-disable-next-line no-console
                  console.log(`File: ${cssFile.replace(/.+\//, '')} was updated. `);
              });
          });
      }
    });
  });
};

/**
 * The watch method
 * @param {object}  options       the options
 * @param {array}   folders       an array of folders to be watched
 * @param {boolean} compileFirst
 */
const watchFiles = (options, folders, compileFirst = false) => {
  const folderz = folders || watches;

  if (compileFirst) {
    compileCSSFiles(options, '');
  }

  folderz.forEach((folder) => {
    Recurs(folder, ['*.css', '*.map', '*.js', '*.svg', '*.png', '*.swf']).then(
      (files) => {
        files.forEach((file) => {
            if (file.match(/\.scss/)) {
              Fs.watchFile(file, () => {
                // eslint-disable-next-line no-console
                console.log(`File: ${file} changed.`);
                Debounce(() => compileCSSFiles(options, ''), 150)();
              });
            }
          },
          (error) => {
            // eslint-disable-next-line no-console
            console.error(`something exploded ${error}`);
          },
        );
      });
  });

  // eslint-disable-next-line no-console
  console.log('Now watching SASS files...');
};

const compileCSS = (options, path) => {
  Promise.resolve()
    // Compile the scss files
    .then(() => compileCSSFiles(options, path))

    // Handle errors
    .catch((error) => {
        // eslint-disable-next-line no-console
        console.error(`${error}`);
        process.exit(1);
    });
};

module.exports.compileCSS = compileCSS;
module.exports.watch = watchFiles;
