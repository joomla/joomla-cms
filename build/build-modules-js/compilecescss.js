const autoprefixer = require('autoprefixer');
const chalk = require('chalk');
const fs = require('fs');
const fsExtra = require('fs-extra');
const Path = require('path');
const postcss = require('postcss');
const Promise = require('bluebird');
const Sass = require('node-sass');
const UglyCss = require('uglifycss');
const rootPath = require('./rootpath.js')._();

const compileSass = (options) => {
  const files = options.settings.elements;

  const dist = `${rootPath}/media/system/webcomponents/css`;

  // Make sure that the dist paths exist
  if (!fs.existsSync(`${rootPath}/media/system/webcomponents`)) {
    fsExtra.mkdirSync(`${rootPath}/media/system/webcomponents`);
  }
  if (!fs.existsSync(`${rootPath}/media/system/webcomponents/js`)) {
    fsExtra.mkdirSync(`${rootPath}/media/system/webcomponents/js`);
  }

  if (!fs.existsSync(Path.join(rootPath, '/media/system/webcomponents/css'))) {
    fs.mkdirSync(Path.join(rootPath, '/media/system/webcomponents/css'));
  }

  // Loop to get some text for the packgage.json
  files.forEach((name) => {
    if (!fs.existsSync(`${rootPath}/build/webcomponents/scss/${name}/${name}.scss`)) {
      return;
    }

    Sass.render({
      file: `${rootPath}/build/webcomponents/scss/${name}/${name}.scss`,
    }, (error, result) => {
      if (error) {
        // eslint-disable-next-line no-console
        console.error(`${chalk.red(error.column)}`);
        // eslint-disable-next-line no-console
        console.error(`${chalk.red(error.message)}`);
        // eslint-disable-next-line no-console
        console.error(`${chalk.red(error.line)}`);
      } else {
        // Auto prefixing
        // eslint-disable-next-line no-console
        console.log(`${chalk.blue('Prefixing for: ', options.settings.browsers)}`);

        const cleaner = postcss([
          autoprefixer({
            add: false,
            browsers: options.settings.browsers,
          }),
        ]);
        const prefixer = postcss([autoprefixer]);

        if (typeof result === 'object' && result.css) {
          cleaner.process(result.css.toString())

            .then((cleaned) => {
              if (typeof cleaned === 'object' && cleaned.css) {
                return prefixer.process(cleaned.css);
              }
              return '';
            })

            .then((res) => {
              if (typeof res === 'object' && res.css) {
                fs.writeFileSync(
                  `${dist}/joomla-${name}.css`,
                  res.css.toString(),
                  { encoding: 'UTF-8' },
                );
                fs.writeFileSync(
                  `${dist}/joomla-${name}.min.css`,
                  UglyCss.processFiles([`${dist}/joomla-${name}.css`], { expandVars: false }),
                  { encoding: 'UTF-8' },
                );
              }
            })

          // Handle errors
            .catch((err) => {
              // eslint-disable-next-line no-console
              console.error(`${chalk.red(err)}`);
              process.exit(-1);
            });

          // eslint-disable-next-line no-console
          console.log(chalk.yellow(`${dist}/joomla-${name} was updated.`));
        }
      }
    });
  });
  // eslint-disable-next-line no-console
  console.log(`${chalk.yellow(' All sass files were compiled.')}`);
};

const compile = (options, path) => {
  Promise.resolve()
    .then(() => compileSass(options, path))

    // Handle errors
    .catch((err) => {
      // eslint-disable-next-line no-console
      console.error(`${chalk.red(err)}`);
      process.exit(-1);
    });
};

module.exports.compile = compile;
