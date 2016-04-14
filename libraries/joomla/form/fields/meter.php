<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('number');

/**
 * Form Field class for the Joomla Platform.
 * Provides a meter to show value in a range.
 *
 * @link   http://www.w3.org/TR/html-markup/input.text.html#input.text
 * @since  3.2
 */
class JFormFieldMeter extends JFormFieldNumber
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $type = 'Meter';

	/**
	 * The width of the field increased or decreased.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $width;

	/**
	 * Whether the field is active or not.
	 *
	 * @var    boolean
	 * @since  3.2
	 */
	protected $active = false;

	/**
	 * Whether the field is animated or not.
	 *
	 * @var    boolean
	 * @since  3.2
	 */
	protected $animated = true;

	/**
	 * The color of the field
	 *
	 * @var    boolean
	 * @since  3.2
	 */
	protected $color;

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
			case 'active':
			case 'width':
			case 'animated':
			case 'color':
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
	 * @since   3.2
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'width':
			case 'color':
				$this->$name = (string) $value;
				break;

			case 'active':
				$value = (string) $value;
				$this->active = ($value === 'true' || $value === $name || $value === '1');
				break;

			case 'animated':
				$value = (string) $value;
				$this->animated = !($value === 'false' || $value === 'off' || $value === '0');
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
	 * @since   3.2
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->width = isset($this->element['width']) ? (string) $this->element['width'] : '';
			$this->color = isset($this->element['color']) ? (string) $this->element['color'] : '';

			$active       = (string) $this->element['active'];
			$this->active = ($active == 'true' || $active == 'on' || $active == '1');

			$animated       = (string) $this->element['animated'];
			$this->animated = !($animated == 'false' || $animated == 'off' || $animated == '0');
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
		// Initialize some field attributes.
		$width = !empty($this->width) ? ' style="width:' . $this->width . ';"' : '';
		$color = !empty($this->color) ? ' background-color:' . $this->color . ';' : '';

		$data = '';
		$data .= ' data-max="' . $this->max . '"';
		$data .= ' data-min="' . $this->min . '"';
		$data .= ' data-step="' . $this->step . '"';

		$class = 'progress ' . $this->class;
		$class .= $this->animated ? ' progress-striped' : '';
		$class .= $this->active ? ' active' : '';
		$class = ' class="' . $class . '"';

		$value = (float) $this->value;
		$value = $value < $this->min ? $this->min : $value;
		$value = $value > $this->max ? $this->max : $value;

		$data .= ' data-value="' . $this->value . '"';

		$value = ((float) ($value - $this->min) * 100) / ($this->max - $this->min);

		$html[] = '<div ' . $class . $width . $data . ' >';
		$html[] = '		<div class="bar" style="width: ' . strval($value) . '%;' . $color . '"></div>';
		$html[] = '</div>';

		return implode('', $html);
	}
}
