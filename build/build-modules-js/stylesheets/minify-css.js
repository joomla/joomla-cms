const Fs = require('fs');
const UglyCss = require('uglifycss');

/**
 * Method to minify a .css file
 */
module.export.uglifyCssFile = (file) => {
  if (
    file.match(/\.css/)
    && !file.match(/\.min\.css/)
    && !file.match(/\.css\.map/)
    && !file.toLowerCase().match(/license/)
  ) {
    console.log(`Minifying: ${file}`);
    // Create the minified file
    Fs.writeFileSync(
        file.replace(/\.css$/, '.min.css'),
        UglyCss.processFiles([file], { expandVars: false }),
        { encoding: 'utf8' },
    );
  }
};
