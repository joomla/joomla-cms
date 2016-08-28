/**
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(function() {
	"use strict";

	/**
	 * Process modal fields in parent.
	 *
	 * @param   string  fieldPrefix  The fields to be updated prefix.
	 * @param   string  id           The new id for the item.
	 * @param   string  title        The new title for the item.
	 * @param   string  catid        Future usage.
	 * @param   object  object       Future usage.
	 * @param   string  url          Future usage.
	 * @param   string  language     Future usage.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	window.processModalParent = function (fieldPrefix, id, title, catid, url, language, object)
	{
		var fieldId = document.getElementById(fieldPrefix + '_id'), fieldTitle = document.getElementById(fieldPrefix + '_name');

		// Default values.
		id       = id || '';
		title    = title || '';
		catid    = catid || '';
		object   = object || '';
		url      = url || '';
		language = language || '';

		if (id)
		{
			fieldId.value    = id;
			fieldTitle.value = title;

			if (document.getElementById(fieldPrefix + '_select'))
			{
				jQuery('#' + fieldPrefix + '_select').addClass('hidden');
			}
			if (document.getElementById(fieldPrefix + '_new'))
			{
				jQuery('#' + fieldPrefix + '_new').addClass('hidden');
			}
			if (document.getElementById(fieldPrefix + '_edit'))
			{
				jQuery('#' + fieldPrefix + '_edit').removeClass('hidden');
			}
			if (document.getElementById(fieldPrefix + '_clear'))
			{
				jQuery('#' + fieldPrefix + '_clear').removeClass('hidden');
			}
		}
		else
		{
			fieldId.value    = '';
			fieldTitle.value = fieldId.getAttribute('data-text');

			if (document.getElementById(fieldPrefix + '_select'))
			{
				jQuery('#' + fieldPrefix + '_select').removeClass('hidden');
			}
			if (document.getElementById(fieldPrefix + '_new'))
			{
				jQuery('#' + fieldPrefix + '_new').removeClass('hidden');
			}
			if (document.getElementById(fieldPrefix + '_edit'))
			{
				jQuery('#' + fieldPrefix + '_edit').addClass('hidden');
			}
			if (document.getElementById(fieldPrefix + '_clear'))
			{
				jQuery('#' + fieldPrefix + '_clear').addClass('hidden');
			}
		}

		if (fieldId.getAttribute('data-required') == '1')
		{
			document.formvalidator.validate(fieldId);
			document.formvalidator.validate(fieldTitle);
		}

		return false;
	}

	/**
	 * Process new/edit modal fields in child.
	 *
	 * @param   object  element      The modal footer button element.
	 * @param   string  fieldPrefix  The fields to be updated prefix.
	 * @param   string  action       Modal action (add, edit).
	 * @param   string  itemType     The item type (Article, Contact, etc).
	 * @param   string  task         Task to be done (apply, save, cancel).
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	window.processModalEdit = function (element, fieldPrefix, action, itemType, task)
	{
		var modalId = element.parentNode.parentNode.id;

		// Set frame id.
		jQuery('#' + modalId + ' iframe').get(0).id = 'Frame_' + modalId;

		var iframeDocument = jQuery('#Frame_' + modalId).contents().get(0);
		var formId         = jQuery('#Frame_' + modalId).contents().find('form').attr('id');

		// Submit button on child iframe.
		document.getElementById('Frame_' + modalId).contentWindow.Joomla.submitbutton(itemType.toLowerCase() + '.' + task);

		// If Cancel, close the modal.
		if (task === 'cancel')
		{
			jQuery('#' + modalId).modal('hide');
			jQuery('#' + modalId).find('.modal-footer .btn-save').addClass('hidden');

			return false;
		}

		// Validate the child form and update parent form.
		if (iframeDocument.formvalidator.isValid(iframeDocument.getElementById(formId)))
		{
			window.processModalParent(fieldPrefix, iframeDocument.getElementById('jform_id').value, iframeDocument.getElementById('jform_title').value);

			// If creating a new item, enable the save and close.
			if (action == 'add' && task === 'apply')
			{
				jQuery('#' + modalId).find('.modal-footer .btn-save').removeClass('hidden');
			}

			// If Save & Close, close the modal.
			if (task === 'save')
			{
				jQuery('#' + modalId).modal('hide');
				jQuery('#' + modalId).find('.modal-footer .btn-save').addClass('hidden');
			}
		}

		return false;
	}

	/**
	 * Process select modal fields in child.
	 *
	 * @param   string  itemType     The item type (Article, Contact, etc).
	 * @param   string  fieldPrefix  The fields to be updated prefix.
	 * @param   string  id           The new id for the item.
	 * @param   string  title        The new title for the item.
	 * @param   string  catid        Future usage.
	 * @param   object  object       Future usage.
	 * @param   string  url          Future usage.
	 * @param   string  language     Future usage.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	window.processModalSelect = function(itemType, fieldPrefix, id, title, catid, object, url, language) {
		window.processModalParent(fieldPrefix, id, title, catid, url, language, object);
		jQuery('#ModalSelect' + itemType + '_' + fieldPrefix).modal('hide');

		return false;
	}

}());
