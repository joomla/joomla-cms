/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};

(function () {
  'use strict';

  var options = Joomla.getOptions('menus-edit-modules');

  if (options) {
    window.viewLevels = options.viewLevels;
    window.menuId = parseInt(options.itemId, 10);
  }

  var baseLink = 'index.php?option=com_modules&client_id=0&task=module.edit&tmpl=component&view=module&layout=modal&id=';
  var assigned1 = document.getElementById('jform_toggle_modules_assigned1');
  var assigned0 = document.getElementById('jform_toggle_modules_assigned0');
  var published1 = document.getElementById('jform_toggle_modules_published1');
  var published0 = document.getElementById('jform_toggle_modules_published0');
  var linkElements = [].slice.call(document.getElementsByClassName('module-edit-link'));
  var elements = [].slice.call(document.querySelectorAll('#moduleEditModal .modal-footer .btn'));

  if (assigned1) {
    assigned1.addEventListener('click', function () {
      var list = [].slice.call(document.querySelectorAll('tr.no'));
      list.forEach(function (item) {
        item.classList.add('table-row');
        item.classList.remove('hidden');
      });
    });
  }

  if (assigned0) {
    assigned0.addEventListener('click', function () {
      var list = [].slice.call(document.querySelectorAll('tr.no'));
      list.forEach(function (item) {
        item.classList.add('hidden');
        item.classList.remove('table-row');
      });
    });
  }

  if (published1) {
    published1.addEventListener('click', function () {
      var list = [].slice.call(document.querySelectorAll('.table tr.unpublished'));
      list.forEach(function (item) {
        item.classList.add('table-row');
        item.classList.remove('hidden');
      });
    });
  }

  if (published0) {
    published0.addEventListener('click', function () {
      var list = [].slice.call(document.querySelectorAll('.table tr.unpublished'));
      list.forEach(function (item) {
        item.classList.add('hidden');
        item.classList.remove('table-row');
      });
    });
  }

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
        var dataTarget = target.getAttribute('data-target');

        if (dataTarget) {
          var iframe = document.querySelector('#moduleEditModal iframe');
          var iframeDocument = iframe.contentDocument || iframe.contentWindow.document;
          iframeDocument.querySelector(dataTarget).click();
        }
      });
    });
  }
})();