/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
Joomla = window.Joomla || {};
/**
 * Method that switches a given class to the following elements of the element provided
 *
 * @param {HTMLElement}  element    The reference element
 * @param {string}       className  The class name to be toggled
 */

Joomla.toggleAllNextElements = function (element, className) {
  var getNextSiblings = function getNextSiblings(el) {
    var siblings = [];
    /* eslint-disable no-cond-assign,no-param-reassign */

    do {
      siblings.push(el);
    } while ((el = el.nextElementSibling) !== null);
    /* eslint-enable no-cond-assign,no-param-reassign */


    return siblings;
  };

  var followingElements = getNextSiblings(element);

  if (followingElements.length) {
    followingElements.forEach(function (elem) {
      if (elem.classList.contains(className)) {
        elem.classList.remove(className);
      } else {
        elem.classList.add(className);
      }
    });
  }
};

(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {
    var dropDownBtn = document.getElementById('toolbar-dropdown-status-group');
    var transitions = [].slice.call(dropDownBtn.querySelectorAll('.button-transition'));
    var headline = dropDownBtn.querySelector('.button-transition-headline');
    var separator = dropDownBtn.querySelector('.button-transition-separator');
    var itemList = document.querySelector('table.itemList');
    var itemListRows = [];
    var transitionIds = [];

    if (itemList) {
      itemListRows = [].slice.call(itemList.querySelectorAll('tbody tr'));
    }

    function enableTransitions() {
      if (transitionIds.length) {
        var availableTrans = transitionIds.shift();

        var _loop = function _loop() {
          var compareTrans = transitionIds.shift();
          availableTrans = availableTrans.filter(function (id) {
            return compareTrans.indexOf(id) !== -1;
          });
        };

        while (transitionIds.length) {
          _loop();
        }

        if (availableTrans.length) {
          if (headline) {
            headline.classList.remove('d-none');
          }

          if (separator) {
            separator.classList.remove('d-none');
          }
        }

        availableTrans.forEach(function (trans) {
          var elem = dropDownBtn.querySelector(".transition-".concat(trans));

          if (elem) {
            elem.parentNode.classList.remove('d-none');
          }
        });
      }
    } // check for common attributes for which the conditions for a transition are possible or not
    // and save this information in a boolean variable.


    function collectTransitions(row) {
      transitionIds.push(row.getAttribute('data-transitions').split(','));
    } // listen to click event to get selected rows


    if (itemList) {
      itemList.addEventListener('click', function () {
        transitions.forEach(function (trans) {
          trans.parentNode.classList.add('d-none');
        });

        if (headline) {
          headline.classList.add('d-none');
        }

        if (separator) {
          separator.classList.add('d-none');
        }

        transitionIds = [];
        itemListRows.forEach(function (el) {
          var checkedBox = el.querySelector('input[type=checkbox]');

          if (checkedBox.checked) {
            var parentTr = checkedBox.closest('tr');
            collectTransitions(parentTr);
          }
        });
        enableTransitions();
      });
    }
  });
})();