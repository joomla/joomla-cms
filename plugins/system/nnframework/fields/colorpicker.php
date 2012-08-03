<?php
/**
 * Element: ColorPicker
 * Displays a textfield with a color picker
 *
 * @package			NoNumber Framework
 * @version			12.6.4
 *
 * @author			Peter van Westen <peter@nonumber.nl>
 * @link			http://www.nonumber.nl
 * @copyright		Copyright © 2012 NoNumber All Rights Reserved
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

/**
 * ColorPicker Element
 *
 * Available extra parameters:
 * title			The title
 */
class nnFieldColorPicker
{
	var $_version = '12.6.4';

	function getInput($name, $id, $value, $params, $children)
	{
		$this->params = $params;

		$document = JFactory::getDocument();
		$document->addStyleSheet(JURI::root(true).'/plugins/system/nnframework/fields/colorpicker/js_color_picker_v2.css?v='.$this->_version);
		$document->addScript(JURI::root(true).'/plugins/system/nnframework/fields/colorpicker/color_functions.js?v='.$this->_version);
		$document->addScript(JURI::root(true).'/plugins/system/nnframework/fields/colorpicker/js_color_picker_v2.js?v='.$this->_version);

		$value = strtoupper(preg_replace('#[^a-z0-9]#si', '', $value));
		$color = $value;
		if (!$color) {
			$color = 'DDDDDD';
		}

		$html = array();
		$html[] = '<fieldset id="'.$id.'" class="radio">';
		$html[] = '<label class="radio" for="'.$id.'" style="width:auto;min-width:0;padding-right:0;">#&nbsp;</label>';
		$html[] = '<input onclick="showColorPicker(this,this)" onchange="this.style.backgroundColor=\'#\'+this.value" style="background-color:#'.$color.';" type="text" name="'.$name.'" id="'.$id.'" value="'.$value.'" class="nn_color" maxlength="6" size="8" />';
		$html[] = '</fieldset>';

		return implode('', $html);
	}

	private function def($val, $default = '')
	{
		return (isset($this->params[$val]) && (string) $this->params[$val] != '') ? (string) $this->params[$val] : $default;
	}
}

class JFormFieldNN_ColorPicker extends JFormField
{
	/**
	 * The form field type
	 *
	 * @var		string
	 */
	public $type = 'ColorPicker';

	protected function getInput()
	{
		$this->_nnfield = new nnFieldColorPicker();
		return $this->_nnfield->getInput($this->name, $this->id, $this->value, $this->element->attributes(), $this->element->children());
	}
}