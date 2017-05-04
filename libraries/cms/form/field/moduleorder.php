<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Module Order field.
 *
 * @since  1.6
 */
class JFormFieldModuleOrder extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var     string
	 * @since   1.6
	 */
	protected $type = 'ModuleOrder';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  3.6.3
	 */
	protected $layout = 'joomla.form.field.moduleorder';

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.6.3
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'linked':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to the the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.6.3
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'linked':
				$this->$name = (string) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   3.6.3
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->linked    = isset($this->element['linked']) ? (int) $this->element['linked'] : 'position';
		}

		return $return;
	}

	/**
	 * Method to get the field input markup for the moduleorder field.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		return $this->getRenderer($this->layout)->render($this->getLayoutData());
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since  3.6.3
	 */
	protected function getLayoutData()
	{
		$data = parent::getLayoutData();

		$extraData = array(
			'ordering' => $this->form->getValue('ordering'),
			'clientId' => $this->form->getValue('client_id'),
			'name'     => $this->name,
			'token'    => JSession::getFormToken() . '=1',
			'element'  => $this->form->getName() . '_' . $this->linked
		);

		return array_merge($data, $extraData);
	}
}
