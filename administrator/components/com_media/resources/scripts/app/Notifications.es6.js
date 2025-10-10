/**
 * Send a notification
 * @param {String} message
 * @param {{}} options
 *
 */
function notify(message, options) {
  let timer;
  if (options.type === 'message') {
    timer = 3000;
  }
  Joomla.renderMessages(
    {
      [options.type]: [Joomla.Text._(message)],
    },
    undefined,
    true,
    timer,
  );
}

const notifications = {
  /* Send a success notification */
  success: (message, options) => {
    notify(message, {
      type: 'message', // @todo rename it to success
      dismiss: true,
      ...options,
    });
  },

  /* Send an error notification */
  error: (message, options) => {
    notify(message, {
      type: 'error', // @todo rename it to danger
      dismiss: true,
      ...options,
    });
  },

  /* Send a general notification */
  notify: (message, options) => {
    notify(message, {
      type: 'message',
      dismiss: true,
      ...options,
    });
  },

  /* Ask the user a question */
  ask: (message) => window.confirm(message),
};

export default notifications;
