/**
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

Joomla = window.Joomla || {};

/**
 * Method that switches a given class to the following elements of the element provided
 *
 * @param {HTMLElement}  element    The reference element
 * @param {string}       className  The class name to be toggled
 */
Joomla.toggleAllNextElements = (element, className) => {
  const getNextSiblings = (el) => {
    const siblings = [];
    /* eslint-disable no-cond-assign,no-param-reassign */
    do {
      siblings.push(el);
    } while ((el = el.nextElementSibling) !== null);
    /* eslint-enable no-cond-assign,no-param-reassign */
    return siblings;
  };

  const followingElements = getNextSiblings(element);
  if (followingElements.length) {
    followingElements.forEach((elem) => {
      if (elem.classList.contains(className)) {
        elem.classList.remove(className);
      } else {
        elem.classList.add(className);
      }
    });
  }
};

(() => {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    const dropDownBtn = document.getElementById('toolbar-status-group');

    if (!dropDownBtn) {
      return;
    }

    const transitions = [].slice.call(dropDownBtn.querySelectorAll('.button-transition'));
    const headline = dropDownBtn.querySelector('.button-transition-headline');
    const separator = dropDownBtn.querySelector('.button-transition-separator');
    const itemList = document.querySelector('table.itemList');

    let itemListRows = [];
    let transitionIds = [];

    if (itemList) {
      itemListRows = [].slice.call(itemList.querySelectorAll('tbody tr'));
    }

    function enableTransitions() {
      if (transitionIds.length) {
        let availableTrans = transitionIds.shift();

        while (transitionIds.length) {
          const compareTrans = transitionIds.shift();

          availableTrans = availableTrans.filter((id) => compareTrans.indexOf(id) !== -1);
        }

        if (availableTrans.length) {
          if (headline) {
            headline.classList.remove('d-none');
          }
          if (separator) {
            separator.classList.remove('d-none');
          }
        }

        availableTrans.forEach((trans) => {
          const elem = dropDownBtn.querySelector(`.transition-${trans}`);

          if (elem) {
            elem.parentNode.classList.remove('d-none');
          }
        });
      }
    }

    // check for common attributes for which the conditions for a transition are possible or not
    // and save this information in a boolean variable.
    function collectTransitions(row) {
      transitionIds.push(row.getAttribute('data-transitions').split(','));
    }

    // listen to click event to get selected rows
    if (itemList) {
      itemList.addEventListener('click', () => {
        transitions.forEach((trans) => {
          trans.parentNode.classList.add('d-none');
        });
        if (headline) {
          headline.classList.add('d-none');
        }
        if (separator) {
          separator.classList.add('d-none');
        }
        transitionIds = [];
        itemListRows.forEach((el) => {
          const checkedBox = el.querySelector('input[type=checkbox]');
          if (checkedBox.checked) {
            const parentTr = checkedBox.closest('tr');
            collectTransitions(parentTr);
          }
        });
        enableTransitions();
      });
    }
  });
})();
