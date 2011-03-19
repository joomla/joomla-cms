<?php
/**
 * Inspector classes for the forms library.
 */
require_once JPATH_PLATFORM.'/joomla/form/form.php';
require_once JPATH_PLATFORM.'/joomla/form/formfield.php';

/**
 * @package		Joomla.UnitTest
 * @subpackage	Form
 */
class JFormInspector extends JForm
{
	public static function addNode(SimpleXMLElement $source, SimpleXMLElement $new)
	{
		return parent::addNode($source, $new);
	}

	public static function mergeNode(SimpleXMLElement $source, SimpleXMLElement $new)
	{
		return parent::mergeNode($source, $new);
	}

	public static function mergeNodes(SimpleXMLElement $source, SimpleXMLElement $new)
	{
		return parent::mergeNodes($source, $new);
	}

	public function filterField($element, $value)
	{
		return parent::filterField($element, $value);
	}

	public function findField($name, $group = null)
	{
		return parent::findField($name, $group);
	}

	public function findGroup($group)
	{
		return parent::findGroup($group);
	}

	public function findFieldsByGroup($group = null, $nested = false)
	{
		return parent::findFieldsByGroup($group, $nested);
	}

	public function findFieldsByFieldset($name)
	{
		return parent::findFieldsByFieldset($name);
	}

	public function getData()
	{
		return $this->data;
	}

	/**
	 * @return	array	Return the protected options array.
	 */
	public function getOptions()
	{
		return $this->options;
	}

	public function getXML()
	{
		return $this->xml;
	}

	public function loadField($element, $group = null, $value = null)
	{
		return parent::loadField($element, $group, $value);
	}

	public function loadFieldType($type, $new = true)
	{
		return parent::loadFieldType($type, $new);
	}

	public function loadRuleType($type, $new = true)
	{
		return parent::loadRuleType($type, $new);
	}

	public function validateField($element, $group = null, $value = null, $input = null)
	{
		return parent::validateField($element, $group, $value, $input);
	}
}

/**
 * @package		Joomla.UnitTest
 * @subpackage	Form
 */
class JFormFieldInspector extends JFormField
{
	public function getInput()
	{
		return null;
	}

	public function getForm()
	{
		return $this->form;
	}

	public function getId($fieldId, $fieldName)
	{
		return parent::getId($fieldId, $fieldName);
	}

	public function getLabel()
	{
		return parent::getLabel();
	}

	public function getTitle()
	{
		return parent::getTitle();
	}
}
