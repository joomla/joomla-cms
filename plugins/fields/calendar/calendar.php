<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Calendar
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
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
	 * Transforms the field into an DOM XML element and appends it as a child on the given parent.
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
		$fieldNode->setAttribute('filter', 'USER_UTC');

		// Set field to use translated formats
		$fieldNode->setAttribute('translateformat', '1');
		$fieldNode->setAttribute('showtime', $field->fieldparams->get('showtime', 0) ? 'true' : 'false');

		return $fieldNode;
	}
}
