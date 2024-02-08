/**
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

if (!Joomla) {
  throw new Error('Joomla API is not properly initialised');
}

const hideElements = (ids) => {
  ids.forEach((id) => {
    const element = document.getElementById(id);
    if (element) {
      element.classList.add('hidden');
    }
  });
};

const createOption = (value, text) => {
  const option = document.createElement('option');
  option.value = value;
  option.innerText = text;
  return option;
};

// Attach behaviour to toggle button.
document.body.addEventListener('click', ({ target }) => {
  if (target.id === 'toggle-left-panel') {
    const referenceHide = target.getAttribute('data-hide-reference');
    const referenceShow = target.getAttribute('data-show-reference');

    if (target.innerText === referenceHide) {
      target.innerText = referenceShow;
    } else {
      target.innerText = referenceHide;
    }

    document.getElementById('left-panel').classList.toggle('hidden');
    document.getElementById('right-panel').classList.toggle('full-width');
  }
});

// Attach behaviour to language selector change event.
document.body.addEventListener('change', ({ target }) => {
  if (target.id === 'jform_itemlanguage') {
    const targetIframe = document.getElementById('target-association');
    const selected = target.value;

    // Populate the data attributes and load the the edit page in target frame.
    if (selected !== '' && typeof selected !== 'undefined') {
      targetIframe.setAttribute('data-action', selected.split(':')[2]);
      targetIframe.setAttribute('data-id', selected.split(':')[1]);
      targetIframe.setAttribute('data-language', selected.split(':')[0]);

      // Iframe load start, show Joomla loading layer.
      document.body.appendChild(document.createElement('joomla-core-loader'));

      // Load the target frame.
      targetIframe.src = `${targetIframe.getAttribute('data-editurl')}&task=${targetIframe.getAttribute('data-item')}.${targetIframe.getAttribute('data-action')}&id=${targetIframe.getAttribute('data-id')}`;
    } else {
      // Reset the data attributes and no item to load.
      hideElements(['toolbar-target', 'toolbar-copy', 'select-change', 'remove-assoc']);

      targetIframe.setAttribute('data-action', '');
      targetIframe.setAttribute('data-id', '0');
      targetIframe.setAttribute('data-language', '');
      targetIframe.src = '';
    }
  }
});

// Attach behaviour to reference frame load event.
document.getElementById('reference-association').addEventListener('load', ({ target }) => {
  // Waiting until the reference has loaded before loading the target to avoid race conditions
  let targetURL = Joomla.getOptions('targetSrc', false);

  if (targetURL) {
    targetURL = targetURL.split('&amp;').join('&');
    document.getElementById('target-association').setAttribute('src', targetURL);
    Joomla.loadOptions({ targetSrc: false });
    return;
  }

  // Load Target Pane AFTER reference pane has loaded to prevent session conflict with checkout
  document.getElementById('target-association').setAttribute('src', document.getElementById('target-association').getAttribute('src'));

  const content = target.contentDocument.body || target.contentWindow.document.body;

  // If copy button used
  if (content.querySelector('#jform_id').value !== target.getAttribute('data-id')) {
    const targetAssociation = document.getElementById('target-association');
    targetAssociation.src = `${targetAssociation.getAttribute('data-editurl')}&task=${targetAssociation.getAttribute('data-item')}.edit&id=${content.querySelector('#jform_id').value}`;
    target.src = `${target.getAttribute('data-editurl')}&task=${target.getAttribute('data-item')}.edit&id=${target.getAttribute('data-id')}`;
  }

  // Disable language field.
  content.querySelector('#jform_language').setAttribute('disabled', 'disabled');

  // Remove modal buttons on the reference
  content.querySelector('#associations .btn').remove();

  document.querySelectorAll('#jform_itemlanguage option').forEach((option) => {
    const parse = option.value.split(':');

    if (typeof parse[0] !== 'undefined') {
      // - For modal association selectors.
      const langAssociation = parse[0].replace(/-/, '_');

      const langAssociationId = content.querySelector(`#jform_associations_${langAssociation}_id`);
      if (langAssociationId && langAssociationId.value === '') {
        const referenceAssociation = document.getElementById('reference-association');
        if (referenceAssociation.hasAttribute('data-no-assoc')) {
          content.querySelector(`#jform_associations_${langAssociation}_name`).value = referenceAssociation.getAttribute('data-no-assoc');
        }
      }
    }
  });

  // Iframe load finished, hide Joomla loading layer.
  const spinner = document.querySelector('joomla-core-loader');
  if (spinner) {
    spinner.parentNode.removeChild(spinner);
  }
});

// Attach behaviour to target frame load event.
document.getElementById('target-association').addEventListener('load', ({ target }) => {
  // We need to check if we are not loading a blank iframe.
  if (target.getAttribute('src') !== '') {
    document.getElementById('toolbar-target').classList.remove('hidden');
    document.getElementById('toolbar-copy').classList.remove('hidden');
    document.getElementById('select-change').classList.remove('hidden');

    const targetLanguage = target.getAttribute('data-language');
    const targetId = target.getAttribute('data-id');
    const content = target.contentDocument.body || target.contentWindow.document.body;
    const targetLoadedId = content.querySelector('#jform_id').value || '0';

    const reference = document.getElementById('reference-association');

    // Remove modal buttons on the target
    // content.querySelector('a[href="#associations"]').parentNode.querySelector('.btn').forEach(btn => btn.remove());
    // content.querySelector('#associations .btn').forEach(btn => btn.remove());

    // Always show General tab first if associations tab is selected on the reference
    if (content.querySelector('#associations').classList.contains('active')) {
      content.querySelector('a[href="#associations"]').parentNode.classList.remove('active');
      content.querySelector('#associations').classList.remove('active');

      content.querySelector('.nav-tabs li').classList.add('active');
      content.querySelector('.tab-content .tab-pane').classList.add('active');
    }

    // Update language field with the selected language and them disable it.
    content.querySelector('#jform_language').value = targetLanguage;
    content.querySelector('#jform_language').setAttribute('disabled', 'disabled');

    // If we are creating a new association (before save) we need to add the new association.
    if (targetLoadedId === '0') {
      document.getElementById('select-change-text').innerHTML = Joomla.sanitizeHtml(document.getElementById('select-change').getAttribute('data-select'));
    } else {
      // If we are editing an association.

      // Show change language button
      document.getElementById('select-change-text').innerHTML = Joomla.sanitizeHtml(document.getElementById('select-change').getAttribute('data-change'));
      document.getElementById('remove-assoc').classList.remove('hidden');
      document.getElementById('remove-assoc').classList.add('toolbar-copy');

      // Add the id to list of items to check in on close.
      const currentIdList = document.getElementById('target-id').value;
      const updatedList = currentIdList === '' ? targetLoadedId : `${currentIdList},${targetLoadedId}`;
      document.getElementById('target-id').value = updatedList;

      // If we created a new association (after save).
      if (targetLoadedId !== targetId) {
        // Refresh the language selector with the new id (used after save).
        document.querySelector(`#jform_itemlanguage option[value^="${targetLanguage}:${targetId}:add"]`).value = `${targetLanguage}:${targetLoadedId}:edit`;

        // Update main frame data-id attribute (used after save).
        target.setAttribute('data-id', targetLoadedId);
        target.setAttribute('data-action', 'edit');
      }

      // Update the reference item associations tab.
      const referenceContent = reference.contentDocument.body || reference.contentWindow.document.body;
      const languageCode = targetLanguage.replace(/-/, '_');
      const title = content.querySelector(`#jform_${reference.getAttribute('data-title')}`).value;

      // - For modal association selectors.
      const referenceContentId = referenceContent.querySelector(`#jform_associations_${languageCode}_id`);
      if (referenceContentId) {
        referenceContentId.value = targetLoadedId;
      }
      const referenceContentName = referenceContent.querySelector(`#jform_associations_${languageCode}_name`);
      if (referenceContentName) {
        referenceContentName.value = title;
      }

      // - For chosen association selectors (menus).
      const referenceContentDropdown = referenceContent.querySelector(`#jform_associations_${languageCode}`);
      if (referenceContentDropdown) {
        referenceContentDropdown.appendChild(createOption(targetLoadedId, title));
        referenceContentDropdown.value = targetLoadedId;
      }
    }

    // Update the target item associations tab.
    const referenceId = reference.getAttribute('data-id');
    const referenceLanguageCode = reference.getAttribute('data-language').replace(/-/, '_');
    const referenceTitle = reference.getAttribute('data-title-value');

    // - For modal association selectors.
    const targetContentId = content.querySelector(`#jform_associations_${referenceLanguageCode}_id`);
    if (targetContentId) {
      targetContentId.value = referenceId;
    }
    const targetContentName = content.querySelector(`#jform_associations_${referenceLanguageCode}_name`);
    if (targetContentName) {
      targetContentName.value = referenceTitle;
    }

    // - For chosen association selectors (menus).
    let chosenField = content.querySelector(`#jform_associations_${referenceLanguageCode}`);
    chosenField.appendChild(createOption(referenceId, referenceTitle));
    chosenField.value = referenceId;

    document.querySelectorAll('#jform_itemlanguage option').forEach((option) => {
      const parse = option.value.split(':');

      if (typeof parse[1] !== 'undefined' && parse[1] !== '0') {
        // - For modal association selectors.
        const langAssociation = parse[0].replace(/-/, '_');
        // eslint-disable-next-line prefer-destructuring
        content.querySelector(`#jform_associations_${langAssociation}_id`).value = parse[1];

        // - For chosen association selectors (menus).
        chosenField = content.querySelector(`#jform_associations_${langAssociation}`);
        chosenField.appendChild(createOption(parse[1], ''));
        // eslint-disable-next-line prefer-destructuring
        chosenField.value = parse[1];
      }
    });

    // Iframe load finished, hide Joomla loading layer.
    const spinner = document.querySelector('joomla-core-loader');
    if (spinner) {
      spinner.parentNode.removeChild(spinner);
    }
  }
});

// Save button actions, replacing the default Joomla.submitbutton() with custom function.
Joomla.submitbutton = (task) => {
  // Using close button, normal joomla submit.
  if (task === 'association.cancel') {
    Joomla.submitform(task);
  } else if (task === 'copy') {
    document.body.appendChild(document.createElement('joomla-core-loader'));

    const targetLang = document.getElementById('target-association').getAttribute('data-language');
    const referlangInput = window.frames['reference-association'].document.getElementById('jform_language');

    // Set target language, to get correct content language in the copy
    referlangInput.removeAttribute('disabled');
    referlangInput.value = targetLang;

    window.frames['reference-association'].Joomla.submitbutton(`${document.getElementById('adminForm').getAttribute('data-associatedview')}.save2copy`);
  } else if (task === 'undo-association') { // Undo association
    const referenceEl = document.getElementById('reference-association');
    const targetEl = document.getElementById('target-association');
    const referenceLang = referenceEl.getAttribute('data-language').replace(/-/, '_');
    const targetLang = targetEl.getAttribute('data-language').replace(/-/, '_');
    const reference = referenceEl.contentDocument.body || referenceEl.contentWindow.document.body;
    const target = targetEl.contentDocument.body || targetEl.contentWindow.document.body;

    // Remove it on the reference
    // - For modal association selectors.
    const referenceAssocId = reference.querySelector(`#jform_associations_${targetLang}_id`);
    if (referenceAssocId) {
      referenceAssocId.value = '';
    }
    const referenceAssocName = reference.querySelector(`#jform_associations_${targetLang}_name`);
    if (referenceAssocName) {
      referenceAssocName.value = '';
    }

    // - For chosen association selectors (menus).
    const referenceAssoc = reference.querySelector(`#jform_associations_${targetLang}`);
    if (referenceAssoc) {
      referenceAssoc.value = '';
    }

    // Remove it on the target
    document.querySelectorAll('#jform_itemlanguage option').forEach((option) => {
      let lang = option.value.split(':')[0];

      if (lang) {
        lang = lang.replace(/-/, '_');

        // - For modal association selectors.
        target.querySelector(`#jform_associations_${lang}_id`).value = '';
        // - For chosen association selectors (menus).
        target.querySelector(`#jform_associations_${lang}`).value = '';
      }
    });

    // Same as above but reference language is not in the selector
    // - For modal association selectors.
    const targetAssocId = target.querySelector(`#jform_associations_${referenceLang}_id`);
    if (targetAssocId) {
      targetAssocId.value = '';
    }
    const targetAssocName = target.querySelector(`#jform_associations_${referenceLang}_name`);
    if (targetAssocName) {
      targetAssocName.value = '';
    }

    // - For chosen association selectors (menus).
    const targetAssoc = target.querySelector(`#jform_associations_${referenceLang}`);
    if (targetAssoc) {
      targetAssoc.value = '';
    }

    // Reset switcher after removing association
    const currentLangSelect = document.getElementById('jform_itemlanguage');
    const currentSwitcher = currentLangSelect.value;
    const currentLang = targetLang.replace(/_/, '-');
    document.querySelector(`#jform_itemlanguage option[value="${currentSwitcher}"]`).value = `${currentLang}:0:add`;
    currentLangSelect.value = '';
    currentLangSelect.dispatchEvent(new CustomEvent('change', {
      bubbles: true,
      cancelable: true,
    }));

    // Save one of the items to confirm action
    Joomla.submitbutton('reference');
  } else {
    // Saving target or reference, send the save action to the target/reference iframe.
    // We need to re-enable the language field to save.
    const el = document.getElementById(`${task}-association`);
    const content = el.contentDocument.body || el.contentWindow.document.body;
    content.querySelector('#jform_language').removeAttribute('disabled');
    window.frames[`${task}-association`].Joomla.submitbutton(`${document.getElementById('adminForm').getAttribute('data-associatedview')}.apply`);
  }

  return false;
};

hideElements(['toolbar-target', 'toolbar-copy']);
