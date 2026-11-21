/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

const options = Joomla.getOptions('menus-edit-modules', {});
const viewLevels = options.viewLevels || [];
const menuId = options.itemId || 0;
const assigned1 = document.getElementById('jform_toggle_modules_assigned1');
const assigned0 = document.getElementById('jform_toggle_modules_assigned0');
const published1 = document.getElementById('jform_toggle_modules_published1');
const published0 = document.getElementById('jform_toggle_modules_published0');

if (assigned1) {
  assigned1.addEventListener('click', () => {
    document.querySelectorAll('tr.no').forEach((item) => {
      item.classList.add('table-row');
      item.classList.remove('hidden');
    });
  });
}

if (assigned0) {
  assigned0.addEventListener('click', () => {
    document.querySelectorAll('tr.no').forEach((item) => {
      item.classList.add('hidden');
      item.classList.remove('table-row');
    });
  });
}

if (published1) {
  published1.addEventListener('click', () => {
    document.querySelectorAll('.table tr.unpublished').forEach((item) => {
      item.classList.add('table-row');
      item.classList.remove('hidden');
    });
  });
}

if (published0) {
  published0.addEventListener('click', () => {
    document.querySelectorAll('.table tr.unpublished').forEach((item) => {
      item.classList.add('hidden');
      item.classList.remove('table-row');
    });
  });
}

/**
 * A helper to create an element
 * @param {String} tag
 * @param {String} content
 * @param {Array} classList
 * @returns {HTMLElement}
 */
const createElement = (tag, content, classList = []) => {
  const el = document.createElement(tag);
  el.textContent = content;
  if (classList.length) {
    el.classList.add(...classList);
  }
  return el;
};

/**
 * Update module in list
 * @param {Object} data
 */
const updateView = (data) => {
  const modId = data.id;
  const updPosition = data.position;
  const updTitle = data.title;
  const updMenus = data.assignment;
  const updStatus = data.status;
  const updAccess = data.access;
  const tmpMenu = document.getElementById(`menus-${modId}`);
  const tmpRow = document.getElementById(`tr-${modId}`);
  const tmpStatus = document.getElementById(`status-${modId}`);
  const assigned = data.assigned || [];
  const inMenus = assigned.map((v) => Math.abs(v));
  const inAssignedList = inMenus.indexOf(menuId);
  let assignedState = 0; // 0 = No, 1 = Yes, 2 = All

  // Update assignment badge
  if (updMenus === '-') {
    assignedState = 0;
  } else if (updMenus === 0) {
    assignedState = 2;
  } else if (updMenus > 0) {
    if (inAssignedList >= 0) {
      assignedState = 1;
    } else if (inAssignedList < 0) {
      assignedState = 0;
    }
  } else if (updMenus < 0) {
    if (inAssignedList >= 0) {
      assignedState = 0;
    } else if (inAssignedList < 0) {
      assignedState = 1;
    }
  }

  switch (assignedState) {
    case 1:
      tmpMenu.innerHTML = createElement('span', Joomla.Text._('JYES'), ['badge', 'bg-success']).outerHTML;
      tmpRow.classList.add('no');
      break;

    case 2:
      tmpMenu.innerHTML = createElement('span', Joomla.Text._('JALL'), ['badge', 'bg-info']).outerHTML;
      tmpRow.classList.add('no');
      break;

    case 0:
    default:
      tmpMenu.innerHTML = createElement('span', Joomla.Text._('JNO'), ['badge', 'bg-danger']).outerHTML;
      tmpRow.classList.add('no');
  }

  // Update status
  if (updStatus === 1) {
    tmpStatus.innerHTML = createElement('span', Joomla.Text._('JYES'), ['badge', 'bg-success']).outerHTML;
    tmpRow.classList.remove('unpublished');
  } else if (updStatus === 0) {
    tmpStatus.innerHTML = createElement('span', Joomla.Text._('JNO'), ['badge', 'bg-danger']).outerHTML;
    tmpRow.classList.add('unpublished');
  } else if (updStatus === -2) {
    tmpStatus.innerHTML = createElement('span', Joomla.Text._('JTRASHED'), ['badge', 'bg-secondary']).outerHTML;
    tmpRow.classList.add('unpublished');
  }

  // Update Title, Position and Access
  document.querySelector(`#title-${modId}`).textContent = updTitle;
  document.querySelector(`#position-${modId}`).textContent = updPosition;
  document.querySelector(`#access-${modId}`).textContent = viewLevels[updAccess] || '';
};

/**
 * Message listener
 * @param {MessageEvent} event
 */
const msgListener = function (event) {
  // Avoid cross origins
  if (event.origin !== window.location.origin) return;
  // Check message
  if (event.data.messageType === 'joomla:content-select' && event.data.contentType === 'com_modules.module') {
    // Update view, if there are any changes
    if (event.data.id) {
      updateView(event.data);
    }
    // Close dialog
    this.close();
  }
};

// Listen when "add module" dialog opens, and add message listener
document.addEventListener('joomla-dialog:open', ({ target }) => {
  if (!target.classList.contains('menus-dialog-module-editing')) return;
  // Create a listener with current dialog context
  const listener = msgListener.bind(target);

  // Wait for a message
  window.addEventListener('message', listener);

  // Remove listener on close
  target.addEventListener('joomla-dialog:close', () => {
    window.removeEventListener('message', listener);
  });
});
