(function () {
  'use strict';

  /* eslint-disable */
  /* eslint-disable no-undef */
  tinymce.PluginManager.add('jimage', function (editor) {
    // Event that fires on dialog open
    editor.on('OpenWindow', function (e) {
      if(document.querySelector('.tox-dialog__title').innerText === 'Insert/Edit Image') {
        var dialogBody = document.querySelector('.tox-dialog__body');

        // Content gets generated again every time tab changes
        dialogBody.querySelectorAll('.tox-tab').forEach(function (tab) {
          tab.addEventListener('click', function (e) {
            // Insert content to advanced tabpanel
            if (e.target.innerText === 'Advanced' && !e.target.classList.contains('tox-dialog__body-nav-item--active')) {
              setTimeout(function () {
                var form = dialogBody.querySelector('.tox-form');
                var formGroup = '<div class="tox-form__group"><label class="tox-label">Simple control</label><input type="text" class="tox-textfield"></div>';
                form.insertAdjacentHTML('beforeend', formGroup);
              });
            }
          });
        });
      }
    });

    console.log(tinymce.Editor.getLang('Insert/edit image'));

    // Event that fires on dialog form submit
    editor.on('ExecCommand', function (e) {
      if (e.command === 'mceUpdateImage') {
        console.log(e.value);
        tinymce.activeEditor.execCommand('mceImage', false, 'test');
      }
    });
  });
}());
