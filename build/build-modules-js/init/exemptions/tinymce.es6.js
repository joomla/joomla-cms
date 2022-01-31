const {
  existsSync, copy, readFile, writeFile, mkdir,
} = require('fs-extra');
const CssNano = require('cssnano');
const Postcss = require('postcss');
const { minify } = require('terser');

const { join } = require('path');

const { copyAllFiles } = require('../common/copy-all-files.es6.js');

const RootPath = process.cwd();
const xmlVersionStr = /(<version>)(.+)(<\/version>)/;

/**
 * Copies an array of files from a directory
 *
 * @param {string} dirName the name of the source folder
 * @param {array}  files   the array of files to be be copied
 * @param {string} name    the name of the destination folder
 * @param {string} type    the type of the folder, eg: js, css, fonts, images
 *
 * @returns { void }
 */
const copyArrayFiles = async (dirName, files, name, type) => {
  const promises = [];
  // eslint-disable-next-line guard-for-in,no-restricted-syntax
  for (const file of files) {
    const folderName = dirName === '/' ? '/' : `/${dirName}/`;

    if (existsSync(`node_modules/${name}${folderName}${file}`)) {
      promises.push(copy(`node_modules/${name}${folderName}${file}`, `media/vendor/${name.replace(/.+\//, '')}${type ? `/${type}` : ''}/${file}`, { preserveTimestamps: true }));
    }
  }

  await Promise.all(promises);
};

/**
 * tinyMCE needs special treatment
 */
module.exports.tinyMCE = async (packageName, version) => {
  const itemvendorPath = join(RootPath, `media/vendor/${packageName}`);

  if (!await existsSync(itemvendorPath)) {
    await mkdir(itemvendorPath, { mode: 0o755 });
    await mkdir(join(itemvendorPath, 'icons'), { mode: 0o755 });
    await mkdir(join(itemvendorPath, 'plugins'), { mode: 0o755 });
    await mkdir(join(itemvendorPath, 'langs'), { mode: 0o755 });
    await mkdir(join(itemvendorPath, 'skins'), { mode: 0o755 });
    await mkdir(join(itemvendorPath, 'themes'), { mode: 0o755 });
    await mkdir(join(itemvendorPath, 'templates'), { mode: 0o755 });
  }

  await copyAllFiles('icons', 'tinymce', 'icons');
  await copyAllFiles('plugins', 'tinymce', 'plugins');
  await copyAllFiles('skins', 'tinymce', 'skins');
  await copyAllFiles('themes', 'tinymce', 'themes');

  await copyArrayFiles('', ['tinymce.js', 'tinymce.min.js', 'changelog.txt', 'license.txt'], 'tinymce', '');

  // Update the XML file for tinyMCE
  let tinyXml = await readFile(`${RootPath}/plugins/editors/tinymce/tinymce.xml`, { encoding: 'utf8' });
  tinyXml = tinyXml.replace(xmlVersionStr, `$1${version}$3`);
  await writeFile(`${RootPath}/plugins/editors/tinymce/tinymce.xml`, tinyXml, { encoding: 'utf8', mode: 0o644 });

  // Remove that sourcemap...
  let tinyWrongMap = await readFile(`${RootPath}/media/vendor/tinymce/skins/ui/oxide/skin.min.css`, { encoding: 'utf8' });
  tinyWrongMap = tinyWrongMap.replace('/*# sourceMappingURL=skin.min.css.map */', '');
  await writeFile(`${RootPath}/media/vendor/tinymce/skins/ui/oxide/skin.min.css`, tinyWrongMap, { encoding: 'utf8', mode: 0o644 });

  /* Create the Highlighter plugin */
  // Get the css
  let cssContent = await readFile('build/media_source/plg_editors_tinymce/js/plugins/highlighter/source.css', { encoding: 'utf8' });
  cssContent = await Postcss([CssNano()]).process(cssContent, { from: undefined });
  // Get the JS
  let jsContent = await readFile('build/media_source/plg_editors_tinymce/js/plugins/highlighter/source.es6.js', { encoding: 'utf8' });
  jsContent = await minify(jsContent, { sourceMap: false, format: { comments: false } });
  // Write the HTML file
  const htmlContent = `<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <script type="module">${jsContent.code}</script>
    <style type="text/css">${cssContent.css}</style>
  </head>
  <body style="height: 100vh"></body>
</html>
`;

  await writeFile('media/plg_editors_tinymce/js/plugins/highlighter/source.html', htmlContent, { encoding: 'utf8', mode: 0o644 });

  // Restore our code on the vendor folders
  await copy(join(RootPath, 'build/media_source/vendor/tinymce/templates'), join(RootPath, 'media/vendor/tinymce/templates'), { preserveTimestamps: true });
};
