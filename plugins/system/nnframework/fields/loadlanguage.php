<?php
/**
 * Element: Load Language
 * Loads the English language file as fallback
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
 * Load Language Element
 */
class nnFieldLoadLanguage
{
	var $_version = '12.6.4';

	function getInput($name, $id, $value, $params, $children)
	{
		$this->params = $params;

		JHtml::_('behavior.mootools');
		$document = JFactory::getDocument();
		$document->addScript(JURI::root(true).'/plugins/system/nnframework/js/script.js?v='.$this->_version);

		$extension = $this->def('extension');
		$admin = $this->def('admin', 1);

		$path = $admin ? JPATH_ADMINISTRATOR : JPATH_SITE;
		// load the admin language file
		$lang = JFactory::getLanguage();
		if ($lang->getTag() != 'en-GB') {
			// Loads English language file as fallback (for undefined stuff in other language file)
			$lang->load($extension, $path, 'en-GB');
		}
		$lang->load($extension, $path, null, 1);

		return;
	}

	function loadLanguage($extension, $admin = 1)
	{
		if ($extension) {
			if ($admin) {
				$path = JPATH_ADMINISTRATOR;
			} else {
				$path = JPATH_SITE;
			}
			$lang = JFactory::getLanguage();
			if ($lang->getTag() != 'en-GB') {
				// Loads English language file as fallback (for undefined stuff in other language file)
				$lang->load($extension, $path, 'en-GB');
			}
			$lang->load($extension, $path, null, 1);
		}
	}

	private function def($val, $default = '')
	{
		return (isset($this->params[$val]) && (string) $this->params[$val] != '') ? (string) $this->params[$val] : $default;
	}
}

class JFormFieldNN_LoadLanguage extends JFormField
{
	/**
	 * The form field type
	 *
	 * @var		string
	 */
	public $type = 'LoadLanguage';

	protected function getLabel()
	{
		return;
	}

	protected function getInput()
	{
		$this->_nnfield = new nnFieldLoadLanguage();
		return $this->_nnfield->getInput($this->name, $this->id, $this->value, $this->element->attributes(), $this->element->children());
	}
}