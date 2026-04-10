/**
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {

  const showDiffChangedOff = () => {
    const diffMain = document.getElementById('diff-main');
    if (diffMain) {
      diffMain.classList.remove('active');
      if (typeof Storage !== 'undefined') {
        localStorage.removeItem('diffSwitchState');
      }
    }
  };
  const showDiffChangedOn = () => {
    const diffMain = document.getElementById('diff-main');
    if (diffMain) {
      diffMain.classList.add('active');
      if (typeof Storage !== 'undefined') {
        localStorage.setItem('diffSwitchState', 'checked');
      }
    }
  };
  const showCoreChangedOff = () => {
    const override = document.getElementById('override-pane');
    const corePane = document.getElementById('core-pane');
    const fieldset = override.parentElement.parentElement;
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
  const showCoreChangedOn = () => {
    const override = document.getElementById('override-pane');
    const corePane = document.getElementById('core-pane');
    const fieldset = override.parentElement.parentElement;
    if (corePane && override) {
      corePane.classList.add('active');
      if (fieldset.classList.contains('options-grid-form-full')) {
        fieldset.classList.remove('options-grid-form-full');
        fieldset.classList.add('options-grid-form-half');
      }
      if (typeof Storage !== 'undefined') {
        localStorage.setItem('coreSwitchState', 'checked');
      }
    }
  };
  document.addEventListener('DOMContentLoaded', () => {
    const JformShowDiffOn = document.getElementById('jform_show_diff1');
    const JformShowDiffOff = document.getElementById('jform_show_diff0');
    const JformShowCoreOn = document.getElementById('jform_show_core1');
    const JformShowCoreOff = document.getElementById('jform_show_core0');
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
