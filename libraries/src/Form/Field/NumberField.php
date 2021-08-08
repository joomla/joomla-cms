<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/**
 * Form Field class for the Joomla Platform.
 * Provides a one line text box with up-down handles to set a number in the field.
 *
 * @link   https://html.spec.whatwg.org/multipage/input.html#number-state-(type=number)
 * @since  3.2
 */
class NumberField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $type = 'Number';

	/**
	 * The allowable maximum value of the field.
	 *
	 * @var    float
	 * @since  3.2
	 */
	protected $max = null;

	/**
	 * The allowable minimum value of the field.
	 *
	 * @var    float
	 * @since  3.2
	 */
	protected $min = null;

	/**
	 * The step by which value of the field increased or decreased.
	 *
	 * @var    float
	 * @since  3.2
	 */
	protected $step = 0;

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  3.7
	 */
	protected $layout = 'joomla.form.field.number';

	/**
	 * The parent class of the field
	 *
	 * @var  string
	 * @since 4.0.0
	 */
	protected $parentclass;

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
			case 'max':
			case 'min':
			case 'step':
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
			case 'step':
			case 'min':
			case 'max':
				$this->$name = (float) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a Form object to the field.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     FormField::setup()
	 * @since   3.2
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			// It is better not to force any default limits if none is specified
			$this->max  = isset($this->element['max']) ? (float) $this->element['max'] : null;
			$this->min  = isset($this->element['min']) ? (float) $this->element['min'] : null;
			$this->step = isset($this->element['step']) ? (float) $this->element['step'] : 1;
		}

		return $return;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.2
	 */
	protected function getInput()
	{
		if ($this->element['useglobal'])
		{
			$component = Factory::getApplication()->input->getCmd('option');

			// Get correct component for menu items
			if ($component === 'com_menus')
			{
				$link      = $this->form->getData()->get('link');
				$uri       = new Uri($link);
				$component = $uri->getVar('option', 'com_menus');
			}

			$params = ComponentHelper::getParams($component);
			$value  = $params->get($this->fieldname);

			// Try with global configuration
			if (\is_null($value))
			{
				$value = Factory::getApplication()->get($this->fieldname);
			}

			// Try with menu configuration
			if (\is_null($value) && Factory::getApplication()->input->getCmd('option') === 'com_menus')
			{
				$value = ComponentHelper::getParams('com_menus')->get($this->fieldname);
			}

			if (!\is_null($value))
			{
				$value = (string) $value;

				$this->hint = Text::sprintf('JGLOBAL_USE_GLOBAL_VALUE', $value);
			}
		}

		// Trim the trailing line in the layout file
		return rtrim($this->getRenderer($this->layout)->render($this->getLayoutData()), PHP_EOL);
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since 3.7
	 */
	protected function getLayoutData()
	{
		$data = parent::getLayoutData();

		// Initialize some field attributes.
		$extraData = array(
			'max'   => $this->max,
			'min'   => $this->min,
			'step'  => $this->step,
			'value' => $this->value,
		);

		return array_merge($data, $extraData);
	}
}
