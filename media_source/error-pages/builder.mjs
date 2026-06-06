/**
 * Assets Builder
 */
import fs from 'node:fs';
import fsp from 'node:fs/promises';
import path from 'node:path';
import Ini from 'ini';
import buildSettings from '../../build/build-modules-js/settings.json' with { type: 'json' };
import DefaultModuleBuilder from '../../build/build-modules-js/builder/default-module-builder.mjs';
import { minifyCSS } from '../../build/build-modules-js/stylesheets/css-handler.mjs';
import { minifyJSContent } from '../../build/build-modules-js/javascript/js-handle.mjs';

const processIni = async (file, state) => {
  const languageStrings = Ini.parse(fs.readFileSync(file, { encoding: 'utf8' }));

  // Build the variables into json for the unsupported page
  if (languageStrings.BUILD_MIN_PHP_ERROR_LANGUAGE) {
    const name = path.dirname(file).replace(/.+\//, '').replace(/.+\\/, '');
    state.unsupportedObj = {
      ...state.unsupportedObj,
      [name]: {
        language: Ini.unsafe(languageStrings.BUILD_MIN_PHP_ERROR_LANGUAGE),
        header: languageStrings.BUILD_MIN_PHP_ERROR_HEADER,
        text1: languageStrings.BUILD_MIN_PHP_ERROR_TEXT,
        'help-url-text': languageStrings.BUILD_MIN_PHP_ERROR_URL_TEXT,
      },
    };
  }

  // Build the variables into json for the build incomplete page
  if (languageStrings.BUILD_INCOMPLETE_LANGUAGE) {
    const name = path.dirname(file).replace(/.+\//, '').replace(/.+\\/, '');
    state.incompleteObj = {
      ...state.incompleteObj,
      [name]: {
        language: Ini.unsafe(languageStrings.BUILD_INCOMPLETE_LANGUAGE),
        header: languageStrings.BUILD_INCOMPLETE_HEADER,
        text1: languageStrings.BUILD_INCOMPLETE_TEXT,
        'help-url-text': languageStrings.BUILD_INCOMPLETE_URL_TEXT,
      },
    };
  }

  // Build the variables into json for the fatal error page
  if (languageStrings.BUILD_FATAL_LANGUAGE) {
    const name = path.dirname(file).replace(/.+\//, '').replace(/.+\\/, '');
    state.fatalObj = {
      ...state.fatalObj,
      [name]: {
        language: Ini.unsafe(languageStrings.BUILD_FATAL_LANGUAGE),
        header: languageStrings.BUILD_FATAL_HEADER,
        text1: languageStrings.BUILD_FATAL_TEXT,
        'help-url-text': languageStrings.BUILD_FATAL_URL_TEXT,
      },
    };
  }

  // Build the variables into json for the missing XML error page
  if (languageStrings.BUILD_NOXML_LANGUAGE) {
    const name = path.dirname(file).replace(/.+\//, '').replace(/.+\\/, '');
    state.noxmlObj = {
      ...state.noxmlObj,
      [name]: {
        language: Ini.unsafe(languageStrings.BUILD_NOXML_LANGUAGE),
        header: languageStrings.BUILD_NOXML_HEADER,
        text1: languageStrings.BUILD_NOXML_TEXT,
        'help-url-text': languageStrings.BUILD_NOXML_URL_TEXT,
      },
    };
  }
};

const processPage = async (name, pageInfo, state, template, css, js) => {
  const sortedJson = Object.fromEntries(Object.entries(state[`${name}Obj`]).sort());
  const jsonContent = `window.errorLocale=${JSON.stringify(sortedJson)};`;
  const rootPath = process.cwd();

  template = template.replace('{{jsonContents}}', jsonContent);
  template = template.replace('{{Title}}', pageInfo.title);
  template = template.replace('{{Header}}', pageInfo.header);
  template = template.replace('{{Description}}', pageInfo.text);
  template = template.replace('{{Link}}', pageInfo.link);
  template = template.replace('{{LinkText}}', pageInfo.linkText);

  if (css) {
    template = template.replace('{{cssContents}}', css);
  }

  if (js) {
    template = template.replace('{{jsContents}}', js);
  }

  const promises = [];
  pageInfo.destFile.forEach((file) => {
    const fullPath = path.join(rootPath, file);
    const folder = path.dirname(fullPath);

    if (!fs.existsSync(folder)) {
      fs.mkdirSync(folder, { recursive: true, mode: 0o755});
    }

    promises.push(fsp.writeFile(fullPath, template, { encoding: 'utf8', mode: 0o644 }));
  });

  return Promise.all(promises);
};

/**
 * Will produce as many .html files as defined in settings.json
 * Expects three files:
 *     build/warning_page/template.css
 *     build/warning_page/template.html
 *     build/warning_page/template.js
 *
 * And also specific strings in the languages in the installation folder!
 * Also, the base strings are held in build/build-modules-js/settings.json
 */
const createErrorPages = async (options, basePath) => {
  const srcPath = path.join(basePath, 'warning_page');
  const langsPath = 'installation/language';
  const state = {
    incompleteObj: {},
    unsupportedObj: {},
    fatalObj: {},
    noxmlObj: {},
  };

  // Lookup and parse language files
  const iniFilesProcess = [];
  fs.readdirSync(langsPath, { recursive: true, withFileTypes: true}).forEach((file) => {
    if (file.isDirectory() || file.name.endsWith('langmetadata.xml')) {
      return;
    }
    const iniFile = path.join(file.parentPath, file.name);

    iniFilesProcess.push(processIni(iniFile, state).catch((error) => {
      throw new Error(`Parsing INI file failed for "${iniFile}".`, { cause: error });
    }));
  });

  await Promise.all(iniFilesProcess);

  // Load template files
  const initTemplate = fs.readFileSync(path.join(srcPath, 'template.html'), { encoding: 'utf8' });
  let cssContent = fs.readFileSync(path.join(srcPath, 'template.css'), { encoding: 'utf8' });
  let jsContent = fs.readFileSync(path.join(srcPath, 'template.js'), { encoding: 'utf8' });

  cssContent = await minifyCSS(cssContent);
  jsContent = await minifyJSContent(jsContent);

  // Lookup and build the pages
  const processPages = [];
  Object.keys(options.settings.errorPages).forEach((name) => {
    const pageInfo = options.settings.errorPages[name];

    processPages.push(processPage(name, pageInfo, state, initTemplate, cssContent, jsContent).catch((error) => {
      throw new Error(`Failed to build page "${name}".`, { cause: error });
    }))
  });

  return Promise.all(processPages);
};


export default class ErrorPagesModuleBuilder extends DefaultModuleBuilder
{
  tasksBuild = ['build'];
  tasksExtras = [];

  /**
   * Create the pages
   * @returns { Promise }
   */
  async build() {
    return createErrorPages(buildSettings, this.basePath);
  }
};
