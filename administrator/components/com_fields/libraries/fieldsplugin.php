<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Abstract Fields Plugin
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class FieldsPlugin extends JPlugin
{
	protected $autoloadLanguage = true;

	/**
	 * Returns the custom fields specification.
	 *
	 * @return  string[][]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onCustomFieldsGetTypes()
	{
		// The data array
		$data = array();

		// Generic attributes
		$data['type']  = $this->_name;
		$data['label'] = JText::_('PLG_FIELDS_' . strtoupper($this->_name) . '_LABEL');

		$path = JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/fields';

		// Add the path when it exists
		if (file_exists($path))
		{
			$data['path'] = $path;
		}

		// Return the data
		return array($data);
	}

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
		// Check if the field should be processed by us
		if ($field->type != $this->_name)
		{
			return;
		}

		$app = JFactory::getApplication();

		// Detect if the field should be shown at all
		if ($field->params->get('show_on') == 1 && $app->isClient('administrator'))
		{
			return;
		}
		elseif ($field->params->get('show_on') == 2 && $app->isClient('site'))
		{
			return;
		}

		// Create the node
		$node = $parent->appendChild(new DOMElement('field'));

		// Set the attributes
		$node->setAttribute('name', $field->alias);
		$node->setAttribute('type', $field->type);
		$node->setAttribute('default', $field->default_value);
		$node->setAttribute('label', $field->label);
		$node->setAttribute('description', $field->description);
		$node->setAttribute('class', $field->params->get('class'));
		$node->setAttribute('hint', $field->params->get('hint'));
		$node->setAttribute('required', $field->required ? 'true' : 'false');
		$node->setAttribute('readonly', $field->params->get('readonly', 0) ? 'true' : 'false');

		// Set the disabled state based on the parameter and the permission
		if ($field->params->get('disabled', 0))
		{
			$node->setAttribute('disabled', 'true');
		}

		// Set the specific field parameters
		foreach ($field->fieldparams->toArray() as $key => $param)
		{
			if (is_array($param))
			{
				// Multidimensional arrays (eg. list options) can't be transformed properly
				$param = count($param) == count($param, COUNT_RECURSIVE) ? implode(',', $param) : '';
			}

			if (!$param)
			{
				continue;
			}

			$node->setAttribute($key, $param);
		}

		// Check if it is allowed to edit the field
		if (!FieldsHelperInternal::canEditFieldValue($field))
		{
			$node->setAttribute('disabled', 'true');
		}

		// Return the node
		return $node;
	}

	/**
	 * The form event. Load additional parameters when available into the field form.
	 * Only when the type of the form is of interest.
	 *
	 * @param   JForm     $form  The form
	 * @param   stdClass  $data  The data
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onContentPrepareForm(JForm $form, $data)
	{
		// Check if the field form is calling us
		if (strpos($form->getName(), 'com_fields.field') !== 0)
		{
			return;
		}

		$path = JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/params.xml';

		// Check if params file exists
		if (!file_exists($path))
		{
			return;
		}

		// Ensure it is an object
		$formData = (object)$data;

		// Gather the type
		$type = $form->getValue('type');

		if(isset($formData->type) && $formData->type)
		{
			$type = $formData->type;
		}

		// Not us
		if ($type != $this->_name)
		{
			return;
		}

		// Load the specific plugin parameters
		$form->load(file_get_contents($path), true, '/form/*');
	}
}
