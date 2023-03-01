((document, Joomla) => {
  'use strict';

  if (!Joomla) {
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

  const initOverviewModal = (options) => {
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

    let dl = '<dl>';
    dlItems.forEach((titles, shortcut) => {
      dl += '<dt><kbd>J</kbd>';
      shortcut.split('+').forEach((key) => {
        dl += ` ${Joomla.Text._('PLG_SYSTEM_SHORTCUT_THEN')} <kbd>${key.trim()}</kbd>`;
      });
      dl += '</dt>';
      titles.forEach((title) => {
        dl += `<dd>${title}</dd>`;
      });
    });
    dl += '</dl>';

    const modal = `
      <div class="modal fade" id="shortcutOverviewModal" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-labelledby="shortcutOverviewModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h3 id="shortcutOverviewModalLabel" class="modal-title">
                ${Joomla.Text._('PLG_SYSTEM_SHORTCUT_OVERVIEW_TITLE')}
              </h3>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="${Joomla.Text._('JCLOSE')}"></button>
            </div>
            <div class="modal-body p-3">
              <p>${Joomla.Text._('PLG_SYSTEM_SHORTCUT_OVERVIEW_DESC')}</p>
              <div class="mb-3">
                ${dl}
              </div>
            </div>
          </div>
        </div>
      </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modal);

    const bootstrapModal = new bootstrap.Modal(document.getElementById('shortcutOverviewModal'), {
      keyboard: true,
      backdrop: true,
    });
    hotkeys('X', 'joomla', () => bootstrapModal.show());
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
      initOverviewModal(options);
      addOverviewHint();
    }
    setShortcutFilter();
    startupShortcuts();
  });
})(document, Joomla);
