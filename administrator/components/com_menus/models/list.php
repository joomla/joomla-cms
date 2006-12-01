<?php
/**
 * @version $Id$
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

jimport( 'joomla.application.component.model' );

/**
 * @package Joomla
 * @subpackage Menus
 * @author Andrew Eddie
 */
class MenusModelList extends JModel
{
	/** @var object JTable object */
	var $_table = null;

	var $_pagination = null;

	/**
	 * Returns the internal table object
	 * @return JTable
	 */
	function &getTable()
	{
		if ($this->_table == null)
		{
			$this->_table =& JTable::getInstance( 'menu');
		}
		return $this->_table;
	}

	function &getItems()
	{
		global $mainframe;

		static $items;

		if (isset($items)) {
			return $items;
		}

		$db =& $this->getDBO();

		$menutype 			= $mainframe->getUserStateFromRequest( "com_menus.menutype",				 		'menutype', 		'mainmenu' );
		$filter_order		= $mainframe->getUserStateFromRequest( "com_menus.$menutype.filter_order", 		'filter_order', 	'm.ordering' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( "com_menus.$menutype.filter_order_Dir",	'filter_order_Dir',	'ASC' );
		$filter_state		= $mainframe->getUserStateFromRequest( "com_menus.$menutype.filter_state", 		'filter_state', 	'*' );
		$limit 				= $mainframe->getUserStateFromRequest( "limit", 									'limit', 			$mainframe->getCfg( 'list_limit' ) );
		$limitstart 		= $mainframe->getUserStateFromRequest( "com_menus.$menutype.limitstart", 			'limitstart', 		0 );
		$levellimit 		= $mainframe->getUserStateFromRequest( "com_menus.$menutype.levellimit", 			'levellimit', 		10 );
		$search 			= $mainframe->getUserStateFromRequest( "com_menus.$menutype.search", 				'search', 			'' );
		$search 			= $db->getEscaped( JString::strtolower( $search ) );

		$and = '';
		if ( $filter_state )
		{
			if ( $filter_state == 'P' ) {
				$and = "\n AND m.published = 1";
			} else if ($filter_state == 'U' ) {
				$and = "\n AND m.published = 0";
			}
		}

		// just in case filter_order get's messed up
		if ($filter_order) {
			$orderby = "\n ORDER BY $filter_order $filter_order_Dir, m.parent, m.ordering";
		} else {
			$orderby = "\n ORDER BY m.parent, m.ordering";
		}

		// select the records
		// note, since this is a tree we have to do the limits code-side
		if ($search) {
			$query = "SELECT m.id" .
					"\n FROM #__menu AS m" .
					"\n WHERE menutype = '$menutype'" .
					"\n AND LOWER( m.name ) LIKE '%".JString::strtolower( $search )."%'" .
					$and;
			$db->setQuery( $query );
			$search_rows = $db->loadResultArray();
		}

		$query = "SELECT m.*, u.name AS editor, g.name AS groupname, c.publish_up, c.publish_down, com.name AS com_name" .
				"\n FROM #__menu AS m" .
				"\n LEFT JOIN #__users AS u ON u.id = m.checked_out" .
				"\n LEFT JOIN #__groups AS g ON g.id = m.access" .
				"\n LEFT JOIN #__content AS c ON c.id = m.componentid AND m.type = 'content_typed'" .
				"\n LEFT JOIN #__components AS com ON com.id = m.componentid AND m.type = 'component'" .
				"\n WHERE m.menutype = '$menutype'" .
				"\n AND m.published != -2" .
				$and .
				$orderby;
		$db->setQuery( $query );
		$rows = $db->loadObjectList();

		// establish the hierarchy of the menu
		$children = array();
		// first pass - collect children
		foreach ($rows as $v )
		{
			$pt = $v->parent;
			$list = @$children[$pt] ? $children[$pt] : array();
			array_push( $list, $v );
			$children[$pt] = $list;
		}
		// second pass - get an indent list of the items
		$list = mosTreeRecurse( 0, '', array(), $children, max( 0, $levellimit-1 ) );
		// eventually only pick out the searched items.
		if ($search) {
			$list1 = array();

			foreach ($search_rows as $sid )
			{
				foreach ($list as $item)
				{
					if ($item->id == $sid) {
						$list1[] = $item;
					}
				}
			}
			// replace full list with found items
			$list = $list1;
		}

		$total = count( $list );

		jimport('joomla.html.pagination');
		$this->_pagination = new JPagination( $total, $limitstart, $limit );

		// slice out elements based on limits
		$list = array_slice( $list, $this->_pagination->limitstart, $this->_pagination->limit );

		$i = 0;
		foreach ( $list as $mitem )
		{
			$edit = '';
			switch ( $mitem->type )
			{
				case 'separator':
					$list[$i]->descrip 	= JText::_('Separator');
					break;

				case 'url':
					$list[$i]->descrip 	= JText::_('URL');
					break;

				case 'menulink':
					$list[$i]->descrip 	= JText::_('Menu Link');
					break;

				case 'component':
					$list[$i]->descrip 	= JText::_('Component');
					break;

				default:
					$list[$i]->descrip 	= JText::_('Unknown');
					break;
			}
			$i++;
		}

		$items = $list;
		return $items;
	}

	function &getPagination()
	{
		if ($this->_pagination == null) {
			$this->getItems();
		}
		return $this->_pagination;
	}

	/**
	* Form for copying item(s) to a specific menu
	*/
	function getItemsFromRequest()
	{
		static $items;

		if (isset($items)) {
			return $items;
		}

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		if (!is_array( $cid ) || count( $cid ) < 1) {
			$this->setError(JText::_( 'Select an item to move'));
			return false;
		}

		// Query to list the selected menu items
		$db =& $this->getDBO();
		$cids = implode( ',', $cid );
		$query = "SELECT `id`, `name`" .
				"\n FROM `#__menu`" .
				"\n WHERE `id` IN ( $cids )";

		$db->setQuery( $query );
		$items = $db->loadObjectList();

		return $items;
	}

	/**
	 * Gets the componet table object related to this menu item
	 */
	function &getComponent()
	{
		$id = $this->_table->componentid;
		$component	= & JTable::getInstance( 'component');
		$component->load( $id );
		return $component;
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function copy( $items, $menu )
	{
		$curr =& JTable::getInstance('menu');
		$itemref = array();
		foreach( $items as $id )
		{
			$curr->load( $id );
			$curr->id = NULL;
			if ( !$curr->store() ) {
				$this->setError($row->getError());
				return false;
			}
			$itemref[] = array($id, $curr->id);
		}
		foreach ( $itemref as $ref )
		{
			$curr->load( $ref[1] );
			if ($curr->parent!=0) {
				$found = false;
				foreach ( $itemref as $ref2 )
				{
					if ($curr->parent == $ref2[0]) {
						$curr->parent = $ref2[1];
						$found = true;
						break;
					} // if
				}
				if (!$found && $curr->menutype!=$menu) {
					$curr->parent = 0;
				}
			} // if
			$curr->menutype = $menu;
			$curr->ordering = '9999';
			if ( !$curr->store() ) {
				$this->setError($row->getError());
				return false;
			}
			$curr->reorder( "menutype = '$curr->menutype' AND parent = $curr->parent" );
		} // foreach
		return true;
	}

	function move($items, $menu)
	{
		// Add all children to the list
		foreach ($items as $id)
		{
			$this->_addChildren($id, $items);
		}

		$row =& $this->getTable();
		$ordering = 1000000;
		$firstroot = 0;
		foreach ($items as $id) {
			$row->load( $id );

			// is it moved together with his parent?
			$found = false;
			if ($row->parent != 0) {
				foreach ($items as $idx)
				{
					if ($idx == $row->parent) {
						$found = true;
						break;
					} // if
				}
			}
			if (!$found) {
				$row->parent = 0;
				$row->ordering = $ordering++;
				if (!$firstroot) $firstroot = $row->id;
			} // if

			$row->menutype = $menu;
			if ( !$row->store() ) {
				$this->setError($row->getError());
				return false;
			} // if
		} // foreach

		if ($firstroot) {
			$row->load( $firstroot );
			$row->reorder( "menutype = '$row->menutype' AND parent = $row->parent" );
		} // if
		return true;
	}

	function toTrash($items, $menutype)
	{
		$db		=& $this->getDBO();
		$nd		= $db->getNullDate();
		$state	= -2;

		$query = "SELECT *" .
				"\n FROM #__menu" .
				"\n WHERE menutype = '$menutype'" .
				"\n AND published != $state" .
				"\n ORDER BY menutype, parent, ordering";
		$db->setQuery( $query );
		$mitems = $db->loadObjectList();

		// determine if selected item has an child items
		$children = array();
		foreach ( $items as $id )
		{
			foreach ( $mitems as $item )
			{
				if ( $item->parent == $id ) {
					$children[] = $item->id;
				}
			}
		}
		$list 	= $this->_josMenuChildrenRecurse( $mitems, $children, $children );
		$list 	= array_merge( $items, $list );

		$ids 	= implode( ',', $list );

		$query = "UPDATE #__menu" .
				"\n SET published = $state, ordering = 0, checked_out = 0, checked_out_time = '$nd'" .
				"\n WHERE id IN ( $ids )";
		$db->setQuery( $query );
		if ( !$db->query() ) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Clear the content cache
		// TODO: Is this necessary?
		$cache = & JFactory::getCache('com_content');
		$cache->cleanCache();
		return true;
	}

	/**
	* Set the state of selected menu items
	*/
	function setHome( $item )
	{
		$db =& $this->getDBO();

		// Clear home field for all other items
		$query = "UPDATE #__menu" .
				"\n SET home = 0" .
				"\n WHERE 1";
		$db->setQuery( $query );
		if ( !$db->query() ) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Set the given item to home
		$query = "UPDATE #__menu" .
				"\n SET home = 1" .
				"\n WHERE id = $item";
		$db->setQuery( $query );
		if ( !$db->query() ) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		return true;
	}

	/**
	* Set the state of selected menu items
	*/
	function setState( $items, $state )
	{
		if(is_array($items)) 
		{
			$row =& $this->getTable();
			foreach ($items as $id)
			{
				$row->load( $id );
				$row->published = $state;

				if ($state != 1) {
					// Set any alias menu types to not point to unpublished menu items
					$db = &$this->getDBO();
					$query = 'UPDATE #__menu SET link = 0 WHERE type = \'menulink\' AND link = '.(int)$id;
					$db->setQuery( $query );
					if (!$db->query()) {
						$this->setError( $db->getErrorMsg() );
						return false;
					}
				}

				if (!$row->check()) {
					$this->setError($row->getError());
					return false;
				}
				if (!$row->store()) {
					$this->setError($row->getError());
					return false;
				}
			}
		}
		// Clear the content cache
		// TODO: Is this necessary?
		$cache = & JFactory::getCache('com_content');
		$cache->cleanCache();
		return true;
	}

	/**
	* Set the access of selected menu items
	*/
	function setAccess( $items, $access )
	{
		$row =& $this->getTable();
		foreach ($items as $id)
		{
			$row->load( $id );
			$row->access = $access;

			// Set any alias menu types to not point to unpublished menu items
			$db = &$this->getDBO();
			$query = 'UPDATE #__menu SET link = 0 WHERE type = \'menulink\' AND access < '.(int)$access.' AND link = '.(int)$id;
			$db->setQuery( $query );
			if (!$db->query()) {
				$this->setError( $db->getErrorMsg() );
				return false;
			}

			if (!$row->check()) {
				$this->setError($row->getError());
				return false;
			}
			if (!$row->store()) {
				$this->setError($row->getError());
				return false;
			}
		}
		// Clear the content cache
		// TODO: Is this necessary?
		$cache = & JFactory::getCache('com_content');
		$cache->cleanCache();
		return true;
	}

	function orderItem($item, $movement)
	{
		$row =& $this->getTable();
		$row->load( $item );
		if (!$row->move( $movement, "menutype = '$row->menutype' AND parent = $row->parent" )) {
			$this->setError($row->getError());
			return false;
		}
		// Clear the content cache
		// TODO: Is this necessary?
		$cache = & JFactory::getCache('com_content');
		$cache->cleanCache();
		return true;
	}

	function setOrder($items, $menutype)
	{
		$total		= count( $items );
		$order 		= JRequest::getVar( 'order', array(), 'post', 'array' );
		$row		=& $this->getTable();
		$groupings = array();

		// update ordering values
		for( $i=0; $i < $total; $i++ ) {
			$row->load( $items[$i] );
			// track parents
			$groupings[] = $row->parent;
			if ($row->ordering != $order[$i]) {
				$row->ordering = $order[$i];
				if (!$row->store()) {
					$this->setError($row->getError());
					return false;
				}

			} // if
		} // for

		// execute updateOrder for each parent group
		$groupings = array_unique( $groupings );
		foreach ($groupings as $group){
			$row->reorder("menutype = '$menutype' AND parent = $group AND published >=0");
		}


		// Clear the content cache
		// TODO: Is this necessary?
		$cache = & JFactory::getCache('com_content');
		$cache->cleanCache();
		return true;
	}

	/**
	 * Delete one or more menu items
	 * @param mixed int or array of id values
	 */
	function delete( $ids )
	{
		if (!is_array( $ids )) {
			$ids = array( $ids );
		}

		$db = &$this->getDBO();

		if (count( $ids )) {
			// Delete associated module and template mappings
			$where = 'WHERE menuid = ' . implode( ' OR menuid = ', $ids );

			$query = 'DELETE FROM #__modules_menu '
				. $where;
			$db->setQuery( $query );
			if (!$db->query()) {
				$this->setError( $menuTable->getErrorMsg() );
				return false;
			}

			$query = 'DELETE FROM #__templates_menu '
				. $where;
			$db->setQuery( $query );
			if (!$db->query()) {
				$this->setError( $menuTable->getErrorMsg() );
				return false;
			}

			// Set any alias menu types to not point to missing menu items
			$query = 'UPDATE #__menu SET link = 0 WHERE type = \'menulink\' AND (link = '.implode( ' OR id = ', $ids ).')';
			$db->setQuery( $query );
			if (!$db->query()) {
				$this->setError( $db->getErrorMsg() );
				return false;
			}

			// Delete the menu items
			$where = 'WHERE id = ' . implode( ' OR id = ', $ids );

			$query = 'DELETE FROM #__menu ' . $where;
			$db->setQuery( $query );
			if (!$db->query()) {
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

		if ($db->getErrorNum()) {
			$this->setError( $db->getErrorMsg() );
			return false;
		}

		return $this->delete( $ids );
	}

	function _addChildren($id, &$list)
	{
		// Initialize variables
		$return = true;

		// Get all rows with parent of $id
		$db =& $this->getDBO();
		$query = "SELECT id" .
				"\n FROM #__menu" .
				"\n WHERE parent = $id";
		$db->setQuery( $query );
		$rows = $db->loadObjectList();

		// Make sure there aren't any errors
		if ($db->getErrorNum()) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Recursively iterate through all children... kinda messy
		// TODO: Cleanup this method
		foreach ($rows as $row)
		{
			$found = false;
			foreach ($list as $idx)
			{
				if ($idx == $row->id) {
					$found = true;
					break;
				}
			}
			if (!$found) {
				$list[] = $row->id;
			}
			$return = $this->_addChildren($row->id, $list);
		}
		return $return;
	}

	/**
	* Returns list of child items for a given set of ids from menu items supplied
	*
	*/
	function _josMenuChildrenRecurse( $mitems, $parents, $list, $maxlevel=99, $level=0 ) {
		// check to reduce recursive processing
		if ( $level <= $maxlevel && count( $parents ) ) {
			$children = array();
			foreach ( $parents as $id )
			{
				foreach ( $mitems as $item )
				{
					if ( $item->parent == $id ) {
						$children[] = $item->id;
					}
				}
			}

			// check to reduce recursive processing
			if ( count( $children ) ) {
				$list = $this->_josMenuChildrenRecurse( $mitems, $children, $list, $maxlevel, $level+1 );
				$list = array_merge( $list, $children );
			}
		}
		return $list;
	}
}
?>