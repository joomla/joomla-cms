<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Templates
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/*
 * Make sure the user is authorized to view this page
 */
$user = & $mainframe->getUser();
if (!$user->authorize('com_templates', 'manage')) {
	josRedirect('index2.php', JText::_('ALERTNOTAUTH'));
}

require_once (dirname(__FILE__).'/admin.templates.html.php');
require_once (dirname(__FILE__).'/admin.templates.class.php');

$task	= JRequest::getVar('task');
$id		= JRequest::getVar('id');
$cid	= JRequest::getVar('cid', array (), '', 'array');

if (!isset($cid[0])) {
	$cid[0] = $id;
}

switch ($task)
{
	case 'edit' :
		JTemplatesController::editTemplate($cid[0]);
		break;

	case 'save'  :
	case 'apply' :
		JTemplatesController::saveTemplate($cid[0], $task);
		break;

	case 'edit_source' :
		JTemplatesController::editTemplateSource($cid[0]);
		break;

	case 'save_source' :
		JTemplatesController::saveTemplateSource();
		break;

	case 'choose_css' :
		JTemplatesController::chooseTemplateCSS($cid[0]);
		break;

	case 'edit_css' :
		JTemplatesController::editTemplateCSS($cid[0]);
		break;

	case 'save_css' :
		JTemplatesController::saveTemplateCSS($cid[0]);
		break;

	case 'publish' :
	case 'default' :
		JTemplatesController::publishTemplate($cid[0]);
		break;

	case 'cancel' :
		JTemplatesController::cancelTemplate();
		break;

	case 'positions' :
		JTemplatesController::editPositions();
		break;

	case 'save_positions' :
		JTemplatesController::savePositions();
		break;
		
	case 'preview' :
		JTemplatesController::previewTemplate($cid[0], 1);
		break;

	default :
		JTemplatesController::viewTemplates();
		break;
}

class JTemplatesController
{
	/**
	* Compiles a list of installed, version 4.5+ templates
	*
	* Based on xml files found.  If no xml file found the template
	* is ignored
	*/
	function viewTemplates()
	{
		global $mainframe;

		/*
		 * Initialize some variables
		 */
		$db		= & $mainframe->getDBO();
		$option = JRequest::getVar('option');
		$client	= JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		
		// Initialize the pagination variables
		$limit		= $mainframe->getUserStateFromRequest("limit", 'limit', $mainframe->getCfg('list_limit'));
		$limitstart	= $mainframe->getUserStateFromRequest("$option.limitstart", 'limitstart', 0);

		$select[] 			= mosHTML::makeOption('0', JText::_('Site'));
		$select[] 			= mosHTML::makeOption('1', JText::_('Administrator'));
		$lists['client'] 	= mosHTML::selectList($select, 'client', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $client->id);
		
		$templateBaseDir = JPath::clean($client->path.DS.'templates');
		
		//get template xml file info
		$rows = array();
		$rows = JTemplatesHelper::parseXMLTemplateFiles($templateBaseDir);
		
		// set dynamic template information
		for($i = 0; $i < count($rows); $i++)  {
			$rows[$i]->assigned  = JTemplatesHelper::isTemplateAssigned($rows[$i]->directory);
			$rows[$i]->published = JTemplatesHelper::isTemplateDefault($rows[$i]->directory, $client->id);
		}
		
		jimport('joomla.presentation.pagination');
		$page = new JPagination(count($rows), $limitstart, $limit);
		
		$rows = array_slice($rows, $page->limitstart, $page->limit);
		
		JTemplatesView::showTemplates($rows, $lists, $page, $option, $client);
	}
	
	/**
	* Show the template with module position in an iframe
	*/
	function previewTemplate($template, $showPositions=0)
	{
		$option = JRequest::getVar('option');
		$client	= JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		
		JTemplatesView::previewTemplate($template, $showPositions, $client, $option);
	}

