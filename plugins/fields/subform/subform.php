<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Subform
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::import('components.com_fields.libraries.fieldsplugin', JPATH_ADMINISTRATOR);

/**
 * Fields subform Plugin
 *
 * @since __DEPLOY_VERSION__
 */
class PlgFieldsSubform extends FieldsPlugin
{
	/**
	 * Two-dimensional array to hold to do a fast in-memory caching of rendered
	 * subfield values.
	 *
	 * @var array
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected $renderCache = array();

	public function onContentPrepareForm(JForm $form, $data)
	{
		$path = $this->getFormPath($form, $data);
		if ($path === null)
		{
			return;
		}

		// Load our own form definition
		$xml = new DOMDocument();
		$xml->load($path);

		// Get the options subform
		$xmlxpath = new DOMXPath($xml);
		$hiddenform = $xmlxpath->evaluate(
			'/form/fields[@name="fieldparams"]/fieldset[@name="fieldparams"]/field[@name="options"]/form'
		);
		if ($hiddenform->length != 1)
		{
			// Something is wrong, abort.
			return;
		}
		$hiddenform = $hiddenform->item(0);

		// Iterate over all fields which we know (all our wanted subfields).
		$fieldTypes = FieldsHelper::getFieldTypes();
		foreach ($fieldTypes as $fieldType)
		{
			// Skip subform type, we dont want to allow subforms in subforms
			// (to ease complexity)
			if ($fieldType['type'] == 'subform')
			{
				continue;
			}
			// Check whether the XML definition file for that type exists
			$path = (JPATH_PLUGINS . '/' . $this->_type . '/' . $fieldType['type'] . '/params/' . $fieldType['type'] . '.xml');
			if (!file_exists($path))
			{
				continue;
			}

			try
			{
				// Try to load the XML definition file into a DOMDocument
				$subxml = new DOMDocument();
				$subxml->load($path);
				$subxmlxpath = new DOMXPath($subxml);

				// XPath all fields from that XML document
				$fields = $subxmlxpath->evaluate('/form/fields[@name="fieldparams"]/fieldset[@name="fieldparams"]/field');
				for ($i = 0; $i < $fields->length; $i++)
				{
					$field = $fields->item($i);
					/* @var $field \DOMElement */

					// Rewrite this fields name, e.g. rewrite name=='buttons' for
					// the editor fieldtype to '_type-editor_buttons'
					$this->rewriteNodeNameRecursive(
						$field,
						('_type-' . $fieldType['type'] . '_')
					);

					// Only show this field when the field 'type' is of the specific type
					$field->setAttribute('showon', 'type:' . $fieldType['type']);

					// Those cannot be required, the 'showon' does only control visibility, and hence
					// invisible elements would be required else
					$field->setAttribute('required', '0');

