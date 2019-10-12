const Fs = require('fs');
const FsExtra = require('fs-extra');
const Path = require('path');
const UglifyJS = require('uglify-es');
const TranspileJs = require('./compile-es6.es6.js');
const TranspileWc = require('./compile-w-c.es6.js');

module.exports.run = (file) => {
  if (file.match(/\.js/) && file.match(/\.es6\.js/) && !file.match(/\.w-c\.es6\.js/)) {
    // ES6 file so we need to transpile it
    TranspileJs.compileFile(file);
  } else if (file.match(/\.js/) && file.match(/\.es5\.js/)) {
    // ES5 file, we will copy the file and then minify it in place
    // Ensure that the directories exist or create them
    FsExtra.mkdirsSync(Path.dirname(file).replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\'), {});
    Fs.copyFileSync(file, file.replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\').replace('.es5.js', '.js'));
    Fs.writeFileSync(file.replace('/build/media_source/', '/media/').replace('\\build\\media_source\\', '\\media\\').replace('.es5.js', '.min.js'), UglifyJS.minify(Fs.readFileSync(file, 'utf8')).code, { encoding: 'utf8' });
    // eslint-disable-next-line no-console
    console.log(`Es5 file copied/minified: ${file}`);
  } else if (file.match(/\.js/) && file.match(/\.w-c\.es6\.js/)) {
    // Web Component, so we need to transpile it
    TranspileWc.compile(file);
  }
};
