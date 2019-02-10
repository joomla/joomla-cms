const Fs = require('fs');
const Path = require('path');
const Recurs = require('recursive-readdir');
const UglyCss = require('uglifycss');
const MakeDir = require('./utils/make-dir.es6.js');
const CompileScss = require('./stylesheets/scss-transform.es6.js');
const RootPath = require('./utils/rootpath.es6.js')._();

let folders = [];
const mediaFiles = [];
const templateFiles = [];

/**
 * Method that will crawl the media_source folder and
 * compile any scss files to css and .min.css
 * copy any css files to the appropriate destination and
 * minify them in place
 *
 * Expects scss files to have ext: .scss
 *         css files to have ext: .css
 * Ignores scss files that their filename starts with `_`
 *
 * @param {object} options  The options
 * @param {string} path     The folder that needs to be compiled, optional
 */
module.exports.compile = (path) => {
  Promise.resolve()
  // Compile the scss files
    .then(() => {
      if (path) {
        const stats = Fs.lstatSync(`${RootPath}/${path}`);

        if (stats.isDirectory()) {
          folders.push(`${RootPath}/${path}`);
        } else if (stats.isFile()) {
          // files.push(`${RootPath}/${path}`);
        } else {
          // eslint-disable-next-line no-console
          console.error(`Unknown path ${path}`);
          process.exit(1);
        }
      } else {
        folders = [
          `${RootPath}/build/media_source`,
          `${RootPath}/administrator/templates`,
          `${RootPath}/templates`,
          `${RootPath}/installation/template`,
        ];
      }

      // Loop to get the files that should be compiled via parameter
      folders.forEach((folder) => {
        Recurs(folder, ['*.js', '*.map', '*.svg', '*.png', '*.jpg', '*.gif', '*.ico', '*.swf', '*.json', '*.php', '*.ini', '*.xml', '*.html', '.DS_Store']).then(
          (filesRc) => {
            filesRc.forEach(
              (file) => {
                if (file.match(/\.scss$/) && Path.basename(file).charAt(0) !== '_') {
                  if (folder.includes(`${RootPath}/build/media_source`)) {
                    mediaFiles.push(file);
                  } else if (folder.includes('/template')) {
                    if (file.includes('/assets_source/scss/')) {
                      templateFiles.push(file);
                    }
                  }
                }
                if (file.match(/\.css$/)) {
                  // CSS file, we will copy the file and then minify it in place
                  if (folder.includes(`${RootPath}/build/media_source`)) {
                    // Ensure that the directories exist or create them
                    MakeDir.run(Path.dirname(file).replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\'));
                    Fs.copyFileSync(file, file.replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\'));
                    Fs.writeFileSync(
                      file.replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\').replace('.css', '.min.css'),
                      UglyCss.processFiles([file], { expandVars: false }),
                      { encoding: 'utf8' },
                    );
                  } else if (folder.includes(`${RootPath}/administrator/templates`) || folder.includes(`${RootPath}/templates`)) {
                    if (file.match('/assets_source/css/')) {
                      // Ensure that the directories exist or create them
                      MakeDir.run(Path.dirname(file.replace('/assets_source/css/', '/css/').replace('\\assets_source\\css\\', '\\css\\')));
                      Fs.copyFileSync(file, file.replace('/assets_source/css/', '/css/').replace('\\assets_source\\css\\', '\\css\\'));
                      Fs.writeFileSync(
                        file.replace('/assets_source/css/', '/css/').replace('\\assets_source\\css\\', '\\css\\').replace('.css', '.min.css'),
                        UglyCss.processFiles([file], { expandVars: false }),
                        { encoding: 'utf8' },
                      );
                    }
                  }


                  // eslint-disable-next-line no-console
                  console.log(`CSS file copied/minified: ${file}`);
                }
              },
              (error) => {
                // eslint-disable-next-line no-console
                console.error(`something exploded here ${error}`);
              },
            );
          },
        )
          .then(() => {
            mediaFiles.forEach((inputFile) => {
              CompileScss.compile(inputFile, 'media');
            });
            templateFiles.forEach((inputFile) => {
              CompileScss.compile(inputFile, 'templates');
            });
          });
      });
    })


    // Handle errors
    .catch((error) => {
      // eslint-disable-next-line no-console
      console.error(`${error}`);
      process.exit(1);
    });
};
