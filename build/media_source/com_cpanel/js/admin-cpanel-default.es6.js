/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

((window, document, Joomla) => {
  let matchesFn = 'matches';

  Joomla.unpublishModule = (element) => {
    // Get variables
    const baseUrl = 'index.php?option=com_modules&task=modules.unpublish&format=json';
    const id = element.getAttribute('data-module-id');

    Joomla.request({
      url: `${baseUrl}&cid=${id}`,
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      onSuccess: (resp) => {
        const wrapper = closest(element, '.module-wrapper');
        wrapper.parentNode.removeChild(wrapper);

        Joomla.renderMessages({
          message: [Joomla.JText._('COM_CPANEL_UNPUBLISH_MODULE_SUCCESS')]
        });
      },
      onError: () => {
        Joomla.renderMessages({
          error: [Joomla.JText._('COM_CPANEL_UNPUBLISH_MODULE_ERROR')]
        });
      },
    });
  };

  const closest = (element, selector) => {
    let parent;

    // Traverse parents
    while (element) {
      parent = element.parentElement;
      if (parent && parent[matchesFn](selector)) {
        return parent;
      }
      element = parent;
    }

    return null;
  }

  const onBoot = () => {
    // Find matchesFn with vendor prefix
    ['matches', 'msMatchesSelector'].some((fn) => {
      if (typeof document.body[fn] === 'function') {
        matchesFn = fn;
        return true;
      }
      return false;
    });

    const cpanelModules = document.getElementById('cpanel-modules');
    if (cpanelModules) {
      const links = [].slice.call(cpanelModules.querySelectorAll('.unpublish-module'));
      links.forEach((link) => {
        link.addEventListener('click', event => Joomla.unpublishModule(event.target));
      });
    }

    // Cleanup
    document.removeEventListener('DOMContentLoaded', onBoot);
  };

  // Initialise
  document.addEventListener('DOMContentLoaded', onBoot);
})(window, document, window.Joomla);
