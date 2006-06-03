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
class JMenuModelItem extends JModel
{
	/** @var object JTable object */
	var $_table = null;

	/**
	 * Returns the internal table object
	 * @return JTable
	 */
	function &getTable()
	{
		if ($this->_table == null)
		{
			jimport( 'joomla.database.table.menu' );

			$db = &$this->getDBO();
			$this->_table = new JTableMenu( $db );
		}
		return $this->_table;
	}

	function &getItem() {
		$table =& $this->getTable();
		
		if ($id = JRequest::getVar('id', '', '', 'int')) {
			$table->load($id);
		}

		if ($type = JRequest::getVar('type', false)) {
			$table->type = $type;
		}

		if ($menu_type = JRequest::getVar('menu_type', false)) {
			$table->menutype = $menu_type;
		}
		return $table;
	}

	function &getDetails()
	{
		$item =& $this->getItem();

		// Include and create the helper object
		if ($item->type) {
			require_once(COM_MENUS.'helpers'.DS.$item->type.'.php');
			$class = 'JMenuHelper'.ucfirst($item->type);
			$this->_helper =& new $class($this);
			$details =& $this->_helper->getDetails();
		} else {
			$details = array();
		}
		return $details;
	}

	function &getControlParams()
	{
		$item =& $this->getItem();
		$ini = $item->control;
		$params =& new JParameter($ini);

		if ($control = JRequest::getVar('control', false, '', 'array')) {
			$params->loadArray($control);
		}

		return $params;
	}

	function &getControlFields()
	{
		$params =& $this->getControlParams();
		
		$array = $params->toArray();

		foreach($array as $k => $v) {
			$fields[] = "<input type=\"hidden\" name=\"control[$k]\" value=\"$v\" />";
		}

		return $fields;
	}

	function &getStateParams()
	{
		$ini = $item->params;
		$params =& new JParameter($ini);

		if ($state =& $this->_getStateXML()) {
			if (is_a($state, 'JSimpleXMLElement')) {
				$sp =& $state->getElementByPath('params');
				$params->setXML($sp);
			}
		}
		return $params;
	}

	function getStateName()
	{
		if ($state =& $this->_getStateXML()) {
			if (is_a($state, 'JSimpleXMLElement')) {
				$sn =& $state->getElementByPath('name');
				$name = $sn->data();
			}
		}
		return $name;
	}

	function getStateDescription()
	{
		if ($state =& $this->_getStateXML()) {
			if (is_a($state, 'JSimpleXMLElement')) {
				$sd =& $state->getElementByPath('description');
				$description = $sd->data();
			}
		}
		return $description;
	}

	function store()
	{
		$row =& $this->getItem();
	
		// Include and create the helper object
		if ($row->type && file_exists(COM_MENUS.'helpers'.DS.$row->type.'.php')) {
			require_once(COM_MENUS.'helpers'.DS.$row->type.'.php');
			$class = 'JMenuHelper'.ucfirst($row->type);
			$this->_helper =& new $class($this);
			$values =& $this->_helper->prepForStore($_POST);
		} else {
			$values =& $_POST;
		}

		if (!$row->bind( $values )) {
//			echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
			return false;
		}
	
		$row->name = ampReplace( $row->name );
	
		if (!$row->check()) {
//			echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
			return false;
		}
		if (!$row->store()) {
//			echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
			return false;
		}
		$row->checkin();
		$row->reorder( "menutype = '$row->menutype' AND parent = $row->parent" );

		return true;
	}

