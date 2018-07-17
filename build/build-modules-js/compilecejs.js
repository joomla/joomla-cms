const autoprefixer = require('autoprefixer');
const babelify = require('babelify');
const browserify = require('browserify');
const fs = require('fs');
const fsExtra = require('fs-extra');
const kleur = require('kleur');
const Path = require('path');
const postcss = require('postcss');
const Promise = require('bluebird');
const Sass = require('node-sass');
const UglyCss = require('uglifycss');
const UglifyJS = require('uglify-es');
const rootPath = require('./rootpath.js')._();

const createJsFiles = (element, es6File) => {
  const b = browserify();
  const c = browserify();

  fs.writeFileSync(`${rootPath}/media/system/webcomponents/js/joomla-${element}.js`, es6File, { encoding: 'utf8' });

  // And the minified version
  fs.writeFileSync(`${rootPath}/media/system/webcomponents/js/joomla-${element}.min.js`, UglifyJS.minify(es6File).code, { encoding: 'utf8' });

  // Transpile a copy for ES5
  fs.writeFileSync(`${rootPath}/media/system/webcomponents/js/joomla-${element}-es5.js`, '');
  const bundleFs = fs.createWriteStream(`${rootPath}/media/system/webcomponents/js/joomla-${element}-es5.js`);
  const bundleFsMin = fs.createWriteStream(`${rootPath}/media/system/webcomponents/js/joomla-${element}-es5.min.js`);

  b.add(`${rootPath}/build/media/webcomponents/js/${element}/${element}.js`);
  c.add(`${rootPath}/build/media/webcomponents/js/${element}/${element}.js`);
  b.transform(babelify, { presets: ['babel-preset-es2015'] }).bundle().pipe(bundleFs);
  c.transform(babelify, { presets: ['babel-preset-es2015', 'babel-preset-minify'] }).bundle().pipe(bundleFsMin);
};

const compile = (options) => {
  // Make sure that the dist paths exist
  if (!fs.existsSync(`${rootPath}/media/system/webcomponents`)) {
    fsExtra.mkdirSync(`${rootPath}/media/system/webcomponents`);
  }
  if (!fs.existsSync(`${rootPath}/media/system/webcomponents/js`)) {
    fsExtra.mkdirSync(`${rootPath}/media/system/webcomponents/js`);
  }

  if (!fs.existsSync(`${rootPath}/media/system/webcomponents/css`)) {
    fs.mkdirSync(`${rootPath}/media/system/webcomponents/css`);
  }

  options.settings.elements.forEach((element) => {
    // Copy the ES6 file
    let es6File = fs.readFileSync(`${rootPath}/build/media/webcomponents/js/${element}/${element}.js`, 'utf8');
    // Check if there is a css file
    if (fs.existsSync(`${rootPath}/build/media/webcomponents/scss/${element}/${element}.scss`)) {
      if (!fs.existsSync(`${rootPath}/build/media/webcomponents/scss/${element}/${element}.scss`)) {
        return;
      }

      Sass.render({
        file: `${rootPath}/build/media/webcomponents/scss/${element}/${element}.scss`,
      }, (error, result) => {
        if (error) {
          // eslint-disable-next-line no-console
          console.error(`${kleur.red(error.column)}`);
          // eslint-disable-next-line no-console
          console.error(`${kleur.red(error.message)}`);
          // eslint-disable-next-line no-console
          console.error(`${kleur.red(error.line)}`);
        } else {
          // Auto prefixing
          // eslint-disable-next-line no-console
          console.log(`${kleur.blue('Prefixing for: ', options.settings.browsers)}`);

          const cleaner = postcss(
            [
              autoprefixer({
                add: false,
                browsers: options.settings.browsers
              }),
            ],
          );

          const prefixer = postcss([autoprefixer]);

          if (typeof result === 'object' && result.css) {
            cleaner.process(result.css.toString())
              .then(cleaned => prefixer.process(cleaned.css))
              .then((res) => {
                if (/{{CSS_CONTENTS_PLACEHOLDER}}/.test(es6File)) {
                  if (typeof res === 'object' && res.css) {
                    es6File = es6File.replace('{{CSS_CONTENTS_PLACEHOLDER}}', UglyCss.processString(res.css.toString()));

                    createJsFiles(element, es6File);
                  }
                } else {
                  if (typeof res === 'object' && res.css) {
                    fs.writeFileSync(
                      `${rootPath}/media/system/webcomponents/css/joomla-${element}.css`,
                      res.css.toString(),
                      { encoding: 'UTF-8' },
                    );
                    fs.writeFileSync(
                      `${rootPath}/media/system/webcomponents/css/joomla-${element}.min.css`,
                      UglyCss.processString(res.css.toString(), { expandVars: false }),
                      { encoding: 'UTF-8' },
                    );
                  }

                  createJsFiles(element, es6File);
                }
              })

              // Handle errors
              .catch((err) => {
                // eslint-disable-next-line no-console
                console.error(`${kleur.red(err)}`);
                process.exit(-1);
              });

            return ;
            // eslint-disable-next-line no-console
            console.log(kleur.yellow(`joomla-${element} was updated.`));
          }
        }
      });
    } else {
      createJsFiles(element, es6File);
    }
  });
};

const compileCEjs = (options, path) => {
  Promise.resolve()
    .then(() => compile(options, path))

    // Handle errors
    .catch((err) => {
      // eslint-disable-next-line no-console
      console.error(`${kleur.red(err)}`);
      process.exit(-1);
    });
};

module.exports.compile = compileCEjs;
