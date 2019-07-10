const Fs = require('fs');
const Recurs = require('recursive-readdir');
const HandleFile = require('./javascript/handle-file.es6.js');
const RootPath = require('./utils/rootpath.es6.js')._();

/**
 * Method that will crawl the media_source folder and
 * compile ES6 to ES5 and ES6
 * copy any ES5 files to the appropriate destination and
 * minify them in place
 * compile any custom elements/webcomponents
 *
 * Expects ES6 files to have ext: .es6.js
 *         ES5 files to have ext: .es5.js
 *         WC/CE files to have ext: .w-c.es6.js
 *
 * @param { object } options The options from settings.json
 * @param { string } path    The folder that needs to be compiled, optional
 */
module.exports.compileJS = (options, path) => {
  Promise.resolve(options, path)
    // Compile the scss files
    .then(() => {
      let folders = [];
      if (path) {
        const stats = Fs.lstatSync(`${RootPath}/${path}`);

        if (!stats.isDirectory()) {
          // @todo: allow to compile single file
          throw new Error(`Path should be a directory: ${path}`);
        }

        folders.push(`${RootPath}/${path}`);
      } else {
        folders = [
          `${RootPath}/build/media_source`,
          `${RootPath}/templates/cassiopeia/js`,
        ];
      }

      // Loop to get some text for the packgage.json
      folders.forEach((folder) => {
        Recurs(folder, ['*.min.js', '*.map', '*.scss', '*.css', '*.svg', '*.png', '*.swf', '*.gif', '*.json']).then(
          (files) => {
            files.forEach(
              (file) => {
                HandleFile.run(file, options);
              },
              (error) => {
                // eslint-disable-next-line no-console
                console.error(error.formatted);
              },
            );
          },
        );
      });
    })

    // Handle errors
    .catch((error) => {
      throw new Error(`${error}`);
    });
};
