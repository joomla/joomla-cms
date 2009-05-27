<?php
/**
 * @version		$Id: module.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Administrator
 * @subpackage	Modules
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

/**
 * @package		Joomla.Administrator
 * @subpackage	Modules
 */
class ModulesModelModule extends JModel
{
	var $_xml;

	function &getModule()
	{
		static $instance;

		if (!$instance)
		{
			$instance = $this->getTable('Module', 'JTable');
			if ($id = $this->getState('id')) {
				$instance->load((int) $id);
			}
		}
		return $instance;
	}

	function &_getXML()
	{
		if (!$this->_xml)
		{
			$clientId	= $this->getState('clientId', 0);
			$path		= ($clientId == 1) ? 'mod1_xml' : 'mod0_xml';
			$module		= &$this->getModule();

			if ($module->module == 'custom') {
				$xmlpath = JApplicationHelper::getPath($path, 'mod_custom');
			} else {
				$xmlpath = JApplicationHelper::getPath($path, $module->module);
			}

			if (file_exists($xmlpath))
			{
				$xml = &JFactory::getXMLParser('Simple');
				if ($xml->loadFile($xmlpath)) {
					$this->_xml = &$xml;
				}
			}
		}
		return $this->_xml;
	}

	function &getParams()
	{
		// Get the state parameters
		$module	= &$this->getModule();
		$params	= new JParameter($module->params);

		if ($xml = &$this->_getXML())
		{
			if ($ps = & $xml->document->params) {
				foreach ($ps as $p)
				{
					$params->setXML($p);
				}
			}
		}
		return $params;
	}

	function getPositions()
	{
		jimport('joomla.filesystem.folder');

		$client = &JApplicationHelper::getClientInfo($this->getState('clientId'));
		if ($client === false) {
			return false;
		}

		//Get the database object
		$db	= &JFactory::getDbo();

		// template assignment filter
		$query = 'SELECT DISTINCT(template) AS text, template AS value'.
				' FROM #__templates_menu' .
				' WHERE client_id = '.(int) $client->id;
		$db->setQuery($query);
		$templates = $db->loadObjectList();

		// Get a list of all module positions as set in the database
		$query = 'SELECT DISTINCT(position)'.
				' FROM #__modules' .
				' WHERE client_id = '.(int) $client->id;
		$db->setQuery($query);
		$positions = $db->loadResultArray();
		$positions = (is_array($positions)) ? $positions : array();

		// Get a list of all template xml files for a given application

		// Get the xml parser first
		for ($i = 0, $n = count($templates); $i < $n; $i++)
		{
			$path = $client->path.DS.'templates'.DS.$templates[$i]->value;

			$xml = &JFactory::getXMLParser('Simple');
			if ($xml->loadFile($path.DS.'templateDetails.xml'))
			{
				$p = &$xml->document->getElementByPath('positions');
				if (is_a($p, 'JSimpleXMLElement') && count($p->children()))
				{
					foreach ($p->children() as $child)
					{
						if (!in_array($child->data(), $positions)) {
							$positions[] = $child->data();
						}
					}
				}
			}
		}

		$positions = array_unique($positions);
		sort($positions);

		return $positions;
	}
}
