<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.Subfields
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::import('components.com_fields.libraries.fieldsplugin', JPATH_ADMINISTRATOR);

/**
 * Fields subfields Plugin
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgFieldsSubfields extends FieldsPlugin
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

	/**
	 * Handles the onContentPrepareForm event. Adds form definitions to relevant forms.
	 *
	 * @param   JForm         $form  The form to manipulate
	 * @param   array|object  $data  The data of the form
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function onContentPrepareForm(JForm $form, $data)
	{
		$path = $this->getFormPath($form, $data);

		if ($path === null)
		{
			return;
		}

		// Load our own form definition
		$xml = new DOMDocument;
		$xml->load($path);

		// Get this fields options
		$xmlxpath   = new DOMXPath($xml);
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
			// Skip our own subfields type, we dont want to allow subfields in subfields
			// (to ease complexity)
			if ($fieldType['type'] == 'subfields')
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
				// Try to load the XML definition file of that field type into a DOMDocument
				$subxml = new DOMDocument;
				$subxml->load($path);
				$subxmlxpath = new DOMXPath($subxml);

				// Use XPath to find the top-level `fields` and `fieldset` elements in that XML
				$toplevelElements = $subxmlxpath->evaluate(
					'/form/fields[@name="fieldparams"]|/form/fields[@name="fieldparams"]/fieldset[@name="fieldparams"]'
				);

				// Iterate over those toplevel elements, they contain data we want to preserve in our own XML
				foreach ($toplevelElements as $toplevelElement)
				{
					/* @var $toplevelElement \DOMElement */

					// E.g. we want to preserve the `addfieldpath` property by passing it to \JForm
					if ($toplevelElement->hasAttribute('addfieldpath'))
					{
						\JForm::addFieldPath(\JPATH_ROOT . $toplevelElement->getAttribute('addfieldpath'));
					}
				}

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
	 * @param   string     $context  The context
	 * @param   object     $item     The item
	 * @param   \stdClass  $field    The field
	 *
	 * @return  void
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
	 * Renders this fields value by rendering all subfields and joining all those rendered subfields.
	 * Additionally stores the value and raw value of all rendered subfields into $field->subfield_rows.
	 *
	 * @param   string     $context  The context
	 * @param   object     $item     The item
	 * @param   \stdClass  $field    The field
	 *
	 * @return  string
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
		 * Placeholder to hold all subfield rows (if this field is repeatable).
		 * Each array entry is a \stdClass object which holds all the rendered values of the configured subfields
		 * for that specific row (or only one row, if not repeatable).
		 */
		$final_values = array();

		/**
		 * Placeholder to hold all subfield rows (if this field is repeatable).
		 * Each array entry is a \stdClass object representing a row, having a \stdClass attribute for each
		 * configured subfield (named after the name of the subfield), which has a `value` and `rawvalue`
		 * attribute, holding the rendered and raw value of that subfield for that row.
		 */
		$subfield_rows = array();

		// Create an array with entries being subfields forms, and if not repeatable, containing only one element.
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
			$row_subfields = new \stdClass;

			// For each row, iterate over all the subfields
			foreach ($this->getSubfieldsFromField($field) as $_subfield)
			{
				// Clone this virtual subfield to not interfere with the other rows
				$subfield = (clone $_subfield);

				// Just to be sure, unset this subfields value (and rawvalue)
				$subfield->rawvalue = $subfield->value = '';

				// If we have data for this field in the current row
				if (isset($row[$subfield->name]) && $row[$subfield->name])
				{
					// Take over the data into our virtual subfield
					$subfield->rawvalue = $subfield->value = $row[$subfield->name];
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
					$renderCache_key = serialize($subfield->rawvalue);
					if (isset($this->renderCache[$subfield->type][$renderCache_key]))
					{
						$subfield->value = $this->renderCache[$subfield->type][$renderCache_key];
					}
					else
					{
						// Render this virtual subfield
						$subfield->value                                         = \JEventDispatcher::getInstance()->trigger(
							'onCustomFieldsPrepareField',
							array($context, $item, $subfield)
						);
						$this->renderCache[$subfield->type][$renderCache_key] = $subfield->value;
					}
				}

				// Flatten the value if it is an array (list, checkboxes, etc.) [independent of render_values]
				if (is_array($subfield->value))
				{
					$subfield->value = implode(' ', $subfield->value);
				}

				// Store this subfields rendered value into our $row_values object
				$row_values->{$subfield->name} = $subfield->value;

				// Store the value and rawvalue of this subfield into our $row_subfields object
				$row_subfields->{$subfield->name}           = new \stdClass;
				$row_subfields->{$subfield->name}->value    = $subfield->value;
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
	 * the form XML definition for this field.
	 *
	 * @param   \stdClass   $field        The field
	 * @param   DOMElement  $orig_parent  The original parent element
	 * @param   JForm       $form         The form
	 *
	 * @return  \DOMElement
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function onCustomFieldsPrepareDom($field, DOMElement $orig_parent, JForm $form)
	{
		// Call the onCustomFieldsPrepareDom method on FieldsPlugin
		$parent_field = parent::onCustomFieldsPrepareDom($field, $orig_parent, $form);

		if (!$parent_field)
		{
			return $parent_field;
		}

		// Make sure this `field` DOMElement has an attribute type=subform - our parent set this to
		// subfields, because that is our name. But we want the XML to be a subform.
		$parent_field->setAttribute('type', 'subform');

		// Get the configured parameters for this field
		$field_params = $this->getParamsFromField($field);

		// If this fields should be repeatable, set some attributes on the subform element
		if ($field_params->get('repeat', '1') == '1')
		{
			$parent_field->setAttribute('multiple', 'true');
			$parent_field->setAttribute('layout', 'joomla.form.field.subform.repeatable-table');
		}

		// Create a child 'form' DOMElement under the field[type=subform] element.
		$parent_fieldset = $parent_field->appendChild(new DOMElement('form'));
		$parent_fieldset->setAttribute('hidden', 'true');
		$parent_fieldset->setAttribute('name', ($field->name . '_modal'));

		// If this field should be repeatable, set some attributes on the modal
		if ($field_params->get('repeat', '1') == '1')
		{
			$parent_fieldset->setAttribute('repeat', 'true');
		}

		// Get the configured sub fields for this field
		$subfields = $this->getSubfieldsFromField($field);

		// If we have 5 or more of them, use the `repeatable` layout instead of the `repeatable-table`
		if (count($subfields) >= 5)
		{
			$parent_field->setAttribute('layout', 'joomla.form.field.subform.repeatable');
		}

		// Iterate over the sub fields to call prepareDom on each of those sub-fields
		foreach ($subfields as $subfield)
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
	 * @param   \stdClass  $field  The field
	 *
	 * @return  \stdClass[]
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
	 * Returns the configured params for a given field.
	 *
	 * @param   \stdClass  $field  The field
	 *
	 * @return  \Joomla\Registry\Registry
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
	 * Returns an array of all subfields for a given field.
	 *
	 * @param   \stdClass  $field  The field
	 *
	 * @return  \stdClass[]
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected function getSubfieldsFromField(\stdClass $field)
	{
		$result = array();

		foreach ($this->getOptionsFromField($field) as $option)
		{
			// The subfield starts with a bare copy of our own field
			$subfield                = (clone $field);
			$subfield->id            = null;
			$subfield->title         = $option->label;
			$subfield->fieldparams   = new Joomla\Registry\Registry;

			/**
			 * In onContentPrepareForm() we added a prefix for all type-dependant options (options for a sub-field
			 * that were in the fieldparams of that field type). We did this to could support same-named options
			 * for a sub-field for different types, because we need to construct the XML for the selection of the
			 * type before we actually know the type. So, now that we have a prefix on the type-specific options, we
			 * need to remove that prefix again, and add all those options under the fieldparams, so that we then have
			 * a valid (sub-)field of that type.
			 */
			$prefix = ('_type-' . $option->type . '_');

			// Copy all values from the subfields options into the subfield
			foreach (array_keys(get_object_vars($option)) as $key)
			{
				// If we have our prefix, copy the value into the fieldparams (and not directly into the subfield)
				if (strpos($key, $prefix) === 0)
				{
					// If this is an array, make sure to remove the prefix also from its elements
					$this->removeTypePrefixRecursive($option->{$key}, $prefix);

					$subfield->fieldparams->set(
						substr($key, strlen($prefix)), // Remove the prefix from the key
						$option->{$key}
					);
				}
				// If we have another type prefix, just ignore it
				elseif (strpos($key, '_type-') === 0)
				{
					continue;
				}
				// Else copy the value from the option directly to the subfield
				else
				{
					$subfield->{$key} = &$option->{$key};
				}
			}

			$result[] = $subfield;
		}

		return $result;
	}

	/**
	 * Recursively prefixes the 'name' attribute of $node with $prefix
	 *
	 * @param   \DOMElement  $node    The node
	 * @param   string       $prefix  The prefix
	 *
	 * @return  void
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

	/**
	 * Recursively removes our type prefix from array attribute names.
	 *
	 * @param   array   &$var    The object
	 * @param   string  $prefix  The prefix
	 *
	 * @return  void
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected function removeTypePrefixRecursive(&$var, $prefix)
	{
		if (!is_array($var))
		{
			return;
		}

		// Iterate over each key in $var (done this way because we manipulate $var later on, so just to be sure...)
		foreach (array_keys($var) as $key)
		{
			// If that key start with the prefix that shall be removed
			if (strpos($key, $prefix) === 0)
			{
				// Construct the new key without the prefix
				$new_key = substr($key, strlen($prefix));

				// Insert the new key and delete the old one
				$var[$new_key] = &$var[$key];
				unset($var[$key]);

				$key = $new_key;
			}

			// Now do the recursion
			$this->removeTypePrefixRecursive($var[$key], $prefix);
		}
	}
}
