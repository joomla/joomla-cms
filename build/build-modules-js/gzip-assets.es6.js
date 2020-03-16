/**
 * For creating Brotli files you need to install iltorb
 * and import it like:
 * const { compressStream } = require('iltorb');
 */
const Fs = require('fs');
const { gzip } = require('@gfx/zopfli');
const walkSync = require('walk-sync');

const RootPath = process.cwd();
const compressStream = '';
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
const handleFile = (file, enableBrotli) => {
  if (file.match('/images') || file.match('\\images')) {
    return;
  }


  if (file.match(/\.min\.js/) && !file.match(/\.min\.js\.gz/) && !file.match(/\.min\.js\.br/) && !file.toLowerCase().match(/json/) && !file.toLowerCase().match(/license/)) {
    // eslint-disable-next-line no-console
    console.log(`Processing: ${file}`);

    if (enableBrotli && compressStream) {
      // Brotli file
      Fs.createReadStream(file)
        .pipe(compressStream())
        .pipe(Fs.createWriteStream(file.replace(/\.js$/, '.js.br')));
    } else {
      // Gzip the file
      Fs.readFile(file, (err, data) => {
        if (err) throw err;
        gzip(data, options, (error, output) => {
          if (error) throw err;
          // Save the gzipped file
          Fs.writeFileSync(
            file.replace(/\.js$/, '.js.gz'),
            output,
            { encoding: 'utf8' },
          );
        });
      });
    }
  }

  if (file.match(/\.min\.css/) && !file.match(/\.min\.css\.gz/) && !file.match(/\.min\.css\.br/) && !file.match(/\.css\.map/) && !file.toLowerCase().match(/license/)) {
    // eslint-disable-next-line no-console
    console.log(`Processing: ${file}`);

    if (enableBrotli && compressStream) {
      // Brotli file
      Fs.createReadStream(file)
        .pipe(compressStream())
        .pipe(Fs.createWriteStream(file.replace(/\.css$/, '.css.br')));
    } else {
      // Gzip the file
      Fs.readFile(file, (err, data) => {
        if (err) throw err;
        gzip(data, options, (error, output) => {
          if (error) throw err;
          // Save the gzipped file
          Fs.writeFileSync(
            file.replace(/\.css$/, '.css.gz'),
            output,
            { encoding: 'utf8' },
          );
        });
      });
    }
  }
};

/**
 * Method to gzip the script and stylesheet files
 *
 * @param brotliParam {string} The CLI argument
 *
 * @returns { void }
 */
const gzipFiles = (brotliParam) => {
  let enableBrotli = false;
  if (brotliParam === 'brotli') {
    enableBrotli = true;
  }
  // Minify the legacy files
  // eslint-disable-next-line no-console
  console.log('Gziping stylesheets and scripts...');

  const templatesFiles = walkSync(`${RootPath}/templates`, {
    globs: ['**/*.{js,css}'],
    includeBasePath: true,
    ignore: [],
    directories: false,
  });
  const adminTemplatesFiles = walkSync(`${RootPath}/administrator/templates`, {
    globs: ['**/*.{js,css}'],
    includeBasePath: true,
    ignore: [],
    directories: false,
  });
  const mediaFiles = walkSync(`${RootPath}/media`, {
    globs: ['**/*.{js,css}'],
    includeBasePath: true,
    ignore: [],
    directories: false,
  });

  if (templatesFiles.length) {
    templatesFiles.forEach(file => handleFile(file, enableBrotli));
  }
  if (adminTemplatesFiles.length) {
    adminTemplatesFiles.forEach(file => handleFile(file, enableBrotli));
  }
  if (mediaFiles.length) {
    mediaFiles.forEach(file => handleFile(file, enableBrotli));
  }
};

module.exports.gzipFiles = gzipFiles;
