<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Templates
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

/**
 * @package		Joomla
 * @subpackage	Templates
 */
class TemplatesController
{
	/**
	* Compiles a list of installed, version 4.5+ templates
	*
	* Based on xml files found.  If no xml file found the template
	* is ignored
	*/
	function viewTemplates()
	{
		global $mainframe, $option;

		// Initialize some variables
		$db		=& JFactory::getDBO();
		$client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

		// Initialize the pagination variables
		$limit		= $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = $mainframe->getUserStateFromRequest($option.'.'.$client->id.'.limitstart', 'limitstart', 0, 'int');

		$select[] 			= JHTML::_('select.option', '0', JText::_('Site'));
		$select[] 			= JHTML::_('select.option', '1', JText::_('Administrator'));
		$lists['client'] 	= JHTML::_('select.genericlist',  $select, 'client', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $client->id);

		$tBaseDir = $client->path.DS.'templates';

		//get template xml file info
		$rows = array();
		$rows = TemplatesHelper::parseXMLTemplateFiles($tBaseDir);

		// set dynamic template information
		for($i = 0; $i < count($rows); $i++)  {
			$rows[$i]->assigned		= TemplatesHelper::isTemplateAssigned($rows[$i]->directory);
			$rows[$i]->published	= TemplatesHelper::isTemplateDefault($rows[$i]->directory, $client->id);
		}

		jimport('joomla.html.pagination');
		$page = new JPagination(count($rows), $limitstart, $limit);

		$rows = array_slice($rows, $page->limitstart, $page->limit);

		require_once (JPATH_COMPONENT.DS.'admin.templates.html.php');
		TemplatesView::showTemplates($rows, $lists, $page, $option, $client);
	}

	/**
	* Show the template with module position in an iframe
	*/
	function previewTemplate()
	{
		$template	= JRequest::getVar('id', '', 'method', 'cmd');
		$option 	= JRequest::getCmd('option');
		$client		=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

		if (!$template)
		{
			return JError::raiseWarning( 500, JText::_('Template not specified') );
		}

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		require_once (JPATH_COMPONENT.DS.'admin.templates.html.php');
		TemplatesView::previewTemplate($template, true, $client, $option);
	}

	/**
	* Publish, or make current, the selected template
	*/
	function publishTemplate()
	{
		global $mainframe;

		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		// Initialize some variables
		$db		= & JFactory::getDBO();
		$cid	= JRequest::getVar('cid', array(), 'method', 'array');
		$cid	= array(JFilterInput::clean(@$cid[0], 'cmd'));
		$option	= JRequest::getCmd('option');
		$client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

		if ($cid[0])
		{
			$query = 'DELETE FROM #__templates_menu' .
					' WHERE client_id = '.(int) $client->id .
					' AND (menuid = 0 OR template = '.$db->Quote($cid[0]).')';
			$db->setQuery($query);
			$db->query();

			$query = 'INSERT INTO #__templates_menu' .
					' SET client_id = '.(int) $client->id .', template = '.$db->Quote($cid[0]).', menuid = 0';
			$db->setQuery($query);
			$db->query();
		}

		$mainframe->redirect('index.php?option='.$option.'&client='.$client->id);
	}

