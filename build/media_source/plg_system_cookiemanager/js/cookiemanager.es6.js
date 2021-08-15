/**
 * @copyright  (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

((document) => {
  'use strict';

  let consentsOptIn = [];
  let consentsOptOut = [];
  const cookie = document.cookie.split('; ');
  const uuid = cookie.find((c) => c.startsWith('uuid=')).split('=')[1];
  const config = Joomla.getOptions('config');
  const code = Joomla.getOptions('code');
  const parse = Range.prototype.createContextualFragment.bind(document.createRange());

  document.addEventListener('DOMContentLoaded', () => {
    document.querySelector('#cookieBanner .modal-dialog').classList.add(config.position);
    document.querySelector('#preferences .modal-dialog').classList.add('modal-dialog-scrollable');

    if (cookie.indexOf('cookieBanner=shown') === -1) {
      const Banner = new bootstrap.Modal(document.querySelector('#cookieBanner'));
      Banner.show();
    }

    if (cookie.find((c) => c.startsWith('consents_opt_in=')) !== undefined) {
      const consentOptIn = cookie.find((c) => c.startsWith('consents_opt_in=')).split('=')[1];
      const consentDate = cookie.find((c) => c.startsWith('consent_date=')).split('=')[1];
      const ccuuid = cookie.find((c) => c.startsWith('ccuuid=')).split('=')[1];

      document.getElementById('ccuuid').innerHTML = ccuuid;
      document.getElementById('consent-date').innerHTML = consentDate;
      document.getElementById('consent-opt-in').innerHTML = consentOptIn;
    }

    document.querySelectorAll('[data-cookiecategory]').forEach((item) => {
      cookie.forEach((i) => {
        if (i.match(`${item.getAttribute('data-cookiecategory')}=true`)) {
          item.checked = true;
        } else if (i.indexOf(`cookie_category_${item.getAttribute('data-cookiecategory')}=false`) === -1 || i.match(`cookie_category_${item.getAttribute('data-cookiecategory')}=false`)) {
          Object.entries(code).forEach(([key, value]) => {
            if (key === item.getAttribute('data-cookiecategory')) {
              Object.values(value).forEach((val) => {
                if (val.type === 3 || val.type === 4 || val.type === 6) {
                  const q = val.code.match(/src="([^\s]*)"\s/)[1];
                  if (document.querySelector(`[src="${q}"]`)) {
                    const p = document.querySelector(`[src="${q}"]`);
                    p.setAttribute('data-src', q);
                    p.removeAttribute('src');
                  }
                } else if (val.type === 5) {
                  const q = val.code.match(/data="([^\s]*)"\s/)[1];
                  if (document.querySelector(`[data="${q}"]`)) {
                    const p = document.querySelector(`[data="${q}"]`);
                    p.setAttribute('data-src', q);
                    p.removeAttribute('data');
                  }
                } else if (val.type === 7) {
                  const q = val.code.match(/href="(.+)"/)[1];
                  if (document.querySelector(`[href="${q}"]`)) {
                    const p = document.querySelector(`[href="${q}"]`);
                    p.setAttribute('data-href', q);
                    p.removeAttribute('href');
                  }
                }
              });
            }
          });
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

  const getExpiration = () => {
    const exp = config.expiration;
    const d = new Date();
    d.setTime(d.getTime() + (exp * 24 * 60 * 60 * 1000));
    const expires = d.toUTCString();
    return expires;
  };

  const storingConsents = () => {
    const consentsIn = consentsOptIn.join(', ');
    const consentsOut = consentsOptOut.join(', ');
    const date = Date();
    document.cookie = `consents_opt_in=${consentsIn}; path=/;`;
    document.cookie = `consent_date=${date}; path=/;`;
    const consentDetails = {
      uuid,
      consent_date: date,
      url: window.location.href,
      consent_opt_in: consentsIn,
      consent_opt_out: consentsOut,
    };
    const data = JSON.stringify(consentDetails);

    Joomla.request({
      url: `index.php?option=com_ajax&plugin=cookiemanager&group=system&format=json&data=${data}`,
      method: 'POST',
      onSuccess: (r) => {
        const res = JSON.parse(r);
        const ccuuid = res.data[0];

        document.cookie = `ccuuid=${ccuuid}; path=/;`;

        document.getElementById('ccuuid').innerHTML = ccuuid;
        document.getElementById('consent-date').innerHTML = consentDetails.consent_date;
        document.getElementById('consent-opt-in').innerHTML = consentDetails.consent_opt_in;
      },
    });
  };

  document.getElementById('bannerConfirmChoice').addEventListener('click', () => {
    const exp = getExpiration();
    document.querySelectorAll('[data-cookiecategory]').forEach((item) => {
      if (item.checked) {
        Object.entries(code).forEach(([key, value]) => {
          if (key === item.getAttribute('data-cookiecategory')) {
            Object.values(value).forEach((i) => {
              if (i.type === 1 || i.type === 2) {
                if (i.position === 1) {
                  document.head.prepend(parse(i.code));
                } else if (i.position === 2) {
                  document.head.append(parse(i.code));
                } else if (i.position === 3) {
                  document.body.prepend(parse(i.code));
                } else {
                  document.body.append(parse(i.code));
                }
              } else if (i.type === 3 || i.type === 4 || i.type === 6) {
                const q = i.code.match(/src="([^\s]*)"\s/)[1];
                if (document.querySelector(`[data-src="${q}"]`)) {
                  const p = document.querySelector(`[data-src="${q}"]`);
                  p.setAttribute('src', q);
                  p.removeAttribute('data-src');
                }
              } else if (i.type === 5) {
                const q = i.code.match(/data="([^\s]*)"\s/)[1];
                if (document.querySelector(`[data-src="${q}"]`)) {
                  const p = document.querySelector(`[data-src="${q}"]`);
                  p.setAttribute('data', q);
                  p.removeAttribute('data-src');
                }
              } else {
                const q = i.code.match(/href="(.+)"/)[1];
                if (document.querySelector(`[data-href="${q}"]`)) {
                  const p = document.querySelector(`[data-href="${q}"]`);
                  p.setAttribute('href', q);
                  p.removeAttribute('data-href');
                }
              }

              document.cookie = `cookie_category_${key}=true; expires=${exp}; path=/;`;
            });
            consentsOptIn.push(key);
          }
        });
      } else {
        const key = item.getAttribute('data-cookiecategory');
        document.cookie = `cookie_category_${key}=false; expires=${exp}; path=/;`;
        consentsOptOut.push(key);
      }
    });
    document.cookie = `cookieBanner=shown; expires=${exp}; path=/;`;
    storingConsents();
    consentsOptIn = [];
    consentsOptOut = [];
  });

  document.getElementById('prefConfirmChoice').addEventListener('click', () => {
    const exp = getExpiration();
    document.querySelectorAll('[data-cookie-category]').forEach((item) => {
      if (item.checked) {
        Object.entries(code).forEach(([key, value]) => {
          if (key === item.getAttribute('data-cookie-category')) {
            Object.values(value).forEach((i) => {
              if (i.type === 1 || i.type === 2) {
                if (i.position === 1) {
                  document.head.prepend(parse(i.code));
                } else if (i.position === 2) {
                  document.head.append(parse(i.code));
                } else if (i.position === 3) {
                  document.body.prepend(parse(i.code));
                } else {
                  document.body.append(parse(i.code));
                }
              } else if (i.type === 3 || i.type === 4 || i.type === 6) {
                const q = i.code.match(/src="([^\s]*)"\s/)[1];
                if (document.querySelector(`[data-src="${q}"]`)) {
                  const p = document.querySelector(`[data-src="${q}"]`);
                  p.setAttribute('src', q);
                  p.removeAttribute('data-src');
                }
              } else if (i.type === 5) {
                const q = i.code.match(/data="([^\s]*)"\s/)[1];
                if (document.querySelector(`[data-object="${q}"]`)) {
                  const p = document.querySelector(`[data-src="${q}"]`);
                  p.setAttribute('data', q);
                  p.removeAttribute('data-src');
                }
              } else {
                const q = i.code.match(/href="(.+)"/)[1];
                if (document.querySelector(`[data-href="${q}"]`)) {
                  const p = document.querySelector(`[data-href="${q}"]`);
                  p.setAttribute('href', q);
                  p.removeAttribute('data-href');
                }
              }

              document.cookie = `cookie_category_${key}=true; expires=${exp}; path=/;`;
            });
            consentsOptIn.push(key);
          }
        });
      } else {
        const key = item.getAttribute('data-cookie-category');
        document.cookie = `cookie_category_${key}=false; expires=${exp}; path=/;`;
        consentsOptOut.push(key);
      }
    });
    document.cookie = `cookieBanner=shown; expires=${exp}; path=/;`;
    storingConsents();
    consentsOptIn = [];
    consentsOptOut = [];
  });

  document.querySelectorAll('[data-button="acceptAllCookies"]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const exp = getExpiration();
      Object.entries(code).forEach(([key, value]) => {
        Object.values(value).forEach((i) => {
          if (i.type === 1 || i.type === 2) {
            if (i.position === 1) {
              document.head.prepend(parse(i.code));
            } else if (i.position === 2) {
              document.head.append(parse(i.code));
            } else if (i.position === 3) {
              document.body.prepend(parse(i.code));
            } else {
              document.body.append(parse(i.code));
            }
          } else if (i.type === 3 || i.type === 4 || i.type === 6) {
            const q = i.code.match(/src="([^\s]*)"\s/)[1];
            if (document.querySelector(`[data-src="${q}"]`)) {
              const p = document.querySelector(`[data-src="${q}"]`);
              p.setAttribute('src', q);
              p.removeAttribute('data-src');
            }
          } else if (i.type === 5) {
            const q = i.code.match(/data="([^\s]*)"\s/)[1];
            if (document.querySelector(`[data-src="${q}"]`)) {
              const p = document.querySelector(`[data-src="${q}"]`);
              p.setAttribute('data', q);
              p.removeAttribute('data-src');
            }
          } else {
            const q = i.code.match(/href="(.+)"/)[1];
            if (document.querySelector(`[data-href="${q}"]`)) {
              const p = document.querySelector(`[data-href="${q}"]`);
              p.setAttribute('href', q);
              p.removeAttribute('data-href');
            }
          }
          document.cookie = `cookie_category_${key}=true; expires=${exp}; path=/;`;
        });
        consentsOptIn.push(key);
      });

      document.cookie = `cookieBanner=shown; expires=${exp}; path=/;`;
      storingConsents();
      consentsOptIn = [];
      consentsOptOut = [];
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
