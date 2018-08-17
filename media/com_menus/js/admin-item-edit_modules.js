/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
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

  document.addEventListener('DOMContentLoaded', function () {
    var baseLink = 'index.php?option=com_modules&amp;client_id=0&amp;task=module.edit&amp;tmpl=component&amp;view=module&amp;layout=modal&amp;id=';
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
          item.style.display = 'table-row';
        });
      });
    }

    if (assigned0) {
      assigned0.addEventListener('click', function () {
        var list = [].slice.call(document.querySelectorAll('tr.no'));

        list.forEach(function (item) {
          item.style.display = 'none';
        });
      });
    }

    if (published1) {
      published1.addEventListener('click', function () {
        var list = [].slice.call(document.querySelectorAll('.table tr.unpublished'));

        list.forEach(function (item) {
          item.style.display = 'table-row';
        });
      });
    }

    if (published0) {
      published0.addEventListener('click', function () {
        var list = [].slice.call(document.querySelectorAll('.table tr.unpublished'));

        list.forEach(function (item) {
          item.style.display = 'none';
        });
      });
    }

    if (linkElements.length) {
      linkElements.forEach(function (linkElement) {
        linkElement.addEventListener('click', function (event) {
          var link = baseLink + event.target.getAttribute('data-moduleId');
          var modal = document.getElementById('moduleEditModal');

          modal.addEventListener('show.bs.modal', function (ev) {
            var iFrame = document.createElement('iframe');
            iFrame.src = link;
            iFrame.setAttribute('class', 'class="iframe jviewport-height70"');
            var body = ev.target.querySelector('.modal-body');
            body.innerHTML = '';
            body.appendChild(iFrame);
          });
        });
      });
    }

    if (elements.length) {
      elements.forEach(function (element) {
        element.addEventListener('click', function (event) {
          var target = event.target.getAttribute('data-target');

          if (target) {
            var iframe = document.querySelector('#moduleEditModal iframe');
            iframe.contents().querySelector(target).click();
          }
        });
      });
    }
  });
})();
