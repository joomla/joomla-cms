/**
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

import JoomlaDialog from 'joomla.dialog';

/**
 * Build the list of link sources: the registered content providers
 * (editor-link-providers) plus a built-in Media source (core com_media).
 *
 * @returns {Array<{key: string, title: string, icon: string, src: string, select: string}>}
 */
const getSources = () => {
  const providers = Joomla.getOptions('editor-link-providers', {});
  const sources = Object.keys(providers).map((key) => ({ key, ...providers[key] }));

  // Built-in Media source: link to any file through the Media Manager
  sources.push({
    key: 'media',
    title: Joomla.Text._('PLG_TINY_LINK_MEDIA'),
    icon: 'pictures',
    src: 'index.php?option=com_media&view=media&tmpl=component&mediatypes=0,1,2,3',
    select: 'media',
  });

  return sources;
};

/**
 * Append the CSRF token to a source URL.
 *
 * @param {string} src
 *
 * @returns {string}
 */
const buildSrc = (src) => {
  let url;
  if (src.indexOf('http') === 0) {
    url = new URL(src);
  } else {
    // Prepend the app base (Uri::base(true)) so it works in a subfolder install
    const base = Joomla.getOptions('system.paths', {}).base || '';
    const path = src.charAt(0) === '/' ? src : `${base}/${src}`;
    url = new URL(path, window.location.origin);
  }
  url.searchParams.set(Joomla.getOptions('csrf.token', ''), '1');
  return url.toString();
};

/**
 * Resolve the root-relative URL for a selected media file via the com_media files API.
 *
 * @param {string} path
 *
 * @returns {Promise<string|null>}
 */
const resolveMediaUrl = (path) => {
  const base = Joomla.getOptions('system.paths', {}).base || '';
  const url = new URL(`${base}/index.php?option=com_media&format=json`, window.location.origin);
  url.searchParams.append('task', 'api.files');
  url.searchParams.append('url', 'true');
  url.searchParams.append('path', path);
  url.searchParams.append('mediatypes', '0,1,2,3');
  url.searchParams.append(Joomla.getOptions('csrf.token', ''), '1');

  return fetch(url, { headers: { 'Content-Type': 'application/json' } })
    .then((response) => response.json())
    .then((response) => {
      if (!response.success || !response.data || !response.data[0] || !response.data[0].url) {
        return null;
      }
      const { rootFull } = Joomla.getOptions('system.paths', {});
      const parts = rootFull ? response.data[0].url.split(rootFull) : [response.data[0].url];
      return parts.length > 1 ? parts[1] : response.data[0].url;
    });
};

/**
 * Open the two-pane link picker (left = sources, right = item iframe) and resolve
 * with the chosen link URL and default text, or null when dismissed.
 *
 * @returns {Promise<{url: string, text: string}|null>}
 */
