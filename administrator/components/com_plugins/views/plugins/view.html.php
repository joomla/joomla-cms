<?php
/**
* @version		$Id$
 * @package		Joomla.Administrator
* @subpackage	Config
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
*/

// no direct access
defined('_JEXEC') or die;

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Plugins component
 *
 * @static
 * @package		Joomla.Administrator
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
		$app	= &JFactory::getApplication();
		$db		=& JFactory::getDBO();

		$client = JRequest::getWord( 'filter_client', 'site' );

		$filter_order		= $app->getUserStateFromRequest( "com_plugins.$client.filter_order",		'filter_order',		'p.folder',	'cmd' );
		$filter_order_Dir	= $app->getUserStateFromRequest( "com_plugins.$client.filter_order_Dir",	'filter_order_Dir',	'',			'word' );
		$filter_state		= $app->getUserStateFromRequest( "com_plugins.$client.filter_state",		'filter_state',		'',			'word' );
		$filter_type		= $app->getUserStateFromRequest( "com_plugins.$client.filter_type", 		'filter_type',		1,			'cmd' );
		$search				= $app->getUserStateFromRequest( "com_plugins.$client.search",			'search',			'',			'string' );
		$search				= JString::strtolower( $search );

		$limit		= $app->getUserStateFromRequest( 'global.list.limit', 'limit', $app->getCfg('list_limit'), 'int' );
		$limitstart	= $app->getUserStateFromRequest( 'com_plugins.limitstart', 'limitstart', 0, 'int' );

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
		if ($filter_order == 'p.ordering') {
			$orderby = ' ORDER BY p.folder, p.ordering '. $filter_order_Dir;
		} else {
		$orderby 	= ' ORDER BY '.$filter_order .' '. $filter_order_Dir .', p.ordering ASC';
		}


		// get the total number of records
		$query = 'SELECT COUNT(*)'
			. ' FROM #__extensions AS p'
			. $where
			;
		$db->setQuery( $query );
		$total = $db->loadResult();

		jimport('joomla.html.pagination');
		$pagination = new JPagination( $total, $limitstart, $limit );

		$query = 'SELECT p.*, p.extension_id AS id, p.enabled AS published, u.name AS editor, ag.title AS groupname'
			. ' FROM #__extensions AS p'
			. ' LEFT JOIN #__users AS u ON u.id = p.checked_out'
			. ' LEFT JOIN #__viewlevels AS ag ON ag.id = p.access'
			. $where
			. ' GROUP BY p.extension_id'
			. $orderby
			;
		$db->setQuery( $query, $pagination->limitstart, $pagination->limit );
		$rows = $db->loadObjectList();
		if ($db->getErrorNum()) {
			echo $db->stderr();
			return false;
		}


		// get list of Positions for dropdown filter
		$query = 'SELECT folder AS value, folder AS text'
			. ' FROM #__extensions'
			. ' WHERE client_id = '.(int) $client_id
			. ' AND type = "plugin"'
			. ' AND state > -1'
			. ' GROUP BY folder'
			. ' ORDER BY folder'
			;
		$types[] = JHTML::_('select.option',  1, '- '. JText::_( 'Select Type' ) .' -' );
		$db->setQuery( $query );
		$types 			= array_merge( $types, $db->loadObjectList() );
		$lists['type']	= JHTML::_('select.genericlist',   $types, 'filter_type', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $filter_type );

		// state filter
		$lists['state']	= JHTML::_('grid.state',  $filter_state );


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

		$this->_setToolbar();
		parent::display($tpl);
	}

	/**
	 * Display the toolbar
	 */
	protected function _setToolbar()
	{
		JToolBarHelper::title( JText::_( 'Plugin Manager' ), 'plugin.png' );
		JToolBarHelper::editList();
		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::divider();
		JToolBarHelper::preferences('com_plugins');
		JToolBarHelper::divider();
		JToolBarHelper::help( 'screen.plugins' );
	}
}