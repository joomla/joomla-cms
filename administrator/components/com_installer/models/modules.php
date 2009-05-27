<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Menus
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// Import library dependencies
require_once(dirname(__FILE__).DS.'extension.php');

/**
 * Extension Manager Modules Model
 *
 * @package		Joomla.Administrator
 * @subpackage	Installer
 * @since		1.5
 */
class InstallerModelModules extends InstallerModel
{
	/**
	 * Extension Type
	 * @var	string
	 */
	var $_type = 'module';

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
		$this->setState('filter.string', $mainframe->getUserStateFromRequest('com_installer.modules.string', 'filter', '', 'string'));
		$this->setState('filter.client', $mainframe->getUserStateFromRequest('com_installer.modules.client', 'client', -1, 'int'));
	}

	function _loadItems()
	{
		global $mainframe, $option;

		$db = &JFactory::getDbo();

		$and = null;
		if ($this->_state->get('filter.client') < 0) {
			if ($search = $this->_state->get('filter.string')) {
				$and = ' AND title LIKE '.$db->Quote('%'.$db->getEscaped($search, true).'%', false);
			}
		} else {
			if ($search = $this->_state->get('filter.string'))
			{
				$and = ' AND client_id = '.(int)$this->_state->get('filter.client');
				$and .= ' AND title LIKE '.$db->Quote('%'.$db->getEscaped($search, true).'%', false);
			}
			else {
				$and = ' AND client_id = '.(int)$this->_state->get('filter.client');
			}
		}

		$query = 'SELECT id, module, client_id, title, iscore' .
				' FROM #__modules' .
				' WHERE module LIKE "mod_%" ' .
				$and .
				' GROUP BY module, client_id' .
				' ORDER BY iscore, client_id, module';
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$n = count($rows);
		for ($i = 0; $i < $n; $i ++) {
			$row = & $rows[$i];

			// path to module directory
			if ($row->client_id == "1") {
				$moduleBaseDir = JPATH_ADMINISTRATOR.DS."modules";
			} else {
				$moduleBaseDir = JPATH_SITE.DS."modules";
			}

			// xml file for module
			$xmlfile = $moduleBaseDir . DS . $row->module .DS. $row->module.".xml";

			if (file_exists($xmlfile))
			{
				if ($data = JApplicationHelper::parseXMLInstallFile($xmlfile)) {
					foreach($data as $key => $value) {
						$row->$key = $value;
					}
				}
			}
		}
		$this->setState('pagination.total', $n);
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