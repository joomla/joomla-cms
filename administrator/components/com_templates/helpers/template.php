<?php
/**
 * @version		$Id$
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
	function isTemplateAssigned($id)
	{
		$db = &JFactory::getDbo();
		// check if template is assigned
		$query = 'SELECT COUNT(*)' .
				' FROM #__menu' .
				' WHERE template_id = '.$db->Quote($id);
		$db->setQuery($query);
		return $db->loadResult() ? 1 : 0;
	}

	function isTemplateDefault($id)
	{
		$db = &JFactory::getDbo();
		// check if template is assigned
		$query = 'SELECT home' .
				' FROM #__menu_template' .
				' WHERE id = '.$db->Quote($id);
		$db->setQuery($query);
		return $db->loadResult() == 1 ;
	}

	function isTemplateNameAssigned($template,$client_id)
	{
		$db = &JFactory::getDbo();
		if ($client_id==1)
			return 0;
		// check if template is assigned
		$query = 'SELECT COUNT(*) FROM #__menu'.
				' LEFT JOIN #__menu_template'.
				' ON #__menu.template_id=#__menu_template.id'.
				' WHERE #__menu_template.template = '.$db->Quote($template);
		$db->setQuery($query);
		return $db->loadResult() ? 1 : 0;
	}

	function isTemplateNameDefault($template,$client_id)
	{
		$db = &JFactory::getDbo();

		// check if template is assigned
		$query = 'SELECT COUNT(*) FROM #__menu_template'.
				' WHERE template = '.$db->Quote($template).
				' AND client_id = '.$db->Quote($client_id).
				' AND home = 1';
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
				$rows[$templateDir] = $data;
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

	function createMenuList($id)
	{
		$db = &JFactory::getDbo();
		// get selected pages for $menulist
		$query = 'SELECT id AS value FROM #__menu'.
				' WHERE template_id = '.$db->Quote($id);
		$db->setQuery($query);
		$lookup = $db->loadObjectList();
		if (empty($lookup)) {
			$lookup = array(JHtml::_('select.option',  '-1'));
		}
		// build the html select list
		$options = JHtml::_('menu.linkoptions');
		$result = JHtml::_(
			'select.genericlist',
			$options,
			'selections[]',
			array(
				'id' => 'selections',
				'list.attr' => 'class="inputbox" size="15" multiple="multiple"',
				'list.select' => $lookup,
			)
		);
		return $result;
	}

	function getTemplateName($id)
	{
		$db = &JFactory::getDbo();
		$query = 'SELECT template FROM #__menu_template'.
				' WHERE id = '.$db->Quote($id).'';
		$db->setQuery($query);
		$db->query();
		if ($db->getNumRows() == 0) {
				JError::raiseWarning(500, JText::_('Template not found'));
				return '';
		}
		return $db->loadResult();
	}
}