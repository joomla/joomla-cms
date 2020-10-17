class Notifications {

    /* Send and success notification */
    success(message, options) {
        notifications.notify(message, Object.assign({
            type: 'message',
            dismiss: true
        }, options));
    }

    /* Send an error notification */
    error(message, options) {
        notifications.notify(message, Object.assign({
            type: 'error',
            dismiss: true
        }, options));
    }

    /* Ask the user a question */
    ask(message, options) {
        return window.confirm(message);
    }

    /* Send a notification */
    notify(message, options) {
        Joomla.renderMessages(
        {
            [options.type]: [Joomla.JText._(message)],
        });
    }
}

export let notifications = new Notifications();
