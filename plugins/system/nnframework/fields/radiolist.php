<?php
/**
 * Element: Radio List
 * Displays a list of radio items with a break after each item
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
 * Radio List Element
 */
class nnFieldRadioList
{
	var $_version = '12.6.4';

	function getInput($name, $id, $value, $params, $children)
	{
		$this->params = $params;

		$html = array();

		$html[] = '<fieldset id="'.$id.'" class="radio">';

		$options = array();
		$i = 0;
		foreach ($children as $option) {
			$i++;
			$checked = ((string) $option['value'] == (string) $value) ? ' checked="checked"' : '';
			$text = trim((string) $option);
			$value = htmlspecialchars((string) $option['value'], ENT_COMPAT, 'UTF-8');
			$html[] = '<input type="radio" id="'.$id.$i.'" name="'.$name.'"'.
				' value="'.$value.'"'
				.$checked.' class="radio" style="clear:left;" />';

			$html[] = '<label for="'.$id.$i.'" class="radio" style="width:auto;min-width:none;">'.JText::_($text).'</label>';
		}

		$html[] = '</fieldset>';

		return implode('', $html);
	}

	private function def($val, $default = '')
	{
		return (isset($this->params[$val]) && (string) $this->params[$val] != '') ? (string) $this->params[$val] : $default;
	}
}

class JFormFieldNN_RadioList extends JFormField
{
	/**
	 * The form field type
	 *
	 * @var		string
	 */
	public $type = 'RadioList';

	protected function getInput()
	{
		$this->_nnfield = new nnFieldRadioList();
		return $this->_nnfield->getInput($this->name, $this->id, $this->value, $this->element->attributes(), $this->element->children());
	}
}