					// Import the rewritten field into our parent form
					$hiddenform->appendChild($xml->importNode($field, true));
				}
			}
			catch (Exception $e)
			{
				// Ignore this type for now.
			}
		}

		// And finally load the form into the JForm
		$form->load($xml->saveXML(), true, '/form/*');
	}

	/**
	 * Manipulates the $field->value before the field is being passed to
	 * onCustomFieldsPrepareField.
	 *
	 * @param string $context
	 * @param object $item
	 * @param \stdClass $field
	 *
	 * @return void
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function onCustomFieldsBeforePrepareField($context, $item, $field)
	{
		// Check if the field should be processed by us
		if (!$this->isTypeSupported($field->type))
		{
			return;
		}

		$decoded_value = json_decode($field->value, true);
		if (!$decoded_value || !is_array($decoded_value))
		{
			return;
		}

		$field->value = $decoded_value;
	}

	/**
	 * Renders this fields value by rendering all subfields of this subform
	 * and joining all those rendered subfields. Additionally stores the value
	 * and raw value of all rendered subfields into $field->subfield_rows.
	 *
	 * @param string $context
	 * @param object $item
	 * @param \stdClass $field
	 *
	 * @return string
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function onCustomFieldsPrepareField($context, $item, $field)
	{
		// Check if the field should be processed by us
		if (!$this->isTypeSupported($field->type))
		{
			return;
		}

		// If we dont have any subfields (or values for them), nothing to do.
		if (!is_array($field->value) || count($field->value) < 1)
		{
			return;
		}

		// Get the field params
		$field_params = $this->getParamsFromField($field);

		/**
		 * Placeholder to hold all subform rows (if this subform field is repeatable)
		 * and per array entry a \stdClass object which holds all the rendered values
		 * of the configured subfields.
		 */
		$final_values = array();
		/**
		 * Placeholder to hold all subform rows (if this subform field is repeatable)
		 * and per array entry a \stdClass object which holds another \stdClass object
		 * for each configured subfield, which then again holds the rendered value and
		 * the raw value of each subfield.
		 */
		$subfield_rows = array();

		// Create an array with entries being subform forms, and if not repeatable,
		// containing only one element.
		$rows = $field->value;
		if ($field_params->get('repeat', '1') == '0')
		{
			$rows = array($field->value);
		}
		// Iterate over each row of the data
		foreach ($rows as $row)
		{
			// The rendered values for this row, indexed by the name of the subfield
			$row_values = new \stdClass;
			// Holds for all subfields (indexed by their name) for this row their rendered and raw value.
			$row_subfields = new \stdClass();
			// For each row, iterate over all the subfields
			foreach ($this->getSubfieldsFromField($field) as $_subfield)
			{
				// Clone this virtual subfield to not interfere with the other rows
				$subfield = (clone $_subfield);
				// Just to be sure, unset this subfields value (and rawvalue)
				$subfield->rawvalue = $subfield->value = '';
				// If we have data for this field in the current row
				if (isset($row[$subfield->name]))
				{
					// Take over the data into our virtual subfield
					$subfield->rawvalue = $subfield->value = trim($row[$subfield->name]);
				}

				// Do we want to render the value of our fields?
				if ($field_params->get('render_values', '1') == '1')
				{
					// Do we have a rendercache entry for this type?
					if (!isset($this->renderCache[$subfield->type]))
					{
						$this->renderCache[$subfield->type] = array();
					}

					// Lets see if we have a fast in-memory result for this
					if (isset($this->renderCache[$subfield->type][$subfield->rawvalue]))
					{
						$subfield->value = $this->renderCache[$subfield->type][$subfield->rawvalue];
					}
					else
					{
						// Render this virtual subfield
						$subfield->value = \JEventDispatcher::getInstance()->trigger(
							'onCustomFieldsPrepareField',
							array($context, $item, $subfield)
						);
						$this->renderCache[$subfield->type][$subfield->rawvalue] = $subfield->value;
					}
					if (is_array($subfield->value))
					{
						$subfield->value = implode(' ', $subfield->value);
					}
				}

				// Store this subfields rendered value into our $row_values object
				$row_values->{$subfield->name} = $subfield->value;
				// Store the value and rawvalue of this subfield into our $row_subfields object
				$row_subfields->{$subfield->name} = new \stdClass();
				$row_subfields->{$subfield->name}->value = $subfield->value;
				$row_subfields->{$subfield->name}->rawvalue = $subfield->rawvalue;
			}
			// Store all the rendered subfield values of this row
			$final_values[] = $row_values;
			// Store all the rendered and raw subfield values of this row
			$subfield_rows[] = $row_subfields;
		}
		/**
		 * Store all the rendered and raw values of this subfield rows in $field->subfield_rows,
		 * because we maybe want to be able to have access to the rendered (and raw)
		 * value of each row and subfield.
		 */
		$field->subfield_rows = $subfield_rows;
		/**
		 * Store the renderer per-row subfield values in $field->value, which
		 * will be rendered (combined into one rendered string) by our parent next.
		 */
		$field->value = $final_values;

		return parent::onCustomFieldsPrepareField($context, $item, $field);
	}

	/**
	 * Returns a DOMElement which is the child of $orig_parent and represents
	 * the form XML definition for this subform field.
	 *
	 * @param \stdClass $field
	 * @param DOMElement $orig_parent
	 * @param JForm $form
	 *
	 * @return \DOMElement
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function onCustomFieldsPrepareDom($field, DOMElement $orig_parent, JForm $form)
	{
		// Call the onCustomFieldsPrepareDom method on FieldsPlugin
		// This will create a new 'field' DOMElement with type=subform
		$parent_field = parent::onCustomFieldsPrepareDom($field, $orig_parent, $form);
		if (!$parent_field)
		{
			return $parent_field;
		}

		// Get the configured parameters for this subform field
		$field_params = $this->getParamsFromField($field);

		// If this subform should be repeatable, set some attributes on the subform element
		if ($field_params->get('repeat', '1') == '1')
		{
			$parent_field->setAttribute('multiple', 'true');
			$parent_field->setAttribute('layout', 'joomla.form.field.subform.repeatable-table');
		}

		// Create a child 'form' DOMElement under the field[type=subform] element.
		$parent_fieldset = $parent_field->appendChild(new DOMElement('form'));
		$parent_fieldset->setAttribute('hidden', 'true');
		$parent_fieldset->setAttribute('name', ($field->name . '_modal'));
		// If this subform should be repeatable, set some attributes on the modal
		if ($field_params->get('repeat', '1') == '1')
		{
			$parent_fieldset->setAttribute('repeat', 'true');
		}

		// Iterate over the configured fields of this subform
		foreach ($this->getSubfieldsFromField($field) as $subfield)
		{
			// Let the relevant plugins do their work and insert the correct
			// DOMElement's into our $parent_fieldset.
			\JEventDispatcher::getInstance()->trigger(
				'onCustomFieldsPrepareDom',
				array($subfield, $parent_fieldset, $form)
			);
		}

		return $parent_field;
	}

	/**
	 * Returns an array of all options configured for this field.
	 *
	 * @param \stdClass $field
	 *
	 * @return \stdClass[]
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected function getOptionsFromField(\stdClass $field)
	{
		$result = array();

		// Fetch the options from the plugin
		$params = $this->getParamsFromField($field);
		foreach ($params->get('options', array()) as $option)
		{
			$result[] = (object) $option;
		}

		return $result;
	}

	/**
	 * Returns the configured params for a given subform field.
	 *
	 * @param \stdClass $field
	 *
	 * @return Joomla\Registry\Registry
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected function getParamsFromField(\stdClass $field)
	{
		$params = clone($this->params);
		if (isset($field->fieldparams) && is_object($field->fieldparams))
		{
			$params->merge($field->fieldparams);
		}
		return $params;
	}

	/**
	 * Returns an array of all subfields for this subform field.
	 *
	 * @param stdClass $field
	 *
	 * @return \stdClass[]
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected function getSubfieldsFromField(\stdClass $field)
	{
		$result = array();

		foreach ($this->getOptionsFromField($field) as $option)
		{
			/* @TODO Better solution to this? */
			$subfield = (clone $field);
			$subfield->id = null;
			$subfield->title = $option->label;
			$subfield->name = $option->name;
			$subfield->type = $option->type;
			$subfield->required = '0';
			$subfield->default_value = $option->default_value;
			$subfield->label = $option->label;
			$subfield->description = $option->description;

			$result[] = $subfield;
		}

		return $result;
	}

	/**
	 * Recursively prefixes the 'name' attribute of $node with $prefix
	 *
	 * @param DOMElement $node
	 * @param string $prefix
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected function rewriteNodeNameRecursive(DOMElement $node, $prefix)
	{
		if ($node->hasAttribute('name'))
		{
			$node->setAttribute('name', ($prefix . $node->getAttribute('name')));
		}

		foreach ($node->childNodes as $childNode)
		{
			if ($childNode instanceof DOMElement)
			{
				$this->rewriteNodeNameRecursive($childNode, $prefix);
			}
		}
	}
}
