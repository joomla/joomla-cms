class Notifications {
  /* Send and success notification */
  // eslint-disable-next-line class-methods-use-this
  success(message, options) {
    // eslint-disable-next-line no-use-before-define
    notifications.notify(message, {
      type: 'message', // @todo rename it to success
      dismiss: true,
      ...options,
    });
  }

  /* Send an error notification */
  // eslint-disable-next-line class-methods-use-this
  error(message, options) {
    // eslint-disable-next-line no-use-before-define
    notifications.notify(message, {
      type: 'error', // @todo rename it to danger
      dismiss: true,
      ...options,
    });
  }

  /* Ask the user a question */
  // eslint-disable-next-line class-methods-use-this
  ask(message) {
    return window.confirm(message);
  }

  /* Send a notification */
  // eslint-disable-next-line class-methods-use-this
  notify(message, options) {
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
}

// eslint-disable-next-line import/no-mutable-exports,import/prefer-default-export
export let notifications = new Notifications();
