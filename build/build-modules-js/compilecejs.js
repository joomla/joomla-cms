const babelify = require('babelify');
const browserify = require('browserify');
const chalk = require('chalk');
const fs = require('fs');
const fsExtra = require('fs-extra');
const Path = require('path');
const Promise = require('bluebird');
const UglifyJS = require('uglify-es');
const rootPath = require('./rootpath.js')._();

const compile = (options) => {
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

  options.settings.elements.forEach((element) => {
    const b = browserify();
    const c = browserify();

    // Copy the ES6 file
    const es6File = fs.readFileSync(`${rootPath}/build/webcomponents/js/${element}/${element}.js`, 'utf8');
    fs.writeFileSync(`${rootPath}/media/system/webcomponents/js/joomla-${element}.js`, es6File, { encoding: 'utf8' });

    // And the minified version
    fs.writeFileSync(`${rootPath}/media/system/webcomponents/js/joomla-${element}.min.js`, UglifyJS.minify(es6File).code, { encoding: 'utf8' });

    // Transpile a copy for ES5
    fs.writeFileSync(`${rootPath}/media/system/webcomponents/js/joomla-${element}-es5.js`, '');
    const bundleFs = fs.createWriteStream(`${rootPath}/media/system/webcomponents/js/joomla-${element}-es5.js`);
    const bundleFsMin = fs.createWriteStream(`${rootPath}/media/system/webcomponents/js/joomla-${element}-es5.min.js`);

    b.add(`${rootPath}/build/webcomponents/js/${element}/${element}.js`);
    c.add(`${rootPath}/build/webcomponents/js/${element}/${element}.js`);
    b.transform(babelify, { presets: ['babel-preset-es2015'] }).bundle().pipe(bundleFs);
    c.transform(babelify, { presets: ['babel-preset-es2015', 'babel-preset-minify'] }).bundle().pipe(bundleFsMin);
  });
};

const compileCEjs = (options, path) => {
  Promise.resolve()
    .then(() => compile(options, path))

    // Handle errors
    .catch((err) => {
      // eslint-disable-next-line no-console
      console.error(`${chalk.red(err)}`);
      process.exit(-1);
    });
};

module.exports.compile = compileCEjs;
