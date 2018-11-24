const Copydir = require('copy-dir');
const Fs = require('fs');
const FsExtra = require('fs-extra');
const Path = require('path');
const Promise = require('bluebird');
const RootPath = require('./rootpath.js')._();
const UglyCss = require('uglifycss');
const UglifyJS = require('uglify-es');
const WalkSync = require('./walk-sync.js');

const xmlVersionStr = /(<version>)(\d+.\d+.\d+)(<\/version>)/;

/**
 * Clean the media/vendor folder
 */
const cleanVendors = () => {
  // Remove the vendor folder
  FsExtra.removeSync(Path.join(RootPath, 'media/vendor'));

  // eslint-disable-next-line no-console
  console.error('/media/vendor has been removed.');

  // Restore our code on the vendor folders
  FsExtra.copySync(Path.join(RootPath, 'build/media/vendor/tinymce/langs'), Path.join(RootPath, 'media/vendor/tinymce/langs'));
  FsExtra.copySync(Path.join(RootPath, 'build/media/vendor/tinymce/templates'), Path.join(RootPath, 'media/vendor/tinymce/templates'));
  FsExtra.copySync(Path.join(RootPath, 'build/media/vendor/jquery-ui'), Path.join(RootPath, 'media/vendor/jquery-ui'));

  // And here some assets from a PHP package
    if (Fs.existsSync(Path.join(RootPath, 'libraries/vendor/maximebf/debugbar/src/DebugBar/Resources'))) {
        FsExtra.copySync(Path.join(RootPath, 'libraries/vendor/maximebf/debugbar/src/DebugBar/Resources'), Path.join(RootPath, 'media/vendor/debugbar'));
    } else {
        throw new Error('You need to run `npm install AFTER the command composer install!!!`. The debug plugin HASN\'T install all the front end assets');
    }
};

/**
 * Copies all the files from a directory
 *
 * @param {string} dirName the name of the source folder
 * @param {string} name    the name of the destination folder
 * @param {string} type    the type of the folder, eg: js, css, fonts, images
 */
const copyAll = (dirName, name, type) => {
  const folderName = dirName === '/' ? '/' : `/${dirName}`;
  FsExtra.copySync(Path.join(RootPath, `node_modules/${name}/${folderName}`),
    Path.join(RootPath, `media/vendor/${name.replace(/.+\//, '')}/${type}`));
};

/**
 * Copies an array of files from a directory
 *
 * @param {string} dirName the name of the source folder
 * @param {array}  files   the array of files to be be copied
 * @param {string} name    the name of the destination folder
 * @param {string} type    the type of the folder, eg: js, css, fonts, images
 */
const copyArrayFiles = (dirName, files, name, type) => {
  files.forEach((file) => {
    const folderName = dirName === '/' ? '/' : `/${dirName}/`;
    if (FsExtra.existsSync(`node_modules/${name}${folderName}${file}`)) {
      FsExtra.copySync(`node_modules/${name}${folderName}${file}`, `media/vendor/${name.replace(/.+\//, '')}${type ? `/${type}` : ''}/${file}`);
    }
  });
};

/**
 *
 * @param {object} files    the object of files map, eg {"src.js": "js/src.js"}
 * @param {string} srcDir   the name of the package root dir
 * @param {string} destDir  the name of the Vendor destination dir
 *
 * @returns {Array}
 */
const copyFilesTo = (files, srcDir, destDir) => {
  const filesResult = [];

  // Copy each file
  // eslint-disable-next-line guard-for-in, no-restricted-syntax
  for (const srcFile in files) {
    const destFile = files[srcFile];
    const srcPath = Path.join(srcDir, srcFile);
    const destPath = Path.join(destDir, destFile);

    FsExtra.copySync(srcPath, destPath);
    filesResult.push(destPath);
  }

  return filesResult;
};

/**
 * Method to concatenate some files
 *
 * @param {array}  files   the array of files to be be concatenated
 * @param {string} output  the name of the output file
 */
const concatFiles = (files, output) => {
  let tempMem = '';
  files.forEach((file) => {
    if (FsExtra.existsSync(`${RootPath}/${file}`)) {
      tempMem += Fs.readFileSync(`${RootPath}/${file}`);
    }
  });

  Fs.writeFileSync(`${RootPath}/${output}`, tempMem);
};

/**
 * Main method that will copy all vendor files according to Joomla's specs
 *
 * @param options
 */
