const {
  existsSync, readFile, writeFile, mkdir,
} = require('fs-extra');
const { join } = require('path');

const { concatFiles } = require('../common/concat-files.es6.js');
const { copyAllFiles } = require('../common/copy-all-files.es6.js');

const RootPath = process.cwd();
const xmlVersionStr = /(<version>)(.+)(<\/version>)/;

/**
 * Codemirror needs special treatment
 */
module.exports.codeMirror = async (packageName, version) => {
  const itemvendorPath = join(RootPath, `media/vendor/${packageName}`);
  if (!await existsSync(itemvendorPath)) {
    await mkdir(itemvendorPath, { recursive: true, mode: 0o755 });
    await mkdir(join(itemvendorPath, 'addon'), { mode: 0o755 });
    await mkdir(join(itemvendorPath, 'lib'), { mode: 0o755 });
    await mkdir(join(itemvendorPath, 'mode'), { mode: 0o755 });
    await mkdir(join(itemvendorPath, 'keymap'), { mode: 0o755 });
    await mkdir(join(itemvendorPath, 'theme'), { mode: 0o755 });
  }

  await copyAllFiles('addon', 'codemirror', 'addon');
  await copyAllFiles('lib', 'codemirror', 'lib');
  await copyAllFiles('mode', 'codemirror', 'mode');
  await copyAllFiles('keymap', 'codemirror', 'keymap');
  await copyAllFiles('theme', 'codemirror', 'theme');

  await concatFiles(
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
      'media/vendor/codemirror/addon/search/match-highlighter.js',
      'media/vendor/codemirror/addon/search/matchesonscrollbar.js',
      'media/vendor/codemirror/addon/search/search.js',
      'media/vendor/codemirror/addon/search/searchcursor.js',
      'media/vendor/codemirror/addon/selection/active-line.js',
      'media/vendor/codemirror/mode/meta.js',
    ],
    'media/vendor/codemirror/lib/addons.js',
  );

  await concatFiles(
    [
      'media/vendor/codemirror/addon/display/fullscreen.css',
      'media/vendor/codemirror/addon/fold/foldgutter.css',
      'media/vendor/codemirror/addon/scroll/simplescrollbars.css',
      'media/vendor/codemirror/addon/search/matchesonscrollbar.css',
    ],
    'media/vendor/codemirror/lib/addons.css',
  );

  // Update the XML file for Codemirror
  let codemirrorXml = await readFile(`${RootPath}/plugins/editors/codemirror/codemirror.xml`, { encoding: 'utf8' });
  codemirrorXml = codemirrorXml.replace(xmlVersionStr, `$1${version}$3`);
  await writeFile(`${RootPath}/plugins/editors/codemirror/codemirror.xml`, codemirrorXml, { encoding: 'utf8', mode: 0o644 });
};
