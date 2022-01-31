<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;

/**
 * Color Form Field class for the Joomla Platform.
 * This implementation is designed to be compatible with HTML5's `<input type="color">`
 *
 * @link   https://html.spec.whatwg.org/multipage/input.html#color-state-(type=color)
 * @since  1.7.3
 */
class ColorField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.7.3
	 */
	protected $type = 'Color';

	/**
	 * The control.
	 *
	 * @var    mixed
	 * @since  3.2
	 */
	protected $control = 'hue';

	/**
	 * Default color when there is no value.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $default;

	/**
	 * The type of value the slider should display: 'hue', 'saturation' or 'light'.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $display = 'hue';

	/**
	 * The format.
	 *
	 * @var    string
	 * @since  3.6.0
	 */
	protected $format = 'hex';

	/**
	 * The keywords (transparent,initial,inherit).
	 *
	 * @var    string
	 * @since  3.6.0
	 */
	protected $keywords = '';

	/**
	 * The position.
	 *
	 * @var    mixed
	 * @since  3.2
	 */
	protected $position = 'default';

	/**
	 * The colors.
	 *
	 * @var    mixed
	 * @since  3.2
	 */
	protected $colors;

	/**
	 * Shows preview of the selected color
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $preview = false;

	/**
	 * Color format to use when value gets saved
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $saveFormat = 'hex';

	/**
	 * The split.
	 *
	 * @var    integer
	 * @since  3.2
	 */
	protected $split = 3;

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  3.5
	 */
	protected $layout = 'joomla.form.field.color';

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
			case 'colors':
			case 'control':
			case 'default':
			case 'display':
			case 'exclude':
			case 'format':
			case 'keywords':
			case 'preview':
			case 'saveFormat':
			case 'split':
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
			case 'colors':
			case 'control':
			case 'default':
			case 'display':
			case 'exclude':
			case 'format':
			case 'keywords':
			case 'saveFormat':
				$this->$name = (string) $value;
				break;
			case 'split':
				$this->$name = (int) $value;
				break;
			case 'preview':
				$this->$name = (boolean) $value;
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
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
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
			$this->colors     = (string) $this->element['colors'];
			$this->control    = isset($this->element['control']) ? (string) $this->element['control'] : 'hue';
			$this->default    = (string) $this->element['default'];
			$this->display    = isset($this->element['display']) ? (string) $this->element['display'] : 'hue';
			$this->format     = isset($this->element['format']) ? (string) $this->element['format'] : 'hex';
			$this->keywords   = (string) $this->element['keywords'];
			$this->position   = isset($this->element['position']) ? (string) $this->element['position'] : 'default';
			$this->preview    = isset($this->element['preview']) ? (string) $this->element['preview'] : false;
			$this->saveFormat = isset($this->element['saveFormat']) ? (string) $this->element['saveFormat'] : 'hex';
			$this->split      = isset($this->element['split']) ? (int) $this->element['split'] : 3;
		}

		return $return;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   1.7.3
	 */
	protected function getInput()
	{
		// Switch the layouts
		if ($this->control === 'simple' || $this->control === 'slider')
		{
			$this->layout .= '.' . $this->control;
		}
		else
		{
			$this->layout .= '.advanced';
		}

		// Trim the trailing line in the layout file
		return rtrim($this->getRenderer($this->layout)->render($this->getLayoutData()), PHP_EOL);
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since 3.5
	 */
	protected function getLayoutData()
	{
		$lang  = Factory::getApplication()->getLanguage();
		$data  = parent::getLayoutData();
		$color = strtolower($this->value);
		$color = !$color && $color !== '0' ? '' : $color;

		// Position of the panel can be: right (default), left, top or bottom (default RTL is left)
		$position = ' data-position="' . (($lang->isRtl() && $this->position === 'default') ? 'left' : $this->position) . '"';

		if ($color === '' || \in_array($color, array('none', 'transparent')))
		{
			$color = 'none';
		}
		elseif ($color[0] !== '#' && $this->format === 'hex')
		{
			$color = '#' . $color;
		}

		switch ($this->control)
		{
			case 'simple':
				$controlModeData = $this->getSimpleModeLayoutData();
				break;
			case 'slider':
				$controlModeData = $this->getSliderModeLayoutData();
				break;
			case 'advanced':
			default:
				$controlModeData = $this->getAdvancedModeLayoutData($lang);
				break;
		}

		$extraData = array(
			'color'    => $color,
			'format'   => $this->format,
			'keywords' => $this->keywords,
			'position' => $position,
			'validate' => $this->validate,
		);

		return array_merge($data, $extraData, $controlModeData);
	}

	/**
	 * Method to get the data for the simple mode to be passed to the layout for rendering.
	 *
	 * @return  array
	 *
	 * @since 3.5
	 */
	protected function getSimpleModeLayoutData()
	{
		$colors = strtolower($this->colors);

		if (empty($colors))
		{
			$colors = array(
				'none',
				'#049cdb',
				'#46a546',
				'#9d261d',
				'#ffc40d',
				'#f89406',
				'#c3325f',
				'#7a43b6',
				'#ffffff',
				'#999999',
				'#555555',
				'#000000',
			);
		}
		else
		{
			$colors = explode(',', $colors);
		}

		if (!$this->split)
		{
			$count = \count($colors);

			if ($count % 5 == 0)
			{
				$split = 5;
			}
			else
			{
				if ($count % 4 == 0)
				{
					$split = 4;
				}
			}
		}

		$split = $this->split ?: 3;

		return array(
			'colors' => $colors,
			'split'  => $split,
		);
	}

	/**
	 * Method to get the data for the advanced mode to be passed to the layout for rendering.
	 *
	 * @param   object  $lang  The language object
	 *
	 * @return  array
	 *
	 * @since   3.5
	 */
	protected function getAdvancedModeLayoutData($lang)
	{
		return array(
			'colors'  => $this->colors,
			'control' => $this->control,
			'lang'    => $lang,
		);
	}

	/**
	 * Method to get the data for the slider
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	protected function getSliderModeLayoutData()
	{
		return array(
			'default'    => $this->default,
			'display'    => $this->display,
			'preview'    => $this->preview,
			'saveFormat' => $this->saveFormat,
		);
	}
}
