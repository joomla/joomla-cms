const Fs = require('fs');
const FsExtra = require('fs-extra');
const Path = require('path');
const Recurs = require('recursive-readdir');
const UglyCss = require('uglifycss');
const CompileScss = require('./stylesheets/scss-transform.es6.js');

const RootPath = process.cwd();

/**
 * Method that will crawl the media_source folder
 * and compile any scss files to css and .min.css
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
module.exports.compile = (options, path) => {
  Promise.resolve()
  // Compile the scss files
    .then(() => {
      const files = [];
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
<<<<<<< HEAD
=======
        files = [
          `${RootPath}/templates/cassiopeia/scss/offline.scss`,
          `${RootPath}/templates/cassiopeia/scss/template.scss`,
          `${RootPath}/templates/cassiopeia/scss/template-rtl.scss`,
          `${RootPath}/templates/cassiopeia/scss/global/fonts-local_roboto.scss`,
          `${RootPath}/templates/cassiopeia/scss/global/fonts-web_fira-sans.scss`,
          `${RootPath}/templates/cassiopeia/scss/global/fonts-web_roboto+noto-sans.scss`,
          `${RootPath}/templates/cassiopeia/scss/global/colors_standard.scss`,
          `${RootPath}/templates/cassiopeia/scss/global/colors_alternative.scss`,
          `${RootPath}/templates/cassiopeia/scss/system/searchtools/searchtools.scss`,
          `${RootPath}/templates/cassiopeia/scss/vendor/choicesjs/choices.scss`,
          `${RootPath}/templates/cassiopeia/scss/vendor/joomla-custom-elements/joomla-alert.scss`,
          `${RootPath}/templates/cassiopeia/scss/vendor/fontawesome-free/fontawesome.scss`,
          `${RootPath}/administrator/templates/atum/scss/template.scss`,
          `${RootPath}/administrator/templates/atum/scss/template-rtl.scss`,
          `${RootPath}/administrator/templates/atum/scss/system/searchtools/searchtools.scss`,
          `${RootPath}/administrator/templates/atum/scss/vendor/awesomplete/awesomplete.scss`,
          `${RootPath}/administrator/templates/atum/scss/vendor/choicesjs/choices.scss`,
          `${RootPath}/administrator/templates/atum/scss/vendor/minicolors/minicolors.scss`,
          `${RootPath}/administrator/templates/atum/scss/vendor/joomla-custom-elements/joomla-alert.scss`,
          `${RootPath}/administrator/templates/atum/scss/vendor/joomla-custom-elements/joomla-tab.scss`,
          `${RootPath}/administrator/templates/atum/scss/vendor/fontawesome-free/fontawesome.scss`,
          `${RootPath}/installation/template/scss/template.scss`,
          `${RootPath}/installation/template/scss/template-rtl.scss`,
        ];

>>>>>>> 42d855489a5ae6450bf42d0ef9cd566a2a647146
        folders = [
          `${RootPath}/build/media_source`,
          `${RootPath}/templates`,
          `${RootPath}/installation/template`,
          `${RootPath}/administrator/templates`,
        ];
      }

      // Loop to get the files that should be compiled via parameter
      folders.forEach((folder) => {
        Recurs(folder, ['*.js', '*.map', '*.svg', '*.png', '*.gif', '*.swf', '*.html', '*.json']).then(
          (filesRc) => {
            filesRc.forEach(
              (file) => {
                // Don't take files with "_" but "file" has the full path, so check via match
                if (file.match(/\.scss$/)) {
                  if (!file.match(/(\/|\\)_[^/\\]+$/)) {
                    files.push(file);
                  }

                  // Update the scss in the media folder
                  if (file.match(/build\/media_source\//)) {
                    FsExtra.mkdirsSync(Path.dirname(file).replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\'), {});
                    Fs.copyFileSync(file, file.replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\'));
                  }
                }
                if (file.match(/\.css/) && !file.match(/\/template(s)?\//)) {
                  // CSS file, we will copy the file and then minify it in place
                  // Ensure that the directories exist or create them
                  FsExtra.mkdirsSync(Path.dirname(file).replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\'), {});
                  Fs.copyFileSync(file, file.replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\'));
                  Fs.writeFileSync(
                    file.replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\').replace('.css', '.min.css'),
                    UglyCss.processFiles([file], { expandVars: false }),
                    { encoding: 'utf8' },
                  );

                  // eslint-disable-next-line no-console
                  console.log(`CSS file copied/minified: ${file}`);
                }
              },
              (error) => {
                // eslint-disable-next-line no-console
                console.error(error.formatted);
              },
            );

            return files;
          },
        ).then(
          (scssFiles) => {
            scssFiles.forEach(
              (inputFile) => {
                CompileScss.compile(inputFile);
              },
            );
          },
        );
      });
    })

    // Handle errors
    .catch((error) => {
      // eslint-disable-next-line no-console
      console.error(`${error}`);
      process.exit(1);
    });
};
