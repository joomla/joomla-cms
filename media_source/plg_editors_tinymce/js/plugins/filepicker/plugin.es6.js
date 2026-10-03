/**
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Connects the "browse" button of TinyMCE's image, media and link dialogs to Joomla pickers
 * registered on Joomla.editorFilePickers. The dialog filetypes are 'image', 'media' and 'file'
 * (the link dialog).
 *
 * Register a picker for all editors, keyed by filetype:
 *   Joomla.editorFilePickers.image = (meta) => Promise<{ url, alt?, text? } | null>;
 * Override it for a single editor, keyed by the editor id:
 *   Joomla.editorFilePickers[editorId] = { image: (meta) => ... };
 *
 * The resolved url is inserted; the image/media dialogs read { alt }, the link dialog reads
 * { text }. The browse button is only shown for filetypes this editor can serve (global pickers
 * plus its own overrides). A file_picker_callback or file_picker_types set by a developer on the
 * editor options wins over this plugin.
 */
window.tinymce.PluginManager.add('jfilepicker', (editor) => {
  const registry = Joomla.editorFilePickers || {};
  const types = [...new Set([
    ...Object.keys(registry).filter((key) => typeof registry[key] === 'function'),
    ...Object.keys(registry[editor.id] || {}),
  ])];

  // Nothing registered, or the developer supplied their own picker: do nothing
  if (!types.length || editor.options.isSet('file_picker_callback')) {
    return {};
  }

  if (!editor.options.isSet('file_picker_types')) {
    editor.options.set('file_picker_types', types.join(' '));
  }

  editor.options.set('file_picker_callback', (cb, value, meta) => {
    const pickers = Joomla.editorFilePickers || {};
    const picker = (pickers[editor.id] || {})[meta.filetype] || pickers[meta.filetype];
    if (!picker) {
      return;
    }
    picker(meta).then((result) => {
      if (result && result.url) {
        cb(result.url, { alt: result.alt || '', text: result.text || '' });
      }
    });
  });

  return {
    getMetadata: () => ({
      name: 'Joomla file picker',
      url: 'https://www.joomla.org/',
    }),
  };
});
