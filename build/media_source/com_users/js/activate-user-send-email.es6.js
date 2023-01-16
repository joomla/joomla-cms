/**
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

if (!Joomla) {
  throw new Error('Joomla API was not properly initialized');
}

const button = document.querySelector('.activate-send-mail');

if (button && Object.keys(button.dataset).length !== 0) {
  button.addEventListener('click', () => {
    button.querySelector('span').className = 'icon-spinner';
    button.disabled = true;

    const queryString = Object.keys(button.dataset)
      .reduce((a, k) => {
        a.push(`${k}=${encodeURIComponent(button.dataset[k])}`);
        return a;
      }, [])
      .join('&');

    const url = `index.php?${queryString}`;

    Joomla.request({
      url,
      method: 'GET',

      onSuccess: (resp) => {
        let response;
        try {
          response = JSON.parse(resp);
        } catch (error) {
          button.classList.add('error');
        }

        button.querySelector('span').className = 'icon-mail';
        button.disabled = false;

        if (response.messages) {
          Joomla.renderMessages(response.messages);
        }
      },
      onError: (resp) => {
        const response = JSON.parse(resp);
        button.classList.add('error');
        if (response.messages) {
          Joomla.renderMessages(response.messages);
        }
      },
    });
  });
}
