(function () {
  'use strict';

  /**
   * @copyright  (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
   * @license    GNU General Public License version 2 or later; see LICENSE.txt
   */
  Joomla = window.Joomla || {};

  (function () {

    var baseLink = 'index.php?option=com_modules&client_id=0&task=module.edit&tmpl=component&view=module&layout=modal&id=';
    var linkElements = [].slice.call(document.getElementsByClassName('module-edit-link'));
    var elements = [].slice.call(document.querySelectorAll('#moduleEditModal .modal-footer .btn'));

    if (linkElements.length) {
      linkElements.forEach(function (linkElement) {
        linkElement.addEventListener('click', function (_ref) {
          var target = _ref.target;
          var link = baseLink + target.getAttribute('data-module-id');
          var modal = document.getElementById('moduleEditModal');
          var body = modal.querySelector('.modal-body');
          var iFrame = document.createElement('iframe');
          iFrame.src = link;
          iFrame.setAttribute('class', 'class="iframe jviewport-height70"');
          body.innerHTML = '';
          body.appendChild(iFrame);
          modal.open();
        });
      });
    }

    if (elements.length) {
      elements.forEach(function (element) {
        element.addEventListener('click', function (_ref2) {
          var target = _ref2.target;
          var dataTarget = target.getAttribute('data-bs-target');

          if (dataTarget) {
            var iframe = document.querySelector('#moduleEditModal iframe');
            var iframeDocument = iframe.contentDocument || iframe.contentWindow.document;
            iframeDocument.querySelector(dataTarget).click();
          }
        });
      });
    }
  })();

}());
