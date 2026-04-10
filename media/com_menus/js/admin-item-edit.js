/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

if (!window.Joomla) {
  throw new Error('core.js was not properly initialised');
}
Joomla.submitbutton = (task, type) => {
  if (task === 'item.setType' || task === 'item.setMenuType') {
    if (task === 'item.setType') {
      document.querySelectorAll('#item-form input[name="jform[type]"]').forEach(item => {
        item.value = type;
      });
      document.getElementById('fieldtype').value = 'type';
    } else {
      document.querySelectorAll('#item-form input[name="jform[menutype]"]').forEach(item => {
        item.value = type;
      });
    }
    Joomla.submitform('item.setType', document.getElementById('item-form'));
  } else if (task === 'item.cancel' || document.formvalidator.isValid(document.getElementById('item-form'))) {
    Joomla.submitform(task, document.getElementById('item-form'));
  } else {
    // special case for modal popups validation response
    const list = document.querySelectorAll('#item-form .modal-value.invalid');
    list.forEach(field => {
      const textInput = field.parentElement.querySelector('.js-input-title, [type="text"]');
      textInput.classList.add('invalid');
    });
  }
};

// Listen to "joomla:content-select-menutype" message
window.addEventListener('message', event => {
  // Avoid cross origins
  if (event.origin !== window.location.origin) return;
  // Check message type
  if (event.data.messageType === 'joomla:content-select-menutype') {
    // Set and submit values
    Joomla.submitbutton('item.setType', event.data.encoded);
  }
});
const onChange = ({
  target
}) => {
  const menuType = target.value;
  Joomla.request({
    url: `index.php?option=com_menus&task=item.getParentItem&menutype=${menuType}`,
    headers: {
      'Content-Type': 'application/json'
    },
    onSuccess(response) {
      const data = JSON.parse(response);
      const fancySelect = document.getElementById('jform_parent_id').closest('joomla-field-fancy-select');
      fancySelect.choicesInstance.clearChoices();
      fancySelect.choicesInstance.setChoices([{
        id: '1',
        text: Joomla.Text._('JGLOBAL_ROOT_PARENT')
      }], 'id', 'text', false);
      data.forEach(value => {
        const option = {};
        option.innerText = value.title;
        option.id = value.id;
        fancySelect.choicesInstance.setChoices([option], 'id', 'innerText', false);
      });
      fancySelect.choicesInstance.setChoiceByValue('1');
      const newEvent = document.createEvent('HTMLEvents');
      newEvent.initEvent('change', true, false);
      document.getElementById('jform_parent_id').dispatchEvent(newEvent);
    },
    onError: xhr => {
      Joomla.renderMessages(Joomla.ajaxErrorsMessages(xhr));
    }
  });
};
const element = document.getElementById('jform_menutype');
if (element) {
  element.addEventListener('change', onChange);
}

// Menu type Login Form specific
document.getElementById('item-form').addEventListener('submit', () => {
  if (document.getElementById('jform_params_login_redirect_url') && document.getElementById('jform_params_logout_redirect_url')) {
    // Login
    if (!document.getElementById('jform_params_login_redirect_url').closest('.control-group').classList.contains('hidden')) {
      document.getElementById('jform_params_login_redirect_menuitem_id').value = '';
    }
    if (!document.getElementById('jform_params_login_redirect_menuitem_name').closest('.control-group').classList.contains('hidden')) {
      document.getElementById('jform_params_login_redirect_url').value = '';
    }

    // Logout
    if (!document.getElementById('jform_params_logout_redirect_url').closest('.control-group').classList.contains('hidden')) {
      document.getElementById('jform_params_logout_redirect_menuitem_id').value = '';
    }
    if (!document.getElementById('jform_params_logout_redirect_menuitem_id').closest('.control-group').classList.contains('hidden')) {
      document.getElementById('jform_params_logout_redirect_url').value = '';
    }
  }
});
