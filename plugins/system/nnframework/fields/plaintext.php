<?php
/**
 * Element: PlainText
 * Displays plain text as element
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
 * PlainText Element
 */
class nnFieldPlainText
{
	var $_version = '12.6.4';

	function getInput($name, $id, $value, $params, $children)
	{
		// Load common functions
		require_once JPATH_PLUGINS.'/system/nnframework/helpers/text.php';

		$this->params = $params;

		$description = ($value != '') ? $value : $this->def('description');

		// variables
		$v1 = JText::_($this->def('var1'));
		$v2 = JText::_($this->def('var2'));
		$v3 = JText::_($this->def('var3'));
		$v4 = JText::_($this->def('var4'));
		$v5 = JText::_($this->def('var5'));

		$html = JText::sprintf($description, $v1, $v2, $v3, $v4, $v5);
		$html = trim(NNText::html_entity_decoder($html));
		$html = str_replace('&quot;', '"', $html);
		$html = str_replace('span style="font-family:monospace;"', 'span class="nn_code"', $html);

		if ($this->def('label') || $value) {
			// display as label if there is more than just a description
			$html = '<fieldset id="'.$id.'" class="radio"><label>'.$html.'</label></fieldset>';
		}

		return $html;
	}

	private function def($val, $default = '')
	{
		return (isset($this->params[$val]) && (string) $this->params[$val] != '') ? (string) $this->params[$val] : $default;
	}
}

class JFormFieldNN_PlainText extends JFormField
{
	/**
	 * The form field type
	 *
	 * @var		string
	 */
	public $type = 'PlainText';

	protected function getLabel()
	{
		$this->_nnfield = new nnFieldPlainText();

		$attribs = $this->element->attributes();
		$label = (string) $attribs['label'];
		if (!$label != '') {
			return '';
		}
		return parent::getLabel();
	}

	protected function getInput()
	{
		return $this->_nnfield->getInput($this->name, $this->id, $this->value, $this->element->attributes(), $this->element->children());
	}
}