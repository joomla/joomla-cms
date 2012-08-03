<?php
/**
 * Element: Custom Field Key
 * Displays a custom key field (use in combination with customfieldvalue)
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
class nnFieldCustomFieldKey
{
	var $_version = '12.6.4';

	function getInput($name, $id, $value, $params, $children)
	{
		$this->params = $params;

		$size = ($this->def('size') ? 'size="'.$this->def('size').'"' : '');
		$class = ($this->def('class') ? 'class="'.$this->def('class').'"' : 'class="text_area"');
		$value = htmlspecialchars(html_entity_decode($value, ENT_QUOTES), ENT_QUOTES);

		JHtml::_('behavior.mootools');
		$document = JFactory::getDocument();
		$document->addScript(JURI::root(true).'/plugins/system/nnframework/js/script.js?v='.$this->_version);

		$html = '<input type="text" name="'.$name.'" id="'.$id.'" value="'.$value.'" '.$class.' '.$size.' />';
		$html = '<label for="'.$id.'" style="margin: 0;">'.$html.'</label>';

		return $html;
	}

	private function def($val, $default = '')
	{
		return (isset($this->params[$val]) && (string) $this->params[$val] != '') ? (string) $this->params[$val] : $default;
	}
}

class JFormFieldNN_CustomFieldKey extends JFormField
{
	/**
	 * The form field type
	 *
	 * @var		string
	 */
	public $type = 'CustomFieldKey';

	protected function getLabel()
	{
		$this->_nnfield = new nnFieldCustomFieldKey();
		return;
	}

	protected function getInput()
	{
		return $this->_nnfield->getInput($this->name, $this->id, $this->value, $this->element->attributes(), $this->element->children());
	}
}