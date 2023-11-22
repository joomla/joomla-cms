/**
  * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
  * @license    GNU General Public License version 2 or later; see LICENSE.txt
  */

(() => {
  // Use a JoomlaExpectingPostMessage flag to be able to distinct legacy methods
  if (window.parent.JoomlaExpectingPostMessage) {
    return;
  }

  // Assign listener for click event (for single module id insertion)
  document.querySelectorAll('.js-module-insert').forEach((element) => {
    element.addEventListener('click', (event) => {
      event.preventDefault();
      const modid = event.target.getAttribute('data-module');
      const editor = event.target.getAttribute('data-editor');

      // Use the API
      if (window.parent.Joomla && window.parent.Joomla.editors
        && window.parent.Joomla.editors.instances
        && Object.prototype.hasOwnProperty.call(window.parent.Joomla.editors.instances, editor)) {
        window.parent.Joomla.editors.instances[editor].replaceSelection(`{loadmoduleid ${modid}}`);
      }

      if (window.parent.Joomla.Modal) {
        window.parent.Joomla.Modal.getCurrent().close();
      }
    });
  });

  // Assign listener for click event (for position insertion)
  document.querySelectorAll('.js-position-insert').forEach((element) => {
    element.addEventListener('click', (event) => {
      event.preventDefault();
      const position = event.target.getAttribute('data-position');
      const editor = event.target.getAttribute('data-editor');

      // Use the API
      if (window.Joomla && window.Joomla.editors && Joomla.editors.instances
        && Object.prototype.hasOwnProperty.call(window.parent.Joomla.editors.instances, editor)) {
        window.parent.Joomla.editors.instances[editor].replaceSelection(`{loadposition ${position}}`);
      }

      if (window.parent.Joomla.Modal) {
        window.parent.Joomla.Modal.getCurrent().close();
      }
    });
  });

    // Save a cookie that expires in one hour to notify that the module has to be imported if saved.
  document.getElementById('importnewmodule').addEventListener('click', () => {
    const expirationTime = new Date();
    expirationTime.setTime(expirationTime.getTime() + (60 * 60 * 1000));
    document.cookie = `com_modules_importOnSave=1;expires=${expirationTime.toUTCString()}`;
  });
})();
