<?php
/**
* @version		$Id: helper.php 7286 2007-05-03 01:44:29Z jinx $
* @package		Joomla
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

/**
 * Utility class working with menu select lists
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
class JHTMLMenu
{
   /**
	* Build the select list for Menu Ordering
	*/
	function ordering( &$row, $id )
	{
		$db =& JFactory::getDBO();

		if ( $id )
		{
			$query = 'SELECT ordering AS value, name AS text'
			. ' FROM #__menu'
			. ' WHERE menutype = \''.$row->menutype
			. '\' AND parent = '.$row->parent
			. ' AND published != -2'
			. ' ORDER BY ordering';
			$order = JHTML::_('list.genericordering',  $query );
			$ordering = JHTML::_('select.genericlist',   $order, 'ordering', 'class="inputbox" size="1"', 'value', 'text', intval( $row->ordering ) );
		}
		else
		{
			$ordering = '<input type="hidden" name="ordering" value="'. $row->ordering .'" />'. JText::_( 'DESCNEWITEMSLAST' );
		}
		return $ordering;
	}

	/**
	* Build the multiple select list for Menu Links/Pages
	*/
	function linkoptions( $all=false, $unassigned=false )
	{
		$db =& JFactory::getDBO();

		// get a list of the menu items
		$query = 'SELECT m.id, m.parent, m.name, m.menutype'
		. ' FROM #__menu AS m'
		. ' WHERE m.published = 1'
		. ' ORDER BY m.menutype, m.parent, m.ordering'
		;
		$db->setQuery( $query );
		$mitems = $db->loadObjectList();
		$mitems_temp = $mitems;

		// establish the hierarchy of the menu
		$children = array();
		// first pass - collect children
		foreach ( $mitems as $v )
		{
			$id = $v->id;
			$pt = $v->parent;
			$list = @$children[$pt] ? $children[$pt] : array();
			array_push( $list, $v );
			$children[$pt] = $list;
		}
		// second pass - get an indent list of the items
		$list = JHTMLMenu::TreeRecurse( intval( $mitems[0]->parent ), '', array(), $children, 9999, 0, 0 );

		// Code that adds menu name to Display of Page(s)
		$mitems_spacer 	= $mitems_temp[0]->menutype;

		$mitems = array();
		if ($all | $unassigned) {
			$mitems[] = JHTML::_('select.option',  '<OPTGROUP>', JText::_( 'Menus' ) );

			if ( $all ) {
				$mitems[] = JHTML::_('select.option',  0, JText::_( 'All' ) );
			}
			if ( $unassigned ) {
				$mitems[] = JHTML::_('select.option',  -1, JText::_( 'Unassigned' ) );
			}

			$mitems[] = JHTML::_('select.option',  '</OPTGROUP>' );
		}

		$lastMenuType	= null;
		$tmpMenuType	= null;
		foreach ($list as $list_a)
		{
			if ($list_a->menutype != $lastMenuType)
			{
				if ($tmpMenuType) {
					$mitems[] = JHTML::_('select.option',  '</OPTGROUP>' );
				}
				$mitems[] = JHTML::_('select.option',  '<OPTGROUP>', $list_a->menutype );
				$lastMenuType = $list_a->menutype;
				$tmpMenuType  = $list_a->menutype;
			}

			$mitems[] = JHTML::_('select.option',  $list_a->id, $list_a->treename );
		}
		if ($lastMenuType !== null) {
			$mitems[] = JHTML::_('select.option',  '</OPTGROUP>' );
		}

		return $mitems;
	}

	function treerecurse( $id, $indent, $list, &$children, $maxlevel=9999, $level=0, $type=1 )
	{
		if (@$children[$id] && $level <= $maxlevel)
		{
			foreach ($children[$id] as $v)
			{
				$id = $v->id;

				if ( $type ) {
					$pre 	= '<sup>L</sup>&nbsp;';
					$spacer = '.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				} else {
					$pre 	= '- ';
					$spacer = '&nbsp;&nbsp;';
				}

				if ( $v->parent == 0 ) {
					$txt 	= $v->name;
				} else {
					$txt 	= $pre . $v->name;
				}
				$pt = $v->parent;
				$list[$id] = $v;
				$list[$id]->treename = "$indent$txt";
				$list[$id]->children = count( @$children[$id] );
				$list = JHTMLMenu::TreeRecurse( $id, $indent . $spacer, $list, $children, $maxlevel, $level+1, $type );
			}
		}
		return $list;
	}
}

?>