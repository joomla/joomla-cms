<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 * 
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

class FieldsTypeBase
{

	/**
	 * Returns an XML field tag for that type which can be placed in the given
	 * form. The form can be manipulated during preparation of the dom node.
	 *
	 * @param stdClass $field
	 * @param DOMElement $parent
	 * @param JForm $form
	 * @return DOMElement
	 */
	public function appendXMLFieldTag ($field, DOMElement $parent, JForm $form)
	{
		$app = JFactory::getApplication();
		if ($field->params->get('show_on') == 1 && $app->isAdmin())
		{
			return;
		}
		else if ($field->params->get('show_on') == 2 && $app->isSite())
		{
			return;
		}
		$node = $parent->appendChild(new DOMElement('field'));

		$node->setAttribute('name', $field->alias);
		$node->setAttribute('type', $field->type);
		$node->setAttribute('default', $field->default_value);
		$node->setAttribute('label', $field->label);
		$node->setAttribute('description', $field->description);
		$node->setAttribute('class', $field->class);
		$node->setAttribute('required', $field->required ? 'true' : 'false');
		$node->setAttribute('readonly', $field->params->get('readonly', 0) ? 'true' : 'false');

		// Set the disabled state based on the parameter and the permission
		$authorizedToEdit = JFactory::getUser()->authorise('edit.value', $field->context . '.field.' . (int) $field->id);
		if ($field->params->get('disabled', 0) || !$authorizedToEdit)
		{
			$node->setAttribute('disabled', 'true');
		}

		foreach ($field->fieldparams->toArray() as $key => $param)
		{
			if (is_array($param))
			{
				$param = implode(',', $param);
			}
			$node->setAttribute($key, $param);
		}
		$this->postProcessDomNode($field, $node, $form);

		return $node;
	}

	/**
	 * Prepares the given value to be ready to be displayed in an HTML context.
	 *
	 * @param stdClass $field
	 * @param mixed $value
	 * @return string
	 *
	 * @deprecated is replaced trough layouts
	 */
	public function prepareValueForDisplay ($value, $field)
	{
		return null;
	}

	/**
	 * Function to manipulate the DOM node before it is returned to the form
	 * document. The form can be manipulated during preparation of the dom node.
	 *
	 * @param stdClass $field
	 * @param DOMElement $fieldNode
	 * @param JForm $form
	 */
	protected function postProcessDomNode ($field, DOMElement $fieldNode, JForm $form)
	{
	}
}
