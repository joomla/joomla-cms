/**
 * source.js
 *
 * Original code by Arjan Haverkamp
 * Copyright 2013-2015 Arjan Haverkamp (arjan@webgear.nl)
 *
 * Adapted for use in Joomla by Dimitrios Grammatikogiannis
 */

// CodeMirror settings
const CMsettings = {
  path: '../../../../vendor/codemirror',
  indentOnInit: true,
  config: {
    mode: 'htmlmixed',
    theme: 'default',
    lineNumbers: true,
    lineWrapping: true,
    indentUnit: 2,
    tabSize: 2,
    indentWithTabs: true,
    matchBrackets: true,
    saveCursorPosition: true,
    styleActiveLine: true,
  },
  jsFiles: [// Default JS files
    'lib/codemirror.min.js',
    'addon/edit/matchbrackets.min.js',
    'mode/xml/xml.min.js',
    'mode/javascript/javascript.min.js',
    'mode/css/css.min.js',
    'mode/htmlmixed/htmlmixed.min.js',
    'addon/dialog/dialog.min.js',
    'addon/search/searchcursor.min.js',
    'addon/search/search.min.js',
    'addon/selection/active-line.min.js',
  ],
  cssFiles: [// Default CSS files
    'lib/codemirror.css',
    'addon/dialog/dialog.css',
  ],
};

// Global vars:
let tinymce;     // Reference to TinyMCE
let editor;      // Reference to TinyMCE editor
let codemirror;  // CodeMirror instance
let chr = 0;     // Unused utf-8 character, placeholder for cursor
const isMac = /macintosh|mac os/i.test(navigator.userAgent);

/**
 * Find the depth level
 */
const findDepth = (haystack, needle) => {
  const idx = haystack.indexOf(needle);
  let depth = 0;
  for (let x = idx -1; x >= 0; x--) {
    switch(haystack.charAt(x)) {
      case '<': depth--; break;
      case '>': depth++; break;
      case '&': depth++; break;
    }
  }
  return depth;
}

/**
 * This function is called by plugin.js, when user clicks 'Ok' button
 */
window.tinymceHighlighterSubmit = () => {
  const cc = '&#x0;';
  const isDirty = codemirror.isDirty;
  const doc = codemirror.doc;

  if (doc.somethingSelected()) {
    // Clear selection:
    doc.setCursor(doc.getCursor());
  }

  // Insert cursor placeholder (&#x0;)
  doc.replaceSelection(cc);

  var pos = codemirror.getCursor(),
  curLineHTML = doc.getLine(pos.line);

  if (findDepth(curLineHTML, cc) !== 0) {
    // Cursor is inside a <tag>, don't set cursor:
    curLineHTML = curLineHTML.replace(cc, '');
    doc.replaceRange(curLineHTML, CodeMirror.Pos(pos.line, 0), CodeMirror.Pos(pos.line));
  }

  // Submit HTML to TinyMCE:
  // [FIX] Cursor position inside JS, style or &nbps;
  // Workaround to fix cursor position if inside script tag
  const code = codemirror.getValue();

  /* Regex to check if inside script or style tags */
  const ccScript = new RegExp("<script(.*?)>(.*?)" + cc + "(.*?)<\/script>", "ms");
  const ccStyle = new RegExp("<style(.*?)>(.*?)" + cc + "(.*?)<\/style>", "ms");

  /* Regex to check if in beginning or end or if between < & > */
  const ccLocationCheck = new RegExp("<[^>]*(" + cc + ").*>|^(" + cc + ")|(" + cc + ")$");

  if (
    code.search(ccScript) !== -1 ||
    code.search(ccStyle) !== -1 ||
    code.search(ccLocationCheck) !== -1
  ){
    editor.setContent(code.replace(cc, ''));
  } else {
    editor.setContent(code.replace(cc, '<span id="CmCaReT"></span>'));
  }

  editor.isNotDirty = !isDirty;
  if (isDirty) {
    editor.nodeChanged();
  }

  // Set cursor:
  var el = editor.dom.select('span#CmCaReT')[0];
  if (el) {
    editor.selection.scrollIntoView(el);
    editor.selection.setCursorLocation(el,0);
    editor.dom.remove(el);
  }
}

/**
 * Append some help text in the modal footer
 */
