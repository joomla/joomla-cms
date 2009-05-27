<?php
/**
 * @version		$Id: template.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Administrator
 * @subpackage	Templates
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	Templates
 */
class TemplatesHelper
{
	function isTemplateDefault($template, $clientId)
	{
		$db = &JFactory::getDbo();

		// Get the current default template
		$query = ' SELECT template '
				.' FROM #__templates_menu '
				.' WHERE client_id = ' . (int) $clientId
				.' AND menuid = 0 ';
		$db->setQuery($query);
		$defaultemplate = $db->loadResult();

		return $defaultemplate == $template ? 1 : 0;
	}

	function isTemplateAssigned($template)
	{
		$db = &JFactory::getDbo();

		// check if template is assigned
		$query = 'SELECT COUNT(*)' .
				' FROM #__templates_menu' .
				' WHERE client_id = 0' .
				' AND template = '.$db->Quote($template) .
				' AND menuid <> 0';
		$db->setQuery($query);
		return $db->loadResult() ? 1 : 0;
	}

	function parseXMLTemplateFiles($templateBaseDir)
	{
		// Read the template folder to find templates
		jimport('joomla.filesystem.folder');
		$templateDirs = JFolder::folders($templateBaseDir);

		$rows = array();

		// Check that the directory contains an xml file
		foreach ($templateDirs as $templateDir)
		{
			if (!$data = TemplatesHelper::parseXMLTemplateFile($templateBaseDir, $templateDir)){
				continue;
			} else {
				$rows[] = $data;
			}
		}

		return $rows;
	}

	function parseXMLTemplateFile($templateBaseDir, $templateDir)
	{
		// Check of the xml file exists
		if (!is_file($templateBaseDir.DS.$templateDir.DS.'templateDetails.xml')) {
			return false;
		}

		$xml = JApplicationHelper::parseXMLInstallFile($templateBaseDir.DS.$templateDir.DS.'templateDetails.xml');

		if ($xml['type'] != 'template') {
			return false;
		}

		$data = new StdClass();
		$data->directory = $templateDir;

		foreach($xml as $key => $value) {
			$data->$key = $value;
		}

		$data->checked_out = 0;
		$data->mosname = JString::strtolower(str_replace(' ', '_', $data->name));

		return $data;
	}

	function createMenuList($template)
	{
		$db = &JFactory::getDbo();

		// get selected pages for $menulist
		$query = 'SELECT menuid AS value' .
				' FROM #__templates_menu' .
				' WHERE client_id = 0' .
				' AND template = '.$db->Quote($template);
		$db->setQuery($query);
		$lookup = $db->loadObjectList();
		if (empty($lookup)) {
			$lookup = array(JHtml::_('select.option',  '-1'));
		}

		// build the html select list
		$options	= JHtml::_('menu.linkoptions');
		$result		= JHtml::_('select.genericlist',   $options, 'selections[]', 'class="inputbox" size="15" multiple="multiple"', 'value', 'text', $lookup, 'selections');
		return $result;
	}
}