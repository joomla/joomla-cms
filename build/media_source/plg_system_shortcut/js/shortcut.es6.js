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

  const addOverviewHint = () => {
    const iconElement = document.createElement('span');
    iconElement.className = 'icon-info me-2';
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

  const initOverviewModal = () => {
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
                <h4>${Joomla.Text._('PLG_SYSTEM_SHORTCUT_ACTIONS_GENERAL_LABEL')}</h4>
                <ul>
                  <li>${Joomla.Text._('JHELP')}: J + H</li>
                  <li>${Joomla.Text._('JOPTIONS')}: J + O</li>
                  <li>${Joomla.Text._('JSEARCH_FILTER')}: J + S</li>
                </ul>

                <h4>${Joomla.Text._('PLG_SYSTEM_SHORTCUT_ACTIONS_LIST_LABEL')}</h4>
                <ul>
                  <li>${Joomla.Text._('JTOOLBAR_NEW')}: J + N</li>
                  <li>${Joomla.Text._('JCANCEL')}: J + Q</li>
                </ul>

                <h4>${Joomla.Text._('PLG_SYSTEM_SHORTCUT_ACTIONS_FORM_LABEL')}</h4>
                <ul>
                  <li>${Joomla.Text._('JAPPLY')}: J + A</li>
                  <li>${Joomla.Text._('JTOOLBAR_SAVE')}: J + S</li>
                  <li>${Joomla.Text._('JTOOLBAR_SAVE_AND_NEW')}: J + N</li>
                  <li>${Joomla.Text._('JTOOLBAR_CLOSE')}: J + Q</li>
                </ul>
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
    addOverviewHint();
    initOverviewModal();
  });
})(document, Joomla);