const start = () => {
  // Initialise (on load)
  if (typeof(window.CodeMirror) !== 'function') {
    throw new Error(`CodeMirror not found in "${CMsettings.path}", aborting...`);
  }

  // Create legend for keyboard shortcuts for find & replace:
  const head = parent.document.querySelectorAll('.tox-dialog__footer')[0];
  const div = parent.document.createElement('div');
  const td1 = '<td style="font-size:11px;background:#777;color:#fff;padding:0 4px">';
  const td2 = '<td style="font-size:11px;padding-right:5px">';
  div.innerHTML = `
<table cellspacing="0" cellpadding="0" style="border-spacing:4px">
  <tr>
    ${td1}${(isMac ? '&#8984;-F' : 'Ctrl-F</td>')}${td2}${tinymce.translate('Start search')}</td>
    ${td1}${(isMac ? '&#8984;-G' : 'Ctrl-G')}</td>
    ${td2}${tinymce.translate('Find next')}</td>
    ${td1}${(isMac ? '&#8984;-Alt-F' : 'Shift-Ctrl-F')}</td>
    ${td2}${tinymce.translate('Find previous')}</td>
  </tr>
  <tr>
    ${td1}${(isMac ? '&#8984;-Alt-F' : 'Shift-Ctrl-F')}</td>
    ${td2}${tinymce.translate('Replace')}</td>
    ${td1}${(isMac ? 'Shift-&#8984;-Alt-F' : 'Shift-Ctrl-R')}</td>
    ${td2}${tinymce.translate('Replace all')}</td>
  </tr>
</table>`;
  div.style.position = 'absolute';
  div.style.left = div.style.bottom = '5px';
  head.appendChild(div);

  // Set CodeMirror cursor and bookmark to same position as cursor was in TinyMCE:
  let html = editor.getContent({source_view: true});

  // [FIX] #6 z-index issue with table panel and source code dialog
  //  editor.selection.getBookmark();

  html = html.replace(/<span\s+style="display: none;"\s+class="CmCaReT"([^>]*)>([^<]*)<\/span>/gm, String.fromCharCode(chr));
  editor.dom.remove(editor.dom.select('.CmCaReT'));

  // Hide TinyMCE toolbar panels, [FIX] #6 z-index issue with table panel and source code dialog
  // https://github.com/christiaan/tinymce-codemirror/issues/6
  tinymce.each(editor.contextToolbars, (toolbar) => { if (toolbar.panel) { toolbar.panel.hide(); } });

  CodeMirror.defineInitHook((inst) => {
    // Move cursor to correct position:
    inst.focus();
    const cursor = inst.getSearchCursor(String.fromCharCode(chr), false);
    if (cursor.findNext()) {
      inst.setCursor(cursor.to());
      cursor.replace('');
    }

    // Indent all code, if so requested:
    if (editor.settings.codemirror.indentOnInit) {
      const last = inst.lineCount();
      inst.operation(function() {
        for (let i = 0; i < last; ++i) {
          inst.indentLine(i);
        }
      });
    }
  });

  CMsettings.config.value = html;

  // Instantiante CodeMirror:
  codemirror = CodeMirror(document.body, CMsettings.config);
  codemirror.isDirty = false;
  codemirror.on('change', (inst) => {
    inst.isDirty = true;
  });
}

/**
 * Listen for the escape key and close the modal
 *
 * @param {Event} evt
 */
document.addEventListener('keydown', (evt) => {
  evt = evt || window.event;
  let isEscape = false;
  if ("key" in evt)
    isEscape = (evt.key === "Escape" || evt.key === "Esc");
  else
    isEscape = (evt.keyCode === 27);

  if (isEscape)
    tinymce.activeEditor.windowManager.close();
});

(() => {
  // Initialise (before load)
  tinymce = parent.tinymce;
  if (!tinymce) {
    throw new Error('tinyMCE not found');
  }

  editor = tinymce.activeEditor;
  const userSettings = editor.settings.codemirror;

  if (userSettings.fullscreen) {
    CMsettings.jsFiles.push('addon/display/fullscreen.min.js');
    CMsettings.cssFiles.push('addon/display/fullscreen.css');
  }

  // Merge config
  for (const i in userSettings.config) {
    CMsettings.config[i] = userSettings.config[i];
  }

  // Merge jsFiles
  for (const i in userSettings.jsFiles) {
    if (!CMsettings.jsFiles.includes(userSettings.jsFiles[i])) {
      CMsettings.jsFiles.push(userSettings.jsFiles[i]);
    }
  }

  // Merge cssFiles
  for (const i in userSettings.cssFiles) {
    if (!CMsettings.cssFiles.includes(userSettings.cssFiles[i])) {
      CMsettings.cssFiles.push(userSettings.cssFiles[i]);
    }
  }

  // Add trailing slash to path
  if (!/\/$/.test(CMsettings.path)) {
    CMsettings.path += '/';
  }

  // Write stylesheets
  for (let i = 0; i < CMsettings.cssFiles.length; i++) {
    const $link = document.createElement('link');
    $link.type = 'text/css';
    $link.href = CMsettings.path + CMsettings.cssFiles[i];
    document.head.append($link);
  }

  // Write JS source files
  for (let i = 0; i < CMsettings.jsFiles.length; i++) {
    const $script = document.createElement('script');
    $script.defer = true;
    $script.src = CMsettings.path + CMsettings.jsFiles[i];
    document.head.append($script);
  }

  // Borrowed from codemirror.js themeChanged function. Sets the theme's class names to the html element.
  // Without this, the background color outside of the codemirror wrapper element remains white.
    // [TMP] commented temporary, cause JS error: Uncaught TypeError: Cannot read property 'replace' of undefined
    if(CMsettings.config.theme) {
      document.documentElement.className += CMsettings.config.theme.replace(/(^|\s)\s*/g, " cm-s-");
    }

  window.onload = start;
})();
