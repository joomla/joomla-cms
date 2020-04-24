/**
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
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
    const dropDownBtn = document.getElementById('toolbar-dropdown-status-group');
    const transitions = [].slice.call(dropDownBtn.querySelectorAll('.button-transition'));
    const headline = dropDownBtn.getElementsByClassName('button-transition-headline')[0];
    const separator = dropDownBtn.getElementsByClassName('button-transition-separator')[0];
    const itemList = [].slice.call(document.querySelectorAll('table.itemList'))[0];

    let itemListRows = [];
    let transition_ids = [];

    if (itemList) {
      itemListRows = [].slice.call(itemList.querySelectorAll('tbody tr'));
    }

    function enableTransitions() {
      if (transition_ids.length)
      {
        let availableTrans = transition_ids.shift();

        while(transition_ids.length)
        {
          const compareTrans = transition_ids.shift();

          availableTrans = availableTrans.filter((id) => {return compareTrans.indexOf(id) !== -1});
        }

        if (availableTrans.length)
        {
          if (headline)
          {
            headline.classList.remove('d-none');
          }
          if (separator)
          {
            separator.classList.remove('d-none');
          }
        }

        availableTrans.forEach((trans) => {
          const elem = dropDownBtn.querySelector('.transition-' + trans);

          if (elem)
          {
            elem.parentNode.classList.remove('d-none')
          }
        });
      }
    }

    // check for common attributes for which the conditions for a transition are possible or not
    // and save this information in a boolean variable.
    function collectTransitions(row) {
      transition_ids.push(row.getAttribute('data-transitions').split(','));
    }

    // listen to click event to get selected rows
    if (itemList) {
      itemList.addEventListener('click', () => {
        transitions.forEach((trans) => {
          trans.parentNode.classList.add('d-none');
        });
        if (headline)
        {
          headline.classList.add('d-none');
        }
        if (separator)
        {
          separator.classList.add('d-none');
        }
        transition_ids = [];
        itemListRows.forEach((el) => {
          const checkedBox = el.querySelectorAll('input[type=checkbox]')[0];
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
