<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Color Form Field class for the Joomla Platform.
 * This implementation is designed to be compatible with HTML5's <input type="color">
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @link        http://www.w3.org/TR/html-markup/input.color.html
 * @since       11.3
 */
class JFormFieldColor extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.3
	 */
	protected $type = 'Color';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.3
	 */
	protected function getInput()
	{
		// Control value can be: hue (default), saturation, brightness, wheel or simpel
		$control = (string) $this->element['control'];

		// Position of the panel can be: right (default), left, top or bottom
		$position = $this->element['position'] ? (string) $this->element['position'] : 'right';
		$position = ' data-position="' . $position . '"';

		$onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';
		$class = (string) $this->element['class'];

		$color = strtolower($this->value);

		if (!$color || in_array($color, array('none', 'transparent')))
		{
			$color = 'none';
		}
		elseif ($color['0'] != '#')
		{
			$color = '#' . $color;
		}

		if ($control == 'simple')
		{
			$class = ' class="' . trim('simplecolors chzn-done ' . $class) . '"';
			JHtml::_('behavior.simplecolorpicker');

			$colors = strtolower((string) $this->element['colors']);

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
					'#000000'
				);
			}
			else
			{
				$colors = explode(',', $colors);
			}

			$split = (int) $this->element['split'];

			if (!$split)
			{
				$count = count($colors);

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

			$split = $split ? $split : 3;

			$html = array();
			$html[] = '<select name="' . $this->name . '" id="' . $this->id . '"'
				. $class . $position . $onchange . ' style="visibility:hidden;width:22px;height:1px">';

			foreach ($colors as $i => $c)
			{
				$html[] = '<option' . ($c == $color ? ' selected="selected"' : '') . '>' . $c . '</option>';

				if (($i + 1) % $split == 0)
				{
					$html[] = '<option>-</option>';
				}
			}
			$html[] = '</select>';

			return implode('', $html);
		}
		else
		{
			$class = ' class="' . trim('minicolors ' . $class) . '"';
			$control = $control ? ' data-control="' . $control . '"' : '';
			$disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';

			JHtml::_('behavior.colorpicker');

			return '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="'
				. htmlspecialchars($color, ENT_COMPAT, 'UTF-8') . '"' . $class . $position . $control . $disabled . $onchange . '/>';
		}
	}
}
