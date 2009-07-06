<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Languages
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/*
 * Make sure the user is authorized to view this page
 */
$user = & JFactory::getUser();
$app	= &JFactory::getApplication();
if (!$user->authorize('core.languages.manage')) {
	$app->redirect('index.php', JText::_('ALERTNOTAUTH'));
}

require_once(JApplicationHelper::getPath('admin_html'));

$task 	= strtolower(JRequest::getCmd('task'));
$cid 	= JRequest::getVar('cid', array(0), '', 'array');
$cid	= array(JFilterInput::clean(@$cid[0], 'cmd'));

$client	= JRequest::getVar('client', 0, '', 'int');
if ($client == 1) {
	JSubMenuHelper::addEntry(JText::_('Site'),'#" onclick="javascript:document.adminForm.client.value=\'0\';submitbutton(\'\');');
	JSubMenuHelper::addEntry(JText::_('Administrator'), '#" onclick="javascript:document.adminForm.client.value=\'1\';submitbutton(\'\');', true);
} else {
	JSubMenuHelper::addEntry(JText::_('Site'), '#" onclick="javascript:document.adminForm.client.value=\'0\';submitbutton(\'\');', true);
	JSubMenuHelper::addEntry(JText::_('Administrator'), '#" onclick="javascript:document.adminForm.client.value=\'1\';submitbutton(\'\');');
}

switch ($task)
{
	case 'publish':
		publishLanguage($cid[0]);
		break;

	default:
		viewLanguages();
		break;
}

/**
* Compiles a list of installed languages
 */
function viewLanguages()
{
	// Initialize some variables
	$app	= &JFactory::getApplication();
	$db		= &JFactory::getDbo();
	$client	= &JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
	$rows	= array ();

	$limit		= $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
	$limitstart = $app->getUserStateFromRequest($option.'.limitstart', 'limitstart', 0, 'int');

	$rowid = 0;

	// Set FTP credentials, if given
	jimport('joomla.client.helper');
	$ftp = &JClientHelper::setCredentialsFromRequest('ftp');

	//load folder filesystem class
	jimport('joomla.filesystem.folder');
	$path = JLanguage::getLanguagePath($client->path);
	$dirs = JFolder::folders($path);

	foreach ($dirs as $dir)
	{
		$files = JFolder::files($path.DS.$dir, '^([-_A-Za-z]*)\.xml$');
		foreach ($files as $file)
		{
			$data = JApplicationHelper::parseXMLLangMetaFile($path.DS.$dir.DS.$file);

			$row 			= new StdClass();
			$row->id 		= $rowid;
			$row->language 	= substr($file,0,-4);

			if (!is_array($data)) {
				continue;
			}
			foreach($data as $key => $value) {
				$row->$key = $value;
			}

			// if current than set published
			$params = JComponentHelper::getParams('com_languages');
			if ($params->get($client->name, 'en-GB') == $row->language) {
				$row->published	= 1;
			} else {
				$row->published = 0;
			}

			$row->checked_out = 0;
			$row->mosname = JString::strtolower(str_replace(" ", "_", $row->name));
			$rows[] = $row;
			$rowid++;
		}
	}


	jimport('joomla.html.pagination');
	$pageNav = new JPagination($rowid, $limitstart, $limit);

	$rows = array_slice($rows, $pageNav->limitstart, $pageNav->limit);

	HTML_languages::showLanguages($rows, $pageNav, $option, $client, $ftp);
}

/**
* Publish, or make current, the selected language
 */
function publishLanguage($language)
{
	// Check for request forgeries
	JRequest::checkToken() or jexit('Invalid Token');

	// Initialize some variables
	$app	= &JFactory::getApplication();
	$client	= &JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

	$params = JComponentHelper::getParams('com_languages');
	$params->set($client->name, $language);

	$table = &JTable::getInstance('component');
	$table->loadByOption('com_languages');

	$table->params = $params->toString();

	// pre-save checks
	if (!$table->check()) {
		JError::raiseWarning(500, $table->getError());
		return false;
	}

	// save the changes
	if (!$table->store()) {
		JError::raiseWarning(500, $table->getError());
		return false;
	}

	$app->redirect('index.php?option=com_languages&client='.$client->id);
}