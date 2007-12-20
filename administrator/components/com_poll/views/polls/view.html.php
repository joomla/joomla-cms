<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Config
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

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Poll component
 *
 * @static
 * @package		Joomla
 * @subpackage	Poll
 * @since 1.0
 */
class PollViewPolls extends JView
{
	function display( $tpl = null )
	{
		global $mainframe, $option;

		$db					=& JFactory::getDBO();
		$filter_order		= $mainframe->getUserStateFromRequest( "$option.filter_order",		'filter_order',		'm.id',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.filter_order_Dir",	'filter_order_Dir',	'',		'word' );
		$filter_state		= $mainframe->getUserStateFromRequest( "$option.filter_state",		'filter_state',		'',		'word' );
		$search				= $mainframe->getUserStateFromRequest( "$option.search",			'search',			'',		'string' );
		$search				= JString::strtolower( $search );

		$limit		= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart	= $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );

		$where = array();

		if ( $filter_state )
		{
			if ( $filter_state == 'P' )
			{
				$where[] = 'm.published = 1';
			}
			else if ($filter_state == 'U' )
			{
				$where[] = 'm.published = 0';
			}
		}
		if ($search)
		{
			$where[] = 'LOWER(m.title) LIKE '.$db->Quote('%'.$search.'%');
		}

		$where 		= ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );
		$orderby 	= ' ORDER BY '. $filter_order .' '. $filter_order_Dir;

		$query = 'SELECT COUNT(m.id)'
		. ' FROM #__polls AS m'
		. $where
		;
		$db->setQuery( $query );
		$total = $db->loadResult();

		jimport('joomla.html.pagination');
		$pagination = new JPagination( $total, $limitstart, $limit );

		$query = 'SELECT m.*, u.name AS editor, COUNT(d.id) AS numoptions'
		. ' FROM #__polls AS m'
		. ' LEFT JOIN #__users AS u ON u.id = m.checked_out'
		. ' LEFT JOIN #__poll_data AS d ON d.pollid = m.id AND d.text <> ""'
		. $where
		. ' GROUP BY m.id'
		. $orderby
		;
		$db->setQuery( $query, $pagination->limitstart, $pagination->limit );
		$rows = $db->loadObjectList();

		if ($db->getErrorNum())
		{
			echo $db->stderr();
			return false;
		}

		// state filter
		$lists['state']	= JHTML::_('grid.state',  $filter_state );

		// table ordering
		$lists['order_Dir']	= $filter_order_Dir;
		$lists['order']		= $filter_order;

		// search filter
		$lists['search']= $search;

		$this->assignRef('user',		JFactory::getUser());
		$this->assignRef('lists',		$lists);
		$this->assignRef('items',		$rows);
		$this->assignRef('pagination',	$pagination);

		parent::display($tpl);
	}
}