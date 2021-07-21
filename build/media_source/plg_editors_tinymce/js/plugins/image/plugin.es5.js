(function () {
  'use strict';

  /* eslint-disable */
  /* eslint-disable no-undef */
  tinymce.PluginManager.add('jimage', function (editor) {

    /**
     * Variable to store Insert/edit image dialog body
     *
     * @since  4.1.0
     */
    var dialogBody = null;

    /**
     * Variable to store form state
     *
     * @since  4.1.0
     */
    var formState = {
      imgClass: { id: 'jimage_imageclass', type: 'text', label: 'Image class', value: '' },
      lazyLoading: { id: 'jimage_lazyloading', type: 'checkbox', label: 'Lazy loading', content: 'Lazy load', value: '' },
      figClass: { id: 'jimage_figureclass', type: 'text', label: 'Figure class', value: '' },
      figCaption: { id: 'jimage_figurecaption', type: 'text', label: 'Figure caption', value: '' }
    };

    /**
     * JImage object to store methods and icons
     *
     * @since  4.1.0
     */
    var jimage = {
      icons: {
        unchecked: '<svg width="24" height="24"><path fill-rule="nonzero" d="M6 4h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6c0-1.1.9-2 2-2zm0 1a1 1 0 00-1 1v12c0 .6.4 1 1 1h12c.6 0 1-.4 1-1V6c0-.6-.4-1-1-1H6z"></path></svg>',
        checked: '<svg width="24" height="24"><path fill-rule="nonzero" d="M6 4h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6c0-1.1.9-2 2-2zm3.6 10.9L7 12.3a.7.7 0 00-1 1L9.6 17 18 8.6a.7.7 0 000-1 .7.7 0 00-1 0l-7.4 7.3z"></path></svg>'
      },
      getIcon: function (name) {
        return tinymce.icons[name] || name;
      },
      renderField: function (field) {
        // Return a form field with specified parameters
        if (field.type === 'checkbox') {
          return `
            <div class="tox-form__group">
              <label class="tox-label">` + field.label + `</label>
              <label for="` + field.id + `" class="tox-checkbox">
                <input type="checkbox" id="` + field.id + `" class="tox-checkbox__input">
                <div class="tox-checkbox__icons">
                  <span class="tox-icon tox-checkbox-icon__checked">` + jimage.icons.checked + `</span>
                  <span class="tox-icon tox-checkbox-icon__unchecked">` + jimage.icons.unchecked + `</span>
                </div>
                <span unselectable="on" class="tox-checkbox__label" style="user-select: none;">` + field.content + `</span>
              </label>
            </div>`;
        }

        return `
          <div class="tox-form__group">
            <label for="` + field.id + `" class="tox-label">` + field.label + `</label>
            <input type="` + field.type + `" id="` + field.id + `" class="tox-textfield">
          </div>`;
      },
    };

    // Event that fires on dialog open
    editor.on('OpenWindow', function () {
      if (document.querySelector('.tox-dialog__title').innerText === tinymce.util.I18n.translate('Insert/Edit Image')) {
        dialogBody = document.querySelector('.tox-dialog__body');

        // Content gets generated again every time tab changes
        dialogBody.querySelectorAll('.tox-tab').forEach(function (tab) {
          tab.addEventListener('click', function (e) {
            // Insert content to advanced tabpanel
            if (e.target.innerText === tinymce.util.I18n.translate('Advanced') && e.target.getAttribute('aria-selected') === 'false') {
              setTimeout(function () {
                var formTemplate = '<div class="tox-form__grid tox-form__grid--2col">';

                // Render form fields dynamically
                Object.keys(formState).forEach(function (key) {
                  formTemplate += jimage.renderField(formState[key]);

                  // Add event listener to fields to save their values
                  dialogBody.querySelector('#' + formState[key].id).addEventListener('focusout', function () {
                    formState[key].value = e.target.value;
                    console.log(formState);
                  });
                });
                formTemplate += '</div>';

                dialogBody.querySelector('.tox-form').insertAdjacentHTML('beforeend', formTemplate);

                // Add event listener to fields to save their values
                Object.keys(formState).forEach(function (key) {
                  dialogBody.querySelector('#' + formState[key].id).addEventListener('focusout', function () {
                    formState[key].value = e.target.value;
                  });
                });
              });
            }
          });
        });
      }
    });

    // Event that fires on dialog form submit
    editor.on('ExecCommand', function (e) {
      if (e.command === 'mceUpdateImage' && dialogBody) {
        var image = e.target.contentDocument.body.querySelector(`img[src="` + e.value.src + `"]`);
        var form  = dialogBody.querySelector('.tox-form');

        if (form.querySelector("#jimage_imageclass")) {
          image.className = form.querySelector("#jimage_imageclass").value;
        }
        if (form.querySelector("#jimage_lazyloading")) {
          console.log(form.querySelector("#jimage_lazyloading").checked);
        }

        console.log(image);
      }
    });
  });
}());
