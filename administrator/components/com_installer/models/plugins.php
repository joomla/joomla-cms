<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Menus
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Import library dependencies
require_once(dirname(__FILE__).DS.'extension.php');

/**
 * Installer Plugins Model
 *
 * @package		Joomla.Administrator
 * @subpackage	Installer
 * @since		1.5
 */
class InstallerModelPlugins extends InstallerModel
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
		$this->setState('filter.group', $mainframe->getUserStateFromRequest("com_installer.plugins.group", 'group', '', 'cmd'));
		$this->setState('filter.string', $mainframe->getUserStateFromRequest("com_installer.plugins.string", 'filter', '', 'string'));
	}

	function &getGroups()
	{
		// Get a database connector object
		$db = &$this->getDbo();

		// get list of Positions for dropdown filter
		$query = 'SELECT folder AS value, folder AS text' .
				' FROM #__extensions' .
				' WHERE type = "plugin"' .
				' GROUP BY folder' .
				' ORDER BY folder';
		$db->setQuery($query);

		$types[] = JHtml::_('select.option',  '', JText::_('All'));
		$types = array_merge($types, $db->loadObjectList());

		return $types;
	}

	function _loadItems()
	{
		global $mainframe, $option;

		// Get a database connector
		$db = & JFactory::getDbo();

		$where = null;
		if ($this->_state->get('filter.group')) {
			if ($search = $this->_state->get('filter.string'))
			{
				$where = ' WHERE folder = "'.$db->getEscaped($this->_state->get('filter.group')).'"';
				$where .= ' AND name LIKE '.$db->Quote('%'.$db->getEscaped($search, true).'%', false);
			}
			else {
				$where = ' WHERE folder = "'.$db->getEscaped($this->_state->get('filter.group')).'"';
			}
		} else {
			if ($search = $this->_state->get('filter.string')) {
				$where .= ' WHERE name LIKE '.$db->Quote('%'.$db->getEscaped($search, true).'%', false);
			}
		}

		$where = $where ? $where . ' AND type = "plugin"' : ' WHERE type = "plugin"';
		$query = 'SELECT id, name, folder, element, client_id, protected' .
				' FROM #__extensions' .
				$where .
				' ORDER BY protected, folder, name';
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
		// if the offset is greater than the total, then can the offset
		if ($this->_state->get('pagination.offset') > $this->_state->get('pagination.total')) {
			$this->setState('pagination.offset',0);
		}

		if ($this->_state->get('pagination.limit') > 0) {
			$this->_items = array_slice($rows, $this->_state->get('pagination.offset'), $this->_state->get('pagination.limit'));
		} else {
			$this->_items = $rows;
		}
	}
}