const copyFiles = (options) => {
  const mediaVendorPath = Path.join(RootPath, 'media/vendor');
  const registry = {
    name: options.name,
    version: options.version,
    description: options.description,
    license: options.license,
    assets: {},
  };

  if (!FsExtra.existsSync(mediaVendorPath)) {
    FsExtra.mkdirSync(mediaVendorPath);
  }

  // Loop to get some text for the packgage.json
  // eslint-disable-next-line guard-for-in, no-restricted-syntax
  for (const packageName in options.settings.vendors) {
    const vendor = options.settings.vendors[packageName];
    const vendorName = vendor.name || packageName;
    const modulePathJson = require.resolve(`${packageName}/package.json`);
    const modulePathRoot = Path.dirname(modulePathJson);
    // eslint-disable-next-line global-require, import/no-dynamic-require
    const moduleOptions = require(modulePathJson);

    if (packageName === 'codemirror') {
      const itemvendorPath = Path.join(RootPath, `media/vendor/${packageName}`);
      if (!FsExtra.existsSync(itemvendorPath)) {
        FsExtra.mkdirSync(itemvendorPath);
        FsExtra.mkdirSync(Path.join(itemvendorPath, 'addon'));
        FsExtra.mkdirSync(Path.join(itemvendorPath, 'lib'));
        FsExtra.mkdirSync(Path.join(itemvendorPath, 'mode'));
        FsExtra.mkdirSync(Path.join(itemvendorPath, 'keymap'));
        FsExtra.mkdirSync(Path.join(itemvendorPath, 'theme'));
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
        'media/vendor/codemirror/lib/addons.js');

      concatFiles([
        'media/vendor/codemirror/addon/display/fullscreen.css',
        'media/vendor/codemirror/addon/fold/foldgutter.css',
        'media/vendor/codemirror/addon/search/matchesonscrollbar.css',
        'media/vendor/codemirror/addon/scroll/simplescrollbars.css',
      ], 'media/vendor/codemirror/lib/addons.css');

      // Update the XML file for Codemirror
      let codemirrorXml = Fs.readFileSync(`${RootPath}/plugins/editors/codemirror/codemirror.xml`, { encoding: 'UTF-8' });
      codemirrorXml = codemirrorXml.replace(xmlVersionStr, `$1${options.dependencies.codemirror}$3`);
      Fs.writeFileSync(`${RootPath}/plugins/editors/codemirror/codemirror.xml`, codemirrorXml, { encoding: 'UTF-8' });
    } else if (packageName === 'tinymce') {
      const itemvendorPath = Path.join(RootPath, `media/vendor/${packageName}`);

      if (!FsExtra.existsSync(itemvendorPath)) {
        FsExtra.mkdirSync(itemvendorPath);
        FsExtra.mkdirSync(Path.join(itemvendorPath, 'plugins'));
        FsExtra.mkdirSync(Path.join(itemvendorPath, 'langs'));
        FsExtra.mkdirSync(Path.join(itemvendorPath, 'skins'));
        FsExtra.mkdirSync(Path.join(itemvendorPath, 'themes'));
        FsExtra.mkdirSync(Path.join(itemvendorPath, 'templates'));
      }

      copyAll('plugins', 'tinymce', 'plugins');
      copyAll('skins', 'tinymce', 'skins');
      copyAll('themes', 'tinymce', 'themes');

      copyArrayFiles('', ['tinymce.js', 'tinymce.min.js', 'changelog.txt', 'license.txt'], 'tinymce', '');

      // Update the XML file for tinyMCE
      let tinyXml = Fs.readFileSync(`${RootPath}/plugins/editors/tinymce/tinymce.xml`, { encoding: 'UTF-8' });
      tinyXml = tinyXml.replace(xmlVersionStr, `$1${options.dependencies.tinymce}$3`);
      Fs.writeFileSync(`${RootPath}/plugins/editors/tinymce/tinymce.xml`, tinyXml, { encoding: 'UTF-8' });

      // Remove that sourcemap...
      let tinyWrongMap = Fs.readFileSync(`${RootPath}/media/vendor/tinymce/skins/lightgray/skin.min.css`, { encoding: 'UTF-8' });
      tinyWrongMap = tinyWrongMap.replace('/*# sourceMappingURL=skin.min.css.map */', '');
      Fs.writeFileSync(`${RootPath}/media/vendor/tinymce/skins/lightgray/skin.min.css`, tinyWrongMap, { encoding: 'UTF-8' });
    } else {
      ['js', 'css', 'filesExtra'].forEach((type) => {
        if (!vendor[type]) return;

        const dest = Path.join(mediaVendorPath, vendorName);
        copyFilesTo(vendor[type], modulePathRoot, dest, type);
      });

      // Copy the license if exists
      if (options.settings.vendors[packageName].licenseFilename &&
     Fs.existsSync(`${Path.join(RootPath, `node_modules/${packageName}`)}/${options.settings.vendors[packageName].licenseFilename}`)
      ) {
        const dest = Path.join(mediaVendorPath, vendorName);
        FsExtra.copySync(`${Path.join(RootPath, `node_modules/${packageName}`)}/${options.settings.vendors[packageName].licenseFilename}`, `${dest}/${options.settings.vendors[packageName].licenseFilename}`);
      }
    }

    // Joomla's hack to expose the chosen base classes so we can extend it ourselves (it was better than the
    // many hacks we had before. But I'm still ashamed of myself.
    if (packageName === 'chosen-js') {
      const dest = Path.join(mediaVendorPath, vendorName);
      const chosenPath = `${dest}/${options.settings.vendors[packageName].js['chosen.jquery.js']}`;
      let ChosenJs = Fs.readFileSync(chosenPath, { encoding: 'UTF-8' });
      ChosenJs = ChosenJs.replace('}).call(this);', '  document.AbstractChosen = AbstractChosen;\n' +
          '  document.Chosen = Chosen;\n' +
          '}).call(this);');
      Fs.writeFileSync(chosenPath, ChosenJs, { encoding: 'UTF-8' });
    }

    // Add provided Assets to a registry, if any
    if (vendor.provideAssets && vendor.provideAssets.length) {
      vendor.provideAssets.forEach((assetInfo) => {

        const registryItem = {
          package: packageName,
          name:    assetInfo.name || vendorName,
          version: moduleOptions.version,
          dependencies: assetInfo.dependencies || [],
          js:  [],
          css: [],
          attribute: {}
        };

        // Update path for JS and CSS files
        assetInfo.js && assetInfo.js.length && assetInfo.js.forEach((assetJS) => {
          let itemPath = assetJS;

          // Check for external path
          if (itemPath.indexOf('http://') !== 0 && itemPath.indexOf('https://') !== 0 && itemPath.indexOf('//') !== 0) {
            itemPath = `media/vendor/${vendorName}/js/${itemPath}`;
          }
          registryItem.js.push(itemPath);

          // Check if there are any attribute to this file, then update the path
          if (assetInfo.attribute && assetInfo.attribute[assetJS]) {
            registryItem.attribute[itemPath] = assetInfo.attribute[assetJS]
          }
        });
        assetInfo.css && assetInfo.css.length && assetInfo.css.forEach((assetCSS) => {
          let itemPath = assetCSS;

          // Check for external path
          if (itemPath.indexOf('http://') !== 0 && itemPath.indexOf('https://') !== 0 && itemPath.indexOf('//') !== 0) {
            itemPath = `media/vendor/${vendorName}/css/${itemPath}`;
          }
          registryItem.css.push(itemPath);

          // Check if there are any attribute to this file, then update the path
          if (assetInfo.attribute && assetInfo.attribute[assetCSS]) {
            registryItem.attribute[itemPath] = assetInfo.attribute[assetCSS]
          }
        });

        registry.assets[registryItem.name] = registryItem;
      });
    }

    // eslint-disable-next-line no-console
    console.log(`${packageName} was updated.`);
  }

  // Write assets registry
  Fs.writeFileSync(
    Path.join(mediaVendorPath, 'joomla.asset.json'),
    JSON.stringify(registry, null, 2),
    {encoding: 'UTF-8'}
  );
};

