/**
 * @copyright  (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

((document) => {
  'use strict';

  const cookie = document.cookie.split('; ');
  document.addEventListener('DOMContentLoaded', () => {
    if (cookie.indexOf('cookieBanner=true') === -1) {
      const Banner = new bootstrap.Modal(document.getElementById('cookieBanner'));
      Banner.show();
    }

    document.querySelectorAll('[data-cookiecategory]').forEach((item) => {
      cookie.forEach((i) => {
        if (i.match(`${item.getAttribute('data-cookiecategory')}=true`)) {
          item.checked = true;
        }
      });
    });

    document.querySelectorAll('[data-cookie-category]').forEach((item) => {
      cookie.forEach((i) => {
        if (i.match(`${item.getAttribute('data-cookie-category')}=true`)) {
          item.checked = true;
        }
      });
    });
  });

  const code = Joomla.getOptions('code');
  const parse = Range.prototype.createContextualFragment.bind(document.createRange());

  function getExpiration() {
    const exp = Joomla.getOptions('exp');
    const d = new Date();
    d.setTime(d.getTime() + (exp * 24 * 60 * 60 * 1000));
    const expires = d.toUTCString();
    return expires;
  }

  document.getElementById('bannerConfirmChoice').addEventListener('click', () => {
    const exp = getExpiration();
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
              } else {
                document.body.append(parse(i.code));
              }
              document.cookie = `cookie_category_${key}=true; expires=${exp}; path=/;`;
            });
          }
        });
      } else {
        const key = item.getAttribute('data-cookiecategory');
        document.cookie = `cookie_category_${key}=false; expires=${exp}; path=/;`;
      }
    });
    document.cookie = `cookieBanner=true; expires=${exp}; path=/;`;
  });

  document.getElementById('prefConfirmChoice').addEventListener('click', () => {
    const exp = getExpiration();
    document.querySelectorAll('[data-cookie-category]').forEach((item) => {
      if (item.checked) {
        Object.entries(code).forEach(([key, value]) => {
          if (key === item.getAttribute('data-cookie-category')) {
            Object.values(value).forEach((i) => {
              if (i.position === '1') {
                document.head.prepend(parse(i.code));
              } else if (i.position === '2') {
                document.head.append(parse(i.code));
              } else if (i.position === '3') {
                document.body.prepend(parse(i.code));
              } else {
                document.body.append(parse(i.code));
              }
              document.cookie = `cookie_category_${key}=true; expires=${exp}; path=/;`;
            });
          }
        });
      } else {
        const key = item.getAttribute('data-cookie-category');
        document.cookie = `cookie_category_${key}=false; expires=${exp}; path=/;`;
      }
    });
    document.cookie = `cookieBanner=true; expires=${exp}; path=/;`;
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
