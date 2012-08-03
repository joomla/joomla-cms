<?php
/**
 * Element: Languages
 * Displays a select box of languages
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
 * Templates Element
 */
class nnFieldLanguages
{
	var $_version = '12.6.4';

	function getInput($name, $id, $value, $params, $children)
	{
		$this->params = $params;

		$size = (int) $this->def('size');
		$multiple = $this->def('multiple');
		$client = $this->def('client', 'SITE');

		jimport('joomla.language.helper');
		$options = JLanguageHelper::createLanguageList($value, constant('JPATH_'.strtoupper($client)), true);
		foreach ($options as $i => $option) {
			if ($option['value']) {
				$options[$i]['text'] = $option['text'].' ['.$option['value'].']';
			}
		}

		require_once JPATH_PLUGINS.'/system/nnframework/helpers/html.php';
		return nnHTML::selectlist($options, $name, $value, $id, $size, $multiple, '');
	}

	private function def($val, $default = '')
	{
		return (isset($this->params[$val]) && (string) $this->params[$val] != '') ? (string) $this->params[$val] : $default;
	}
}

class JFormFieldNN_Languages extends JFormField
{
	/**
	 * The form field type
	 *
	 * @var		string
	 */
	public $type = 'Languages';

	protected function getInput()
	{
		$this->_nnfield = new nnFieldLanguages();
		return $this->_nnfield->getInput($this->name, $this->id, $this->value, $this->element->attributes(), $this->element->children());
	}
}