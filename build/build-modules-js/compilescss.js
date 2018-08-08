const Promise = require('bluebird');
const fs = require('fs');
const Path = require('path');
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
  `${rootPath}/build/media_src`,
  `${rootPath}/installation/template/scss/template.scss`,
];

const compileCSSFiles = (options, path) => {
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
      `${rootPath}/build/media_src/plg_installer_webinstaller/scss/client.scss`,
      `${rootPath}/installation/template/scss/template.scss`,
    ];

    folders = [
      `${rootPath}/build/media_src`,
    ];
  }

  // Loop to get some text for the packgage.json
  files.forEach((file) => {
    const cssFile = file.replace('/scss/', '/css/').replace('.scss', '.css').replace('/build/media_src/', '/media/');

    Sass.render({
      file,
    }, (error, result) => {
      if (error) {
        // eslint-disable-next-line no-console
        console.error(`something exploded ${error.column}`);
        // eslint-disable-next-line no-console
        console.error(`something exploded ${error.message}`);
        // eslint-disable-next-line no-console
        console.error(`something exploded ${error.line}`);
      } else {
        // Auto prefixing
        // eslint-disable-next-line no-console
        console.log(`Prefixing for: ${options.settings.browsers}`);

        const cleaner = postcss(
          [
            autoprefixer({
              add: false,
              browsers: options.settings.browsers,
            }),
          ],
        );
        const prefixer = postcss([autoprefixer]);

        cleaner.process(result.css.toString(), {from: undefined})
          .then(cleaned => prefixer.process(cleaned.css, {from: undefined}))
          .then((res) => {
            // Ensure the folder exists or create it
            const currentDir = Path.dirname(cssFile);
            try{
              fs.lstatSync(currentDir).isDirectory()
            }catch(e){
              if(e.code === 'ENOENT'){
                // Directory needs to be created
                fs.mkdirSync(currentDir);
              }
            }

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
            console.log(`File: ${cssFile.replace(/.+\//, '')} was updated. `);
          });
      }
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
            if (file.match(/\.scss/)) {
              fs.watchFile(file, () => {
                // eslint-disable-next-line no-console
                console.log(`File: ${file} changed.`);
                debounce(() => compileFiles(options), 150)();
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
    .catch((err) => {
      // eslint-disable-next-line no-console
      console.error(err);
      process.exit(-1);
    });
};

module.exports.compileCSS = compileCSS;
module.exports.watch = watchFiles;
