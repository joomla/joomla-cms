(function () {
  'use strict';

  /* eslint-disable */
  /* eslint-disable no-undef */
  tinymce.PluginManager.add('jimage', function (editor) {

    /**
     * Variable to store image details form state
     *
     * @since  4.1.0
     */
    var imageDetails = {
      imgClass: {
        id: "jimage_imageclass",
        type: "text",
        label: Joomla.Text._('PLG_TINY_JIMAGE_IMAGE_CLASS'),
        value: "",
      },
      lazyLoading: {
        id: "jimage_lazyloading",
        type: "checkbox",
        label: Joomla.Text._('PLG_TINY_JIMAGE_LOAD_TYPE'),
        content: Joomla.Text._('PLG_TINY_JIMAGE_LAZY_LOAD'),
        value: false,
      },
      figClass: {
        id: "jimage_figureclass",
        type: "text",
        label: Joomla.Text._('PLG_TINY_JIMAGE_FIGURE_CLASS'),
        value: "",
      },
      figCaption: {
        id: "jimage_figurecaption",
        type: "text",
        label: Joomla.Text._('PLG_TINY_JIMAGE_FIGURE_CAPTION'),
        value: "",
      },
    };

    /**
     * Variable to store responsive sizes form state
     *
     * @since  4.1.0
     */
    var responsiveSizes = {
      setCustom: {
        id: "jimage_setcustom",
        type: "checkbox",
        label: Joomla.Text._("PLG_TINY_JIMAGE_RESPONSIVE_IMAGES"),
        content: Joomla.Text._("PLG_TINY_JIMAGE_RESPONSIVE_SIZES"),
        value: false,
      },
      sizes: [],
    };

    /**
     * JImage object to store methods and icons
     *
     * @since  4.1.0
     */
    var jimage = {
      icons: {
        unchecked: '<svg width="24" height="24"><path fill-rule="nonzero" d="M6 4h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6c0-1.1.9-2 2-2zm0 1a1 1 0 00-1 1v12c0 .6.4 1 1 1h12c.6 0 1-.4 1-1V6c0-.6-.4-1-1-1H6z"></path></svg>',
        checked: '<svg width="24" height="24"><path fill-rule="nonzero" d="M6 4h12a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6c0-1.1.9-2 2-2zm3.6 10.9L7 12.3a.7.7 0 00-1 1L9.6 17 18 8.6a.7.7 0 000-1 .7.7 0 00-1 0l-7.4 7.3z"></path></svg>',
        delete: '<svg viewBox="0 0 24 24" width="24px" height="24px"><path d="M 10 2 L 9 3 L 5 3 C 4.4 3 4 3.4 4 4 C 4 4.6 4.4 5 5 5 L 7 5 L 17 5 L 19 5 C 19.6 5 20 4.6 20 4 C 20 3.4 19.6 3 19 3 L 15 3 L 14 2 L 10 2 z M 5 7 L 5 20 C 5 21.1 5.9 22 7 22 L 17 22 C 18.1 22 19 21.1 19 20 L 19 7 L 5 7 z M 9 9 C 9.6 9 10 9.4 10 10 L 10 19 C 10 19.6 9.6 20 9 20 C 8.4 20 8 19.6 8 19 L 8 10 C 8 9.4 8.4 9 9 9 z M 15 9 C 15.6 9 16 9.4 16 10 L 16 19 C 16 19.6 15.6 20 15 20 C 14.4 20 14 19.6 14 19 L 14 10 C 14 9.4 14.4 9 15 9 z"/></svg>',
        add: '<svg viewBox="0 0 24 24" width="24px" height="24px"><path d="M12,2C6.477,2,2,6.477,2,12s4.477,10,10,10s10-4.477,10-10S17.523,2,12,2z M16,13h-3v3c0,0.552-0.448,1-1,1h0 c-0.552,0-1-0.448-1-1v-3H8c-0.552,0-1-0.448-1-1v0c0-0.552,0.448-1,1-1h3V8c0-0.552,0.448-1,1-1h0c0.552,0,1,0.448,1,1v3h3 c0.552,0,1,0.448,1,1v0C17,12.552,16.552,13,16,13z"/></svg>'
      },
      dialogBody: null,
      renderField: function (field) {
        if (field.type === "checkbox") {
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
      },
      renderButton: function (btn) {
        return `
          <button id="` + btn.id + `" title="` + btn.title + `" type="button" class="tox-button tox-button--naked tox-button--icon">
            <span class="tox-icon">` + jimage.icons[btn.icon] + `</span>
          </button>`;
      },
      renderFieldGroup: function (group, btn) {
        var fieldsHTML = "";
        group.forEach(function (field) {
          fieldsHTML += jimage.renderField(field);
        });

        return `
          <div class="jimage_field_group tox-form__controls-h-stack justify-content-between">`
            + fieldsHTML +
            `<div class="tox-form__group"><label class="tox-label">&nbsp;</label>` + jimage.renderButton(btn) + `</div>
          </div>`;
      },
      registerField: function (field, callback = null) {
        jimage.dialogBody.querySelector("#" + field.id).addEventListener("change", function (e) {
          field.value = field.type === "checkbox" ? e.target.checked : e.target.value;

          if (callback) callback();
        });
      },
      createSizeGroup: function (id, width, height) {
        return {
          width: {
            id: "jimage_sizes_width_" + id,
            type: "number",
            label: Joomla.Text._('PLG_TINY_JIMAGE_WIDTH'),
            value: width ?? "",
          },
          height: {
            id: "jimage_sizes_height" + id,
            type: "number",
            label: Joomla.Text._('PLG_TINY_JIMAGE_HEIGHT'),
            value: height ?? "",
          },
        };
      },
      handleDelete: function (id) {
        jimage.dialogBody.querySelector('#jimage_delete_' + id).addEventListener('click', function (e) {
          // Remove object from sizes array and delete from DOM
          responsiveSizes.sizes = responsiveSizes.sizes.filter(function (size, index) {
            return index !== id;
          });
          e.currentTarget.closest('.jimage_field_group').remove();

          jimage.updateImageAttr();
        });
      },
      updateImageAttr: function () {
        var image = editor.selection.getNode();

        // Edit or delete image data-jimage attribute
        if (responsiveSizes.setCustom.value && responsiveSizes.sizes.length > 0) {
          var sizes = [];

          responsiveSizes.sizes.forEach(function (size) {
            var width = parseInt(size.width.value);
            var height = parseInt(size.height.value);

            if (width > 0 && height > 0) {
              sizes.push(width + "x" + height);
            }
          });

          // Set sizes string as data attribute
          if(sizes.length > 0) image.setAttribute('data-jimage', sizes.join(","));
        } else {
          image.removeAttribute("data-jimage");
        }
      }
    };

    // Event that fires on dialog open
    editor.on('OpenWindow', function () {
      if (document.querySelector('.tox-dialog__title').innerText === tinymce.util.I18n.translate('Insert/Edit Image')) {
        jimage.dialogBody = document.querySelector('.tox-dialog__body');

        // Content gets generated again every time tab changes
        jimage.dialogBody.querySelectorAll('.tox-tab').forEach(function (tab) {
          tab.addEventListener('click', function (e) {
            // Insert content to advanced tabpanel
            if (e.target.innerText === tinymce.util.I18n.translate('Advanced') && e.target.getAttribute('aria-selected') === 'false') {
              setTimeout(function () {
                var image = editor.selection.getNode();

                // Set image detail initial values
                imageDetails.imgClass.value = image.className ?? '';
                imageDetails.lazyLoading.value = image.getAttribute('loading') === 'lazy';

                if (image.parentElement.nodeName.toLowerCase() === 'figure') {
                  imageDetails.figClass.value = image.parentElement.className ?? '';
                  imageDetails.figCaption.value = image.parentElement.querySelector('figcaption').innerText ?? '';
                }

                // Custom sizes initial default values
                responsiveSizes.sizes = [];
                responsiveSizes.setCustom.value = false;

                // Set custom sizes initial values if exist
                if (image.getAttribute('data-jimage')) {
                  responsiveSizes.setCustom.value = true;

                  // Create new objects with existing sizes
                  var sizes = image.getAttribute("data-jimage").split(',');
                  sizes.forEach(function (size, index) {
                    var dimensions = size.split("x");
                    responsiveSizes.sizes.push(jimage.createSizeGroup(index, dimensions[0], dimensions[1]));
                  });
                }

                // Render image detail fields
                var formHTML = '<div class="tox-form__grid tox-form__grid--2col">';
                Object.values(imageDetails).forEach(function (field) {
                  formHTML += jimage.renderField(field);
                });
                formHTML += '</div>';

                // Render responsive size fields
                formHTML += jimage.renderFieldGroup([responsiveSizes.setCustom], {
                  id: 'jimage_add', title: Joomla.Text._('PLG_TINY_JIMAGE_ADD_SIZE'), icon: 'add'
                });
                formHTML += '<div id="jimage_sizes_wrapper" style="display: ' + (responsiveSizes.setCustom.value ? 'block' : 'none') + ';">';
                responsiveSizes.sizes.forEach(function (size, index) {
                  formHTML += jimage.renderFieldGroup(Object.values(size), {
                    id: 'jimage_delete_' + index, title: Joomla.Text._('PLG_TINY_JIMAGE_DELETE_SIZE'), icon: 'delete'
                  });
                });
                formHTML += '</div>';

                jimage.dialogBody.querySelector('.tox-form').insertAdjacentHTML('beforeend', formHTML);

                var sizesWrapper = jimage.dialogBody.querySelector('#jimage_sizes_wrapper');
                var addBtn = jimage.dialogBody.querySelector('#jimage_add');

                // Update values of image detail controls on change
                Object.values(imageDetails).forEach(function (field) {
                  jimage.registerField(field);
                });

                // Update values of responsive size controls on change
                responsiveSizes.sizes.forEach(function (group, index) {
                  Object.values(group).forEach(function (field) {
                    jimage.registerField(field);
                  });

                  jimage.handleDelete(index);
                });

                // Show/hide elements depending on custom sizes config
                jimage.registerField(responsiveSizes.setCustom, function () {
                  var isCustom = responsiveSizes.setCustom.value;
                  sizesWrapper.style.display = isCustom ? 'block' : 'none';
                  addBtn.style.display = isCustom ? 'block' : 'none';
                });

                // Hide add button if custom size option is off
                if (!responsiveSizes.setCustom.value) {
                  addBtn.style.display = 'none';
                }

                // Handle insertion of responsive size controls
                addBtn.addEventListener('click', function () {
                  // Create new field object with new id
                  var id = responsiveSizes.sizes.length;
                  var newGroup = jimage.createSizeGroup(id);

                  // Append new object to the array and render field group
                  responsiveSizes.sizes.push(newGroup);
                  sizesWrapper.insertAdjacentHTML('beforeend', jimage.renderFieldGroup(Object.values(newGroup), {
                    id: 'jimage_delete_' + id, title: Joomla.Text._('PLG_TINY_JIMAGE_DELETE_SIZE'), icon: 'delete'
                  }));

                  // Track value of newly created field
                  Object.values(responsiveSizes.sizes[id]).forEach(function (field) {
                    jimage.registerField(field);
                  });

                  jimage.handleDelete(id);
                });
              });
            }
          });
        });
      }
    });

    // Event that fires on dialog form submit
    editor.on('ExecCommand', function (e) {
      if (e.command === 'mceUpdateImage' && jimage.dialogBody) {
        var image = e.target.contentDocument.body.querySelector(`img[src="` + e.value.src + `"]`);
        image.className = imageDetails.imgClass.value;

        // Add or remove loading attribute from image
        if (imageDetails.lazyLoading.value) {
          image.setAttribute('loading', 'lazy');
        } else {
          image.removeAttribute('loading');
        }

        if (imageDetails.figCaption.value) {
          // Check if figure element already exists
          if (image.parentElement.nodeName.toLowerCase() === 'figure') {
            image.parentElement.querySelector('figcaption').innerText = imageDetails.figCaption.value;
            image.parentElement.className = imageDetails.figClass.value;
          } else {
            // Create figure and figcaption elements
            var figure = document.createElement('figure');
            var figCaption = document.createElement('figcaption');
            figure.appendChild(figCaption);

            figure.className = imageDetails.figClass.value;
            figCaption.innerText = imageDetails.figCaption.value;

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

        jimage.updateImageAttr();
      }
    });
  });
}());
