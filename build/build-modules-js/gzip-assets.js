const Fs = require('fs');
const RootPath = require('./rootpath.js')._();
const WalkSync = require('./walk-sync.js');
const zopfli = require('node-zopfli-es');

const options = {
  verbose: false,
  verbose_more: false,
  numiterations: 15,
  blocksplitting: true,
  blocksplittinglast: false,
  blocksplittingmax: 15,
};

/**
 * Method to gzip the script and stylesheets files
 */
const gzipFiles = () => {
  // Minify the legacy files
  console.log('Gziping stylesheets and scripts...');
  const files = WalkSync.run(`${RootPath}/media`, []);

  if (files.length) {
    files.forEach(
      (file) => {
        if (file.match('/images') || file.match('\\images')) {
          return;
        }
        if (file.match(/\.min\.js/) && !file.toLowerCase().match(/json/) && !file.toLowerCase().match(/license/)) {
          console.log(`Processing: ${file}`);
          // Create the gziped file
          Fs.createReadStream(file)
            .pipe(zopfli.createGzip(options))
            .pipe(Fs.createWriteStream(file.replace(/\.js$/, '.js.gz')));
        }
        if (file.match(/\.min\.css/) && !file.match(/\.css\.map/) && !file.toLowerCase().match(/license/)) {
          console.log(`Processing: ${file}`);
          // Create the gziped file
          Fs.createReadStream(file)
            .pipe(zopfli.createGzip(options))
            .pipe(Fs.createWriteStream(file.replace(/\.css$/, '.css.gz')));
        }
      });
  }
};

module.exports.run = gzipFiles
