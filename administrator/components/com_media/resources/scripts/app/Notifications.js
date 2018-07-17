class Notifications {

    /* Send and success notification */
    success(message, options) {
        notifications.notify(message, Object.assign({
            type: 'success',
            dismiss: true
        }, options));
    }

    /* Send an error notification */
    error(message, options) {
        notifications.notify(message, Object.assign({
            type: 'danger',
            dismiss: true
        }, options));
    }

    /* Ask the user a question */
    ask(message, options) {
        return window.confirm(message);
    }

    /* Send a notification */
    notify(message, options) {
        const alert = document.createElement('joomla-alert');
        alert.setAttribute('type', options.type || 'info');
        alert.setAttribute('dismiss', options.dismiss || true);
        alert.setAttribute('auto-dismiss', options.autoDismiss || true);
        alert.innerHTML = Joomla.JText._(message, message) || '';

        const messageContainer = document.getElementById('system-message');
        messageContainer.appendChild(alert);
    }
}

export let notifications = new Notifications();