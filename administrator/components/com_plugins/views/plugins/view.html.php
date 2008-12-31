<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Config
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Plugins component
 *
 * @static
 * @package		Joomla
 * @subpackage	Plugins
 * @since 1.0
 */
class PluginsViewPlugins extends JView
{
	protected $client;
	protected $user;
	protected $lists;
	protected $items;
	protected $item;
	protected $pagination;

	function display( $tpl = null )
	{
		global $mainframe, $option;

		$db =& JFactory::getDBO();

		$client = JRequest::getWord( 'filter_client', 'site' );

		$filter_order		= $mainframe->getUserStateFromRequest( "$option.$client.filter_order",		'filter_order',		'p.folder',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.$client.filter_order_Dir",	'filter_order_Dir',	'',			'word' );
		$filter_state		= $mainframe->getUserStateFromRequest( "$option.$client.filter_state",		'filter_state',		'',			'word' );
		$filter_type		= $mainframe->getUserStateFromRequest( "$option.$client.filter_type", 		'filter_type',		1,			'cmd' );
		$search				= $mainframe->getUserStateFromRequest( "$option.$client.search",			'search',			'',			'string' );
		$search				= JString::strtolower( $search );

		$limit		= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart	= $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );

		$where = '';
		if ($client == 'admin') {
			$where[] = 'p.client_id = 1';
			$client_id = 1;
		} else {
			$where[] = 'p.client_id = 0';
			$client_id = 0;
		}

		// used by filter
		if ( $filter_type != 1 ) {
			$where[] = 'p.folder = '.$db->Quote($filter_type);
		}
		if ( $search ) {
			$where[] = 'LOWER( p.name ) LIKE '.$db->Quote( '%'.$db->getEscaped( $search, true ).'%', false );
		}
		if ( $filter_state ) {
			if ( $filter_state == 'P' ) {
				$where[] = 'p.enabled = 1';
			} else if ($filter_state == 'U' ) {
				$where[] = 'p.enabled = 0';
			}
		}

		$where[] = 'type = "plugin"';
		$where[] = 'state > -1';

		$where 		= ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );
		$orderby 	= ' ORDER BY '.$filter_order .' '. $filter_order_Dir .', p.ordering ASC';

		// get the total number of records
		$query = 'SELECT COUNT(*)'
			. ' FROM #__extensions AS p'
			. $where
			;
		$db->setQuery( $query );
		try {
			$total = $db->loadResult();
		} catch(JException $e) {
			echo $db->stderr();
			return false;
		}

		jimport('joomla.html.pagination');
		$pagination = new JPagination( $total, $limitstart, $limit );

		$query = 'SELECT p.*, p.extension_id AS id, p.enabled AS published, u.name AS editor, g.name AS groupname'
			. ' FROM #__extensions AS p'
			. ' LEFT JOIN #__users AS u ON u.id = p.checked_out'
			. ' LEFT JOIN #__core_acl_axo_groups AS g ON g.value = p.access'
			. $where
			. ' GROUP BY p.extension_id'
			. $orderby
			;
		$db->setQuery( $query, $pagination->limitstart, $pagination->limit );
		try {
			$rows = $db->loadObjectList();
		} catch (JException $e) {
			echo $db->stderr();
			return false;
		}


		// get list of Positions for dropdown filter
		$query = 'SELECT folder AS value, folder AS text'
			. ' FROM #__extensions'
			. ' WHERE client_id = '.(int) $client_id
			. ' AND type = "plugin" AND state > -1'
			. ' GROUP BY folder'
			. ' ORDER BY folder'
			;
		$types[] = JHtml::_('select.option',  1, '- '. JText::_( 'Select Type' ) .' -' );
		$db->setQuery( $query );
		try {
			$types = array_merge( $types, $db->loadObjectList() );
		} catch(JException $e) {
			echo $db->stderr();
			return false;
		}
		$lists['type']	= JHtml::_(
			'select.genericlist',
			$types,
			'filter_type',
			array(
				'list.attr' => 'class="inputbox" size="1" onchange="document.adminForm.submit( );"',
				'list.select' => $filter_type
			)
		);

		// state filter
		$lists['state']	= JHtml::_('grid.state',  $filter_state );


		// table ordering
		$lists['order_Dir']	= $filter_order_Dir;
		$lists['order']		= $filter_order;

		// search filter
		$lists['search']= $search;

		$this->assign('client',		$client);

		$this->assignRef('user',		JFactory::getUser());
		$this->assignRef('lists',		$lists);
		$this->assignRef('items',		$rows);
		$this->assignRef('pagination',	$pagination);

		parent::display($tpl);
	}
}