	/**
	* Publish, or make current, the selected template
	*/
	function publishTemplate($template)
	{
		global $mainframe;

		/*
		 * Initialize some variables
		 */
		$db		= & $mainframe->getDBO();
		$option	= JRequest::getVar('option');
		$client	= JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

		$query = "DELETE FROM #__templates_menu" .
				"\n WHERE client_id = $client->id" .
				"\n AND menuid = 0";
		$db->setQuery($query);
		$db->query();

		$query = "INSERT INTO #__templates_menu" .
				"\n SET client_id = $client->id, template = '$template', menuid = 0";
		$db->setQuery($query);
		$db->query();

		josRedirect('index2.php?option='.$option.'&client='.$client->id);
	}

	/**
	* Remove the selected template
	*/
	function removeTemplate($cid)
	{
		global $mainframe;

		/*
		 * Initialize some variables
		 */
		$db		= & $mainframe->getDBO();
		$option	= JRequest::getVar('option');
		$client	= JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

		$query = "SELECT template" .
				"\n FROM #__templates_menu" .
				"\n WHERE client_id = $client->id" .
				"\n AND menuid = 0";
		$db->setQuery($query);
		$cur_template = $db->loadResult();

		if ($cur_template == $cid)
		{
			mosErrorAlert(JText::_('You can not delete template in use.'));
		}

		// Un-assign
		$query = "DELETE FROM #__templates_menu" .
				"\n WHERE template = '$cid'" .
				"\n AND client_id = $client->id" .
				"\n AND menuid <> 0";
		$db->setQuery($query);
		$db->query();

		josRedirect('index2.php?option=com_installer&type=template&client='.$client->id.'&task=remove&eid[]='.$cid);
	}

	function editTemplate($template)
	{
		global $mainframe;
		
		/*
		 * Initialize some variables
		 */
		$db	    = & $mainframe->getDBO();
		$option	= JRequest::getVar('option');
		$client	= JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		
		$ini		= $client->path.DS.'templates'.DS.$template.DS.'params.ini';
		$xml		= $client->path.DS.'templates'.DS.$template.DS.'templateDetails.xml';
		
		$templateBaseDir = JPath::clean($client->path.DS.'templates');
		$row = JTemplatesHelper::parseXMLTemplateFile($templateBaseDir, $template);
		$row->published = JTemplatesHelper::isTemplateDefault($row->directory, $client->id);

		// Read the ini file
		if (JFile::exists($ini)) {
			$content = JFile::read($ini);
		} else {
			$content = null;
		}
		
		$params = new JParameter($content, $xml, 'template');
		
		$lists['published']  = mosHTML::yesnoRadioList( 'published', 'class="inputbox"', $row->published);
		
		$lists['selections'] = '';
		if($client->id == '1')  {
			$lists['selections'] =  JText::_("Can't assign an administrator template");
		} else  {
			if(JTemplatesHelper::isTemplateDefault($row->directory, $client->id)) {
				$lists['selections'] =  JText::_("Can't assign a default template");
			} else {
				$lists['selections'] = JTemplatesHelper::createMenuList($template);
			}
		}
		 
		JTemplatesView::editTemplate($row, $lists, $params, $option, $client);
	}

