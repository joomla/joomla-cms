const fs = require('fs');
const ini = require('ini');
const Recurs = require('recursive-readdir');
const uglifyCss = require('uglifycss');
const uglifyJs = require('uglify-es');
const rootPath = require('./rootpath.js')._();

const dir = `${rootPath}/installation/language`;
const installationFile = `${rootPath}/templates/system/incompatible.html`;
const srcPath = `${rootPath}/build/incompatible_page`;

// Set the initial template
let template = 'window.errorLocale = {';

const installation = () => {
  let installationContent = fs.readFileSync(`${srcPath}/incompatible.html`, 'utf-8');
  let cssContent = fs.readFileSync(`${srcPath}/incompatible.css`, 'utf-8');
  let jsContent = fs.readFileSync(`${srcPath}/incompatible.js`, 'utf-8');

  cssContent = uglifyCss.processString(cssContent, { expandVars: false });
  jsContent = uglifyJs.minify(jsContent);

  Recurs(dir).then(
    (files) => {
      files.forEach((file) => {
        const languageStrings = ini.parse(fs.readFileSync(file, 'UTF-8'));
        if (languageStrings.MIN_PHP_ERROR_LANGUAGE) {
          const name = file.replace('.ini', '').replace(/.+\//, '');
          template += `
"${name}":{"language":"${languageStrings.MIN_PHP_ERROR_LANGUAGE}","header":"${languageStrings.MIN_PHP_ERROR_HEADER}","text1":"${languageStrings.MIN_PHP_ERROR_TEXT}","help-url-text":"${languageStrings.MIN_PHP_ERROR_URL_TEXT}"},`;
        }
      });

      template = `${template}
}`;

      installationContent = installationContent.replace('{{jsonContents}}', template);

      if (cssContent) {
        installationContent = installationContent.replace('{{cssContents}}', cssContent);
      }

      if (jsContent) {
        installationContent = installationContent.replace('{{jsContents}}', jsContent.code);
      }

      fs.writeFile(
        installationFile,
        installationContent,
        (err) => {
          if (err) {
            // eslint-disable-next-line no-console
            console.log(err);
            return;
          }

          // eslint-disable-next-line no-console
          console.log('The installation error page was saved!');
        },
      );
    },
    (error) => {
      // eslint-disable-next-line no-console
      console.error('something exploded', error);
    },
  );
};

module.exports.installation = installation;
