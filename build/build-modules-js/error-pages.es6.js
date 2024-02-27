const {
  access, mkdir, readFile, writeFile,
} = require('fs').promises;
const Ini = require('ini');
const { dirname } = require('path');
const Recurs = require('recursive-readdir');
const { transform } = require('esbuild');
const LightningCSS = require('lightningcss');

const RootPath = process.cwd();
const dir = `${RootPath}/installation/language`;
const srcPath = `${RootPath}/build/warning_page`;

/**
 * Will produce as many .html files as defined in settings.json
 * Expects three files:
 *     build/warning_page/template.css
 *     build/warning_page/template.html
 *     build/warning_page/template.js
 *
 * And also specific strings in the languages in the installation folder!
 * Also the base strings are held in build/build-modules-js/settings.json
 */
module.exports.createErrorPages = async (options) => {
  const iniFilesProcess = [];
  const processPages = [];
  global.incompleteObj = {};
  global.unsupportedObj = {};
  global.fatalObj = {};
  global.noxmlObj = {};

  const initTemplate = await readFile(`${srcPath}/template.html`, { encoding: 'utf8' });
  let cssContent = await readFile(`${srcPath}/template.css`, { encoding: 'utf8' });
  let jsContent = await readFile(`${srcPath}/template.js`, { encoding: 'utf8' });

  const { code } = LightningCSS.transform({
    code: Buffer.from(cssContent),
    minify: true,
  });

  cssContent = code;
  jsContent = await transform(jsContent, { minify: true });

  const processIni = async (file) => {
    const languageStrings = Ini.parse(await readFile(file, { encoding: 'utf8' }));

    // Build the variables into json for the unsupported page
    if (languageStrings.BUILD_MIN_PHP_ERROR_LANGUAGE) {
      const name = dirname(file).replace(/.+\//, '').replace(/.+\\/, '');
      global.unsupportedObj = {
        ...global.unsupportedObj,
        [name]: {
          language: languageStrings.BUILD_MIN_PHP_ERROR_LANGUAGE,
          header: languageStrings.BUILD_MIN_PHP_ERROR_HEADER,
          text1: languageStrings.BUILD_MIN_PHP_ERROR_TEXT,
          'help-url-text': languageStrings.BUILD_MIN_PHP_ERROR_URL_TEXT,
        },
      };
    }

    // Build the variables into json for the build incomplete page
    if (languageStrings.BUILD_INCOMPLETE_LANGUAGE) {
      const name = dirname(file).replace(/.+\//, '').replace(/.+\\/, '');
      global.incompleteObj = {
        ...global.incompleteObj,
        [name]: {
          language: languageStrings.BUILD_INCOMPLETE_LANGUAGE,
          header: languageStrings.BUILD_INCOMPLETE_HEADER,
          text1: languageStrings.BUILD_INCOMPLETE_TEXT,
          'help-url-text': languageStrings.BUILD_INCOMPLETE_URL_TEXT,
        },
      };
    }

    // Build the variables into json for the fatal error page
    if (languageStrings.BUILD_FATAL_LANGUAGE) {
      const name = dirname(file).replace(/.+\//, '').replace(/.+\\/, '');
      global.fatalObj = {
        ...global.fatalObj,
        [name]: {
          language: languageStrings.BUILD_FATAL_LANGUAGE,
          header: languageStrings.BUILD_FATAL_HEADER,
          text1: languageStrings.BUILD_FATAL_TEXT,
          'help-url-text': languageStrings.BUILD_FATAL_URL_TEXT,
        },
      };
    }

    // Build the variables into json for the missing XML error page
    if (languageStrings.BUILD_NOXML_LANGUAGE) {
      const name = dirname(file).replace(/.+\//, '').replace(/.+\\/, '');
      global.noxmlObj = {
        ...global.noxmlObj,
        [name]: {
          language: languageStrings.BUILD_NOXML_LANGUAGE,
          header: languageStrings.BUILD_NOXML_HEADER,
          text1: languageStrings.BUILD_NOXML_TEXT,
          'help-url-text': languageStrings.BUILD_NOXML_URL_TEXT,
        },
      };
    }
  };

  const files = await Recurs(dir);
  files.sort().forEach((file) => {
    if (file.endsWith('langmetadata.xml')) {
      return;
    }
    iniFilesProcess.push(processIni(file));
  });

  await Promise.all(iniFilesProcess).catch((err) => {
    // eslint-disable-next-line no-console
    console.error(err);
    process.exitCode = -1;
  });

  const processPage = async (name) => {
    const sortedJson = Object.fromEntries(Object.entries(global[`${name}Obj`]).sort());
    const jsonContent = `window.errorLocale=${JSON.stringify(sortedJson)};`;

    let template = initTemplate;

    template = template.replace('{{jsonContents}}', jsonContent);
    template = template.replace('{{Title}}', options.settings.errorPages[name].title);
    template = template.replace('{{Header}}', options.settings.errorPages[name].header);
    template = template.replace('{{Description}}', options.settings.errorPages[name].text);
    template = template.replace('{{Link}}', options.settings.errorPages[name].link);
    template = template.replace('{{LinkText}}', options.settings.errorPages[name].linkText);

    if (cssContent) {
      template = template.replace('{{cssContents}}', cssContent);
    }

    if (jsContent) {
      template = template.replace('{{jsContents}}', jsContent.code);
    }

    let mediaExists = false;
    try {
      await access(dirname(`${RootPath}${options.settings.errorPages[name].destFile}`));
      mediaExists = true;
    } catch (err) {
      // Do nothing
    }

    if (!mediaExists) {
      await mkdir(dirname(`${RootPath}${options.settings.errorPages[name].destFile}`), { recursive: true, mode: 0o755 });
    }

    await writeFile(
      `${RootPath}${options.settings.errorPages[name].destFile}`,
      template,
      { encoding: 'utf8', mode: 0o644 },
    );

    // eslint-disable-next-line no-console
    console.error(`✅ Created the file: ${options.settings.errorPages[name].destFile}`);
  };

  Object.keys(options.settings.errorPages).forEach((name) => processPages.push(processPage(name)));

  return Promise.all(processPages).catch((err) => {
    // eslint-disable-next-line no-console
    console.error(err);
    process.exitCode = -1;
  });
};
