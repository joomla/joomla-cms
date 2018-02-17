const Promise = require('bluebird');
const fs = require('fs');
const fsExtra = require('fs-extra');
const Path = require('path');
const Chalk = require('chalk');

// Various variables
const rootPath = __dirname.replace('/build/build-modules-js', '').replace('\\build\\build-modules-js', '');
const xmlVersionStr = /(<version>)(\d+.\d+.\d+)(<\/version>)/;

// rm -rf media/vendor
const cleanVendors = () => {
  // Let's keep some tinyMCE folders
  fsExtra.copySync(Path.join(rootPath, 'media/vendor/tinymce/langs'), Path.join(rootPath, 'build/tiny_langs'));
  fsExtra.copySync(Path.join(rootPath, 'media/vendor/tinymce/templates'), Path.join(rootPath, 'build/tiny_templates'));
  fsExtra.copySync(Path.join(rootPath, 'media/vendor/jquery-ui'), Path.join(rootPath, 'build/jquery-ui'));

  fsExtra.removeSync(Path.join(rootPath, 'media/vendor'));
  fsExtra.removeSync(Path.join(rootPath, 'media/system/js/polyfills'));

  // Restore and erase the tmp folders
  fsExtra.copySync(Path.join(rootPath, 'build/tiny_langs'), Path.join(rootPath, 'media/vendor/tinymce/langs'));
  fsExtra.copySync(Path.join(rootPath, 'build/tiny_templates'), Path.join(rootPath, 'media/vendor/tinymce/templates'));
  fsExtra.copySync(Path.join(rootPath, 'build/jquery-ui'), Path.join(rootPath, 'media/vendor/jquery-ui'));
  fsExtra.removeSync(Path.join(rootPath, 'build/tiny_langs'));
  fsExtra.removeSync(Path.join(rootPath, 'build/tiny_templates'));
  fsExtra.removeSync(Path.join(rootPath, 'build/jquery-ui'));

  console.log(Chalk.blue('/media/vendor has been removed.'));
};

// Copies all the files from a directory
const copyAll = (dirName, name, type) => {
  const folderName = dirName === '/' ? '/' : `/${dirName}`;
  fsExtra.copySync(
    Path.join(rootPath, `node_modules/${name}/${folderName}`),
    Path.join(rootPath, `media/vendor/${name.replace(/.+\//, '')}/${type}`),
  );
};

// Copies an array of files from a directory
const copyArrayFiles = (dirName, files, name, type) => {
  files.forEach((file) => {
    const folderName = dirName === '/' ? '/' : `/${dirName}/`;
    if (fsExtra.existsSync(`node_modules/${name}${folderName}${file}`)) {
      fsExtra.copySync(`node_modules/${name}${folderName}${file}`, `media/vendor/${name.replace(/.+\//, '')}${type ? `/${type}` : ''}/${file}`);
    }
  });
};
/**
 *
 * @param files   Object of files map, eg {"src.js": "js/src.js"}
 * @param srcDir  Package root dir
 * @param destDir Vendor destination dir
 * @param type
 * @returns {Array}
 */
const copyFilesTo = (files, srcDir, destDir) => {
  const filesResult = [];

  // Copy each file
  files.forEach((srcFile) => {
    const destFile = files[srcFile];
    const srcPath = Path.join(srcDir, srcFile);
    const destPath = Path.join(destDir, destFile);

    fsExtra.copySync(srcPath, destPath);
    filesResult.push(destPath);
  });

  return filesResult;
};

// Concatenate some files
const concatFiles = (files, output) => {
  let tempMem = '';
  files.forEach((file) => {
    if (fsExtra.existsSync(`${rootPath}/${file}`)) {
      tempMem += fs.readFileSync(`${rootPath}/${file}`);
    }
  });

  fs.writeFileSync(`${rootPath}/${output}`, tempMem);
};

