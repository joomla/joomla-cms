/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function () {
  'use strict';

  var showDiffChangedOff = function showDiffChangedOff() {
    var diffMain = document.getElementById('diff-main');

    if (diffMain) {
      diffMain.classList.remove('active');

      if (typeof Storage !== 'undefined') {
        localStorage.removeItem('diffSwitchState');
      }
    }
  };

  var showDiffChangedOn = function showDiffChangedOn() {
    var diffMain = document.getElementById('diff-main');

    if (diffMain) {
      diffMain.classList.add('active');

      if (typeof Storage !== 'undefined') {
        localStorage.setItem('diffSwitchState', 'checked');
      }
    }
  };

  var showCoreChangedOff = function showCoreChangedOff() {
    var override = document.getElementById('override-pane');
    var corePane = document.getElementById('core-pane');
    var fieldset = override.parentElement.parentElement;

    if (corePane && override) {
      corePane.classList.remove('active');

      if (fieldset.classList.contains('options-grid-form-half')) {
        fieldset.classList.remove('options-grid-form-half');
        fieldset.classList.add('options-grid-form-full');
      }

      if (typeof Storage !== 'undefined') {
        localStorage.removeItem('coreSwitchState');
      }
    }
  };

  var showCoreChangedOn = function showCoreChangedOn() {
    var override = document.getElementById('override-pane');
    var corePane = document.getElementById('core-pane');
    var fieldset = override.parentElement.parentElement;

    if (corePane && override) {
      corePane.classList.add('active');

      if (fieldset.classList.contains('options-grid-form-full')) {
        fieldset.classList.remove('options-grid-form-full');
        fieldset.classList.add('options-grid-form-half');
      }

      if (Joomla.editors.instances.jform_core) {
        Joomla.editors.instances.jform_core.refresh();
      }

      if (typeof Storage !== 'undefined') {
        localStorage.setItem('coreSwitchState', 'checked');
      }
    }
  };

  document.addEventListener('DOMContentLoaded', function () {
    var JformShowDiffOn = document.getElementById('jform_show_diff1');
    var JformShowDiffOff = document.getElementById('jform_show_diff0');
    var JformShowCoreOn = document.getElementById('jform_show_core1');
    var JformShowCoreOff = document.getElementById('jform_show_core0');

    if (JformShowDiffOn && JformShowDiffOff) {
      JformShowDiffOn.addEventListener('click', showDiffChangedOn);
      JformShowDiffOff.addEventListener('click', showDiffChangedOff);
    }

    if (JformShowCoreOn && JformShowCoreOff) {
      JformShowCoreOn.addEventListener('click', showCoreChangedOn);
      JformShowCoreOff.addEventListener('click', showCoreChangedOff);
    }

    if (typeof Storage !== 'undefined' && localStorage.getItem('coreSwitchState') && JformShowCoreOn) {
      JformShowCoreOn.checked = true;
      JformShowCoreOff.checked = false;
      showCoreChangedOn();
    }

    if (typeof Storage !== 'undefined' && localStorage.getItem('diffSwitchState') && JformShowDiffOn) {
      JformShowDiffOn.checked = true;
      JformShowDiffOff.checked = false;
      showDiffChangedOn();
    }
  });
})();