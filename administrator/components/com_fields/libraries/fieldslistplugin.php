<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JLoader::import('components.com_fields.libraries.fieldsplugin', JPATH_ADMINISTRATOR);

/**
 * Base plugin for all list based plugins
 *
 * @since  3.7.0
 */
class FieldsListPlugin extends FieldsPlugin
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

		$fieldNode->setAttribute('validate', 'options');

		foreach ($this->getOptionsFromField($field) as $value => $name)
		{
			$option = new DOMElement('option', htmlspecialchars($value, ENT_COMPAT, 'UTF-8'));
			$option->textContent = htmlspecialchars(JText::_($name), ENT_COMPAT, 'UTF-8');

			$element = $fieldNode->appendChild($option);
			$element->setAttribute('value', $value);
		}

		return $fieldNode;
	}

	/**
	 * Returns an array of key values to put in a list from the given field.
	 *
	 * @param   stdClass  $field  The field.
	 *
	 * @return  array
	 *
	 * @since   3.7.0
	 */
	public function getOptionsFromField($field)
	{
		$data = array();

		// Fetch the options from the plugin
		$params = clone $this->params;
		$params->merge($field->fieldparams);

		foreach ($params->get('options', array()) as $option)
		{
			$op = (object) $option;
			$data[$op->value] = $op->name;
		}

		return $data;
	}
}
