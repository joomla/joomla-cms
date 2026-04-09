/**
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(() => {
  'use strict';

  const TOGGLE_TASKS = {
    'articles.publish': 'articles.unpublish',
    'articles.unpublish': 'articles.publish',
    'featured.publish': 'featured.unpublish',
    'featured.unpublish': 'featured.publish',
  };

  const getTokenName = () => Joomla.getOptions('csrf.token', '');

  const showMessage = (type, message) => {
    if (!message) {
      return;
    }

    if (typeof Joomla.renderMessages === 'function') {
      Joomla.renderMessages({ [type]: [message] });
    }
  };

  const updateButtonUi = (button, task) => {
    const publish = task.endsWith('.publish');
    const icon = button.querySelector('span');
    const tooltipId = button.getAttribute('aria-labelledby');
    const tooltip = tooltipId ? document.getElementById(tooltipId) : null;

    button.classList.remove('data-state-0', 'data-state-1');
    button.classList.add(publish ? 'data-state-1' : 'data-state-0');
    button.classList.toggle('published', publish);
    button.dataset.itemTask = TOGGLE_TASKS[task] || button.dataset.itemTask;

    if (icon) {
      icon.className = publish ? 'icon-publish' : 'icon-unpublish';
    }

    if (tooltip) {
      tooltip.textContent = publish
        ? Joomla.Text._('JPUBLISHED')
        : Joomla.Text._('JUNPUBLISHED');
    }
  };

  const fallbackSubmit = (button) => {
    const { itemId, itemTask } = button.dataset;

    if (!itemId || !itemTask) {
      return;
    }

    Joomla.listItemTask(itemId, itemTask, 'adminForm');
  };

  const sendToggleRequest = (button, task) => {
    const form = document.getElementById('adminForm');
    const token = getTokenName();
    const itemId = button.dataset.itemId;
    const item = form && itemId ? form.elements[itemId] : null;

    if (!item || !item.value) {
      delete button.dataset.ajaxBusy;
      fallbackSubmit(button);
      return;
    }

    const requestData = new URLSearchParams();
    requestData.append('cid[]', item.value);

    if (token) {
      requestData.append(token, '1');
    }

    button.disabled = true;

    Joomla.request({
      url: `index.php?option=com_content&task=${encodeURIComponent(task)}&format=json`,
      method: 'POST',
      data: requestData.toString(),
      onSuccess: (response) => {
        delete button.dataset.ajaxBusy;
        button.disabled = false;

        try {
          const result = JSON.parse(response);
          const payload = result && typeof result.data === 'object' ? result.data : {};
          const successful = result.success === true || payload.status === 'success';

          if (!successful) {
            const message = payload.message || result.message || 'Unable to update article status.';
            showMessage('error', message);
            return;
          }

          updateButtonUi(button, task);
          showMessage('message', payload.message || result.message || 'Article status updated');
        } catch (error) {
          delete button.dataset.ajaxBusy;
          fallbackSubmit(button);
        }
      },
      onError: () => {
        delete button.dataset.ajaxBusy;
        button.disabled = false;
        fallbackSubmit(button);
      },
    });
  };

  const handleStatusToggle = (event) => {
    const button = event.currentTarget;
    const { itemTask } = button.dataset;

    if (button.hasAttribute('disabled') || !Object.prototype.hasOwnProperty.call(TOGGLE_TASKS, itemTask)) {
      return;
    }

    event.preventDefault();
    event.stopPropagation();
    event.stopImmediatePropagation();

    if (button.dataset.ajaxBusy === '1') {
      return;
    }

    button.dataset.ajaxBusy = '1';
    sendToggleRequest(button, itemTask);
  };

  const setup = ({ target }) => {
    target.querySelectorAll('.article-status').forEach((element) => {
      if (element.dataset.statusStopBound === '1') {
        return;
      }

      element.dataset.statusStopBound = '1';
      element.addEventListener('click', (event) => event.stopPropagation());
    });

    target.querySelectorAll('.article-status .js-grid-item-action').forEach((button) => {
      if (button.dataset.ajaxToggleBound === '1') {
        return;
      }

      button.dataset.ajaxToggleBound = '1';
      button.addEventListener('click', handleStatusToggle, true);
    });
  };

  document.addEventListener('DOMContentLoaded', () => {
    setup({ target: document });
  });

  document.addEventListener('joomla:updated', setup);
})();
