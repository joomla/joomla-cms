(function () {
  'use strict';

  /* eslint-disable */
  /* eslint-disable no-undef */
  tinymce.PluginManager.add('jimage', function (editor) {

    /**
     * Variable to store form state
     *
     * @since  4.1.0
     */
    var formState = {
      imgClass: {
        id: "jimage_imageclass",
        type: "text",
        label: "Image class",
        value: "",
      },
      lazyLoading: {
        id: "jimage_lazyloading",
        type: "checkbox",
        label: "Lazy loading",
        content: "Lazy load",
        value: false,
      },
      figClass: {
        id: "jimage_figureclass",
        type: "text",
        label: "Figure class",
        value: "",
      },
      figCaption: {
        id: "jimage_figurecaption",
        type: "text",
        label: "Figure caption",
        value: "",
      },
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
      renderField: function (field) {
        if (field.type === 'checkbox') {
          return `
            <div class="tox-form__group">
              <label class="tox-label">` + field.label + `</label>
              <label for="` + field.id + `" class="tox-checkbox">
                <input type="checkbox" id="` + field.id + `" ` + (field.value ? 'checked' : '') + ` class="tox-checkbox__input">
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
            <input type="` + field.type + `" id="` + field.id + `" value="` + field.value + `" class="tox-textfield">
          </div>`;
      }
    };

    // Event that fires on dialog open
    editor.on('OpenWindow', function () {
      if (document.querySelector('.tox-dialog__title').innerText === tinymce.util.I18n.translate('Insert/Edit Image')) {
        var dialogBody = document.querySelector('.tox-dialog__body');

        // Content gets generated again every time tab changes
        dialogBody.querySelectorAll('.tox-tab').forEach(function (tab) {
          tab.addEventListener('click', function (e) {
            // Insert content to advanced tabpanel
            if (e.target.innerText === tinymce.util.I18n.translate('Advanced') && e.target.getAttribute('aria-selected') === 'false') {
              setTimeout(function () {
                // Set initial values
                var image = editor.selection.getNode();
                formState.imgClass.value = image.className;
                formState.lazyLoading.value = image.getAttribute('loading') === 'lazy';

                if (image.parentElement.nodeName.toLowerCase() === 'figure') {
                  formState.figClass.value = image.parentElement.className;
                  formState.figCaption.value = image.parentElement.querySelector('figcaption').innerText;
                }

                // Render form fields dynamically
                var formHTML = '<div class="tox-form__grid tox-form__grid--2col">';
                Object.keys(formState).forEach(function (key) {
                  formHTML += jimage.renderField(formState[key]);
                });
                formHTML += '</div>';
                dialogBody.querySelector('.tox-form').insertAdjacentHTML('beforeend', formHTML);

                // Update values of form controls on change
                Object.keys(formState).forEach(function (key) {
                  dialogBody.querySelector('#' + formState[key].id).addEventListener('change', function (e) {
                    formState[key].value = formState[key].type === 'checkbox' ? e.target.checked : e.target.value;
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
      if (e.command === 'mceUpdateImage') {
        var image = e.target.contentDocument.body.querySelector(`img[src="` + e.value.src + `"]`);
        image.className = formState.imgClass.value;

        // Add or remove loading attribute from image
        if (formState.lazyLoading.value) {
          image.setAttribute('loading', 'lazy');
        } else {
          image.removeAttribute('loading');
        }

        if (formState.figCaption.value) {
          // Check if figure element already exists
          if (image.parentElement.nodeName.toLowerCase() === 'figure') {
            image.parentElement.querySelector('figcaption').innerText = formState.figCaption.value;
            image.parentElement.className = formState.figClass.value;
          } else {
            // Create figure and figcaption elements
            var figure = document.createElement('figure');
            var figCaption = document.createElement('figcaption');
            figure.appendChild(figCaption);

            figure.className = formState.figClass.value;
            figCaption.innerText = formState.figCaption.value;

            // Append image to the figure element
            image.parentElement.appendChild(figure);
            figure.insertBefore(image, figCaption);
          }
        } else {
          // Delete figure element if exists
          if (image.parentElement.nodeName.toLowerCase() === 'figure') {
            var figure = image.parentElement;
            figure.parentNode.insertBefore(image, figure);
            figure.parentNode.removeChild(figure);
          }
        }
      }
    });
  });
}());
