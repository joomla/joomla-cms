<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * JFormInspector class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       3.1
 */
class JFormInspector extends JForm
{
	/**
	 * Adds a new child SimpleXMLElement node to the source.
	 *
	 * @param   SimpleXMLElement  $source  The source element on which to append.
	 * @param   SimpleXMLElement  $new     The new element to append.
	 *
	 * @return  void
	 */
	public static function addNode(SimpleXMLElement $source, SimpleXMLElement $new)
	{
		parent::addNode($source, $new);
	}

	/**
	 * Update the attributes of a child node
	 *
	 * @param   SimpleXMLElement  $source  The source element on which to append the attributes
	 * @param   SimpleXMLElement  $new     The new element to append
	 *
	 * @return  void
	 */
	public static function mergeNode(SimpleXMLElement $source, SimpleXMLElement $new)
	{
		parent::mergeNode($source, $new);
	}

	/**
	 * Merges new elements into a source <fields> element.
	 *
	 * @param   SimpleXMLElement  $source  The source element.
	 * @param   SimpleXMLElement  $new     The new element to merge.
	 *
	 * @return  void
	 */
	public static function mergeNodes(SimpleXMLElement $source, SimpleXMLElement $new)
	{
		parent::mergeNodes($source, $new);
	}

	/**
	 * Method to apply an input filter to a value based on field data.
	 *
	 * @param   string  $element  The XML element object representation of the form field.
	 * @param   mixed   $value    The value to filter for the field.
	 *
	 * @return  mixed   The filtered value.
	 */
	public function filterField($element, $value)
	{
		return parent::filterField($element, $value);
	}

	/**
	 * Method to get a form field represented as an XML element object.
	 *
	 * @param   string  $name   The name of the form field.
	 * @param   string  $group  The optional dot-separated form group path on which to find the field.
	 *
	 * @return  mixed  The XML element object for the field or boolean false on error.
	 */
	public function findField($name, $group = null)
	{
		return parent::findField($name, $group);
	}

	/**
	 * Method to get a form field group represented as an XML element object.
	 *
	 * @param   string  $group  The dot-separated form group path on which to find the group.
	 *
	 * @return  mixed  An array of XML element objects for the group or boolean false on error.
	 */
	public function &findGroup($group)
	{
		return parent::findGroup($group);
	}

	/**
	 * Method to get an array of <field /> elements from the form XML document which are
	 * in a control group by name.
	 *
	 * @param   mixed    $group   The optional dot-separated form group path on which to find the fields.
	 *                            Null will return all fields. False will return fields not in a group.
	 * @param   boolean  $nested  True to also include fields in nested groups that are inside of the
	 *                            group for which to find fields.
	 *
	 * @return  mixed  Boolean false on error or array of SimpleXMLElement objects.
	 */
	public function &findFieldsByGroup($group = null, $nested = false)
	{
		return parent::findFieldsByGroup($group, $nested);
	}

	/**
	 * Method to get an array of <field /> elements from the form XML document which are
	 * in a specified fieldset by name.
	 *
	 * @param   string  $name  The name of the fieldset.
	 *
	 * @return  mixed  Boolean false on error or array of SimpleXMLElement objects.
	 */
	public function findFieldsByFieldset($name)
	{
		return parent::findFieldsByFieldset($name);
	}

	/**
	 * Return the protected data object.
	 *
	 * @return  JRegistry
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Return the protected options array.
	 *
	 * @return  array
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * Return the protected xml definition.
	 *
	 * @return  SimpleXMLElement
	 */
	public function getXML()
	{
		return $this->xml;
	}

	/**
	 * Method to load, setup and return a JFormField object based on field data.
	 *
	 * @param   string  $element  The XML element object representation of the form field.
	 * @param   string  $group    The optional dot-separated form group path on which to find the field.
	 * @param   mixed   $value    The optional value to use as the default for the field.
	 *
	 * @return  mixed  The JFormField object for the field or boolean false on error.
	 */
	public function loadField($element, $group = null, $value = null)
	{
		return parent::loadField($element, $group, $value);
	}

	/**
	 * Proxy for {@link JFormHelper::loadFieldType()}.
	 *
	 * @param   string   $type  The field type.
	 * @param   boolean  $new   Flag to toggle whether we should get a new instance of the object.
	 *
	 * @return  mixed  JFormField object on success, false otherwise.
	 */
	public function loadFieldType($type, $new = true)
	{
		return parent::loadFieldType($type, $new);
	}

	/**
	 * Proxy for JFormHelper::loadRuleType().
	 *
	 * @param   string   $type  The rule type.
	 * @param   boolean  $new   Flag to toggle whether we should get a new instance of the object.
	 *
	 * @return  mixed  JFormRule object on success, false otherwise.
	 */
	public function loadRuleType($type, $new = true)
	{
		return parent::loadRuleType($type, $new);
	}

	/**
	 * Method to validate a JFormField object based on field data.
	 *
	 * @param   SimpleXMLElement  $element  The XML element object representation of the form field.
	 * @param   string            $group    The optional dot-separated form group path on which to find the field.
	 * @param   mixed             $value    The optional value to use as the default for the field.
	 * @param   JRegistry         $input    An optional JRegistry object with the entire data set to validate
	 *                                      against the entire form.
	 *
	 * @return  mixed  Boolean true if field value is valid, Exception on failure.
	 */
	public function validateField($element, $group = null, $value = null, $input = null)
	{
		return parent::validateField($element, $group, $value, $input);
	}
}

/**
 * JFormFieldInspector class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       3.1
 */
class JFormFieldInspector extends JFormField
{
	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 */
	public function __get($name)
	{
		if ($name == 'element')
		{
			return $this->element;
		}
		else
		{
			return parent::__get($name);
		}
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	public function getInput()
	{
		return '';
	}

	/**
	 * Return the protected form object.
	 *
	 * @return  JForm
	 */
	public function getForm()
	{
		return $this->form;
	}

	/**
	 * Method to get the id used for the field input tag.
	 *
	 * @param   string  $fieldId    The field element id.
	 * @param   string  $fieldName  The field element name.
	 *
	 * @return  string  The id to be used for the field input tag.
	 */
	public function getId($fieldId, $fieldName)
	{
		return parent::getId($fieldId, $fieldName);
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return  string  The field label markup.
	 */
	public function getLabel()
	{
		return parent::getLabel();
	}

	/**
	 * Method to get the field title.
	 *
	 * @return  string  The field title.
	 */
	public function getTitle()
	{
		return parent::getTitle();
	}
}
