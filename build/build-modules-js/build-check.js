/**
 * Will produce 2 .html files
 * Expects three files:
 *     build/warning_page/template.css
 *     build/warning_page/template.html
 *     build/warning_page/template.js
 *
 * And also specific strings in the languages in the installation folder!
 * Also the base strings are held in build/build-modules-js/settings.json
 */
const fs = require('fs');
const ini = require('ini');
const Recurs = require('recursive-readdir');
const uglifyCss = require('uglifycss');
const uglifyJs = require('uglify-es');
const rootPath = require('./rootpath.js')._();

const dir = `${rootPath}/installation/language`;
const srcPath = `${rootPath}/build/warning_page`;

// Set the initial template
let incomplete = 'window.errorLocale = {';
let unsupported = 'window.errorLocale = {';

const buildCheck = (options) => {
  let initTemplate = fs.readFileSync(`${srcPath}/template.html`, 'utf-8');
  let cssContent = fs.readFileSync(`${srcPath}/template.css`, 'utf-8');
  let jsContent = fs.readFileSync(`${srcPath}/template.js`, 'utf-8');

  cssContent = uglifyCss.processString(cssContent, { expandVars: false });
  jsContent = uglifyJs.minify(jsContent);

  Recurs(dir).then(
    (files) => {
      files.forEach((file) => {
        const languageStrings = ini.parse(fs.readFileSync(file, 'UTF-8'));

        // Build the variables into json for the unsupported page
        if (languageStrings.MIN_PHP_ERROR_LANGUAGE) {
          const name = file.replace('.ini', '').replace(/.+\//, '').replace(/.+\\/, '');
          unsupported += `"${name}":{"language":"${languageStrings.MIN_PHP_ERROR_LANGUAGE}","header":"${languageStrings.MIN_PHP_ERROR_HEADER}","text1":"${languageStrings.MIN_PHP_ERROR_TEXT}","help-url-text":"${languageStrings.MIN_PHP_ERROR_URL_TEXT}"},`;
        }

        // Build the variables into json for the unsupported page
        if (languageStrings.BUILD_INCOMPLETE_LANGUAGE) {
          const name = file.replace('.ini', '').replace(/.+\//, '').replace(/.+\\/, '');
          incomplete += `"${name}":{"language":"${languageStrings.BUILD_INCOMPLETE_LANGUAGE}","header":"${languageStrings.BUILD_INCOMPLETE_HEADER}","text1":"${languageStrings.BUILD_INCOMPLETE_TEXT}","help-url-text":"${languageStrings.BUILD_INCOMPLETE_URL_TEXT}"},`;
        }
      });

      unsupported = `${unsupported}}`;
      incomplete = `${incomplete}}`;

      for (const name in options.settings.errorPages) {
	      checkContent = initTemplate;
	      checkContent = checkContent.replace('{{jsonContents}}', name === 'incomplete' ? incomplete : unsupported);
          checkContent = checkContent.replace('{{PHP_VERSION}}', '');
          checkContent = checkContent.replace('{{Title}}', options.settings.errorPages[name].title);
          checkContent = checkContent.replace('{{Header}}', options.settings.errorPages[name].header);
          checkContent = checkContent.replace('{{Description}}', options.settings.errorPages[name].text);
          checkContent = checkContent.replace('{{Link}}', options.settings.errorPages[name].link);
          checkContent = checkContent.replace('{{LinkText}}', options.settings.errorPages[name].linkText);

        if (cssContent) {
          checkContent = checkContent.replace('{{cssContents}}', cssContent);
        }

        if (jsContent) {
          checkContent = checkContent.replace('{{jsContents}}', jsContent.code);
        }

        fs.writeFile(
          `${rootPath}${options.settings.errorPages[name].destFile}`,
          checkContent,
          (err) => {
            if (err) {
              // eslint-disable-next-line no-console
              console.log(err);
              return;
            }

            // eslint-disable-next-line no-console
            console.log(`The ${options.settings.errorPages[name].destFile} page was created successfully!`);
          },
        );
      }
    },
    (error) => {
      // eslint-disable-next-line no-console
      console.error('something exploded', error);
    },
  );
};

module.exports.buildCheck = buildCheck;
