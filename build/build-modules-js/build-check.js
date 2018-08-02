const fs = require('fs');
const ini = require('ini');
const Recurs = require('recursive-readdir');
const uglifyCss = require('uglifycss');
const uglifyJs = require('uglify-es');
const rootPath = require('./rootpath.js')._();

const dir = `${rootPath}/installation/language`;
const installationFile = `${rootPath}/templates/system/build_incomplete.html`;
const srcPath = `${rootPath}/build/repo_build_incomplete`;

// Set the initial template
let template = 'window.errorLocale = {';

const buildCheck = () => {
  let checkContent = fs.readFileSync(`${srcPath}/incomplete.html`, 'utf-8');
  let cssContent = fs.readFileSync(`${srcPath}/incomplete.css`, 'utf-8');
  let jsContent = fs.readFileSync(`${srcPath}/incomplete.js`, 'utf-8');

  cssContent = uglifyCss.processString(cssContent, { expandVars: false });
  jsContent = uglifyJs.minify(jsContent);

  Recurs(dir).then(
    (files) => {
      files.forEach((file) => {
        const languageStrings = ini.parse(fs.readFileSync(file, 'UTF-8'));
        if (languageStrings.BUILD_INCOMPLETE_LANGUAGE) {
          const name = file.replace('.ini', '').replace(/.+\//, '');
          template += `
"${name}":{"language":"${languageStrings.BUILD_INCOMPLETE_LANGUAGE}","header":"${languageStrings.BUILD_INCOMPLETE_HEADER}","text1":"${languageStrings.BUILD_INCOMPLETE_TEXT}","help-url-text":"${languageStrings.BUILD_INCOMPLETE_URL_TEXT}"},`;
        }
      });

      template = `${template}
}`;

      checkContent = checkContent.replace('{{jsonContents}}', template);

      if (cssContent) {
        checkContent = checkContent.replace('{{cssContents}}', cssContent);
      }

      if (jsContent) {
        checkContent = checkContent.replace('{{jsContents}}', jsContent.code);
      }

      fs.writeFile(
        installationFile,
        checkContent,
        (err) => {
          if (err) {
            // eslint-disable-next-line no-console
            console.log(err);
            return;
          }

          // eslint-disable-next-line no-console
          console.log('The build check error page was saved!');
        },
      );
    },
    (error) => {
      // eslint-disable-next-line no-console
      console.error('something exploded', error);
    },
  );
};

module.exports.buildCheck = buildCheck;
