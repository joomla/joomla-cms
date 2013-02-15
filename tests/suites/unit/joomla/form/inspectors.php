<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Inspector classes for the forms library.
 */
require_once JPATH_PLATFORM . '/joomla/form/form.php';
require_once JPATH_PLATFORM . '/joomla/form/field.php';

/**
 * JFormInspector class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @since       11.1
 */
class JFormInspector extends JForm
{
	/**
	 * Test...
	 *
	 * @param   SimpleXMLElement  $source  @todo
	 * @param   SimpleXMLElement  $new     @todo
	 *
	 * @return void
	 */
	public static function addNode(SimpleXMLElement $source, SimpleXMLElement $new)
	{
		return parent::addNode($source, $new);
	}

	/**
	 * Test...
	 *
	 * @param   SimpleXMLElement  $source  @todo
	 * @param   SimpleXMLElement  $new     @todo
	 *
	 * @return void
	 */
	public static function mergeNode(SimpleXMLElement $source, SimpleXMLElement $new)
	{
		return parent::mergeNode($source, $new);
	}

	/**
	 * Test...
	 *
	 * @param   SimpleXMLElement  $source  @todo
	 * @param   SimpleXMLElement  $new     @todo
	 *
	 * @return void
	 */
	public static function mergeNodes(SimpleXMLElement $source, SimpleXMLElement $new)
	{
		return parent::mergeNodes($source, $new);
	}

	/**
	 * Test...
	 *
	 * @param   string  $element  @todo
	 * @param   mixed   $value    @todo
	 *
	 * @return void
	 */
	public function filterField($element, $value)
	{
		return parent::filterField($element, $value);
	}

	/**
	 * Test...
	 *
	 * @param   string  $name   @todo
	 * @param   null    $group  @todo
	 *
	 * @return void
	 */
	public function findField($name, $group = null)
	{
		return parent::findField($name, $group);
	}

	/**
	 * Test...
	 *
	 * @param   string  $group  @todo
	 *
	 * @return void
	 */
	public function findGroup($group)
	{
		return parent::findGroup($group);
	}

	/**
	 * Test...
	 *
	 * @param   null  $group   @todo
	 * @param   bool  $nested  @todo
	 *
	 * @return void
	 */
	public function findFieldsByGroup($group = null, $nested = false)
	{
		return parent::findFieldsByGroup($group, $nested);
	}

	/**
	 * Test...
	 *
	 * @param   string  $name  @todo
	 *
	 * @return void
	 */
	public function findFieldsByFieldset($name)
	{
		return parent::findFieldsByFieldset($name);
	}

	/**
	 * Test...
	 *
	 * @return void
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Test...
	 *
	 * @return  array    Return the protected options array.
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * Test...
	 *
	 * @return string
	 */
	public function getXML()
	{
		return $this->xml;
	}

	/**
	 * Test...
	 *
	 * @param   string  $element  @todo
	 * @param   null    $group    @todo
	 * @param   null    $value    @todo
	 *
	 * @return JFormField
	 */
	public function loadField($element, $group = null, $value = null)
	{
		return parent::loadField($element, $group, $value);
	}

	/**
	 * Test...
	 *
	 * @param   string  $type  @todo
	 * @param   bool    $new   @todo
	 *
	 * @return JFormField
	 */
	public function loadFieldType($type, $new = true)
	{
		return parent::loadFieldType($type, $new);
	}

	/**
	 * Test...
	 *
	 * @param   string  $type  @todo
	 * @param   bool    $new   @todo
	 *
	 * @return JFormRule
	 */
	public function loadRuleType($type, $new = true)
	{
		return parent::loadRuleType($type, $new);
	}

	/**
	 * Test...
	 *
	 * @param   SimpleXMLElement  $element  @todo
	 * @param   null              $group    @todo
	 * @param   null              $value    @todo
	 * @param   null              $input    @todo
	 *
	 * @return boolean
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
 *
 * @since       11.1
 */
class JFormFieldInspector extends JFormField
{
	/**
	 * Test...
	 *
	 * @param   string  $name  Element name
	 *
	 * @return mixed
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
	 * Test...
	 *
	 * @return void
	 */
	public function getInput()
	{
		return null;
	}

	/**
	 * Test...
	 *
	 * @return JForm
	 */
	public function getForm()
	{
		return $this->form;
	}

	/**
	 * Test...
	 *
	 * @param   string  $fieldId    Field id
	 * @param   string  $fieldName  Field name
	 *
	 * @return string
	 */
	public function getId($fieldId, $fieldName)
	{
		return parent::getId($fieldId, $fieldName);
	}

	/**
	 * Test...
	 *
	 * @return string
	 */
	public function getLabel()
	{
		return parent::getLabel();
	}

	/**
	 * Test...
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return parent::getTitle();
	}
}
