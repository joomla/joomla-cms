<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
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
	 * Transforms the field into an XML element and appends it as child on the given parent. This
	 * is the default implementation of a field. Form fields which do support to be transformed into
	 * an XML Element mut implemet the JFormDomfieldinterface.
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

		foreach ($this->getOptionsFromField($field) as $value => $name)
		{
			$option = new DOMElement('option', $value);
			$option->nodeValue = JText::_($name);

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
		$params = clone($this->params);
		$params->merge($field->fieldparams);

		foreach ($params->get('options', array()) as $option)
		{
			$op = (object) $option;
			$data[$op->value] = $op->name;
		}

		return $data;
	}
}
