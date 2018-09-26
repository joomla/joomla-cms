<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Calendar
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::import('components.com_fields.libraries.fieldsplugin', JPATH_ADMINISTRATOR);

/**
 * Fields Calendar Plugin
 *
 * @since  3.7.0
 */
class PlgFieldsCalendar extends FieldsPlugin
{
	/**
	 * Transforms the field into a DOM XML element and appends it as a child on the given parent.
	 *
	 * @param   stdClass    $field   The field.
	 * @param   DOMElement  $parent  The field node parent.
	 * @param   JForm       $form    The form.
	 *
	 * @return  DOMElement
	 *
	 * @since   3.7.0
	 */
	public function onCustomFieldsPrepareDom($field, DOMElement $parent, JForm $form)
	{
		$fieldNode = parent::onCustomFieldsPrepareDom($field, $parent, $form);

		if (!$fieldNode)
		{
			return $fieldNode;
		}

		// Set filter to user UTC
		$fieldNode->setAttribute('filter',  $field->fieldparams->get('filter', 'USER_UTC'));

		// Set field to use translated formats
		$translateFormat = 1;

		// If user uses a custom format, we do not translate
		if ($field->fieldparams->get('format', '') !== '')
		{
			$translateFormat = 0;
		}

		$fieldNode->setAttribute('translateformat', $translateFormat);
		$fieldNode->setAttribute('format', $field->fieldparams->get('format', '%Y-%m-%d'));
		$fieldNode->setAttribute('showtime', $field->fieldparams->get('showtime', 0) ? 'true' : 'false');

		return $fieldNode;
	}
}
