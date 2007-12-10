<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Languages
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/*
 * Make sure the user is authorized to view this page
 */
$user = & JFactory::getUser();
if (!$user->authorize( 'com_languages', 'manage' )) {
	$mainframe->redirect( 'index.php', JText::_('ALERTNOTAUTH') );
}

require_once( JApplicationHelper::getPath( 'admin_html' ) );

$task 	= strtolower( JRequest::getCmd( 'task' ) );
$cid 	= JRequest::getVar( 'cid', array(0), '', 'array' );
$cid	= array(JFilterInput::clean(@$cid[0], 'cmd'));

$client	= JRequest::getVar('client', 0, '', 'int');
if ($client == 1) {
	JSubMenuHelper::addEntry(JText::_('Site'),'#" onclick="javascript:document.adminForm.client.value=\'0\';submitbutton(\'\');');
	JSubMenuHelper::addEntry(JText::_('Administrator'), '#" onclick="javascript:document.adminForm.client.value=\'1\';submitbutton(\'\');', true );
} else {
	JSubMenuHelper::addEntry(JText::_('Site'), '#" onclick="javascript:document.adminForm.client.value=\'0\';submitbutton(\'\');', true );
	JSubMenuHelper::addEntry(JText::_('Administrator'), '#" onclick="javascript:document.adminForm.client.value=\'1\';submitbutton(\'\');');
}

switch ($task)
{
	case 'publish':
		publishLanguage( $cid[0]);
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
	global $mainframe, $option;

	// Initialize some variables
	$db		=& JFactory::getDBO();
	$client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
	$rows	= array ();

	$limit		= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
	$limitstart = $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );

	$rowid = 0;

	// Set FTP credentials, if given
	jimport('joomla.client.helper');
	$ftp =& JClientHelper::setCredentialsFromRequest('ftp');

	//load folder filesystem class
	jimport('joomla.filesystem.folder');
	$path = JLanguage::getLanguagePath($client->path);
	$dirs = JFolder::folders( $path );

	foreach ($dirs as $dir)
	{
		$files = JFolder::files( $path.DS.$dir, '^([-_A-Za-z]*)\.xml$' );
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
			if ( $params->get($client->name, 'en-GB') == $row->language) {
				$row->published	= 1;
			} else {
				$row->published = 0;
			}

			$row->checked_out = 0;
			$row->mosname = JString::strtolower( str_replace( " ", "_", $row->name ) );
			$rows[] = $row;
			$rowid++;
		}
	}


	jimport('joomla.html.pagination');
	$pageNav = new JPagination( $rowid, $limitstart, $limit );

	$rows = array_slice( $rows, $pageNav->limitstart, $pageNav->limit );

	HTML_languages::showLanguages( $rows, $pageNav, $option, $client, $ftp );
}

/**
* Publish, or make current, the selected language
*/
function publishLanguage( $language )
{
	global $mainframe;

	// Check for request forgeries.
	$token = JUtility::getToken();
	if (!JRequest::getInt($token, 0, 'post')) {
		JError::raiseError(403, 'Request Forbidden');
	}

	// Initialize some variables
	$client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

	$params = JComponentHelper::getParams('com_languages');
	$params->set($client->name, $language);

	$table =& JTable::getInstance('component');
	$table->loadByOption( 'com_languages' );

	$table->params = $params->toString();

	// pre-save checks
	if (!$table->check()) {
		JError::raiseWarning( 500, $table->getError() );
		return false;
	}

	// save the changes
	if (!$table->store()) {
		JError::raiseWarning( 500, $table->getError() );
		return false;
	}

	$mainframe->redirect('index.php?option=com_languages&client='.$client->id);
}
