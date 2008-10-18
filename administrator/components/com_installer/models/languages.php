<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Menus
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Import library dependencies
require_once dirname(__FILE__).DS.'extension.php';
jimport( 'joomla.filesystem.folder' );

/**
 * Installer Languages Model
 *
 * @package		Joomla
 * @subpackage	Installer
 * @since		1.5
 */
class InstallerModelLanguages extends InstallerModel
{
	/**
	 * Extension Type
	 * @var	string
	 */
	var $_type = 'language';

	/**
	 * Overridden constructor
	 * @access	protected
	 */
	function __construct()
	{
		global $mainframe;

		// Call the parent constructor
		parent::__construct();

		// Set state variables from the request
		$this->setState('filter.string', $mainframe->getUserStateFromRequest( "com_installer.languages.string", 'filter', '', 'string' ));
		$this->setState('filter.client', $mainframe->getUserStateFromRequest( "com_installer.languages.client", 'client', -1, 'int' ));
	}

	function _loadItems()
	{
		global $mainframe, $option;

		$db = &JFactory::getDBO();

		if ($this->_state->get('filter.client') < 0) {
			$client = 'all';
			// Get the site languages
			$langBDir = JLanguage::getLanguagePath(JPATH_SITE);
			$langDirs = JFolder::folders($langBDir);

			for ($i=0; $i < count($langDirs); $i++)
			{
				$lang = new stdClass();
				$lang->folder = $langDirs[$i];
				$lang->client = 0;
				$lang->baseDir = $langBDir;
				$languages[] = $lang;
			}
			// Get the admin languages
			$langBDir = JLanguage::getLanguagePath(JPATH_ADMINISTRATOR);
			$langDirs = JFolder::folders($langBDir);

			for ($i=0; $i < count($langDirs); $i++)
			{
				$lang = new stdClass();
				$lang->folder = $langDirs[$i];
				$lang->client = 1;
				$lang->baseDir = $langBDir;
				$languages[] = $lang;
			}
		}
		else
		{
			$clientInfo =& JApplicationHelper::getClientInfo($this->_state->get('filter.client'));
			$client = $clientInfo->name;
			$langBDir = JLanguage::getLanguagePath($clientInfo->path);
			$langDirs = JFolder::folders($langBDir);

			for ($i=0, $n=count($langDirs); $i < $n; $i++)
			{
				$lang = new stdClass();
				$lang->folder = $langDirs[$i];
				$lang->client = $clientInfo->id;
				$lang->baseDir = $langBDir;

				if ($this->_state->get('filter.string')) {
					if (strpos($lang->folder, $this->_state->get('filter.string')) !== false) {
						$languages[] = $lang;
					}
				} else {
					$languages[] = $lang;
				}
			}
		}

		$rows = array();
		$rowid = 0;
		foreach ($languages as $language)
		{
			$files = JFolder::files( $language->baseDir.DS.$language->folder, '^([-_A-Za-z]*)\.xml$' );
			foreach ($files as $file)
			{
				$data = JApplicationHelper::parseXMLLangMetaFile($language->baseDir.DS.$language->folder.DS.$file);

				$row 			= new StdClass();
				$row->id 		= $rowid;
				$row->client_id = $language->client;
				$row->language 	= $language->baseDir.DS.$language->folder;

				// If we didn't get valid data from the xml file, move on...
				if (!is_array($data)) {
					continue;
				}

				// Populate the row from the xml meta file
				foreach($data as $key => $value) {
					$row->$key = $value;
				}

				// if current than set published
				$clientVals =& JApplicationHelper::getClientInfo($row->client_id);
				$lang = 'lang_'.$clientVals->name;
				if ( $mainframe->getCfg($lang) == basename( $row->language ) ) {
					$row->published	= 1;
				} else {
					$row->published = 0;
				}

				$row->checked_out = 0;
				$row->jname = JString::strtolower( str_replace( " ", "_", $row->name ) );
				$rows[] = $row;
				$rowid++;
			}
		}
		$this->setState('pagination.total', count($rows));
		if($this->_state->get('pagination.limit') > 0) {
			$this->_items = array_slice( $rows, $this->_state->get('pagination.offset'), $this->_state->get('pagination.limit') );
		} else {
			$this->_items = $rows;
		}
	}

	/**
	 * Remove (uninstall) an extension
	 *
	 * @static
	 * @return boolean True on success
	 * @since 1.0
	 */
	function remove($eid=array())
	{
		global $mainframe;

		$lang =& JFactory::getLanguage();
		$lang->load('com_installer');

		// Initialize variables
		$failed = array ();

		/*
		 * Ensure eid is an array of extension ids
		 * TODO: If it isn't an array do we want to set an error and fail?
		 */
		if (!is_array($eid)) {
			$eid = array ($eid);
		}
		// construct the list of all language
		$this->_loadItems();

		// Get a database connector
		$db =& JFactory::getDBO();

		// Get an installer object for the extension type
		jimport('joomla.installer.installer');
		$installer	=& JInstaller::getInstance($db, $this->_type);

		// Uninstall the chosen extensions
		foreach ($eid as $id)
		{
			$item = $this->_items[$id];

			// Get client information
			$client	=& JApplicationHelper::getClientInfo($item->client_id);

			// Don't delete a default ( published language )
			$params = JComponentHelper::getParams('com_languages');
			$tag	= basename($item->language);
			if ( $params->get($client->name, 'en-GB') == $tag ) {
				$failed[]	= $id;
				JError::raiseWarning('', JText::_('UNINSTALLLANGPUBLISHEDALREADY'));
				return;
			}

			$result = $installer->uninstall( 'language', $item->language );

			// Build an array of extensions that failed to uninstall
			if ($result === false) {
				$failed[] = $id;
			}
		}

		if (count($failed)) {
			// There was an error in uninstalling the package
			$msg = JText::sprintf('UNINSTALLEXT', JText::_($this->_type), JText::_('Error'));
			$result = false;
		} else {
			// Package uninstalled sucessfully
			$msg = JText::sprintf('UNINSTALLEXT', JText::_($this->_type), JText::_('Success'));
			$result = true;
		}

		$mainframe->enqueueMessage($msg);
		$this->setState('action', 'remove');
		$this->setState('message', $installer->message);
		// re-construct the list of all language
		$this->_loadItems();

		return $result;
	}

}