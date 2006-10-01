<?php
/**
 * @version $Id: item.php 5216 2006-09-27 19:02:42Z Jinx $
 * @package Joomla
 * @subpackage Menus
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights
 * reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Import library dependencies
require_once(dirname(__FILE__).DS.'extension.php');

/**
 * Extension Manager Plugins Model
 * 
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package		Joomla
 * @subpackage	Installer
 * @since		1.5
 */
class ExtensionManagerModelPlugins extends ExtensionManagerModel
{
	/**
	 * Extension Type
	 * @var	string
	 */
	var $_type = 'plugin';

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
		$this->setState('filter.group', $mainframe->getUserStateFromRequest( "com_installer.plugins.group", 'group' ));
		$this->setState('filter.string', $mainframe->getUserStateFromRequest( "com_installer.plugins.string", 'filter' ));
	}

	function &getGroups()
	{
		// Get a database connector object
		$db = &$this->getDBO();

		// get list of Positions for dropdown filter
		$query = "SELECT folder AS value, folder AS text" .
				"\n FROM #__plugins" .
				"\n GROUP BY folder" .
				"\n ORDER BY folder";
		$db->setQuery( $query );

		$types[] = mosHTML::makeOption( '', JText::_( 'All' ) );
		$types = array_merge( $types, $db->loadObjectList() );

		return $types;
	}

	function _loadItems()
	{
		global $mainframe, $option;

		// Get a database connector
		$db = & JFactory::getDBO();

		$where = null;
		if ($this->_state->get('filter.group')) {
			if ($this->_state->get('filter.string')) {
				$where = "\n WHERE folder = '".$db->getEscaped($this->_state->get('filter.group'))."'";
				$where .= "\n AND name LIKE '%".$db->getEscaped($this->_state->get('filter.string'))."%'";
			} else {
				$where = "\n WHERE folder = '".$db->getEscaped($this->_state->get('filter.group'))."'";
			}
		} else {
			if ($this->_state->get('filter.string')) {
				$where .= "\n WHERE name LIKE '%".$db->getEscaped($this->_state->get('filter.string'))."%'";
			}
		}

		$query = "SELECT id, name, folder, element, client_id, iscore" .
				"\n FROM #__plugins" .
				$where .
				"\n ORDER BY iscore, folder, name";
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		// Get the plugin base path
		$baseDir = JPATH_ROOT.DS.'plugins';

		$numRows = count($rows);
		for ($i = 0; $i < $numRows; $i ++) {
			$row = & $rows[$i];

			// Get the plugin xml file
			$xmlfile = $baseDir.DS.$row->folder.DS.$row->element.".xml";

			if (file_exists($xmlfile)) {
				if ($data = JApplicationHelper::parseXMLInstallFile($xmlfile)) {
					foreach($data as $key => $value)
					{
						$row->$key = $value;
					}
				}
			}
		}

		$this->setState('pagination.total', $numRows);
		$this->_items = array_slice( $rows, $this->_state->get('pagination.offset'), $this->_state->get('pagination.limit') );
	}
}
?>