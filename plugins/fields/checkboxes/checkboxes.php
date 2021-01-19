<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Checkboxes
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Fields Checkboxes Plugin
 *
 * @since  3.7.0
 */
class PlgFieldsCheckboxes extends \Joomla\Component\Fields\Administrator\Plugin\FieldsListPlugin
{
	/**
	 * Before prepares the field value.
	 *
	 * @param   string     $context  The context.
	 * @param   \stdclass  $item     The item.
	 * @param   \stdclass  $field    The field.
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	public function onCustomFieldsBeforePrepareField($context, $item, $field)
	{
		if (!$this->app->isClient('api'))
		{
			return;
		}

		if (!$this->isTypeSupported($field->type))
		{
			return;
		}

		$field->apivalue = [];

		$options = $this->getOptionsFromField($field);

		if (empty($field->value))
		{
			return;
		}

		if (is_array($field->value))
		{
			foreach ($field->value as $key => $value)
			{
				$field->apivalue[$value] = $options[$value];
			}
		}
		else
		{
			$field->apivalue[$field->value] = $options[$field->value];
		}
	}
}