	function saveTemplate($template, $task)
	{
		global $mainframe;
		
		/*
		 * Initialize some variables
		 */
		$db	   		 = & $mainframe->getDBO();
		
		$option		= JRequest::getVar('option');
		$client		= JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$menus		= JRequest::getVar('selections', array (), 'post', 'array');
		$params		= JRequest::getVar('params', array (), '', 'array');
		
		if (!$template)
		{
			josRedirect('index2.php?option='.$option.'&client='.$client->id, JText::_('Operation Failed').': '.JText::_('No template specified.'));
		}

		$file = $client->path.DS.'templates'.DS.$template.DS.'params.ini';
		
		if (JFile::exists($file) && is_array($params))
		{
			$txt = null;
			foreach ($params as $k => $v) {
				$txt .= "$k=$v\n";
			}
			
			if (!JFile::write($file, $txt)) {
				josRedirect('index2.php?option='.$option.'&client='.$client->id, JText::_('Operation Failed').': '.JText::_('Failed to open file for writing.'));
			}
		}
		
		
		$query = "DELETE FROM #__templates_menu" .
				"\n WHERE client_id = 0" .
				"\n AND template = '$template'" .
				"\n AND menuid <> 0";
		$db->setQuery($query);
		$db->query();

		if (!in_array('', $menus))
		{
			foreach ($menus as $menuid)
			{
				// If 'None' is not in array
				if ($menuid != -999)
				{
					// check if there is already a template assigned to this menu item
					$query = "DELETE FROM #__templates_menu" .
							"\n WHERE client_id = 0" .
							"\n AND menuid = $menuid";
					$db->setQuery($query);
					$db->query();

					$query = "INSERT INTO #__templates_menu" .
							"\n SET client_id = 0, template = '$template', menuid = $menuid";
					$db->setQuery($query);
					$db->query();
				}
			}
		}
		
		if($task == 'apply') {
			josRedirect('index2.php?option='.$option.'&task=edit&id='.$template.'&client='.$client->id);
		} else {
			josRedirect('index2.php?option='.$option.'&client='.$client->id);
		}
	}
	
