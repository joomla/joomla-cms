const Fs = require('fs');
const RootPath = require('./utils/rootpath.es6.js')._();
const WalkSync = require('./utils/walk-sync.es6.js');
const universalZopfli = require('@gfx/zopfli/dist');

const options = {
  verbose: false,
  verbose_more: false,
  numiterations: 15,
  blocksplitting: true,
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
          // Gzip the file
          universalZopfli.gzip(Fs.readFileSync(file, 'utf8'), options, (err, output) => {
            if (!err) {
              // Save the gzipped file
              Fs.writeFileSync(
                file.replace(/\.js$/, '.js.gz'),
                output,
                { encoding: 'utf8' },
              )
            } else {
              console.dir(`error: ${err}`);
            }
          });
        }
        if (file.match(/\.min\.css/) && !file.match(/\.css\.map/) && !file.toLowerCase().match(/license/)) {
          console.log(`Processing: ${file}`);
          // Gzip the file
          universalZopfli.gzip(Fs.readFileSync(file, 'utf8'), options, (err, output) => {
            if (!err) {
              // Save the gzipped file
              Fs.writeFileSync(
                file.replace(/\.css$/, '.css.gz'),
                output,
                { encoding: 'utf8' },
              )
            } else {
              console.dir(`error: ${err}`);
            }
          });
        }
      });
  }
};

module.exports.run = gzipFiles;
