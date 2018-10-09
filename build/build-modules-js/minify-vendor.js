const UglifyJS = require('uglify-es');
const path = require('path');
const fs = require('fs');
const rootPath = require('./rootpath.js')._();


// List all files in a directory in Node.js recursively in a synchronous fashion
const walkSync = function(dir, filelist) {
  const files = fs.readdirSync(dir);
  filelist = filelist || [];
  files.forEach(function(file) {
    if (fs.statSync(path.join(dir, file)).isDirectory()) {
      filelist = walkSync(path.join(dir, file), filelist);
    }
    else {
      filelist.push(path.join(dir, file));
    }
  });
  return filelist;
};

const fixVendors = () => {
  Promise.resolve()
    .then(() => {
      const folders = [
        path.join(rootPath, 'media/vendor/codemirror'),
        path.join(rootPath, 'media/vendor/jquery-ui/js'),
        path.join(rootPath, 'media/vendor/punycode/js'),
        path.join(rootPath, 'media/vendor/tinymce/langs'),
        path.join(rootPath, 'media/vendor/webcomponentsjs'),
      ];

      // Loop to get some text for the packgage.json
      folders.forEach((folder) => {
        const files = walkSync(folder);

        if (files.length) {
          files.forEach(
            (file) => {
              if (file.match(/\.js/) && !file.match(/LICENSE\.md/)) {
              console.log(`Processing: ${file}`);
                // Write the file
                fs.writeFileSync(file.replace('.js', '.min.js'), UglifyJS.minify(fs.readFileSync(file, 'utf8')).code, {encoding: 'utf8'});
              }
            });
        }
      });
    })

    // Handle errors
    .catch((err) => {
      // eslint-disable-next-line no-console
      console.error(`${err}`);
      process.exit(-1);
    });
};

module.exports.compile = fixVendors;