	function editTemplate()
	{
		jimport('joomla.filesystem.path');

		// Initialize some variables
		$db			= & JFactory::getDBO();
		$cid		= JRequest::getVar('cid', array(), 'method', 'array');
		$cid		= array(JFilterInput::clean(@$cid[0], 'cmd'));
		$template	= $cid[0];
		$option		= JRequest::getCmd('option');
		$client		=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

		if (!$cid[0]) {
			return JError::raiseWarning( 500, JText::_('Template not specified') );
		}

		$tBaseDir	= JPath::clean($client->path.DS.'templates');

		if (!is_dir( $tBaseDir . DS . $template )) {
			return JError::raiseWarning( 500, JText::_('Template not found') );
		}
		$lang =& JFactory::getLanguage();
		$lang->load( 'tpl_'.$template, JPATH_ADMINISTRATOR );

		$ini	= $client->path.DS.'templates'.DS.$template.DS.'params.ini';
		$xml	= $client->path.DS.'templates'.DS.$template.DS.'templateDetails.xml';
		$row	= TemplatesHelper::parseXMLTemplateFile($tBaseDir, $template);

		jimport('joomla.filesystem.file');
		// Read the ini file
		if (JFile::exists($ini)) {
			$content = JFile::read($ini);
		} else {
			$content = null;
		}

		$params = new JParameter($content, $xml, 'template');

		$assigned = TemplatesHelper::isTemplateAssigned($row->directory);
		$default = TemplatesHelper::isTemplateDefault($row->directory, $client->id);

		if($client->id == '1')  {
			$lists['selections'] =  JText::_('Cannot assign an administrator template');
		} else {
			$lists['selections'] = TemplatesHelper::createMenuList($template);
		}

		if ($default) {
			$row->pages = 'all';
		} elseif (!$assigned) {
			$row->pages = 'none';
		} else {
			$row->pages = null;
		}

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		$ftp =& JClientHelper::setCredentialsFromRequest('ftp');

		require_once (JPATH_COMPONENT.DS.'admin.templates.html.php');
		TemplatesView::editTemplate($row, $lists, $params, $option, $client, $ftp, $template);
	}

	function saveTemplate()
	{
		global $mainframe;

		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		// Initialize some variables
		$db			 = & JFactory::getDBO();

		$template	= JRequest::getVar('id', '', 'method', 'cmd');
		$option		= JRequest::getVar('option', '', '', 'cmd');
		$client		=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$menus		= JRequest::getVar('selections', array(), 'post', 'array');
		$params		= JRequest::getVar('params', array(), 'post', 'array');
		$default	= JRequest::getBool('default');
		JArrayHelper::toInteger($menus);

		if (!$template) {
			$mainframe->redirect('index.php?option='.$option.'&client='.$client->id, JText::_('Operation Failed').': '.JText::_('No template specified.'));
		}

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
		$ftp = JClientHelper::getCredentials('ftp');

		$file = $client->path.DS.'templates'.DS.$template.DS.'params.ini';

		jimport('joomla.filesystem.file');
		if (JFile::exists($file) && count($params))
		{
			$txt = null;
			foreach ($params as $k => $v) {
				$txt .= "$k=$v\n";
			}

			// Try to make the params file writeable
			if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0755')) {
				JError::raiseNotice('SOME_ERROR_CODE', JText::_('Could not make the template parameter file writable'));
			}

			$return = JFile::write($file, $txt);

			// Try to make the params file unwriteable
			if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0555')) {
				JError::raiseNotice('SOME_ERROR_CODE', JText::_('Could not make the template parameter file unwritable'));
			}

