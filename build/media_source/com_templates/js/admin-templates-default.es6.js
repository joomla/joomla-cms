/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

const onClick = async (event) => {
  let response;
  const button = event.currentTarget;
  const { form } = button;

  const baseURL = `${form.action}&task=${button.dataset.task}&${form.dataset.token}=1&cid[]=${button.dataset.item}`;
  // const data = {
  //   templateIds: button.dataset.item,
  //   [form.dataset.token]: 1,
  //   'cid[]': button.dataset.item,
  // };

  button.setAttribute('disabled', '');

  try {
    response = await fetch(baseURL, {
      method: 'POST',
      mode: 'cors',
      cache: 'no-cache',
      credentials: 'same-origin',
      headers: {
        // 'Content-Type': 'application/json',
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      redirect: 'follow',
      referrerPolicy: 'no-referrer',
      // body: JSON.stringify(data),
    });
  } catch (error) {
    // @todo use alert here
    // eslint-disable-next-line no-console
    console.log(error);
    button.removeAttribute('disabled');
  }

  if (response.ok && response.status === 200 && response.statusText === 'OK') {
    window.location.reload();
  } else {
    // @todo use alert here
    // eslint-disable-next-line no-console
    console.log(response.statusText);
    button.removeAttribute('disabled');
  }
};

const actionButtons = [].slice.call(document.querySelectorAll('button.js-action-exec'));

actionButtons.map((button) => button.addEventListener('click', onClick));
