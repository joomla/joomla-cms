<?php
/**
 * Element: HR
 * Displays a line
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
 * HR Element
 */
class nnFieldHR
{
	var $_version = '12.6.4';

	function getInput($name, $id, $value, $params, $children)
	{
		$document = JFactory::getDocument();
		$document->addStyleSheet(JURI::root(true).'/plugins/system/nnframework/css/style.css?v='.$this->_version);

		return '<div class="panel nn_panel nn_hr"></div>';
	}

	private function def($val, $default = '')
	{
		return (isset($this->params[$val]) && (string) $this->params[$val] != '') ? (string) $this->params[$val] : $default;
	}
}

class JFormFieldNN_HR extends JFormField
{
	/**
	 * The form field type
	 *
	 * @var		string
	 */
	public $type = 'HR';

	protected function getLabel()
	{
		$this->_nnfield = new nnFieldHR();
		return;
	}

	protected function getInput()
	{
		return $this->_nnfield->getInput($this->name, $this->id, $this->value, $this->element->attributes(), $this->element->children());
	}
}