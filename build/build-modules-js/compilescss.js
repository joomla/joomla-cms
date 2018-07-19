const Promise = require('bluebird');
const fs = require('fs');
// const fsExtra = require('fs-extra');
const chalk = require('chalk');
const Recurs = require('recursive-readdir');
const Sass = require('node-sass');
const UglyCss = require('uglifycss');
const autoprefixer = require('autoprefixer');
const postcss = require('postcss');
const debounce = require('lodash.debounce');
const rootPath = require('./rootpath.js')._();

const watches = [
  `${rootPath}/templates/cassiopeia/scss`,
  `${rootPath}/administrator/templates/atum/scss`,
  `${rootPath}/media/plg_installer_webinstaller/scss`,
  `${rootPath}/media`,
];

const compileFiles = (options, path) => {
  let files = [];
  let folders = [];

  if (path) {
    const stats = fs.lstatSync(`${rootPath}/${path}`);

    if (stats.isDirectory()) {
      folders.push(`${rootPath}/${path}`);
    } else if (stats.isFile()) {
      files.push(`${rootPath}/${path}`);
    } else {
      throw new Error(`Unknown path ${path}`);
    }
  } else {
    files = [
      `${rootPath}/templates/cassiopeia/scss/template.scss`,
      `${rootPath}/templates/cassiopeia/scss/template-rtl.scss`,
      `${rootPath}/administrator/templates/atum/scss/bootstrap.scss`,
      `${rootPath}/administrator/templates/atum/scss/font-awesome.scss`,
      `${rootPath}/administrator/templates/atum/scss/template.scss`,
      `${rootPath}/administrator/templates/atum/scss/template-rtl.scss`,
      `${rootPath}/media/plg_installer_webinstaller/scss/client.scss`,
    ];

    folders = [
      `${rootPath}/media`,
    ];
  }

  // Loop to get some text for the packgage.json
  files.forEach((file) => {
    const cssFile = file.replace('scss', 'css').replace('.scss', '.css');

    Sass.render({
      file,
    }, (error, result) => {
      if (error) {
        // eslint-disable-next-line no-console
        console.error(chalk.red('something exploded', error.column));
        // eslint-disable-next-line no-console
        console.error(chalk.red('something exploded', error.message));
        // eslint-disable-next-line no-console
        console.error(chalk.red('something exploded', error.line));
      } else {
        // Auto prefixing
        // eslint-disable-next-line no-console
        console.log(chalk.bgBlue('Prefixing for: ', options.settings.browsers));

        const cleaner = postcss(
          [
            autoprefixer({
              add: false,
              browsers: options.settings.browsers,
            }),
          ],
        );
        const prefixer = postcss([autoprefixer]);

        cleaner.process(result.css.toString())
          .then(cleaned => prefixer.process(cleaned.css))
          .then((res) => {
            fs.writeFileSync(
              cssFile,
              res.css.toString(),
              { encoding: 'UTF-8' },
            );
          })
          .then(() => {
            // Uglify it now
            fs.writeFileSync(
              cssFile.replace('.css', '.min.css'),
              UglyCss.processFiles([cssFile], { expandVars: false }),
              { encoding: 'UTF-8' },
            );

            // eslint-disable-next-line no-console
            console.log(chalk.bgGreen(`File: ${cssFile.replace(/.+\//, '')} was updated. `));
          });
      }
    });
  });

  // Loop to get some text for the packgage.json
  folders.forEach((folder) => {
    Recurs(folder, ['*.min.css', '*.map', '*.js', '*.scss', '*.svg', '*.png', '*.swf']).then(
      (filez) => {
        filez.forEach((file) => {
          if (file.match(/.css/) && !file.toLowerCase().match(/license/)) {
            // Write the file
            fs.writeFileSync(
              file.replace('.css', '.min.css'),
              UglyCss.processFiles([file], { expandVars: false }),
              { encoding: 'utf8' },
            );
          }
        },
        (error) => {
          // eslint-disable-next-line no-console
          console.error(chalk.red('something exploded', error));
        },
        );
      });
  });
};

const watchFiles = (options, folders, compileFirst = false) => {
  const folderz = folders || watches;

  if (compileFirst) {
    compileFiles(options);
  }

  folderz.forEach((folder) => {
    Recurs(folder, ['*.css', '*.map', '*.js', '*.svg', '*.png', '*.swf']).then(
      (files) => {
        files.forEach((file) => {
          if (file.match(/.scss/)) {
            fs.watchFile(file, () => {
              // eslint-disable-next-line no-console
              console.log(`File: ${file} changed.`);
              debounce(() => compileFiles(options), 150)();
            });
          }
        },
        (error) => {
          // eslint-disable-next-line no-console
          console.error(chalk.red('something exploded', error));
        },
        );
      });
  });

  // eslint-disable-next-line no-console
  console.log('Now watching SASS files...');
};

const sass = (options, path) => {
  Promise.resolve()
    // Compile the scss files
    .then(() => compileFiles(options, path))

    // Handle errors
    .catch((err) => {
      // eslint-disable-next-line no-console
      console.error(chalk.red(err));
      process.exit(-1);
    });
};

module.exports.compile = sass;
module.exports.watch = watchFiles;
