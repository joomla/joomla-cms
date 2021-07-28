/**
 * @copyright  (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

((document) => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const Banner = new bootstrap.Modal(document.getElementById('cookieBanner'));
    Banner.show();

    document.querySelectorAll('[data-cookiecategory="necessary"]').forEach((item) => {
      item.setAttribute('checked', true);
      item.setAttribute('disabled', true);
    });
  });

  const code = Joomla.getOptions('code');
  const parse = Range.prototype.createContextualFragment.bind(document.createRange());

  document.getElementById('btnConfirmChoice').addEventListener('click', () => {
    document.querySelectorAll('[data-cookiecategory]').forEach((item) => {
      if (item.checked) {
        Object.entries(code).forEach(([key, value]) => {
          if (key === item.getAttribute('data-cookiecategory')) {
            Object.values(value).forEach((i) => {
              if (i.position === '1') {
                document.head.prepend(parse(i.code));
              } else if (i.position === '2') {
                document.head.append(parse(i.code));
              } else if (i.position === '3') {
                document.body.prepend(parse(i.code));
              } else if (i.position === '4') {
                document.body.append(parse(i.code));
              }
            });
          }
        });
      }
    });
  });

  document.querySelectorAll('a[data-bs-toggle="collapse"]').forEach((item) => {
    item.addEventListener('click', () => {
      if (item.innerText === Joomla.Text._('COM_COOKIEMANAGER_PREFERENCES_MORE_BUTTON_TEXT')) {
        item.innerText = Joomla.Text._('COM_COOKIEMANAGER_PREFERENCES_LESS_BUTTON_TEXT');
      } else {
        item.innerText = Joomla.Text._('COM_COOKIEMANAGER_PREFERENCES_MORE_BUTTON_TEXT');
      }
    });
  });
})(document);
