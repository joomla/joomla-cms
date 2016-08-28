/**
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

(function() {
	"use strict";

	/**
	 * Process modal fields in parent.
	 *
	 * @param   string  field_id  xxxxxxxxxxxxx.
	 * @param   string  id        xxxxxxxxxxxxx.
	 * @param   string  title     xxxxxxxxxxxxx.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	window.processModalParent = function (field_id, id, title, catid, object, url, language)
	{
		var fieldId = document.getElementById(field_id + '_id'), fieldTitle = document.getElementById(field_id + '_name');

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

			if (document.getElementById(field_id + '_select'))
			{
				jQuery('#' + field_id + '_select').addClass('hidden');
			}
			if (document.getElementById(field_id + '_new'))
			{
				jQuery('#' + field_id + '_new').addClass('hidden');
			}
			if (document.getElementById(field_id + '_edit'))
			{
				jQuery('#' + field_id + '_edit').removeClass('hidden');
			}
			if (document.getElementById(field_id + '_clear'))
			{
				jQuery('#' + field_id + '_clear').removeClass('hidden');
			}
		}
		else
		{
			fieldId.value    = '';
			fieldTitle.value = fieldId.getAttribute('data-text');

			if (document.getElementById(field_id + '_select'))
			{
				jQuery('#' + field_id + '_select').removeClass('hidden');
			}
			if (document.getElementById(field_id + '_new'))
			{
				jQuery('#' + field_id + '_new').removeClass('hidden');
			}
			if (document.getElementById(field_id + '_edit'))
			{
				jQuery('#' + field_id + '_edit').addClass('hidden');
			}
			if (document.getElementById(field_id + '_clear'))
			{
				jQuery('#' + field_id + '_clear').addClass('hidden');
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
	 * @param   object  element       xxxxxxxxxxxxx.
	 * @param   string  parentFormId  xxxxxxxxxxxxx.
	 * @param   string  action        xxxxxxxxxxxxx.
	 * @param   string  item          xxxxxxxxxxxxx.
	 * @param   string  task          xxxxxxxxxxxxx.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	window.processModalEdit = function (element, parentFormId, action, item, task)
	{
		var modalId = element.parentNode.parentNode.id;

		// Set frame id.
		jQuery('#' + modalId + ' iframe').get(0).id = 'Frame_' + modalId;

		var iframeDocument = jQuery('#Frame_' + modalId).contents().get(0);
		var formId         = jQuery('#Frame_' + modalId).contents().find('form').attr('id');

		// Submit button on child iframe.
		document.getElementById('Frame_' + modalId).contentWindow.Joomla.submitbutton(item + '.' + task);

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
			window.processModalParent(parentFormId, iframeDocument.getElementById('jform_id').value, iframeDocument.getElementById('jform_title').value);

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
	 * @param   string  item          xxxxxxxxxxxxx.
	 * @param   string  parentFormId  xxxxxxxxxxxxx.
	 * @param   string  id            xxxxxxxxxxxxx.
	 * @param   string  title         xxxxxxxxxxxxx.
	 * @param   string  catid         xxxxxxxxxxxxx.
	 * @param   object  object        xxxxxxxxxxxxx.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	window.processModalSelect = function(item, parentFormId, id, title, catid, object, url, language) {
		window.processModalParent(parentFormId, id, title, catid, object, url, language);
		jQuery('#ModalSelect' + item + '_' + parentFormId).modal('hide');
	}

}());
