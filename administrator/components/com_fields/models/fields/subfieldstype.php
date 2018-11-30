<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * Fields Subfieldstype. Represents a list field with the options being all possible
 * custom field types, except the 'subfields' custom field type.
 *
 * @see    \JFormFieldType
 * @since  __DEPLOY_VERSION__
 */
class JFormFieldSubfieldstype extends JFormFieldList
{
	public $type = 'Subfieldstype';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected function getOptions()
	{
		$options = parent::getOptions();

		$fieldTypes = FieldsHelper::getFieldTypes();

		foreach ($fieldTypes as $fieldType)
		{
			// Skip our own subfields type. We won't have subfields in subfields.
			if ($fieldType['type'] == 'subfields')
			{
				continue;
			}

			$options[] = JHtml::_('select.option', $fieldType['type'], $fieldType['label']);
		}

		// Sorting the fields based on the text which is displayed
		usort(
			$options,
			function ($a, $b)
			{
				return strcmp($a->text, $b->text);
			}
		);

		return $options;
	}
}
