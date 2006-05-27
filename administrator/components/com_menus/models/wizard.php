<?php
/**
 * @version $Id: admin.menus.php 3504 2006-05-15 05:25:43Z eddieajau $
 * @package Joomla
 * @subpackage Menus
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights
 * reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport( 'joomla.application.model' );

/**
 * @package Joomla
 * @subpackage Menus
 * @author Andrew Eddie
 */
class JMenuModelWizard extends JModel
{

	var $_step = null;

	/** @var object JRegistry object */
	var $_item = null;

	function init()
	{
		$app =& $this->getApplication();
		$this->_step = JRequest::getVar('step', 0, '', 'int');
		$type = $app->getUserStateFromRequest('menuwizard.type', 'type');
		if ($this->_step && $type) {
			require_once(COM_MENUS.'helpers'.DS.$type.'.php');
			$class = 'JMenuHelper'.ucfirst($type);
			$this->_helper = new $class($app);
		}
		
		// Build registry path
		$regPath = 'newmenu.wizard';
		if ($type) {
			$regPath .= '.'.$type;
		}

		$this->_item = $app->getUserState($regPath);
		
		// Create the object if it does not exist
		if (!is_a($this->_item, 'JParameter')) {
			$this->_item = new JParameter('');
		}
		
		$items = JRequest::getVar('wizVal', array(), '', 'array');
		$this->_item->loadArray($items);
		$app->setUserState($regPath, $this->_item);
	}

	function &getItem()
	{
		return $this->_helper->getParams($this->_item, $this->_step);
	}

	function getStep()
	{
		return $this->_step;
	}

	function getSteps()
	{
		return $this->_helper->getSteps();
	}

	function isStarted()
	{
		return ($this->_step);
	}

	function isFinished()
	{
		$steps = $this->_helper->getSteps();
		return (count($steps) <= $this->_step);
	}

	/**
	 * Get a list of the menu_types records
	 * @return array An array of records as objects
	 */
	function getMenuTypeList()
	{
		$db = $this->getDBO();
		$query = 'SELECT * FROM #__menu_types';
		$db->setQuery( $query );
		return $db->loadObjectList();
	}

	/**
	 * Get a list of the menutypes
	 * @return array An array of menu type names
	 */
	function getMenuTypes()
	{
		$db = $this->getDBO();
		$query = 'SELECT menutype FROM #__menu_types';
		$db->setQuery( $query );
		return $db->loadResultArray();
	}

	/**
	 * Gets a list of components that can link to the menu
	 */
	function getComponentList()
	{
		$db = $this->getDBO();
		$query = "SELECT c.id, c.name, c.link, c.option"
		. "\n FROM #__components AS c"
		. "\n WHERE c.link <> '' AND parent = 0"
		. "\n ORDER BY c.name"
		;
		$db->setQuery( $query );
		$result = $db->loadObjectList( );
		return $result;
	}
}
?>