	/**
	 * Delete one or more menu items
	 * @param mixed int or array of id values
	 */
	function delete( $ids )
	{
		if (!is_array( $ids ))
		{
			$ids = array( $ids );
		}

		$db = &$this->getDBO();
		
		if (count( $ids ))
		{
			// Delete associated module and template mappings
			$where = 'WHERE menuid = ' . implode( ' OR menuid = ', $ids );

			$query = 'DELETE FROM #__modules_menu '
				. $where;
			$db->setQuery( $query );
			if (!$db->query())
			{
				$this->setError( $menuTable->getErrorMsg() );
				return false;				
			}

			$query = 'DELETE FROM #__templates_menu '
				. $where;
			$db->setQuery( $query );
			if (!$db->query())
			{
				$this->setError( $menuTable->getErrorMsg() );
				return false;				
			}

			// Delete the menu items
			$where = 'WHERE id = ' . implode( ' OR id = ', $ids );
			
			$query = 'DELETE FROM #__menu ' . $where;
			$db->setQuery( $query );
			if (!$db->query())
			{
				$this->setError( $db->getErrorMsg() );
				return false;
			}
		}
		return true;
	}

	/**
	 * Delete menu items by type
	 */
	function deleteByType( $type = '' )
	{
		$db = &$this->getDBO();
		
		$query = 'SELECT id' .
				' FROM #__menu' .
				' WHERE menutype = ' . $db->Quote( $type );
		$db->setQuery( $query );
		$ids = $db->loadResultArray();
		
		if ($db->getErrorNum())
		{
			$this->setError( $db->getErrorMsg() );
			return false;
		}

		return $this->delete( $ids );
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
	 * Gets the componet table object related to this menu item
	 */
	function &getComponent()
	{
		jimport( 'joomla.database.table.component' );
		$db = $this->getDBO();
		$id = $this->_table->componentid;
		$component = new JTableComponent( $db );
		$component->load( $id );
		return $component;
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

	function &_getStateXML()
	{

		static $xml;
		
		if (isset($xml)) {
			return $xml;
		}

		$item =& $this->getItem();

		// Include and create the helper object
		if ($item->type) {
			require_once(COM_MENUS.'helpers'.DS.$item->type.'.php');
			$class = 'JMenuHelper'.ucfirst($item->type);
			$this->_helper =& new $class($this);
			$xmlInfo =& $this->_helper->getStateXML();
		} else {
			// Set default state params...
		}

		if (file_exists( $xmlInfo['path'] )) {

			$xml =& JFactory::getXMLParser('Simple');
			if ($xml->loadFile($xmlInfo['path'])) {
				$this->_xml = &$xml;
				$document =& $xml->document;
				$xml =& $document->getElementByPath($xmlInfo['xpath']);

				/*
				 * HANDLE A SWITCH IF IT EXISTS
				 */
				if ($switch = $xml->attributes('switch')) {
					// Handle switch
					$control =& $this->getControlParams();
					$switchVal = $control->get($switch, 'default');

					foreach ($xml->children() as $child) {
						if ($child->name() == $switchVal) {
							$xml =& $child;
							break;
						}
					}
				}

				/*
				 * HANDLE INCLUDED PARAMS
				 */
				$children =& $xml->children();
				if (count($children) == 1) {
					if ($children[0]->name() == 'include') {
						$xml =& $this->_getIncludedParams($children[0]);
					}
				}

				if ($switch = $xml->attributes('switch')) {
					// Handle switch
					$control =& $this->getControlParams();
					$switchVal = $control->get($switch, 'default');

					foreach ($xml->children() as $child) {
						if ($child->name() == $switchVal) {
							$xml =& $child;
							break;
						}
					}
				}
			}
		return $xml;
		}
	}

	function &_getIncludedParams($include)
	{
		$tags	= array();
		$state	= null;
		$source	= $include->attributes('source');
		$path	= $include->attributes('path');
		$control =& $this->getControlParams();

		preg_match_all( "/{([A-Za-z\-_]+)}/", $source, $tags);
		if (isset($tags[1])) {
			for ($i=0;$i<count($tags[1]);$i++) {
				$source = str_replace($tags[0][$i], $control->get($tags[1][$i]), $source);
			}
		}

		// load the source xml file
		if (file_exists( JPATH_ROOT.$source )) {
			$xml = & JFactory::getXMLParser('Simple');

			if ($xml->loadFile(JPATH_ROOT.$source)) {
				$document = &$xml->document;
				$state = $document->getElementByPath($path);
			}
		}
		return $state;
	}
}
?>