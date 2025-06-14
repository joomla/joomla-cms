/**
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// eslint-disable-next-line import/no-unresolved
import JoomlaDialog from 'joomla.dialog';

if (!window.Joomla) {
  throw new Error('Joomla API is not properly initialised');
}

/* global hotkeys */
Joomla.addShortcut = (hotkey, callback) => {
  hotkeys(hotkey, 'joomla', (event) => {
    event.preventDefault();
    event.stopPropagation();
    event.stopImmediatePropagation();

    callback.call();
  });
};

Joomla.addClickShortcut = (hotkey, selector) => {
  Joomla.addShortcut(hotkey, () => {
    const element = document.querySelector(selector);
    if (element) {
      element.click();
    }
  });
};

Joomla.addFocusShortcut = (hotkey, selector) => {
  Joomla.addShortcut(hotkey, () => {
    const element = document.querySelector(selector);
    if (element) {
      element.focus();
    }
  });
};

Joomla.addLinkShortcut = (hotkey, selector) => {
  Joomla.addShortcut(hotkey, () => {
    window.location.href = selector;
  });
};

const setShortcutFilter = () => {
  hotkeys.filter = (event) => {
    const target = event.target || event.srcElement;
    const { tagName } = target;

    // Checkboxes should not block a shortcut event
    if (target.type === 'checkbox') {
      return true;
    }

    // Ignore TinyMCE joomlaHighlighter plugin,
    // @TODO: remove this when the joomlaHighlighter plugin will use JoomlaDialog
    if (target.classList.contains('tox-textarea-wrap') && target.closest('.joomla-highlighter-dialog')) {
      return false;
    }

    // Default hotkeys filter behavior
    return !(target.isContentEditable || tagName === 'INPUT' || tagName === 'SELECT' || tagName === 'TEXTAREA');
  };
};

const startupShortcuts = () => {
  hotkeys('J', (event) => {
    // If we're already in the scope, it's a normal shortkey
    if (hotkeys.getScope() === 'joomla') {
      return;
    }

    event.preventDefault();
    event.stopPropagation();
    event.stopImmediatePropagation();

    hotkeys.setScope('joomla');

    // Leave the scope after x milliseconds
    setTimeout(() => {
      hotkeys.setScope(false);
    }, Joomla.getOptions('plg_system_shortcut.timeout', 2000));
  });
};

const addOverviewHint = () => {
  const mainContainer = document.querySelector('.com_cpanel .container-main');
  if (mainContainer) {
    const containerElement = document.createElement('section');
    containerElement.className = 'content pt-4';
    containerElement.insertAdjacentHTML('beforeend', Joomla.Text._('PLG_SYSTEM_SHORTCUT_OVERVIEW_HINT'));
    mainContainer.appendChild(containerElement);
  }
};

/**
 * Helper to create an element
 * @param {String} nodeName
 * @param {String} text
 * @param {Array} classList
 * @returns {HTMLElement}
 */
const createEl = (nodeName, text = '', classList = []) => {
  const el = document.createElement(nodeName);
  el.textContent = text;
  if (classList && classList.length) {
    el.classList.add(...classList);
  }
  return el;
};

let overviewDialog;
const initOverviewModal = (options) => {
  if (overviewDialog) {
    return overviewDialog;
  }
  const dlItems = new Map();
  Object.values(options).forEach((value) => {
    if (!value.shortcut || !value.title) {
      return;
    }
    let titles = [];
    if (dlItems.has(value.shortcut)) {
      titles = dlItems.get(value.shortcut);
      titles.push(value.title);
    } else {
      titles = [value.title];
    }
    dlItems.set(value.shortcut, titles);
  });

  // Render:
  // <dl>
  // <div><dt><kbd>J</kbd><span>then</span><kbd>...</kbd><dd>....</dd></div>
  // <div><dt><kbd>J</kbd><span>then</span><kbd>...</kbd><dd>....</dd></div>
  // ...
  // </dl>
  const dl = createEl('dl');
  dlItems.forEach((titles, shortcut) => {
    const row = createEl('div');
    const dt = createEl('dt', '', ['d-inline-block']);
    row.appendChild(dt);
    dt.appendChild(createEl('kbd', 'J'));

    shortcut.split('+').forEach((key) => {
      dt.appendChild(createEl('span', Joomla.Text._('PLG_SYSTEM_SHORTCUT_THEN'), ['px-1']));
      dt.appendChild(createEl('kbd', key));
    });

    titles.forEach((title) => {
      const dd = createEl('dd', '', ['d-inline-block', 'ps-1']);
      dd.innerHTML = Joomla.sanitizeHtml(title);
      row.appendChild(dd);
    });

    dl.appendChild(row);
  });

  // Create the content for the dialog
  const intro = createEl('p');
  intro.innerHTML = Joomla.sanitizeHtml(Joomla.Text._('PLG_SYSTEM_SHORTCUT_OVERVIEW_DESC'), { kbd: '*' });
  const info = createEl('div');
  info.appendChild(dl);
  const content = createEl('div', '', ['p-3']);
  content.appendChild(intro);
  content.appendChild(info);

  overviewDialog = new JoomlaDialog({
    textHeader: Joomla.Text._('PLG_SYSTEM_SHORTCUT_OVERVIEW_TITLE'),
    textClose: Joomla.Text._('JCLOSE'),
    popupContent: content,
    width: '600px',
    height: 'fit-content',
  });

  return overviewDialog;
};

const showOverviewModal = (options) => {
  initOverviewModal(options).show();
};

document.addEventListener('DOMContentLoaded', () => {
  const options = Joomla.getOptions('plg_system_shortcut.shortcuts');
  Object.values(options).forEach((value) => {
    if (!value.shortcut || !value.selector) {
      return;
    }
    if (value.selector.startsWith('/') || value.selector.startsWith('http://') || value.selector.startsWith('www.')) {
      Joomla.addLinkShortcut(value.shortcut, value.selector);
    } else if (value.selector.includes('input')) {
      Joomla.addFocusShortcut(value.shortcut, value.selector);
    } else {
      Joomla.addClickShortcut(value.shortcut, value.selector);
    }
  });
  // Show hint and overview on logged in backend only (not login page)
  if (document.querySelector('nav')) {
    hotkeys('X', 'joomla', () => {
      showOverviewModal(options);
    });
    addOverviewHint();
  }
  setShortcutFilter();
  startupShortcuts();
});
