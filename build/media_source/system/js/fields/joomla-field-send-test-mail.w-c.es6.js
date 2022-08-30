((customElements, Joomla) => {
  class JoomlaFieldSendTestMail extends HTMLElement {
    // attributeChangedCallback(attr, oldValue, newValue) {}
    constructor() {
      super();

      if (!Joomla) {
        throw new Error('Joomla API is not properly initiated');
      }

      if (!this.getAttribute('uri')) {
        throw new Error('No valid url for validation');
      }
    }

    connectedCallback() {
      const self = this;
      const button = document.getElementById('sendtestmail');

      if (button) {
        button.addEventListener('click', () => { self.sendTestMail(self); });
      }
    }

    sendTestMail() {
      const emailData = {
        smtpauth: document.getElementById('jform_smtpauth1').checked ? 1 : 0,
        smtpuser: this.querySelector('[name="jform[smtpuser]"]').value,
        smtphost: this.querySelector('[name="jform[smtphost]"]').value,
        smtpsecure: this.querySelector('[name="jform[smtpsecure]"]').value,
        smtpport: this.querySelector('[name="jform[smtpport]"]').value,
        mailfrom: this.querySelector('[name="jform[mailfrom]"]').value,
        fromname: this.querySelector('[name="jform[fromname]"]').value,
        mailer: this.querySelector('[name="jform[mailer]"]').value,
        mailonline: document.getElementById('jform_mailonline1').checked ? 1 : 0,
      };

      const smtppass = this.querySelector('[name="jform[smtppass]"]');

      if (smtppass.disabled === false) {
        emailData.smtppass = smtppass.value;
      }

      // Remove js messages, if they exist.
      Joomla.removeMessages();

      Joomla.request({
        url: this.getAttribute('uri'),
        method: 'POST',
        data: JSON.stringify(emailData),
        perform: true,
        headers: { 'Content-Type': 'application/json' },
        onSuccess: (resp) => {
          let response;
          try {
            response = JSON.parse(resp);
          } catch (e) {
            // eslint-disable-next-line no-console
            console.error(e);
          }

          if (typeof response.messages === 'object' && response.messages !== null) {
            Joomla.renderMessages(response.messages);
          }

          document.body.scrollIntoView({ behavior: 'smooth' });
        },
        onError: (xhr) => {
          Joomla.renderMessages(Joomla.ajaxErrorsMessages(xhr));

          document.body.scrollIntoView({ behavior: 'smooth' });
        },
      });
    }
  }

  customElements.define('joomla-field-send-test-mail', JoomlaFieldSendTestMail);
})(customElements, Joomla);
