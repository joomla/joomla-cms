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
    const publishBtn = dropDownBtn.getElementsByClassName('button-publish')[0];
    const unpublishBtn = dropDownBtn.getElementsByClassName('button-unpublish')[0];
    const archiveBtn = dropDownBtn.getElementsByClassName('button-archive')[0];
    const trashBtn = dropDownBtn.getElementsByClassName('button-trash')[0];
    const articleList = document.querySelector('#articleList');
    const modal = document.getElementById('stageModal');
    const modalcontent = document.getElementById('stageModal-content');
    const modalbutton = document.getElementById('stage-submit-button-id');
    const buttonDataSelector = 'data-submit-task';

    let articleListRows = [];
    let publishBool = false;
    let unpublishBool = false;
    let archiveBool = false;
    let trashBool = false;
    let countChecked = 0;

    if (articleList) {
      articleListRows = [].slice.call(articleList.querySelectorAll('tbody tr'));
    }
    // TODO: remove jQuery dependency, when we have a new modal script
    window.jQuery(modal).on('hide.bs.modal', () => {
      modalcontent.innerHTML = '';
    });

    function checkTransition(e, task) {
      // Let's check for n:1 connections
      const transitions = Joomla.getOptions('articles.transitions')[task];
      const availableTrans = {};
      let showModal = false;

      if (transitions === undefined) {
        return;
      }

      if (articleListRows.length) {
        articleListRows.forEach((el) => {
          const checkedBox = el.querySelectorAll('input[type=checkbox]')[0];

          if (checkedBox.checked) {
            const parentTr = checkedBox.closest('tr');
            const stage = parseInt(parentTr.getAttribute('data-stage_id'), 10);
            const workflow = parseInt(parentTr.getAttribute('data-workflow_id'), 10);

            availableTrans[checkedBox.value] = [];

            if (transitions[workflow] === undefined) {
              return;
            }

            let k = 0;

            // Collect transitions
            if (transitions[workflow][-1] !== undefined) {
              for (let j = 0; j < transitions[workflow][-1].length; j += 1) {
                if (transitions[workflow][-1][j].to_stage_id !== stage) {
                  availableTrans[checkedBox.value][k] = transitions[workflow][-1][j];

                  k += 1;
                }
              }
            }

            if (transitions[workflow][stage] !== undefined) {
              for (let j = 0; j < transitions[workflow][stage].length; j += 1) {
                if (transitions[workflow][stage][j].to_stage_id !== stage) {
                  availableTrans[checkedBox.value][k] = transitions[workflow][stage][j];

                  k += 1;
                }
              }
            }

            if (availableTrans[checkedBox.value].length > 1) {
              showModal = true;
            } else {
              delete availableTrans[checkedBox.value];
            }
          }
        });
      }

      if (showModal) {
        e.stopPropagation();

        const articles = Joomla.getOptions('articles.items');
        let html = '';

        modalbutton.setAttribute(buttonDataSelector, `articles.${task}`);

        Object.keys(availableTrans).forEach((id) => {
          if (articles[`article-${id}`] !== undefined) {
            html += '<div class="form-group col-md-6">';
            html += `<label for="publish_transitions_${id}">${articles[`article-${id}`]}</label>`;
            html += `<select id="publish_transitions_${id}" class="custom-select" name="publish_transitions[${id}]">`;

            Object.keys(availableTrans[id]).forEach((key) => {
              html += `<option value="${availableTrans[id][key].value}">${availableTrans[id][key].text}</option>`;
            });

            html += '</select>';
            html += '</div>';
            html += '</div>';
          }
        });

        modalcontent.innerHTML = html;

        // TODO: remove jQuery dependency, when we have a new modal script
        window.jQuery(modal).modal();
      }
    }

    publishBtn.parentElement.addEventListener('click', (e) => {
      if (publishBtn.classList.contains('disabled')) {
        e.stopImmediatePropagation();

        Joomla.renderMessages({ error: [Joomla.JText._('COM_CONTENT_ERROR_CANNOT_PUBlISH')] });
      } else {
        checkTransition(e, 'publish');
      }
    });

    unpublishBtn.parentElement.addEventListener('click', (e) => {
      if (unpublishBtn.classList.contains('disabled')) {
        e.stopImmediatePropagation();

        Joomla.renderMessages({ error: [Joomla.JText._('COM_CONTENT_ERROR_CANNOT_UNPUBlISH')] });
      } else {
        checkTransition(e, 'unpublish');
      }
    });

    archiveBtn.parentElement.addEventListener('click', (e) => {
      if (archiveBtn.classList.contains('disabled')) {
        e.stopImmediatePropagation();

        Joomla.renderMessages({ error: [Joomla.JText._('COM_CONTENT_ERROR_CANNOT_ARCHIVE')] });
      } else {
        checkTransition(e, 'archive');
      }
    });

    trashBtn.parentElement.addEventListener('click', (e) => {
      if (trashBtn.classList.contains('disabled')) {
        e.stopImmediatePropagation();

        Joomla.renderMessages({ error: [Joomla.JText._('COM_CONTENT_ERROR_CANNOT_TRASH')] });
      } else {
        checkTransition(e, 'trash');
      }
    });

    function setOrRemDisabled(btn, set) {
      if (set) {
        btn.classList.remove('disabled');
      } else {
        btn.classList.add('disabled');
      }
    }

    // disable or enable Buttons of transitions depending on the boolean variables
    function disableButtons() {
      setOrRemDisabled(publishBtn, publishBool);
      setOrRemDisabled(unpublishBtn, unpublishBool);
      setOrRemDisabled(archiveBtn, archiveBool);
      setOrRemDisabled(trashBtn, trashBool);
    }

    // check for common attributes for which the conditions for a transition are possible or not
    // and save this information in a boolean variable.
    function checkForAttributes(row) {
      publishBool = row.getAttribute('data-condition-publish') > 0 && (countChecked === 0 || publishBool);
      unpublishBool = row.getAttribute('data-condition-unpublish') > 0 && (countChecked === 0 || unpublishBool);
      archiveBool = row.getAttribute('data-condition-archive') > 0 && (countChecked === 0 || archiveBool);
      trashBool = row.getAttribute('data-condition-trash') > 0 && (countChecked === 0 || trashBool);
    }

    // listen to click event to get selected rows
    if (articleList) {
      articleList.addEventListener('click', () => {
        articleListRows.forEach((el) => {
          const checkedBox = el.querySelectorAll('input[type=checkbox]')[0];

          if (checkedBox.checked) {
            const parentTr = checkedBox.closest('tr');
            checkForAttributes(parentTr);
            countChecked += 1;
          }
        });
        disableButtons();
        countChecked = 0;
      });
    }
  });
})();
