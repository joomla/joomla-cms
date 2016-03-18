<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 * 
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

JLoader::import('components.com_fields.models.types.base', JPATH_ADMINISTRATOR);

class FieldsTypeList extends FieldsTypeBase
{

	/**
	 * Returns an array of key values to put in a list.
	 *
	 * @param stdClass $field
	 * @return array
	 */
	public function getOptions ($field)
	{
		$options = $field->fieldparams->get('options', array());
		if (! is_array($options))
		{
			$options = json_decode($options);
		}
		$data = array();
		if (isset($options->key))
		{
			foreach ($options->key as $index => $key)
			{
				$data[$key] = $options->value[$index];
			}
		}
		return $data;
	}

	protected function postProcessDomNode ($field, DOMElement $fieldNode, JForm $form)
	{
		foreach ($this->getOptions($field) as $index => $value)
		{
			$element = $fieldNode->appendChild(new DOMElement('option', $value));
			$element->setAttribute('value', $index);
		}

		return parent::postProcessDomNode($field, $fieldNode, $form);
	}
}