	function cancelTemplate()
	{
		global $mainframe;
		
		/*
		 * Initialize some variables
		 */
		$option	= JRequest::getVar('option');
		$client	= JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));	
		
		josRedirect('index2.php?option='.$option.'&client='.$client->id);
	}
	
	function editTemplateSource($p_tname)
	{
		global $mainframe;
		
		/*
		 * Initialize some variables
		 */
		$option	= JRequest::getVar('option');
		$client	= JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$file	= $client->path.DS.'templates'.DS.$p_tname.DS.'index.php';

		// Read the source file
		$content = JFile::read($file);

		if ($content !== false)
		{
			$content = htmlspecialchars($content);
			JTemplatesView::editTemplateSource($p_tname, $content, $option, $client);
		}
		else
		{
			$msg = sprintf(JText::_('Operation Failed Could not open'), $file);
			josRedirect('index2.php?option='.$option.'&client='.$client->id, $msg);
		}
	}

	function saveTemplateSource()
	{
		global $mainframe;
		
		/*
		 * Initialize some variables
		 */
		$option			= JRequest::getVar('option');
		$client			= JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$template		= JRequest::getVar('template');
		$enableWrite	= JRequest::getVar('enable_write', 0, '', 'int');
		$filecontent	= JRequest::getVar('filecontent', '', '', '', _J_ALLOWHTML);

		if (!$template)
		{
			josRedirect('index2.php?option='.$option.'&client='.$client->id, JText::_('Operation Failed').': '.JText::_('No template specified.'));
		}
		if (!$filecontent)
		{
			josRedirect('index2.php?option='.$option.'&client='.$client->id, JText::_('Operation Failed').': '.JText::_('Content empty.'));
		}

		$file = $client->path.DS.'templates'.DS.$template.DS.'index.php';

		/*
		 * Remove any slashes added by magic quotes
		 */
		if (get_magic_quotes_gpc())
		{
			$filecontent = stripslashes($filecontent);
		}

		if (JFile::write($file, $filecontent))
		{
			josRedirect('index2.php?option='.$option.'&client='.$client->id);
		}
		else
		{
			josRedirect('index2.php?option='.$option.'&client='.$client->id, JText::_('Operation Failed').': '.JText::_('Failed to open file for writing.'));
		}
	}

	function chooseTemplateCSS($p_tname)
	{
		global $mainframe;
		
		/*
		 * Initialize some variables
		 */
		$option = JRequest::getVar('option');
		$client	= JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

		if ($client->id == 1)
		{
			// Admin template css dir
			$a_dir = JPATH_ADMINISTRATOR.DS.'templates'.DS.$p_tname.DS.'css';
			// List .css files
			$a_files = JFolder::files($a_dir, $filter = '\.css$', $recurse = false, $fullpath = false);
			$fs_dir = null;
			$fs_files = null;

			JTemplatesView::chooseCSSFiles($p_tname, $a_dir, $a_files, $option, $client);

		}
		else
		{
			// Template css dir
			$f_dir = JPATH_SITE.DS.'templates'.DS.$p_tname.DS.'css';

			// List template .css files
			$f_files = JFolder::files($f_dir, $filter = '\.css$', $recurse = false, $fullpath = false);

			JTemplatesView::chooseCSSFiles($p_tname, $f_dir, $f_files, $option, $client);

		}
	}

	function editTemplateCSS($p_tname)
	{
		global $mainframe;
		
		/*
		 * Initialize some variables
		 */
		$option		= JRequest::getVar('option');
		$client		= JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$template	= JRequest::getVar('template');
		$tp_name	= JRequest::getVar('tp_name');
		$file			= $client->path.$tp_name;
		$p_tname = $template;

		$content = JFile::read($file);
		if ($content !== false)
		{
			$content = htmlspecialchars($content);

			JTemplatesView::editCSSSource($p_tname, $tp_name, $content, $option, $client);
		}
		else
		{
			$msg = sprintf(JText::_('Operation Failed Could not open'), $file);
			josRedirect('index2.php?option='.$option.'&client='.$client->id, $msg);
		}
	}

	function saveTemplateCSS($option, $client)
	{
		global $mainframe;
		
		/*
		 * Initialize some variables
		 */
		$option			= JRequest::getVar('option');
		$client			= JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$template		= JRequest::getVar('template');
		$tp_fname		= JRequest::getVar('tp_fname');
		$filecontent	= JRequest::getVar('filecontent', '', '', '', _J_ALLOWHTML);

		if (!$template)
		{
			josRedirect('index2.php?option='.$option.'&client='.$client->id, JText::_('Operation Failed').': '.JText::_('No template specified.'));
		}

		if (!$filecontent)
		{
			josRedirect('index2.php?option='.$option.'&client='.$client->id, JText::_('Operation Failed').': '.JText::_('Content empty.'));
		}

		if (JFile::write($tp_fname, $filecontent))
		{
			josRedirect('index2.php?option='.$option.'&client='.$client->id);
		}
		else
		{
			josRedirect('index2.php?option='.$option.'&client='.$client->id, JText::_('Operation Failed').': '.JText::_('Failed to open file for writing.'));
		}
	}

	/**
	*/
	function editPositions()
	{
		global $mainframe;

		/*
		 * Initialize some variables
		 */
		$db		= & $mainframe->getDBO();
		$option	= JRequest::getVar('option');

		$query = "SELECT *" .
				"\n FROM #__template_positions";
		$db->setQuery($query);
		$positions = $db->loadObjectList();

		JTemplatesView::editPositions($positions, $option);
	}

	/**
	*/
	function savePositions()
	{
		global $mainframe;

		/*
		 * Initialize some variables
		 */
		$db					= & $mainframe->getDBO();
		$option				= JRequest::getVar('option');
		$positions			= JRequest::getVar('position', array (), 'post', 'array');
		$descriptions		= JRequest::getVar('description', array (), 'post', 'array');

		$query = "DELETE FROM #__template_positions";
		$db->setQuery($query);
		$db->query();

		foreach ($positions as $id => $position)
		{
			$position = trim($db->getEscaped($position));
			$description = $descriptions[$id];
			if ($position != '')
			{
				$id = intval($id);
				$query = "INSERT INTO #__template_positions" .
						"\n VALUES ( $id, '$position', '$description' )";
				$db->setQuery($query);
				$db->query();
			}
		}
		josRedirect('index2.php?option='.$option.'&task=positions', JText::_('Positions saved'));
	}
}

