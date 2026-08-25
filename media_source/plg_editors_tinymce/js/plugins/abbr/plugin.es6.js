tinymce.PluginManager.add('abbr', function (editor) {
  const darkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)');
  const errorColor = (darkMode && darkMode.matches) ? '#ff8c8c' : '#c52827';
  const secondaryColor = (darkMode && darkMode.matches) ? '#d6dade' : '#515861';
  const borderColor = (darkMode && darkMode.matches) ? '#161f29' : '#eee';
  let errorSubmit = false;
  function getCurrentAbbr() {
    const node = editor.selection.getNode();
    return editor.dom.getParent(node, 'abbr');
  }
  function getCurrentDesc() {
    const abbr = getCurrentAbbr();
    if (abbr) {
      let descNext = abbr.nextElementSibling;
      let descPrevious = abbr.previousElementSibling;
      if (descNext && descNext.classList.contains('abbr-definition')) {
        return descNext;
      } else if (descPrevious && descPrevious.classList.contains('abbr-definition')) {
        return descPrevious;
      }
    }
    return false;
  }
  function inputState(name, state) {
    if (name) {
      const element = document.querySelector(`input[data-mce-name="${name}"]`);
      const label = document.querySelector(`label[for="${element.getAttribute('id')}"]`);
      if (state == 'invalid') {
        element.classList.add('is-invalid');
        element.style.border = `1px solid ${errorColor}`;
        label.style.color = errorColor; // dark
      } else if (state == 'valid') {
        element.classList.remove('is-invalid');
      } else if (state == 'hide') {
        element.style.display = 'none';
      } else if (state == 'show') {
        element.style.display = 'block';
      }
    }
  }
  function setAlertMessage(message, level, icon) {
    return {
      type: 'alertbanner',
      level: level ? level : 'info',
      text: `<p>${message}</p>`,
      icon: icon ? icon : 'info',
    };
  }
  function openDialog() {
    const abbr = getCurrentAbbr();
    const desc = getCurrentDesc();
    const getDesc = desc ? desc.innerText.trim().replace(/[()]/g, '') : (abbr ? abbr.getAttribute('title') : '');
    const abbrExpansion = desc ? true : false;
    const abbrMarkup = abbr ? (abbr.getAttribute('title') || abbrExpansion ? false : true) : false;
    const abbrStandard = abbr ? (abbr.getAttribute('title') ? true : false) : false;
    const expansionMessage = '&lt;abbr&gt;JSON&lt;/abbr&gt; (JavaScript Object Notation)';
    const markupMessage = '&lt;abbr&gt;HTML&lt;/abbr&gt;';
    const standardMessage = '&lt;abbr title="Cascading Style Sheets"&gt;CSS&lt;/abbr&gt;';
    const titleAttributeNote = `<div style="margin-top: 24px; padding: 8px 0; font-size: 14px; color: ${secondaryColor}; border-top: 1px solid ${borderColor}; border-bottom: 1px solid ${borderColor};"><p>Note: The use of the 'title' attribute to provide the expansion for the &lt;abbr&gt; (abbreviation) HTML element is inconsistent across browsers and assistive technologies.</p><p>Recommendation: Provide a description of the abbreviation/acronym in plain text on first use.</p></div>`;
    let initialMessage = abbrExpansion ? expansionMessage : (abbrStandard ? standardMessage : markupMessage);
    const initialInsertion = abbrExpansion ? 'expansion' : (abbrStandard ? 'standard' : 'markup');
    let alertMessage = (message, level, icon) => ({
      type: 'alertbanner',
      level: level ? level : 'info',
      text: `<p>${message}</p>`,
      icon: icon ? icon : 'info',
    });
    const abbreviationInput = {
      type: 'input',
      name: 'abbreviation',
      label: Joomla.Text._('Abbreviation'),
      enabled: true,
    };
    const insertionListbox = {
      type: 'listbox',
      name: 'insertion',
      label: 'Insertion Method',
      items: [
        { text: Joomla.Text._('No description (HTML markup only)'), value: 'markup' },
        { text: Joomla.Text._('Description as plain text (recommended on first use)'), value: 'expansion' },
        { text: Joomla.Text._('Description as title attribute'), value: 'standard'}
      ],
    };
    const definitionInput = (enabled) => ({
      type: 'input',
      name: 'definition',
      label: (getDesc || enabled) ? Joomla.Text._('PLG_TINY_ABBREVIATION_DESCRIPTION_LABEL') : Joomla.Text._('To mark up an abbreviation without providing a description.'),
      enabled: (getDesc || enabled) ? true : false,
    });
    const alertBanner = {
      type: 'alertbanner',
      level: 'info',
      text: `<p>${initialMessage}</p>`,
      icon: 'info',
    };
    const htmlPanel = {
      type: 'htmlpanel',
      html: (initialInsertion == 'standard') ? titleAttributeNote : '',
    };
    const markupItems = [abbreviationInput, insertionListbox, definitionInput(false), alertBanner];
    const expansionItems = [abbreviationInput, insertionListbox, definitionInput(true), alertBanner];
    const standardItems = [abbreviationInput, insertionListbox, definitionInput(true), alertBanner, htmlPanel];
    let items = markupItems;
    const selectedText = abbr ? abbr.innerText : editor.selection.getContent({
      format: 'text',
    });
    const errorMessages = (data) => {
      const errors = [];
      let alertMessages = '';
      if (!data.abbreviation.trim()) {
        errors.push(Joomla.Text._('Please enter text for the abbreviation.'));
      }
      if (!data.definition.trim() && data.insertion !== 'markup') {
        errors.push(Joomla.Text._('PLG_TINY_ABBREVIATION_WARNING_NO_DESCRIPTION'));
      }
      if (errors.length) {
        for (let i = 0; i < errors.length; i++) {
          alertMessages = alertMessages+errors[i]+'<br>';
        }
        return alertMessages;
      }
      return false;
    }
    const checkErrors = (api, data) => {
      const errorsFound = errorMessages(data);
      if (errorsFound) {
        for (let key in items) {
          let type = items[key]['type'];
          if (type === 'alertbanner') {
            items[key] = setAlertMessage(errorsFound, 'error', 'warning');
          }
        }
        const setInitialData = {
          abbreviation: data.abbreviation,
          definition: data.definition,
          insertion: data.insertion,
        };
        api.redial(dialogAbbr(setInitialData));
        if (data.insertion == 'markup') {
          inputState('definition', 'hide');
        } else {
          inputState('definition', 'show');
        }
        if (!data.abbreviation.trim()) {
          inputState('abbreviation', 'invalid');
        }
        if (!data.definition.trim() && data.insertion !== 'markup') {
          inputState('definition', 'invalid');
        }
        return true;
      }
      return false;
    };
    const getInitialData = {
      abbreviation: selectedText,
      insertion: initialInsertion,
      definition: getDesc ? getDesc : '',
    };
    const dialogAbbr = (setInitialData) => ({
      title: abbr ? Joomla.Text._('PLG_TINY_ABBREVIATION_EDIT') : Joomla.Text._('PLG_TINY_ABBREVIATION_INSERT'),
      initialData: setInitialData ? setInitialData : getInitialData,
      body: {
        type: 'panel',
        items: items,
      },
      onChange: (api, details) => {
        const data = api.getData();
        let getErrors = errorMessages(data);
        if (details.name === 'insertion' || !getErrors) {
          errorSubmit = false;
        }
        let setInitialData = '';
        if (data.insertion == 'markup') {
          api.setData({insertion: 'markup'});
          api.setEnabled('definition', false);
          items = markupItems;
          for (let key in items) {
            let type = items[key]['type'];
            if (type === 'alertbanner' && (!getErrors || details.name == 'insertion')) {
              items[key] = setAlertMessage(markupMessage);
            }
          }
        } else if (data.insertion == 'expansion') {
          api.setData({insertion: 'expansion'});
          api.setEnabled('definition', true);
          items = expansionItems;
          for (let key in items) {
            let type = items[key]['type'];
            if (type === 'alertbanner' && (!getErrors || details.name == 'insertion')) {
              items[key] = setAlertMessage(expansionMessage);
            }
          }
          } else if (data.insertion == 'standard') {
          api.setData({insertion: 'standard'});
          api.setEnabled('definition', true);
          items = standardItems;
          for (let key in items) {
            let type = items[key]['type'];
            if (type === 'alertbanner' && (!getErrors || details.name == 'insertion')) {
              items[key] = setAlertMessage(standardMessage);
            }
            if (type === 'htmlpanel') {
              items[key] = {
                type: 'htmlpanel',
                html: titleAttributeNote,
              };
            }
          }
        }
        if (errorSubmit && getErrors) {
          for (let key in items) {
            let type = items[key]['type'];
            if (type === 'alertbanner') {
              let alertItem = items[key] = setAlertMessage(getErrors, 'error', 'warning');
            }
          }
        }
        setInitialData = {
          abbreviation: data.abbreviation,
          insertion: data.insertion,
          definition: data.definition,
        };
        api.redial(dialogAbbr(setInitialData));
        if (data.insertion == 'markup') {
          inputState('definition', 'hide');
        } else {
          inputState('definition', 'show');
        }
      },
      buttons: [{
        type: 'cancel',
        text: 'Cancel',
      }, {
        type: 'submit',
        text: abbr ? 'Update' : 'Insert',
        primary: true,
      }],
      onSubmit(api) {
        const data = api.getData();
        let errors = checkErrors(api, data);
        if (errors) {
          errorSubmit = true;
          return;
        }
        errorSubmit = false;
        const dir = (tinymce.util.I18n.isRtl()) ? ' dir="ltr"' : ' dir="rtl"';
        if (abbr) {
          if (data.insertion == 'markup') {
            if (desc) {
              editor.dom.replace(editor.dom.createFragment(''), desc);
            }
            const content = `<abbr>${data.abbreviation}</abbr>`;
            editor.dom.replace(editor.dom.createFragment(content), abbr);
          } else if (data.insertion == 'expansion') {
            if (desc) {
              editor.dom.replace(editor.dom.createFragment(''), desc);
            }
            const content = `<abbr${dir}>${data.abbreviation}</abbr> <span${dir} class="abbr-definition">(${data.definition})</span>`;
            editor.dom.replace(editor.dom.createFragment(content), abbr);
          } else if (data.insertion == 'standard') {
            if (desc) {
              editor.dom.replace(editor.dom.createFragment(''), desc);
            }
            const content = `<abbr title="${data.definition}">${data.abbreviation}</abbr>`;
            editor.dom.replace(editor.dom.createFragment(content), abbr);
          }
        } else {
          if (data.insertion == 'markup') {
            editor.insertContent(`<abbr>${editor.dom.encode(data.abbreviation)}</abbr>`);
          } else if (data.insertion == 'expansion') {
            editor.insertContent(`<abbr${dir}>${editor.dom.encode(data.abbreviation)}</abbr> <span${dir} class="abbr-definition">(${editor.dom.encode(data.definition)})</span>`);
          } else if (data.insertion == 'standard') {
            editor.insertContent(`<abbr title="${editor.dom.encode(data.definition)}">${editor.dom.encode(data.abbreviation)}</abbr>`);
          }
        }
        api.close();
      }
    });
    editor.windowManager.open(
      dialogAbbr(getInitialData)
    );
    if (getDesc) {
      inputState('definition', 'show');
    } else {
      inputState('definition', 'hide');
    }
  }
  function removeAbbr() {
    const abbr = getCurrentAbbr();
    if (!abbr) {
      editor.notificationManager.open({
        text: Joomla.Text._('PLG_TINY_ABBREVIATION_WARNING_REMOVE'),
        type: 'warning',
      });
      return;
    }
    // Remove inline definition element
    const desc = getCurrentDesc();
    if (desc) {
      desc.remove();
    }
    // Replace <abbr> with its inner content
    const content = abbr.innerHTML;
    editor.dom.replace(editor.dom.createFragment(content), abbr);
  }


  /* icons */
  editor.ui.registry.addIcon('abbr', '<svg width="24" height="24"><path d="M20.666.82c0-.456-.391-.82-.875-.82-.503 0-.913.364-.913.82v2.478h-2.495a.845.845 0 0 0-.857.838c0 .474.391.82.857.82h2.495v2.478c0 .474.41.838.894.838.503 0 .894-.364.894-.838V4.956h2.496a.826.826 0 0 0 .838-.82.84.84 0 0 0-.838-.838h-2.496zM10.6 19.45c.14.429.539.742 1.018.742.599 0 1.078-.469 1.078-1.035 0-.176-.04-.313-.1-.469L7.845 6.091c-.22-.605-.559-1.094-1.377-1.094-.719 0-1.118.43-1.378 1.094L.08 18.825a1 1 0 0 0-.08.41.97.97 0 0 0 .978.957.97.97 0 0 0 .918-.625l1.198-3.262h6.408zM6.388 7.4l2.515 7.187h-5.17zm7.685 11.887c0 .495.399.885.905.885.519 0 .918-.39.918-.885v-.195c.439.651 1.092 1.08 2.076 1.08 1.903 0 2.941-1.093 2.941-3.242v-.976c0-2.005-1.011-3.242-2.941-3.242-.971 0-1.637.39-2.076 1.002v-2.851a.9.9 0 0 0-.918-.898.9.9 0 0 0-.905.898zm3.407-5.156c.958 0 1.544.599 1.544 1.758v1.106c0 1.159-.573 1.771-1.517 1.771-.945 0-1.611-.781-1.611-1.771v-1.198c.027-1.028.746-1.666 1.584-1.666M.786 22.428a.786.786 0 1 1 0 1.572.786.786 0 0 1 0-1.572m3.222 0a.787.787 0 1 1-.002 1.574.787.787 0 0 1 .002-1.574m3.221 0a.786.786 0 1 1 0 1.572.786.786 0 0 1 0-1.572m3.221 0a.786.786 0 1 1 0 1.572.786.786 0 0 1 0-1.572m3.222 0a.786.786 0 1 1 0 1.572.786.786 0 0 1 0-1.572m3.221 0a.786.786 0 1 1 0 1.572.786.786 0 0 1 0-1.572m3.221 0a.787.787 0 1 1 0 1.574.787.787 0 0 1 0-1.574" style="fill-rule:nonzero"/></svg>');
  editor.ui.registry.addIcon('abbr_remove', '<svg width="24" height="24"><path d="M10.6 19.45c.14.429.539.742 1.018.742.599 0 1.078-.469 1.078-1.035 0-.176-.04-.313-.1-.469L7.845 6.091c-.22-.605-.559-1.094-1.377-1.094-.719 0-1.118.43-1.378 1.094L.08 18.825a1 1 0 0 0-.08.41.97.97 0 0 0 .978.957.97.97 0 0 0 .918-.625l1.198-3.262h6.408zM6.388 7.4l2.515 7.187h-5.17zm7.685 11.887c0 .495.399.885.905.885.519 0 .918-.39.918-.885v-.195c.439.651 1.092 1.08 2.076 1.08 1.903 0 2.941-1.093 2.941-3.242v-.976c0-2.005-1.011-3.242-2.941-3.242-.971 0-1.637.39-2.076 1.002v-2.851a.9.9 0 0 0-.918-.898.9.9 0 0 0-.905.898zm3.407-5.156c.958 0 1.544.599 1.544 1.758v1.106c0 1.159-.573 1.771-1.517 1.771-.945 0-1.611-.781-1.611-1.771v-1.198c.027-1.028.746-1.666 1.584-1.666M24 5.017q0 2.08-1.468 3.548t-3.549 1.468-3.548-1.468-1.468-3.548q0-2.082 1.468-3.549T18.983 0q2.082 0 3.549 1.468T24 5.017m-3.228 2.917-4.703-4.703a3.4 3.4 0 0 0-.509 1.786q0 1.417 1.004 2.419a3.3 3.3 0 0 0 2.413 1.004q.97 0 1.795-.506m-3.574-5.832 4.701 4.701a3.4 3.4 0 0 0 .507-1.793 3.3 3.3 0 0 0-1-2.416 3.3 3.3 0 0 0-2.416-1q-.95 0-1.792.508" style="fill-rule:nonzero"/></svg>');

  /* Buttons */
  editor.ui.registry.addToggleButton('abbr', {
    title: 'Abbr',
    icon: 'abbr',
    tooltip: Joomla.Text._('PLG_TINY_TOOLBAR_BUTTON_ABBREVIATION'),
    onAction: openDialog,
    onSetup: (api) => {
        const editorEventCallback = (eventApi) => {
          api.setActive(eventApi.element.nodeName.toLowerCase() === 'abbr');
        };
        editor.on('NodeChange', editorEventCallback);
        return () => editor.off('NodeChange', editorEventCallback);
      },
  });
  editor.ui.registry.addButton('abbr_remove', {
    title: 'Remove Abbr',
    icon: 'abbr_remove',
    tooltip: Joomla.Text._('PLG_TINY_TOOLBAR_BUTTON_REMOVE_ABBREVIATION'),
    enabled: false,
    onAction: removeAbbr,
    onSetup: (api) => {
        const editorEventCallback = (eventApi) => {
          api.setEnabled(eventApi.element.nodeName.toLowerCase() === 'abbr');
        };
        editor.on('NodeChange', editorEventCallback);
        return () => editor.off('NodeChange', editorEventCallback);
      },
  });
  return {
    getMetadata() {
      return {
        name: 'Abbreviation Plugin (Joomla)',
        url: 'https://www.joomla.org',
      };
    }
  };
});
