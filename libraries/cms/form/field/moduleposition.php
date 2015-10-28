<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('text');

/**
 * Form Field class for the Joomla! CMS.
 *
 * @since  1.6
 */
class JFormFieldModulePosition extends JFormFieldText
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $type = 'ModulePosition';

	/**
	 * The client ID.
	 *
	 * @var    integer
	 * @since  3.2
	 */
	protected $clientId;

	/**
	* Layout to render the label
	*
	* @var  string
	*/
	protected $layout = 'joomla.form.field.moduleposition';

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.2
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'clientId':
				return $this->clientId;
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
	 * @since   3.2
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'clientId':
				$this->clientId = (string) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   3.2
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$result = parent::setup($element, $value, $group);

		if ($result == true)
		{
			// Get the client id.
			$clientId = $this->element['client_id'];

			if (!isset($clientId))
			{
				$clientName = $this->element['client'];

				if (isset($clientName))
				{
					$client = JApplicationHelper::getClientInfo($clientName, true);
					$clientId = $client->id;
				}
			}

			if (!isset($clientId) && $this->form instanceof JForm)
			{
				$clientId = $this->form->getValue('client_id');
			}

			$this->clientId = (int) $clientId;
		}

		return $result;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string	The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		$displayData = array(
			'id'       => $this->id,
			'clientId' => $this->clientId,
			'inputTag'    => parent::getInput()
		);

		return JLayoutHelper::render($this->layout, $displayData);
	}
}
