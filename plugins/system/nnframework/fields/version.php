<?php
/**
 * Element: Version
 * Displays the version check
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
 * Version Element
 *
 * Available extra parameters:
 * xml			The title
 * description		The description
 */
class nnFieldVersion
{
	var $_version = '12.6.4';

	function getInput($name, $id, $value, $params, $children)
	{
		$this->params = $params;

		$extension = $this->def('extension');
		$xml = $this->def('xml');
		if (!strlen($extension) || !strlen($xml)) {
			return '';
		}

		$user = JFactory::getUser();
		$authorise = $user->authorise('core.manage', 'com_installer');
		if (!$authorise) {
			return '';
		}

		// Import library dependencies
		require_once JPATH_PLUGINS.'/system/nnframework/helpers/versions.php';
		$versions = NNVersions::getInstance();

		return $versions->getMessage($extension, $xml);
	}

	private function def($val, $default = '')
	{
		return (isset($this->params[$val]) && (string) $this->params[$val] != '') ? (string) $this->params[$val] : $default;
	}
}

jimport('joomla.form.formfield');

class JFormFieldNN_Version extends JFormField
{
	/**
	 * The form field type
	 *
	 * @var		string
	 */
	public $type = 'Version';

	protected function getLabel()
	{
		return;
	}

	protected function getInput()
	{
		$this->_nnfield = new nnFieldVersion();
		return $this->_nnfield->getInput($this->name, $this->id, $this->value, $this->element->attributes(), $this->element->children());
	}
}