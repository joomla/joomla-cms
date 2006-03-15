<?php
/**
* @version $Id: mosvote.php 1991 2006-01-27 02:07:34Z Jinx $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$mainframe->registerEvent( 'onBeforeDisplayContent', 'pluginNavigation' );

function pluginNavigation( &$row, &$params, $page=0 ) 
{
	global $Itemid, $access, $mainframe, $my;

	$task 		= JRequest::getVar( 'task' );

	if ($params->get('item_navigation') && ($task == 'view') && !$params->get('popup')) {
		$html 		= '';
		$db 		= & $mainframe->getDBO();
		$user		= & $mainframe->getUser();
		$nullDate	= $db->getNullDate();
		$now 		= date('Y-m-d H:i', time() + $mainframe->getCfg('offset') * 60 * 60);
		$uid 		= $row->id;
		$option 	= 'com_content';
		
		// Editor access object
		$access = new stdClass();
		$access->canEdit 	= $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn = $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish = $user->authorize('action', 'publish', 'content', 'all');		

		// Paramters for menu item as determined by controlling Itemid
		$menu = & JModel::getInstance( 'menu', $db );
		$menu->load($Itemid);
		$mparams = new JParameter($menu->params);
		
		// the following is needed as different menu items types utilise a different param to control ordering
		// for Blogs the `orderby_sec` param is the order controlling param
		// for Table and List views it is the `orderby` param
		$mparams_list = $mparams->toArray();
		if (array_key_exists('orderby_sec', $mparams_list)) {
			$order_method = $mparams->get('orderby_sec', '');
		} else {
			$order_method = $mparams->get('orderby', '');
		}
		// additional check for invalid sort ordering
		if ( $order_method == 'front' ) {
			$order_method = '';
		}

		// Determine sort order
		switch ($order_method)
		{
			case 'date' :
				$orderby = 'a.created';
				break;

			case 'rdate' :
				$orderby = 'a.created DESC';
				break;

			case 'alpha' :
				$orderby = 'a.title';
				break;

			case 'ralpha' :
				$orderby = 'a.title DESC';
				break;

			case 'hits' :
				$orderby = 'a.hits';
				break;

			case 'rhits' :
				$orderby = 'a.hits DESC';
				break;

			case 'order' :
				$orderby = 'a.ordering';
				break;

			case 'author' :
				$orderby = 'a.created_by_alias, u.name';
				break;

			case 'rauthor' :
				$orderby = 'a.created_by_alias DESC, u.name DESC';
				break;

			case 'front' :
				$orderby = 'f.ordering';
				break;

			default :
				$orderby = 'a.ordering';
				break;
		}
		
		if ($access->canEdit) {
			$xwhere = '';
		} else {
			$xwhere = " AND ( a.state = 1 OR a.state = -1 )" .
			"\n AND ( publish_up = '$nullDate' OR publish_up <= '$now' )" .
			"\n AND ( publish_down = '$nullDate' OR publish_down >= '$now' )";
		}
		
		// array of content items in same category corretly ordered
		$query = "SELECT a.id" 
		. "\n FROM #__content AS a" 
		. "\n WHERE a.catid = $row->catid" 
		. "\n AND a.state = $row->state". ($access->canEdit ? '' : "\n AND a.access <= $my->gid")
		. $xwhere
		. "\n ORDER BY $orderby";
		$db->setQuery($query);
		$list = $db->loadResultArray();
		
		// this check needed if incorrect Itemid is given resulting in an incorrect result
		if ( !is_array($list) ) {
			$list = array();
		}
		// location of current content item in array list
		$location = array_search($uid, $list);
		
		$row->prev = null;
		$row->next = null;
		if ($location -1 >= 0) 	{
			// the previous content item cannot be in the array position -1
			$row->prev = $list[$location -1];
		}
		if (($location +1) < count($list)) {
			// the next content item cannot be in an array position greater than the number of array postions
			$row->next = $list[$location +1];
		}

		$pnSpace = "";
		if (JText::_('&lt') || JText::_('&gt')) {
			$pnSpace = " ";
		}
		
		if ($row->prev) {
			$row->prev = sefRelToAbs('index.php?option=com_content&amp;task=view&amp;id='.$row->prev.'&amp;Itemid='.$Itemid);
		} else {
			$row->prev = '';
		}
		if ($row->next) {
			$row->next = sefRelToAbs('index.php?option=com_content&amp;task=view&amp;id='.$row->next.'&amp;Itemid='.$Itemid);
		} else {
			$row->next = '';
		}
		
		
		// output
		if ($row->prev || $row->next) {
			$html = '
			<table align="center" class="pagenav">
			<tr>'
			;
			if ($row->prev) {
				$html .= '
				<th class="pagenav_prev">
					<a href="'. $row->prev .'">'
						. JText::_( '&lt' ) . $pnSpace . JText::_( 'Prev' ) . '</a>
				</th>'
				;
			}
			
			if ($row->prev && $row->next) {
				$html .= '
				<td width="50">
					&nbsp;
				</td>'
				;
			}
			
			if ($row->next) {
				$html .= '
				<th class="pagenav_next">
					<a href="'. $row->next .'">'
						. JText::_( 'Next' ) . $pnSpace . JText::_( '&gt' ) .'</a>
				</th>'
				;
			}
			$html .= '
			</tr>
			</table>'
			;
			
			// Get Plugin info
			$plugin =& JPluginHelper::getPlugin('content', 'pagenavigation'); 	
			$pluginParams = new JParameter( $plugin->params );			
			
			$position = $pluginParams->get('position', 1);

			if ($position) {
			// display after content	
				$row->text .= $html;
			} else {
			// display before content	
				$row->text = $html . $row->text;
			}			
		}
	}
	
	return ;
}
?>