/**
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// @TODO remove jQuery dependency
jQuery(document).ready(function($) {
  $('#toolbar-target').hide();
  $('#toolbar-copy').hide();

  // Save button actions, replacing the default Joomla.submitbutton() with custom function.
  Joomla.submitbutton = function(task) {
    // Using close button, normal joomla submit.
    if (task === 'association.cancel') {
      Joomla.submitform(task);
    } else if(task === 'copy') {
      document.body.appendChild(document.createElement('joomla-core-loader'));

      var targetLang = document.getElementById('target-association').getAttribute('data-language'),
        referlangInput = window.frames['reference-association'].document.getElementById('jform_language');

      // Set target language, to get correct content language in the copy
      referlangInput.removeAttribute('disabled');
      referlangInput.value = targetLang;

      window.frames['reference-association'].Joomla.submitbutton(document.getElementById('adminForm').getAttribute('data-associatedview') + '.save2copy');
    } else if (task === 'undo-association') { // Undo association
      var reference     = document.getElementById('reference-association');
      var target        = document.getElementById('target-association');
      var referenceId   = reference.getAttribute('data-id');
      var referenceLang = reference.getAttribute('data-language').replace(/-/,'_');
      var targetId      = target.getAttribute('data-id');
      var targetLang    = target.getAttribute('data-language').replace(/-/,'_');
      reference         = $(reference).contents();
      target            = $(target).contents();

      // Remove it on the reference
      // - For modal association selectors.
      reference.find('#jform_associations_' + targetLang + '_id').val('');
      reference.find('#jform_associations_' + targetLang + '_name').val('');

      // - For chosen association selectors (menus).
      reference.find('#jform_associations_' + targetLang).val('');

      var lang = '';

      // Remove it on the target
      $('#jform_itemlanguage option').each(function()
      {
        lang = $(this).val().split('|')[0];

        if (typeof lang !== 'undefined') {
          lang = lang.replace(/-/,'_');
          // - For modal association selectors.
          target.find('#jform_associations_' + lang + '_id').val('');
          // - For chosen association selectors (menus).
          target.find('#jform_associations_' + lang).val('');
        }
      });

      // Same as above but reference language is not in the selector
      // - For modal association selectors.
      target.find('#jform_associations_' + referenceLang + '_id').val('');
      target.find('#jform_associations_' + referenceLang + '_name').val('');

      // - For chosen association selectors (menus).
      target.find('#jform_associations_' + referenceLang).val('');

      // Reset switcher after removing association
      var currentLangSelect = $('#jform_itemlanguage');
      var currentSwitcher = currentLangSelect.val();
      var currentLang = targetLang.replace(/_/,'-');
      $('#jform_itemlanguage option[value=\"' + currentSwitcher + '\"]').val(currentLang + ':0:add');
      currentLangSelect.val('');
      currentLangSelect[0].dispatchEvent(new CustomEvent('change', {
        bubbles: true,
        cancelable: true,
      }));

      // Save one of the items to confirm action
      Joomla.submitbutton('reference');
    } else {
      // Saving target or reference, send the save action to the target/reference iframe.
      // We need to re-enable the language field to save.
      $('#' + task + '-association').contents().find('#jform_language').attr('disabled', false);
      window.frames[task + '-association'].Joomla.submitbutton(document.getElementById('adminForm').getAttribute('data-associatedview') + '.apply');
    }

    return false;
  };

  // Attach behaviour to toggle button.
  $(document).on('click', '#toogle-left-panel', function() {
    var referenceHide = this.getAttribute('data-hide-reference');
    var referenceShow = this.getAttribute('data-show-reference');

    if ($(this).text() === referenceHide) {
      $(this).text(referenceShow);
    } else {
      $(this).text(referenceHide);
    }

    $('#left-panel').toggle();
    $('#right-panel').toggleClass('full-width');
  });

  // Attach behaviour to language selector change event.
  $(document).on('change', '#jform_itemlanguage', function() {
    var target   = document.getElementById('target-association');
    var selected = $(this).val();

    // Populate the data attributes and load the the edit page in target frame.
    if (selected !== '' && typeof selected !== 'undefined') {
      target.setAttribute('data-action', selected.split(':')[2]);
      target.setAttribute('data-id', selected.split(':')[1]);
      target.setAttribute('data-language', selected.split(':')[0]);

      // Iframe load start, show Joomla loading layer.
      document.body.appendChild(document.createElement('joomla-core-loader'));

      // Load the target frame.
      target.src = target.getAttribute('data-editurl') + '&task=' + target.getAttribute('data-item') + '.' + target.getAttribute('data-action') + '&id=' + target.getAttribute('data-id');
    } else {
      // Reset the data attributes and no item to load.
      $('#toolbar-target').hide();
      $('#toolbar-copy').hide();
      $('#select-change').addClass("hidden");
      $('#remove-assoc').addClass("hidden");

      target.setAttribute('data-action', '');
      target.setAttribute('data-id', '0');
      target.setAttribute('data-language', '');
      target.src = '';
    }
  });

  // Attach behaviour to reference frame load event.
  $('#reference-association').on('load', function() {
    // Waiting until the reference has loaded before loading the target to avoid race conditions
    var targetURL = Joomla.getOptions('targetSrc', false);

    if (targetURL)
    {
      targetURL = targetURL.split('&amp;').join('&');
      document.getElementById('target-association').setAttribute('src', targetURL);
      Joomla.loadOptions({'targetSrc': false});
      return;
    }

    // Load Target Pane AFTER reference pane has loaded to prevent session conflict with checkout
    document.getElementById('target-association').setAttribute('src', document.getElementById('target-association').getAttribute('src'));

    // If copy button used
    if ($(this).contents().find('#jform_id').val() !== this.getAttribute('data-id')) {
      var target = document.getElementById('target-association');
      target.src = target.getAttribute('data-editurl') + '&task=' + target.getAttribute('data-item') + '.edit' + '&id=' + $(this).contents().find('#jform_id').val();
      this.src   = this.getAttribute('data-editurl') + '&task=' + this.getAttribute('data-item') + '.edit' + '&id=' + this.getAttribute('data-id');
    }

    var reference = $(this).contents();

    // Disable language field.
    reference.find('#jform_language').attr('disabled', '');

    // Remove modal buttons on the reference
    reference.find('#associations').find('.btn').remove();

    var parse = '';

    $('#jform_itemlanguage option').each(function() {
      parse = $(this).val().split(':');

      if (typeof parse[0] !== 'undefined') {
        // - For modal association selectors.
        var langAssociation = parse[0].replace(/-/,'_');

        if (reference.find('#jform_associations_' + langAssociation + '_id').val() == '') {
          reference.find('#jform_associations_' + langAssociation + '_name')
            .val(document.getElementById('reference-association').getAttribute('data-no-assoc'));
        }
      }
    });

    // Iframe load finished, hide Joomla loading layer.
    var spinner = document.querySelector('joomla-core-loader');
    if (spinner) {
      spinner.parentNode.removeChild(spinner);
    }
  });

  // Attach behaviour to target frame load event.
  $('#target-association').on('load', function() {
    // We need to check if we are not loading a blank iframe.
    if (this.getAttribute('src') != '') {
      $('#toolbar-target').show();
      $('#toolbar-copy').show();
      $('#select-change').removeClass("hidden");

      var targetLanguage = this.getAttribute('data-language');
      var targetId = this.getAttribute('data-id');
      var targetLoadedId = $(this).contents().find('#jform_id').val() || '0';

      // Remove modal buttons on the target
      $(this).contents().find('a[href=\"#associations\"]').parent().find('.btn').remove();
      $(this).contents().find('#associations').find('.btn').remove();

      // Always show General tab first if associations tab is selected on the reference
      if ($(this).contents().find('#associations').hasClass('active')) {
        $(this).contents().find('a[href=\"#associations\"]').parent().removeClass('active');
        $(this).contents().find('#associations').removeClass('active');

        $(this).contents().find('.nav-tabs').find('li').first().addClass('active');
        $(this).contents().find('.tab-content').find('.tab-pane').first().addClass('active');
      }

      // Update language field with the selected language and them disable it.
      $(this).contents().find('#jform_language').val(targetLanguage).attr('disabled', '');

      // If we are creating a new association (before save) we need to add the new association.
      if (targetLoadedId == '0')
      {
        document.getElementById('select-change-text').innerHTML =  Joomla.sanitizeHtml(document.getElementById('select-change').getAttribute('data-select'));
      }
      // If we are editing an association.
      else
      {
        // Show change language button
        document.getElementById('select-change-text').innerHTML =  Joomla.sanitizeHtml(document.getElementById('select-change').getAttribute('data-change'));
        $('#remove-assoc').removeClass("hidden");
        $('#toolbar-copy').hide();

        // Add the id to list of items to check in on close.
        var currentIdList = document.getElementById('target-id').value;
        var updatedList = currentIdList == '' ? targetLoadedId : currentIdList + ',' + targetLoadedId;
        document.getElementById('target-id').value = updatedList;

        // If we created a new association (after save).
        if (targetLoadedId != targetId) {
          // Refresh the language selector with the new id (used after save).
          $('#jform_itemlanguage option[value^=\"' + targetLanguage + ':' + targetId + ':add\"]').val(targetLanguage + ':' + targetLoadedId + ':edit');

          // Update main frame data-id attribute (used after save).
          this.setAttribute('data-id', targetLoadedId);
          this.setAttribute('data-action', 'edit');
        }

        // Update the reference item associations tab.
        var reference      = document.getElementById('reference-association');
        var languageCode   = targetLanguage.replace(/-/, '_');
        var referenceTitle = reference.getAttribute('data-title');
        var title          = $(this).contents().find('#jform_' + referenceTitle).val();

        // - For modal association selectors.
        $(reference).contents().find('#jform_associations_' + languageCode + '_id').val(targetLoadedId);
        $(reference).contents().find('#jform_associations_' + languageCode + '_name').val(title);

        // - For chosen association selectors (menus).
        $(reference).contents().find('#jform_associations_' + languageCode).append('<option value=\"'+ targetLoadedId + '\">' + title + '</option>');
        $(reference).contents().find('#jform_associations_' + languageCode).val(targetLoadedId);
      }

      // Update the target item associations tab.
      var reference    = document.getElementById('reference-association');
      var referenceId  = reference.getAttribute('data-id');
      var languageCode = reference.getAttribute('data-language').replace(/-/, '_');
      var target       = document.getElementById('target-association');
      var targetTitle  = target.getAttribute('data-title');
      var title        = reference.getAttribute('data-title-value');
      var target       = $(this).contents();

      // - For modal association selectors.
      target.find('#jform_associations_' + languageCode + '_id').val(referenceId);
      target.find('#jform_associations_' + languageCode + '_name').val(title);

      // - For chosen association selectors (menus).
      var chosenField = target.find('#jform_associations_' + languageCode);
      chosenField.append('<option value=\"'+ referenceId + '\">' + title + '</option>');
      chosenField.val(referenceId);

      var parse, langAssociation;

      $('#jform_itemlanguage option').each(function() {
        parse = $(this).val().split(':');

        if (typeof parse[1] !== 'undefined' && parse[1] !== '0') {
          // - For modal association selectors.
          langAssociation = parse[0].replace(/-/,'_');
          target.find('#jform_associations_' + langAssociation + '_id').val(parse[1]);

          // - For chosen association selectors (menus).
          chosenField = target.find('#jform_associations_' + langAssociation);
          chosenField.append('<option value=\"'+ parse[1] + '\"></option>');
          chosenField.val(parse[1]);
        }
      });

      // Iframe load finished, hide Joomla loading layer.
      var spinner = document.querySelector('joomla-core-loader');
      if (spinner) {
        spinner.parentNode.removeChild(spinner);
      }
    }
  });
});
