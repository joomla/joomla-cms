const Fs = require('fs');
const RootPath = require('./utils/rootpath.es6.js')._();
const WalkSync = require('./utils/walk-sync.es6.js');
const { gzipAsync } = require('@gfx/zopfli');
const { compress } = require('wasm-brotli');
const { promisify } = require('util');

const writeFileAsync = promisify(Fs.writeFile);

const options = {
  verbose: false,
  verbose_more: false,
  numiterations: 15,
  blocksplitting: true,
  blocksplittingmax: 15,
};

/**
 * Method that will create a gzipped vestion of the given file
 *
 * @param   { string }  file  The path of the file
 *
 * @returns { void }
 */
const handleFile = (file) => {
  if (file.match('/images') || file.match('\\images')) {
    return;
  }
  if (file.match(/\.min\.js/) && !file.match(/\.min\.js\.gz/) && !file.toLowerCase().match(/json/) && !file.toLowerCase().match(/license/)) {
    console.log(`Processing: ${file}`);
    // Gzip the file
    gzipAsync(Fs.readFileSync(file, 'utf8'), options, (err, output) => {
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

    // Brotli file
    (async (file) => {
      try {
        const compressedContent = await compress(Buffer.from(Fs.readFileSync(file, 'utf8'), 'utf8'));
        await writeFileAsync(file.replace(/\.js$/, '.js.gz'), compressedContent);
      } catch (err) {
        console.error(err);
      }
    })(file);
  }
  if (file.match(/\.min\.css/) && !file.match(/\.min\.css\.gz/) && !file.match(/\.css\.map/) && !file.toLowerCase().match(/license/)) {
    console.log(`Processing: ${file}`);
    // Gzip the file
    gzipAsync(Fs.readFileSync(file, 'utf8'), options, (err, output) => {
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

      // Brotli file
      (async (file) => {
        try {
          const compressedContent = await compress(Buffer.from(Fs.readFileSync(file, 'utf8'), 'utf8'));
          await writeFileAsync(file.replace(/\.css$/, '.css.gz'), compressedContent);
        } catch (err) {
          console.error(err);
        }
      })(file);
    });
  }
};

/**
 * Method to gzip the script and stylesheets files
 *
 * @returns { void }
 */
const gzipFiles = () => {
  // Minify the legacy files
  console.log('Gziping stylesheets and scripts...');

    const templatesFiles = WalkSync.run(`${RootPath}/templates`, []);
    const adminTemplatesFiles = WalkSync.run(`${RootPath}/administrator/templates`, []);
    const mediaFiles = WalkSync.run(`${RootPath}/media`, []);

    if (templatesFiles.length) {
      templatesFiles.forEach(file => handleFile(file));
    }
    if (adminTemplatesFiles.length) {
      adminTemplatesFiles.forEach(file => handleFile(file));
    }
    if (mediaFiles.length) {
      mediaFiles.forEach(file => handleFile(file));
    }
};

module.exports.run = gzipFiles;
