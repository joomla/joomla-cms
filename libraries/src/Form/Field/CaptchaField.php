<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Captcha\Captcha;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;

/**
 * Captcha field.
 *
 * @since  2.5
 */
class CaptchaField extends FormField
{
	/**
	 * The field type.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $type = 'Captcha';

	/**
	 * The captcha base instance of our type.
	 *
	 * @var Captcha
	 */
	protected $_captcha;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to get the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.2
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'plugin':
			case 'namespace':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to set the value.
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
			case 'plugin':
			case 'namespace':
				$this->$name = (string) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   2.5
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$result = parent::setup($element, $value, $group);

		$app = Factory::getApplication();

		$default = $app->get('captcha');

		if ($app->isClient('site'))
		{
			$default = $app->getParams()->get('captcha', $default);
		}

		$plugin = $this->element['plugin'] ?
			(string) $this->element['plugin'] :
			$default;

		$this->plugin = $plugin;

		if ($plugin === 0 || $plugin === '0' || $plugin === '' || $plugin === null)
		{
			$this->hidden = true;

			return false;
		}
		else
		{
			// Force field to be required. There's no reason to have a captcha if it is not required.
			// Obs: Don't put required="required" in the xml file, you just need to have validate="captcha"
			$this->required = true;

			if (strpos($this->class, 'required') === false)
			{
				$this->class .= ' required';
			}
		}

		$this->namespace = $this->element['namespace'] ? (string) $this->element['namespace'] : $this->form->getName();

		try
		{
			// Get an instance of the captcha class that we are using
			$this->_captcha = Captcha::getInstance($this->plugin, array('namespace' => $this->namespace));

			/**
			 * Give the captcha instance a possibility to react on the setup-process,
			 * e.g. by altering the XML structure of the field, for example hiding the label
			 * when using invisible captchas.
			 */
			$this->_captcha->setupField($this, $element);
		}
		catch (\RuntimeException $e)
		{
			$this->_captcha = null;
			\JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return $result;
	}

	/**
	 * Method to get the field input.
	 *
	 * @return  string  The field input.
	 *
	 * @since   2.5
	 */
	protected function getInput()
	{
		if ($this->hidden || $this->_captcha == null)
		{
			return '';
		}

		try
		{
			return $this->_captcha->display($this->name, $this->id, $this->class);
		}
		catch (\RuntimeException $e)
		{
			\JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}
		return '';
	}
}
