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
            articleListRows = articleList.querySelectorAll( 'tbody tr' );

        let artListRowLength = articleListRows.length,
            publishBool = false,
            unpublishBool = false,
            archiveBool = false,
            trashBool = false,
            countChecked = 0;

		publishBtn.addEventListener('click', function(e)
		{
			if (this.classList.contains('disabled'))
			{
				e.stopPropagation();

				Joomla.renderMessages({'error': [Joomla.JText._('COM_CONTENT_ERROR_CANNOT_PUBlISH')]});
			}
		});

		unpublishBtn.addEventListener('click', function(e)
		{
			if (this.classList.contains('disabled'))
			{
				e.stopPropagation();

				Joomla.renderMessages({'error': [Joomla.JText._('COM_CONTENT_ERROR_CANNOT_UNPUBlISH')]});
			}
		});

		archiveBtn.addEventListener('click', function(e)
		{
			if (this.classList.contains('disabled'))
			{
				e.stopPropagation();

				Joomla.renderMessages({'error': [Joomla.JText._('COM_CONTENT_ERROR_CANNOT_ARCHIVE')]});
			}
		});

		trashBtn.addEventListener('click', function(e)
		{
			if (this.classList.contains('disabled'))
			{
				e.stopPropagation();

				Joomla.renderMessages({'error': [Joomla.JText._('COM_CONTENT_ERROR_CANNOT_TRASH')]});
			}
		});

        // listen to click event to get selected rows
        articleList.addEventListener( "click", function ( ) {
            for ( let i = 0; i < artListRowLength; i += 1 ) {
                let checkedBox = articleListRows[ i ].querySelectorAll( 'input[type=checkbox]' )[ 0 ];

                if ( articleListRows[ i ].querySelectorAll( 'input[type=checkbox]' )[ 0 ].checked === true ) {
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
        function checkForAttributes( row ) {

            publishBool = row.getAttribute( 'data-condition-publish' ) > 0 && (countChecked === 0 || publishBool);
            unpublishBool = row.getAttribute( 'data-condition-unpublish' ) > 0 && (countChecked === 0 || unpublishBool);
            archiveBool = row.getAttribute( 'data-condition-archive' ) > 0 && (countChecked === 0 || archiveBool);
            trashBool = row.getAttribute( 'data-condition-trash' ) > 0 && (countChecked === 0 || trashBool);
        }

        // disable or enable Buttons of transitions depending on the boolean variables
        function disableButtons() {
            setOrRemDisabled( publishBtn, publishBool ?  'rem' : 'set' );
            setOrRemDisabled( unpublishBtn, unpublishBool ?  'rem' : 'set' );
            setOrRemDisabled( archiveBtn, archiveBool ?  'rem' : 'set' );
            setOrRemDisabled( trashBtn, trashBool ?  'rem' : 'set' );
        }

        function setOrRemDisabled( btn, SetOrRem ) {
            ( SetOrRem === 'set' )
                ? btn.setAttribute( 'disabled', true )
                : btn.removeAttribute( 'disabled' );
        }

    } );

} )();
