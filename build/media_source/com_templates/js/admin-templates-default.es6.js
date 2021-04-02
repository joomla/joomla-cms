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
  //   templateIds: button.dataset.item;
  // };

  button.setAttribute('disabled', '');

  try {
    response = await fetch(baseURL, {
      method: 'POST', // *GET, POST, PUT, DELETE, etc.
      mode: 'cors', // no-cors, *cors, same-origin
      cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached
      credentials: 'same-origin', // include, *same-origin, omit
      headers: {
        'Content-Type': 'application/json',
        // 'Content-Type': 'application/x-www-form-urlencoded',
      },
      redirect: 'follow', // manual, *follow, error
      referrerPolicy: 'no-referrer', // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
      // body: JSON.stringify(data) // body data type must match "Content-Type" header
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