/**
 *
 */
class JTemplatesHelper 
{
	function isTemplateDefault($template, $clientId)
	{
		global $mainframe;
		
		$db =& $mainframe->getDBO();
		
		// Get the current default template
		$query = "SELECT template" .
				"\n FROM #__templates_menu" .
				"\n WHERE client_id = $clientId" .
				"\n AND menuid = 0";
		$db->setQuery($query);
		$defaultemplate = $db->loadResult();
		
		return $defaultemplate == $template ? 1 : 0;
	}
	
	function isTemplateAssigned($template)
	{
		global $mainframe;
		
		$db =& $mainframe->getDBO();
		
		// check if template is assigned
		$query = "SELECT COUNT(*)" .
				"\n FROM #__templates_menu" .
				"\n WHERE client_id = 0" .
				"\n AND template = '$template'" .
				"\n AND menuid <> 0";
		$db->setQuery($query);
		return $db->loadResult() ? 1 : 0;
	}
	
	function parseXMLTemplateFiles($templateBaseDir)
	{
		// Read the template folder to find templates
		$templateDirs = JFolder::folders($templateBaseDir);

		$rows = array();
		
		// Check that the directory contains an xml file
		foreach ($templateDirs as $templateDir) 
		{	
			if(!$data = JTemplatesHelper::parseXMLTemplateFile($templateBaseDir, $templateDir)){
				continue;
			} else {
				$rows[]  = $data;
			}
		}
		
		return $rows;
	}
	
	function parseXMLTemplateFile($templateBaseDir, $templateDir)
	{
		// Check of the xml file exists
		if(!JFile::exists($templateBaseDir.$templateDir.DS.'templateDetails.xml')) {
			return false;
		}
		
		// Read the file to see if it's a valid template XML file
		$xmlDoc = & JFactory::getXMLParser();
		$xmlDoc->resolveErrors(true);
		if (!$xmlDoc->loadXML($templateBaseDir.$templateDir.DS.'templateDetails.xml', false, true)) {
			return false;
		}

		$root = & $xmlDoc->documentElement;

		if ($root->getTagName() != 'mosinstall' && $root->getTagName() != 'install') {
			return false;
		}
		if ($root->getAttribute('type') != 'template') {
			return false;
		}

		$data = new StdClass();
		$data->directory = $templateDir;
		$element = & $root->getElementsByPath('name', 1);
		$data->name = $element->getText();

		$element = & $root->getElementsByPath('creationDate', 1);
		$data->creationdate = $element ? $element->getText() : 'Unknown';

		$element = & $root->getElementsByPath('author', 1);
		$data->author = $element ? $element->getText() : 'Unknown';

		$element = & $root->getElementsByPath('copyright', 1);
		$data->copyright = $element ? $element->getText() : '';

		$element = & $root->getElementsByPath('authorEmail', 1);
		$data->authorEmail = $element ? $element->getText() : '';

		$element = & $root->getElementsByPath('authorUrl', 1);
		$data->authorUrl = $element ? $element->getText() : '';

		$element = & $root->getElementsByPath('version', 1);
		$data->version = $element ? $element->getText() : '';
		
		$element = & $root->getElementsByPath('description', 1);
		$data->description = $element ? $element->getText() : '';

		$data->checked_out = 0;
		$data->mosname = strtolower(str_replace(' ', '_', $data->name));
		
		return $data;
	}
	
	function createMenuList($template)
	{
		global $mainframe;
		
		$db =& $mainframe->getDBO();
		
		// get selected pages for $menulist
		$query = "SELECT menuid AS value" .
				"\n FROM #__templates_menu" .
				"\n WHERE client_id = 0" .
				"\n AND template = '$template'";
		$db->setQuery($query);
		$lookup = $db->loadObjectList();

		// build the html select list
		return mosAdminMenus::MenuLinks($lookup, 0, 1);
	}
}
?>