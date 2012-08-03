<?php
/**
 * Element: Editor
 * Displays an HTML editor text field
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
 * Editor Element
 *
 * Available extra parameters:
 * width			Width of the editor (default = 100%)
 * height			Width of the editor (default = 400)
 * newline			Show editor on a new line (under the other blocks)
 */
class nnFieldEditor
{
	var $_version = '12.6.4';

	function getInput($name, $id, $value, $params, $children)
	{
		$this->params = $params;

		$label = $this->def('label');
		$description = $this->def('description');
		$width = $this->def('width', '100%');
		$height = $this->def('height', 400);
		$newline = $this->def('newline');

		$value = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');

		// Get an editor object.
		$editor = JFactory::getEditor();
		$html = $editor->display($name, $value, $width, $height, true, $id);
		$html .= '<br clear="all" />';

		return $html;
	}

	function getInput15($name, $id, $value, $params, $children)
	{
		$this->params = $params;

		$label = $this->def('label');
		$description = $this->def('description');
		$width = $this->def('width', '100%');
		$height = $this->def('height', 400);
		$newline = $this->def('newline');

		$value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

		$option = JRequest::getCmd('option', '');
		if ($option == 'com_modules') {
			$name = $name.'';
		}

		$html = '';
		if ($newline) {
			$html .= JText::_($description);
			$html .= '</td></tr></table>';
			$html .= '</div></div></fieldset></div>';
			$html .= '<div class="clr"></div><div><fieldset class="adminform">';
			if ($label != '') {
				$html .= '<legend>'.JText::_($label).'</legend>';
			}
			$html .= '<div><div><div><table width="100%" class="paramlist admintable" cellspacing="1"><tr><td colspan="2" class="paramlist_value">';
		} else {
			if ($label != '') {
				$html .= '<b>'.JText::_($label).'</b><br />';
			}
			if ($description != '') {
				$html .= JText::_($description).'<br />';
			}
		}

		$editor = JFactory::getEditor();
		$html .= $editor->display($name, $value, $width, $height, '60', '20', true);
		$html .= '<br clear="all" />';

		return $html;
	}

	private function def($val, $default = '')
	{
		return (isset($this->params[$val]) && (string) $this->params[$val] != '') ? (string) $this->params[$val] : $default;
	}
}

class JFormFieldNN_Editor extends JFormField
{
	/**
	 * The form field type
	 *
	 * @var		string
	 */
	public $type = 'Editor';

	protected function getLabel()
	{
		$this->_nnfield = new nnFieldEditor();
		return;
	}

	protected function getInput()
	{
		return $this->_nnfield->getInput($this->name, $this->id, $this->value, $this->element->attributes(), $this->element->children());
	}
}