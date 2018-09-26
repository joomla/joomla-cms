/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

( () => {
  'use strict';

  document.addEventListener( 'DOMContentLoaded', function () {
    const dropDownBtn = document.getElementById( 'toolbar-dropdown-group' ),
      publishBtn = dropDownBtn.getElementsByClassName( 'button-publish' )[ 0 ],
      unpublishBtn = dropDownBtn.getElementsByClassName( 'button-unpublish' )[ 0 ],
      archiveBtn = dropDownBtn.getElementsByClassName( 'button-archive' )[ 0 ],
      trashBtn = dropDownBtn.getElementsByClassName( 'button-trash' )[ 0 ],
      articleList = document.querySelector( '#articleList' ),
      articleListRows = articleList.querySelectorAll( 'tbody tr' ),
      modal = document.getElementById('stageModal'),
      modalcontent = document.getElementById('stageModal-content');

    let artListRowLength = articleListRows.length,
      publishBool = false,
      unpublishBool = false,
      archiveBool = false,
      trashBool = false,
      countChecked = 0;

    // TODO: remove jQuery dependency, when we have a new modal script
    jQuery(modal).on('hide.bs.modal', function ()
    {
      modalcontent.innerHTML = '';
    });

    publishBtn.addEventListener('click', function(e)
    {
      if (this.classList.contains('disabled'))
      {
        e.stopPropagation();

        Joomla.renderMessages({'error': [Joomla.JText._('COM_CONTENT_ERROR_CANNOT_PUBlISH')]});
      }
      else
      {
        checkTransition(e, 'publish');
      }
    });

    unpublishBtn.addEventListener('click', function(e)
    {
      if (this.classList.contains('disabled'))
      {
        e.stopPropagation();

        Joomla.renderMessages({'error': [Joomla.JText._('COM_CONTENT_ERROR_CANNOT_UNPUBlISH')]});
      }
      else
      {
        checkTransition(e, 'unpublish');
      }
    });

    archiveBtn.addEventListener('click', function(e)
    {
      if (this.classList.contains('disabled'))
      {
        e.stopPropagation();

        Joomla.renderMessages({'error': [Joomla.JText._('COM_CONTENT_ERROR_CANNOT_ARCHIVE')]});
      }
      else
      {
        checkTransition(e, 'archive');
      }
    });

    trashBtn.addEventListener('click', function(e)
    {
      if (this.classList.contains('disabled'))
      {
        e.stopPropagation();

        Joomla.renderMessages({'error': [Joomla.JText._('COM_CONTENT_ERROR_CANNOT_TRASH')]});
      }
      else
      {
        checkTransition(e, 'trash');
      }
    });

    // listen to click event to get selected rows
    articleList.addEventListener( "click", function ( ) {
      for ( let i = 0; i < artListRowLength; ++i ) {
        let checkedBox = articleListRows[i].querySelectorAll( 'input[type=checkbox]' )[ 0 ];

        if ( checkedBox.checked )
        {
          const parentTr = checkedBox.closest( 'tr' );
          checkForAttributes( parentTr );
          countChecked += 1;
        }
      }
      disableButtons();
      countChecked = 0;
    } );

    // check for common attributes for which the conditions for a transition are possible or not and save this
    // information in a boolean variable.
    function checkForAttributes(row)
    {
      publishBool = row.getAttribute( 'data-condition-publish' ) > 0 && (countChecked === 0 || publishBool);
      unpublishBool = row.getAttribute( 'data-condition-unpublish' ) > 0 && (countChecked === 0 || unpublishBool);
      archiveBool = row.getAttribute( 'data-condition-archive' ) > 0 && (countChecked === 0 || archiveBool);
      trashBool = row.getAttribute( 'data-condition-trash' ) > 0 && (countChecked === 0 || trashBool);
    }

    // disable or enable Buttons of transitions depending on the boolean variables
    function disableButtons() {
      setOrRemDisabled( publishBtn, publishBool );
      setOrRemDisabled( unpublishBtn, unpublishBool );
      setOrRemDisabled( archiveBtn, archiveBool );
      setOrRemDisabled( trashBtn, trashBool );
    }

    function setOrRemDisabled( btn, set ) {
      ( !set )
        ? btn.classList.add('disabled')
        : btn.classList.remove('disabled');
    }

    function checkTransition(e, task)
    {
      // Let's check for n:1 connections
      let transitions = Joomla.getOptions('articles.transitions')[task],
        availableTrans = {},
        showModal = false;

      if (transitions === undefined)
      {
        return;
      }

      for (let i = 0; i < artListRowLength; ++i) {
        const checkedBox = articleListRows[i].querySelectorAll('input[type=checkbox]')[0];

        if (checkedBox.checked)
        {
          let parentTr = checkedBox.closest('tr'),
            stage = parseInt(parentTr.getAttribute('data-stage_id')),
            workflow = parseInt(parentTr.getAttribute('data-workflow_id'));

          availableTrans[checkedBox.value] = [];

          if (transitions[workflow] === undefined)
          {
            continue;
          }

          // Collect transitions
          if (transitions[workflow][-1] !== undefined)
          {
            let k = 0;

            for (let j = 0; j < transitions[workflow][-1].length; ++j)
            {
              if (transitions[workflow][-1][j].to_stage_id !== stage)
              {
                availableTrans[checkedBox.value][k++] = transitions[workflow][-1][j];
              }
            }
          }

          if (transitions[workflow][stage] !== undefined)
          {
            let k = 0;

            for (let j = 0; j < transitions[workflow][stage].length; ++j)
            {
              if (transitions[workflow][stage][j].to_stage_id != stage)
              {
                availableTrans[checkedBox.value][k++] = transitions[workflow][stage][j];
              }
            }
          }

          if (availableTrans[checkedBox.value].length > 1)
          {
            showModal = true;
          }
          else
          {
            delete availableTrans[checkedBox.value];
          }
        }
      }

      if (showModal)
      {
        e.stopPropagation();

        let articles = Joomla.getOptions('articles.items'),
          html = '';

        for (let id in availableTrans)
        {
          if (articles['article-' + id] === undefined)
          {
            continue;
          }

          html += '<div class="form-group col-md-6">';
            html += '<label for="">' + articles['article-' + id] + '</label>';
              html += '<select class="custom-select" name="publish_transitions[' + id + ']">';
              for (let i in availableTrans[id]) {
                html += '<option value="' + availableTrans[id][i].value + '">' + availableTrans[id][i].text + '</option>';
              }
              html += '</select>';
            html += '</div>';
          html += '</div>'
        }

        modalcontent.innerHTML = html;

        // TODO: remove jQuery dependency, when we have a new modal script
        jQuery(modal).modal();
      }
    }

  } );

} )();