			if (!$return) {
				$mainframe->redirect('index.php?option='.$option.'&client='.$client->id, JText::_('Operation Failed').': '.JText::sprintf('Failed to open file for writing.', $file));
			}
		}

		// Reset all existing assignments
		$query = 'DELETE FROM #__templates_menu' .
				' WHERE client_id = 0' .
				' AND template = '.$db->Quote( $template );
		$db->setQuery($query);
		$db->query();

		if ($default) {
			$menus = array( 0 );
		}

		foreach ($menus as $menuid)
		{
			// If 'None' is not in array
			if ((int) $menuid >= 0)
			{
				// check if there is already a template assigned to this menu item
				$query = 'DELETE FROM #__templates_menu' .
						' WHERE client_id = 0' .
						' AND menuid = '.(int) $menuid;
				$db->setQuery($query);
				$db->query();

				$query = 'INSERT INTO #__templates_menu' .
						' SET client_id = 0, template = '. $db->Quote( $template ) .', menuid = '.(int) $menuid;
				$db->setQuery($query);
				$db->query();
			}
		}

		$task = JRequest::getCmd('task');
		if($task == 'apply') {
			$mainframe->redirect('index.php?option='.$option.'&task=edit&cid[]='.$template.'&client='.$client->id);
		} else {
			$mainframe->redirect('index.php?option='.$option.'&client='.$client->id);
		}
	}

	function cancelTemplate()
	{
		global $mainframe;

		// Initialize some variables
		$option	= JRequest::getCmd('option');
		$client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		$mainframe->redirect('index.php?option='.$option.'&client='.$client->id);
	}

	function editTemplateSource()
	{
		global $mainframe;

		// Initialize some variables
		$option		= JRequest::getCmd('option');
		$client		=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$template	= JRequest::getVar('id', '', 'method', 'cmd');
		$file		= $client->path.DS.'templates'.DS.$template.DS.'index.php';

		// Read the source file
		jimport('joomla.filesystem.file');
		$content = JFile::read($file);

		if ($content !== false)
		{
			// Set FTP credentials, if given
			jimport('joomla.client.helper');
			$ftp =& JClientHelper::setCredentialsFromRequest('ftp');

			$content = htmlspecialchars($content, ENT_COMPAT, 'UTF-8');
			require_once (JPATH_COMPONENT.DS.'admin.templates.html.php');
			TemplatesView::editTemplateSource($template, $content, $option, $client, $ftp);
		} else {
			$msg = JText::sprintf('Operation Failed Could not open', $file);
			$mainframe->redirect('index.php?option='.$option.'&client='.$client->id, $msg);
		}
	}

	function saveTemplateSource()
	{
		global $mainframe;

		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		// Initialize some variables
		$option			= JRequest::getCmd('option');
		$client			=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$template		= JRequest::getVar('id', '', 'method', 'cmd');
		$filecontent	= JRequest::getVar('filecontent', '', 'post', 'string', JREQUEST_ALLOWRAW);

		if (!$template) {
			$mainframe->redirect('index.php?option='.$option.'&client='.$client->id, JText::_('Operation Failed').': '.JText::_('No template specified.'));
		}

		if (!$filecontent) {
			$mainframe->redirect('index.php?option='.$option.'&client='.$client->id, JText::_('Operation Failed').': '.JText::_('Content empty.'));
		}

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
		$ftp = JClientHelper::getCredentials('ftp');

		$file = $client->path.DS.'templates'.DS.$template.DS.'index.php';

		// Try to make the template file writeable
		if (!$ftp['enabled'] && !JPath::setPermissions($file, '0755')) {
			JError::raiseNotice('SOME_ERROR_CODE', JText::_('Could not make the template file writable'));
		}

		jimport('joomla.filesystem.file');
		$return = JFile::write($file, $filecontent);

		// Try to make the template file unwriteable
		if (!$ftp['enabled'] && !JPath::setPermissions($file, '0555')) {
			JError::raiseNotice('SOME_ERROR_CODE', JText::_('Could not make the template file unwritable'));
		}

		if ($return)
		{
			$task = JRequest::getCmd('task');
			switch($task)
			{
				case 'apply_source':
					$mainframe->redirect('index.php?option='.$option.'&client='.$client->id.'&task=edit_source&id='.$template, JText::_('Template source saved'));
					break;

				case 'save_source':
				default:
					$mainframe->redirect('index.php?option='.$option.'&client='.$client->id.'&task=edit&cid[]='.$template, JText::_('Template source saved'));
					break;
			}
		}
		else {
			$mainframe->redirect('index.php?option='.$option.'&client='.$client->id, JText::_('Operation Failed').': '.JText::sprintf('Failed to open file for writing.', $file));
		}
	}

	function chooseTemplateCSS()
	{
		global $mainframe;

		// Initialize some variables
		$option 	= JRequest::getCmd('option');
		$template	= JRequest::getVar('id', '', 'method', 'cmd');
		$client		=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

		// Determine template CSS directory
		$dir = $client->path.DS.'templates'.DS.$template.DS.'css';

		// List template .css files
		jimport('joomla.filesystem.folder');
		$files = JFolder::files($dir, '\.css$', false, false);

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		require_once (JPATH_COMPONENT.DS.'admin.templates.html.php');
		TemplatesView::chooseCSSFiles($template, $dir, $files, $option, $client);
	}

	function editTemplateCSS()
	{
		global $mainframe;

		// Initialize some variables
		$option		= JRequest::getCmd('option');
		$client		=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$template	= JRequest::getVar('id', '', 'method', 'cmd');
		$filename	= JRequest::getVar('filename', '', 'method', 'cmd');

		jimport('joomla.filesystem.file');

		if (JFile::getExt($filename) !== 'css') {
			$msg = JText::_('Wrong file type given, only CSS files can be edited.');
			$mainframe->redirect('index.php?option='.$option.'&client='.$client->id.'&task=choose_css&id='.$template, $msg, 'error');
		}

		$content = JFile::read($client->path.DS.'templates'.DS.$template.DS.'css'.DS.$filename);

		if ($content !== false)
		{
			// Set FTP credentials, if given
			jimport('joomla.client.helper');
			$ftp =& JClientHelper::setCredentialsFromRequest('ftp');

			$content = htmlspecialchars($content, ENT_COMPAT, 'UTF-8');
			require_once (JPATH_COMPONENT.DS.'admin.templates.html.php');
			TemplatesView::editCSSSource($template, $filename, $content, $option, $client, $ftp);
		}
		else
		{
			$msg = JText::sprintf('Operation Failed Could not open', $client->path.$filename);
			$mainframe->redirect('index.php?option='.$option.'&client='.$client->id, $msg);
		}
	}

	function saveTemplateCSS()
	{
		global $mainframe;

		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		// Initialize some variables
		$option			= JRequest::getCmd('option');
		$client			=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$template		= JRequest::getVar('id', '', 'post', 'cmd');
		$filename		= JRequest::getVar('filename', '', 'post', 'cmd');
		$filecontent	= JRequest::getVar('filecontent', '', 'post', 'string', JREQUEST_ALLOWRAW);

		if (!$template) {
			$mainframe->redirect('index.php?option='.$option.'&client='.$client->id, JText::_('Operation Failed').': '.JText::_('No template specified.'));
		}

		if (!$filecontent) {
			$mainframe->redirect('index.php?option='.$option.'&client='.$client->id, JText::_('Operation Failed').': '.JText::_('Content empty.'));
		}

		// Set FTP credentials, if given
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');
		$ftp = JClientHelper::getCredentials('ftp');

		$file = $client->path.DS.'templates'.DS.$template.DS.'css'.DS.$filename;

		// Try to make the css file writeable
		if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0755')) {
			JError::raiseNotice('SOME_ERROR_CODE', JText::_('Could not make the css file writable'));
		}

		jimport('joomla.filesystem.file');
		$return = JFile::write($file, $filecontent);

		// Try to make the css file unwriteable
		if (!$ftp['enabled'] && JPath::isOwner($file) && !JPath::setPermissions($file, '0555')) {
			JError::raiseNotice('SOME_ERROR_CODE', JText::_('Could not make the css file unwritable'));
		}

		if ($return)
		{
			$task = JRequest::getCmd('task');
			switch($task)
			{
				case 'apply_css':
					$mainframe->redirect('index.php?option='.$option.'&client='.$client->id.'&task=edit_css&id='.$template.'&filename='.$filename,  JText::_('File Saved'));
					break;

				case 'save_css':
				default:
					$mainframe->redirect('index.php?option='.$option.'&client='.$client->id.'&task=edit&cid[]='.$template, JText::_('File Saved'));
					break;
			}
		}
		else {
			$mainframe->redirect('index.php?option='.$option.'&client='.$client->id.'&id='.$template.'&task=choose_css', JText::_('Operation Failed').': '.JText::sprintf('Failed to open file for writing.', $file));
		}
	}
}
