<?php
/**
 * Element: DateTime
 * Element to display the date and time
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
 * DateTime Element
 */
class nnFieldDateTime
{
	var $_version = '12.6.4';

	function getInput($name, $id, $value, $params, $children)
	{
		$this->params = $params;

		$label = $this->def('label');
		$format = $this->def('format');

		$config = JFactory::getConfig();
		$date = JFactory::getDate();
		$date->setTimeZone(new DateTimeZone($config->getValue('config.offset')));

		if ($format) {
			$html = $date->toFormat($format, 1);
		} else {
			$html = $date->toFormat('', 1);
		}

		if ($label) {
			$html = JText::sprintf($label, $html);
		}

		return $html;
	}

	private function def($val, $default = '')
	{
		return (isset($this->params[$val]) && (string) $this->params[$val] != '') ? (string) $this->params[$val] : $default;
	}
}

class JFormFieldNN_DateTime extends JFormField
{
	/**
	 * The form field type
	 *
	 * @var		string
	 */
	public $type = 'DateTime';

	protected function getLabel()
	{
		return;
	}

	protected function getInput()
	{
		$this->_nnfield = new nnFieldDateTime();
		return $this->_nnfield->getInput($this->name, $this->id, $this->value, $this->element->attributes(), $this->element->children());
	}
}