const copyFiles = (options) => {
  const mediaVendorPath = Path.join(rootPath, 'media/vendor');
  const registry = {
    name: options.name,
    version: options.version,
    description: options.description,
    license: options.license,
    vendors: {},
  };

  if (!fsExtra.existsSync(mediaVendorPath)) {
    fsExtra.mkdirSync(mediaVendorPath);
  }

  // Loop to get some text for the packgage.json
  options.settings.vendors.forEach((packageName) => {
    const vendor = options.settings.vendors[packageName];
    const vendorName = vendor.name || packageName;
    const modulePathJson = require.resolve(`${packageName}/package.json`);
    const modulePathRoot = Path.dirname(modulePathJson);
    /* eslint-disable global-require */
    /* eslint-disable import/no-dynamic-require */
    const moduleOptions = require(modulePathJson.toString());
    /* eslint-enable global-require */
    /* eslint-enable import/no-dynamic-require */
    const registryItem = {
      package: packageName,
      name: vendorName,
      version: moduleOptions.version,
      dependencies: vendor.dependencies || [],
    };

    if (packageName === 'codemirror') {
      const itemvendorPath = Path.join(rootPath, `media/vendor/${packageName}`);
      if (!fsExtra.existsSync(itemvendorPath)) {
        fsExtra.mkdirSync(itemvendorPath);
        fsExtra.mkdirSync(Path.join(itemvendorPath, 'addon'));
        fsExtra.mkdirSync(Path.join(itemvendorPath, 'lib'));
        fsExtra.mkdirSync(Path.join(itemvendorPath, 'mode'));
        fsExtra.mkdirSync(Path.join(itemvendorPath, 'keymap'));
        fsExtra.mkdirSync(Path.join(itemvendorPath, 'theme'));
      }

      copyAll('addon', 'codemirror', 'addon');
      copyAll('lib', 'codemirror', 'lib');
      copyAll('mode', 'codemirror', 'mode');
      copyAll('keymap', 'codemirror', 'keymap');
      copyAll('theme', 'codemirror', 'theme');

      concatFiles(
        [
          'media/vendor/codemirror/addon/display/fullscreen.js',
          'media/vendor/codemirror/addon/display/panel.js',
          'media/vendor/codemirror/addon/edit/closebrackets.js',
          'media/vendor/codemirror/addon/edit/closetag.js',
          'media/vendor/codemirror/addon/edit/matchbrackets.js',
          'media/vendor/codemirror/addon/edit/matchtags.js',
          'media/vendor/codemirror/addon/fold/brace-fold.js',
          'media/vendor/codemirror/addon/fold/foldcode.js',
          'media/vendor/codemirror/addon/fold/foldgutter.js',
          'media/vendor/codemirror/addon/fold/xml-fold.js',
          'media/vendor/codemirror/addon/mode/loadmode.js',
          'media/vendor/codemirror/addon/mode/multiplex.js',
          'media/vendor/codemirror/addon/scroll/annotatescrollbar.js',
          'media/vendor/codemirror/addon/scroll/simplescrollbars.js',
          'media/vendor/codemirror/addon/scroll/matchesonscrollbar.js',
          'media/vendor/codemirror/addon/scroll/match-highlighter.js',
          'media/vendor/codemirror/addon/scroll/searchcursor.js',
          'media/vendor/codemirror/addon/selection/active-line.js',
          'media/vendor/codemirror/keymap/vim.js',
          'media/vendor/codemirror/mode/meta.js',
        ],
        'media/vendor/codemirror/lib/addons.js',
      );

      concatFiles([
        'media/vendor/codemirror/addon/display/fullscreen.css',
        'media/vendor/codemirror/addon/fold/foldgutter.css',
        'media/vendor/codemirror/addon/search/matchesonscrollbar.css',
        'media/vendor/codemirror/addon/scroll/simplescrollbars.css',
      ], 'media/vendor/codemirror/lib/addons.css');

      // Update the XML file for Codemirror
      let codemirrorXml = fs.readFileSync(`${rootPath}/plugins/editors/codemirror/codemirror.xml`, { encoding: 'UTF-8' });
      codemirrorXml = codemirrorXml.replace(xmlVersionStr, `$1${options.dependencies.codemirror}$3`);
      fs.writeFileSync(`${rootPath}/plugins/editors/codemirror/codemirror.xml`, codemirrorXml, { encoding: 'UTF-8' });
    } else if (packageName === 'tinymce') {
      const itemvendorPath = Path.join(rootPath, `media/vendor/${packageName}`);

      if (!fsExtra.existsSync(itemvendorPath)) {
        fsExtra.mkdirSync(itemvendorPath);
        fsExtra.mkdirSync(Path.join(itemvendorPath, 'plugins'));
        fsExtra.mkdirSync(Path.join(itemvendorPath, 'langs'));
        fsExtra.mkdirSync(Path.join(itemvendorPath, 'skins'));
        fsExtra.mkdirSync(Path.join(itemvendorPath, 'themes'));
        fsExtra.mkdirSync(Path.join(itemvendorPath, 'templates'));
      }

      copyAll('plugins', 'tinymce', 'plugins');
      copyAll('skins', 'tinymce', 'skins');
      copyAll('themes', 'tinymce', 'themes');

      copyArrayFiles('', ['tinymce.js', 'tinymce.min.js', 'changelog.txt', 'license.txt'], 'tinymce', '');

      // Update the XML file for tinyMCE
      let tinyXml = fs.readFileSync(`${rootPath}/plugins/editors/tinymce/tinymce.xml`, { encoding: 'UTF-8' });
      tinyXml = tinyXml.replace(xmlVersionStr, `$1${options.dependencies.tinymce}$3`);
      fs.writeFileSync(`${rootPath}/plugins/editors/tinymce/tinymce.xml`, tinyXml, { encoding: 'UTF-8' });
    } else {
      ['js', 'css', 'filesExtra'].forEach((type) => {
        if (!vendor[type]) return;

        const dest = Path.join(mediaVendorPath, vendorName);
        const files = copyFilesTo(vendor[type], modulePathRoot, dest, type);

        // Add to registry, in format suported by JHtml
        if (type === 'js' || type === 'css') {
          registryItem[type] = [];
          files.forEach((filePath) => {
            registryItem[type].push(`vendor/${vendorName}/${Path.basename(filePath)}`);
          });
        }
      });

      // Copy the license if exists
      if (options.settings.vendors[packageName].licenseFilename &&
        fs.existsSync(`${Path.join(rootPath, `node_modules/${packageName}`)}/${options.settings.vendors[packageName].licenseFilename}`)
      ) {
        const dest = Path.join(mediaVendorPath, vendorName);
        fsExtra.copySync(`${Path.join(rootPath, `node_modules/${packageName}`)}/${options.settings.vendors[packageName].licenseFilename}`, `${dest}/${options.settings.vendors[packageName].licenseFilename}`);
      }

      if (packageName === 'joomla-ui-custom-elements') {
        if (fs.existsSync(Path.join(rootPath, 'node_modules/joomla-ui-custom-elements/dist/polyfills'))) {
          fsExtra.copySync(Path.join(rootPath, 'node_modules/joomla-ui-custom-elements/dist/polyfills'), Path.join(rootPath, 'media/system/js/polyfills/webcomponents'));
        }
      }
    }

    registry.vendors[vendorName] = registryItem;

    console.log(Chalk.green(`${packageName} was updated.`));
  });
};

const update = (options) => {
  Promise.resolve()
  // Copy a fresh version of the files
    .then(cleanVendors())

  // Copy a fresh version of the files
    .then(copyFiles(options))

  // Handle errors
    .catch((err) => {
      console.error(Chalk.red(err));
      process.exit(-1);
    });
};

module.exports.update = update;
