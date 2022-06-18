((document, Joomla) => {
  'use strict';

  if (!Joomla) {
    throw new Error('Joomla API is not properly initialised');
  }

  /* global hotkeys */
  Joomla.addShortcut = (hotkey, callback) => {
    hotkeys(hotkey, (event) => {
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

  const addOverviewHint = () => {
    const iconElement = document.createElement('span');
    iconElement.className = 'icon-keyboard fa-keyboard me-2';
    iconElement.setAttribute('aria-hidden', true);
    const textElement = document.createElement('span');
    textElement.innerText = Joomla.Text._('PLG_SYSTEM_SHORTCUT_OVERVIEW_HINT');
    const hintElement = document.createElement('p');
    hintElement.appendChild(iconElement);
    hintElement.appendChild(textElement);
    const containerElement = document.createElement('section');
    containerElement.className = 'content pt-4';
    containerElement.appendChild(hintElement);
    document.querySelector('.container-main').appendChild(containerElement);
  };

  const initOverviewModal = (options) => {
    const dlItems = new Map();
    Object.values(options).forEach((value) => {
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
      titles.forEach((title) => {
        dl += `<dt>${title}</dt>`;
      });
      dl += `<dd>${shortcut}</dd>`;
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
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
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
    hotkeys('J + X', () => bootstrapModal.show());
  };

  document.addEventListener('DOMContentLoaded', () => {
    const options = Joomla.getOptions('plg_system_shortcut.shortcuts');
    Object.values(options).forEach((value) => {
      if (value.selector.includes('input')) {
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
  });
})(document, Joomla);
