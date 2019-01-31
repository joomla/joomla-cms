const Fs = require('fs');
const Path = require('path');
const Recurs = require('recursive-readdir');
const UglifyJS = require('uglify-es');
const TranspileJs = require('./javascript/compile-es6.es6.js');
const TranspileWc = require('./javascript/compile-w-c.es6.js');
const MakeDir = require('./utils/make-dir.es6.js');
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
    .then((options, path) => {
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
          `${RootPath}/administrator/templates/atum/js`,
          `${RootPath}/templates/cassiopeia/js`,
        ];
      }
    
      // Loop to get some text for the packgage.json
      folders.forEach((folder) => {
        Recurs(folder, ['*.min.js', '*.map', '*.scss', '*.css', '*.svg', '*.png', '*.swf', '*.gif', '*.json']).then(
          (files) => {
            files.forEach(
              (file) => {
                if (file.match(/\.js/) && file.match(/\.es6\.js/) && !file.match(/\.w-c\.es6\.js/)) {
                  // ES6 file so we need to transpile it
                  TranspileJs.compileFile(file);
                } else if (file.match(/\.js/) && file.match(/\.es5\.js/)) {
                  // ES5 file, we will copy the file and then minify it in place
                  // Ensure that the directories exist or create them
                  MakeDir.run(Path.dirname(file).replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\'));
                  Fs.copyFileSync(file, file.replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\').replace('.es5.js', '.js'));
                  Fs.writeFileSync(file.replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\').replace('.es5.js', '.min.js'), UglifyJS.minify(Fs.readFileSync(file, 'utf8')).code, { encoding: 'utf8' });
                  // eslint-disable-next-line no-console
                  console.log(`Es5 file copied/minified: ${file}`);
                } else if (file.match(/\.js/) && file.match(/\.w-c\.es6\.js/)) {
                  // Web Component, so we need to transpile it
                  TranspileWc.compile(file, options);
                }
              },
              (error) => {
                // eslint-disable-next-line no-console
                console.error(`something exploded ${error}`);
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