const openLinkPicker = () => new Promise((resolve) => {
  const sources = getSources();

  if (!sources.length) {
    resolve(null);
    return;
  }

  // Resolve exactly once, whether the user selects or dismisses
  let settled = false;
  const settle = (value) => {
    if (settled) {
      return;
    }
    settled = true;
    resolve(value);
  };

  let activeSource;
  let mediaSelection = null;
  let dialog;
  let selectBtnEl;

  // Media source: the manager dispatches onMediaFileSelected to the parent document
  const onMediaFileSelected = (event) => {
    mediaSelection = event.detail && event.detail.path ? event.detail : null;
  };

  // Content sources: the modal posts the selection to the parent window
  const msgListener = (event) => {
    if (event.origin !== window.location.origin || !event.data || typeof event.data !== 'object') {
      return;
    }
    if (event.data.messageType === 'joomla:content-select') {
      settle({ url: event.data.uri, text: event.data.title || '' });
      dialog.close();
    } else if (event.data.messageType === 'joomla:cancel') {
      dialog.close();
    }
  };

  // Layout, styled by linkpicker.css
  const container = document.createElement('div');
  container.className = 'editor-link-picker';

  const nav = document.createElement('nav');
  nav.className = 'editor-link-picker-nav';

  const frameWrap = document.createElement('div');
  frameWrap.className = 'editor-link-picker-frame';

  const iframe = document.createElement('iframe');
  iframe.title = Joomla.Text._('PLG_TINY_LINK_PICKER_TITLE');
  frameWrap.appendChild(iframe);

  // Modals skip the template JS, so mirror the parent's effective color scheme onto the
  // modal document (same-origin) to keep the iframe in sync with dark/light mode.
  const applyColorScheme = () => {
    const parentEl = document.documentElement;
    let scheme = parentEl.dataset.colorScheme;
    if (!scheme && 'colorSchemeOs' in parentEl.dataset) {
      scheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }
    if (!scheme) {
      return;
    }
    try {
      const idoc = iframe.contentDocument;
      if (idoc && idoc.documentElement) {
        idoc.documentElement.dataset.colorScheme = scheme;
        idoc.documentElement.dataset.bsTheme = scheme;
      }
    } catch (err) {
      // Cross-origin document — cannot adjust, ignore
    }
  };
  iframe.addEventListener('load', applyColorScheme);

  container.appendChild(nav);
  container.appendChild(frameWrap);

  const loadSource = (source) => {
    activeSource = source;
    mediaSelection = null;

    nav.querySelectorAll('button').forEach((btn) => {
      btn.classList.toggle('active', btn.dataset.key === source.key);
    });

    // A confirm button only makes sense for the media source; content selects on row click
    if (selectBtnEl) {
      selectBtnEl.hidden = source.select !== 'media';
    }

    iframe.src = buildSrc(source.src);
  };

  sources.forEach((source) => {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.dataset.key = source.key;
    btn.className = 'editor-link-picker-item';
    btn.innerHTML = `<span class="icon-${source.icon}" aria-hidden="true"></span><span class="editor-link-picker-item-label"></span>`;
    btn.querySelector('.editor-link-picker-item-label').textContent = source.title;
    btn.addEventListener('click', () => loadSource(source));
    nav.appendChild(btn);
  });

  const popupButtons = [
    {
      label: Joomla.Text._('JSELECT'),
      className: 'button button-success btn btn-success btn-sm js-link-picker-select',
      location: 'header',
      onClick: async () => {
        if (!activeSource || activeSource.select !== 'media' || !mediaSelection) {
          return;
        }
        const mediaUrl = await resolveMediaUrl(mediaSelection.path);
        if (mediaUrl) {
          settle({ url: mediaUrl, text: decodeURIComponent(mediaUrl.split('/').pop()) });
          dialog.close();
        }
      },
    },
    {
      label: '',
      ariaLabel: Joomla.Text._('JCLOSE'),
      className: 'button-close btn-close',
      data: { buttonClose: '', dialogClose: '' },
      location: 'header',
    },
  ];

  // Attach to the DOM so JoomlaDialog can move/restore the inline content safely
  document.body.appendChild(container);

  dialog = new JoomlaDialog({
    popupType: 'inline',
    popupContent: container,
    textHeader: Joomla.Text._('PLG_TINY_LINK_PICKER_TITLE'),
    popupButtons,
  });
  dialog.classList.add('editor-link-picker-dialog');

  dialog.addEventListener('joomla-dialog:close', () => {
    window.removeEventListener('message', msgListener);
    document.removeEventListener('onMediaFileSelected', onMediaFileSelected);
    delete window.JoomlaExpectingPostMessage;
    dialog.destroy();
    if (container.parentElement) {
      container.remove();
    }
    settle(null);
  });

  // Disable the legacy jSelect* insertion in the content modals; we use postMessage
  window.JoomlaExpectingPostMessage = true;
  window.addEventListener('message', msgListener);
  document.addEventListener('onMediaFileSelected', onMediaFileSelected);

  dialog.show();

  selectBtnEl = dialog.querySelector('.js-link-picker-select');
  loadSource(sources[0]);
});

// Register as the 'file' picker (the link dialog) in the shared editor file-picker registry
Joomla.editorFilePickers = Joomla.editorFilePickers || {};
Joomla.editorFilePickers.file = openLinkPicker;
