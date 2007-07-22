<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Menus
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights
 * reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.model' );

/**
 * @package		Joomla
 * @subpackage	Menus
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

		$menutype			= $mainframe->getUserStateFromRequest( "com_menus.menutype",						'menutype',			'mainmenu',		'string' );
		$filter_order		= $mainframe->getUserStateFromRequest( 'com_menus.'.$menutype.'.filter_order',		'filter_order',		'm.ordering',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( 'com_menus.'.$menutype.'.filter_order_Dir',	'filter_order_Dir',	'ASC',			'word' );
		$filter_state		= $mainframe->getUserStateFromRequest( 'com_menus.'.$menutype.'.filter_state',		'filter_state',		'',				'word' );
		$limit				= $mainframe->getUserStateFromRequest( 'global.list.limit',							'limit',			$mainframe->getCfg( 'list_limit' ),	'int' );
		$limitstart			= $mainframe->getUserStateFromRequest( 'com_menus.'.$menutype.'.limitstart',		'limitstart',		0,				'int' );
		$levellimit			= $mainframe->getUserStateFromRequest( 'com_menus.'.$menutype.'.levellimit',		'levellimit',		10,				'int' );
		$search				= $mainframe->getUserStateFromRequest( 'com_menus.'.$menutype.'.search',			'search',			'',				'string' );
		$search				= JString::strtolower( $search );

		$and = '';
		if ( $filter_state )
		{
			if ( $filter_state == 'P' ) {
				$and = ' AND m.published = 1';
			} else if ($filter_state == 'U' ) {
				$and = ' AND m.published = 0';
			}
		}

		// just in case filter_order get's messed up
		if ($filter_order) {
			$orderby = ' ORDER BY '.$filter_order .' '. $filter_order_Dir .', m.parent, m.ordering';
		} else {
			$orderby = ' ORDER BY m.parent, m.ordering';
		}

		// select the records
		// note, since this is a tree we have to do the limits code-side
		if ($search) {
			$query = 'SELECT m.id' .
					' FROM #__menu AS m' .
					' WHERE menutype = '.$db->Quote($menutype) .
					' AND LOWER( m.name ) LIKE '.$db->Quote('%'.$search.'%') .
					$and;
			$db->setQuery( $query );
			$search_rows = $db->loadResultArray();
		}

		$query = 'SELECT m.*, u.name AS editor, g.name AS groupname, c.publish_up, c.publish_down, com.name AS com_name' .
				' FROM #__menu AS m' .
				' LEFT JOIN #__users AS u ON u.id = m.checked_out' .
				' LEFT JOIN #__groups AS g ON g.id = m.access' .
				' LEFT JOIN #__content AS c ON c.id = m.componentid AND m.type = "content_typed"' .
				' LEFT JOIN #__components AS com ON com.id = m.componentid AND m.type = "component"' .
				' WHERE m.menutype = '.$db->Quote($menutype) .
				' AND m.published != -2' .
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
		$list = JHTML::_('menu.treerecurse', 0, '', array(), $children, max( 0, $levellimit-1 ) );
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
		JArrayHelper::toInteger($cid);

		if (count($cid) < 1) {
			$this->setError(JText::_( 'Select an item to move'));
			return false;
		}

		// Query to list the selected menu items
		$db =& $this->getDBO();
		$cids = implode( ',', $cid );
		$query = 'SELECT `id`, `name`' .
				' FROM `#__menu`' .
				' WHERE `id` IN ( '.$cids.' )';

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
		foreach ($items as $id)
		{
			$curr->load( $id );
			$curr->id	= NULL;
			$curr->home	= 0;
			if ( !$curr->store() ) {
				$this->setError($row->getError());
				return false;
			}
			$itemref[] = array($id, $curr->id);
		}
		foreach ($itemref as $ref)
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
			$curr->home		= 0;
			if ( !$curr->store() ) {
				$this->setError($row->getError());
				return false;
			}
			$curr->reorder( 'menutype = '.$this->_db->Quote($curr->menutype).' AND parent = '.(int) $curr->parent );
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
			$row->reorder( 'menutype = '.$this->_db->Quote($row->menutype).' AND parent = '.(int) $row->parent );
		} // if
		return true;
	}

	function toTrash($items)
	{
		$db		=& $this->getDBO();
		$nd		= $db->getNullDate();
		$state	= -2;

		// Add all children to the list
		foreach ($items as $id)
		{
			$this->_addChildren($id, $items);
		}

		// Sent menu items to the trash
		JArrayHelper::toInteger($items, array(0));
		$where = ' WHERE (id = ' . implode( ' OR id = ', $items ) . ') AND home = 0';
		$query = 'UPDATE #__menu' .
				' SET published = '.(int) $state.', parent = 0, ordering = 0, checked_out = 0, checked_out_time = '.$db->Quote($nd) .
				$where;
		$db->setQuery( $query );
		if (!$db->query()) {
			$this->setError( $db->getErrorMsg() );
			return false;
		}

		// Clear the content cache
		// TODO: Is this necessary?
		$cache = & JFactory::getCache('com_content');
		$cache->clean();
		return count($items);
	}

	function fromTrash($items)
	{
		$db		=& $this->getDBO();
		$nd		= $db->getNullDate();
		$state	= 0;

		// Add all children to the list
		foreach ($items as $id)
		{
			$this->_addChildren($id, $items);
		}

		// Sent menu items to the trash
		JArrayHelper::toInteger($items, array(0));
		$where = ' WHERE id = ' . implode( ' OR id = ', $items );
		$query = 'UPDATE #__menu' .
				' SET published = '.(int) $state.', parent = 0, ordering = 99999, checked_out = 0, checked_out_time = '.$db->Quote($nd) .
				$where;
		$db->setQuery( $query );
		if (!$db->query()) {
			$this->setError( $db->getErrorMsg() );
			return false;
		}

		// Clear the content cache
		// TODO: Is this necessary?
		$cache = & JFactory::getCache('com_content');
		$cache->clean();
		return count($items);
	}

	/**
	* Set the state of selected menu items
	*/
	function setHome( $item )
	{
		$db =& $this->getDBO();

		// Clear home field for all other items
		$query = 'UPDATE #__menu' .
				' SET home = 0' .
				' WHERE 1';
		$db->setQuery( $query );
		if ( !$db->query() ) {
			$this->setError($db->getErrorMsg());
			return false;
		}

		// Set the given item to home
		$query = 'UPDATE #__menu' .
				' SET home = 1' .
				' WHERE id = '.(int) $item;
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
	function setItemState( $items, $state )
	{
		if(is_array($items))
		{
			$row =& $this->getTable();
			foreach ($items as $id)
			{
				$row->load( $id );

				if ($row->home != 1) {
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
				} else {
					JError::raiseWarning( 'SOME_ERROR_CODE', 'You can\'t unpublish the default menu item'  );
				}
			}
		}

		// clean menu cache
		$cache =& JFactory::getCache('mod_mainmenu');
		$cache->clean();

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

		// clean menu cache
		$cache =& JFactory::getCache('mod_mainmenu');
		$cache->clean();

		return true;
	}

	function orderItem($item, $movement)
	{
		$row =& $this->getTable();
		$row->load( $item );
		if (!$row->move( $movement, 'menutype = '.$this->_db->Quote($row->menutype).' AND parent = '.(int) $row->parent )) {
			$this->setError($row->getError());
			return false;
		}

		// clean menu cache
		$cache =& JFactory::getCache('mod_mainmenu');
		$cache->clean();

		return true;
	}

	function setOrder($items, $menutype)
	{
		$total		= count( $items );
		$row		=& $this->getTable();
		$groupings	= array();

		$order		= JRequest::getVar( 'order', array(), 'post', 'array' );
		JArrayHelper::toInteger($order);

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
			$row->reorder('menutype = '.$this->_db->Quote($menutype).' AND parent = '.(int) $group.' AND published >=0');
		}

		// clean menu cache
		$cache =& JFactory::getCache('mod_mainmenu');
		$cache->clean();

		return true;
	}

	/**
	 * Delete one or more menu items
	 * @param mixed int or array of id values
	 */
	function delete( $ids )
	{
		JArrayHelper::toInteger($ids);

		if (count( $ids )) {

			// Add all children to the list
			foreach ($ids as $id)
			{
				$this->_addChildren($id, $ids);
			}

			$db = &$this->getDBO();

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

		// clean menu cache
		$cache =& JFactory::getCache('mod_mainmenu');
		$cache->clean();

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
		$query = 'SELECT id' .
				' FROM #__menu' .
				' WHERE parent = '.(int) $id;
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
}
?>