/**
 * Method to recreate the basic media folder structure
 */
const recreateMediaFolder = () => {
	// eslint-disable-next-line no-console
	console.log(`Recreating the media folder...`);

    Copydir.sync(Path.join(RootPath, 'build/media'), Path.join(RootPath, 'media'), function(stat, filepath, filename){
        if (stat === 'directory' && (filename === 'webcomponents' || filename === 'scss')) {
            return false;
        }
        return true;
    }, function(err){
        if (!err) {
            console.log('Legacy media files restored');
        }
    });

    Copydir.sync(Path.join(RootPath, 'build/media_src'), Path.join(RootPath, 'media'), function(stat, filepath, filename){
        if (stat === 'directory' && filename === 'scss') {
            return false;
        }
        return true;
    }, function(err){
        if (!err) {
            console.log('Media folder structure was created');
        }
    });
};

/**
 * Method to minify the legacy ES5 files
 */
const uglifyLegacyFiles = () => {
	// Minify the legacy files
	console.log('Minifying legacy stylesheets/scripts...');
	const files = WalkSync.run(`${RootPath}/media`, []);

	if (files.length) {
		files.forEach(
			(file) => {
				if (file.match('/vendor') || file.match('\\vendor')) {
					return;
				}
				if (file.match(/\.js/) && !file.match(/\.min\.js/) && !file.toLowerCase().match(/license/) && !file.toLowerCase().match(/json/) ) {
          console.log(`Processing: ${file}`);
					// Create the minified file
					Fs.writeFileSync(file.replace(/\.js$/, '.min.js'), UglifyJS.minify(Fs.readFileSync(file, 'utf8')).code, {encoding: 'utf8'});
				}
				if (file.match(/\.css/) && !file.match(/\.min\.css/) && !file.match(/\.css\.map/) && !file.toLowerCase().match(/license/)) {
					console.log(`Processing: ${file}`);
					// Create the minified file
					Fs.writeFileSync(
						file.replace(/\.css$/, '.min.css'),
						UglyCss.processFiles([file], { expandVars: false }),
						{ encoding: 'utf8' },
					);
				}
			});
	}
};

const copyAssets = (options) => {
  Promise.resolve()
    // Copy a fresh version of the files
    .then(cleanVendors())

    // Copy a fresh version of the files
    .then(recreateMediaFolder())

    // Copy a fresh version of the files
    .then(copyFiles(options))

    // Uglify the legacy css/js files
    .then(uglifyLegacyFiles(options))

    // Handle errors
    .catch((err) => {
        throw new Error(err);
    });
};

module.exports.copyAssets = copyAssets;
