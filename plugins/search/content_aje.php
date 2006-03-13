<?php
/**
* @version $Id: content.php 2503 2006-02-20 14:04:42Z Jinx $
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

class JSearchContent extends JPlugin {
	/**
	 * Constructor
	 * 
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 * 
	 * @param object $subject The object to observe
	 * @since 1.1
	 */
	function JSearchContent(& $subject) {
		parent::__construct($subject);
	}

	/**
	 * Returns a named array of areas that this plugin is capable of searching
	 * @return array
	 */
	function onSearchAreas() {
		static $areas = array(
			'content' => 'Content'
		);
		return $areas;
	}

	/**
	 * @param JSearch The search object
	 */
	function onSearch( &$oSearch ) {
		global $mainframe, $my;

		$database =& $mainframe->getDBO();

		if (!JSearchHelper::inArea( $oSearch->getAreas(), JSearchContent::onSearchAreas() )) {
			// this bot is not in the search areas to be searched
			return;
		}
	
		$offset = $mainframe->getCfg( 'offset' );

		// load plugin params info
	 	$pluginParams = JSearchHelper::getPluginParams( 'search', 'content' );
	
		$sContent 	= $pluginParams->get( 'search_content', 	1 );
		$sStatic 	= $pluginParams->get( 'search_static', 		1 );
		$sArchived 	= $pluginParams->get( 'search_archived', 	1 );
	
		$limit 		= $pluginParams->def( 'search_limit', 		50 );
	
		$nullDate 	= $database->getNullDate();
		$now 		= date( 'Y-m-d H:i:s', time()+$offset*60*60 );
	
		$text = trim( $oSearch->getText() );
		if ($text == '') {
			return array();
		}
	
		$wheres = array();
		$phrase = $oSearch->getMatchType();

		switch ($phrase) {
			case 'exact':
				$wheres2 	= array();
				$wheres2[] 	= "LOWER(a.title) LIKE '%$text%'";
				$wheres2[] 	= "LOWER(a.introtext) LIKE '%$text%'";
				$wheres2[] 	= "LOWER(a.fulltext) LIKE '%$text%'";
				$wheres2[] 	= "LOWER(a.metakey) LIKE '%$text%'";
				$wheres2[] 	= "LOWER(a.metadesc) LIKE '%$text%'";
				$where 		= '(' . implode( ') OR (', $wheres2 ) . ')';
				break;
	
			case 'all':
			case 'any':
			default:
				$words = explode( ' ', $text );
				$wheres = array();
				foreach ($words as $word) {
					$wheres2 	= array();
					$wheres2[] 	= "LOWER(a.title) LIKE '%$word%'";
					$wheres2[] 	= "LOWER(a.introtext) LIKE '%$word%'";
					$wheres2[] 	= "LOWER(a.fulltext) LIKE '%$word%'";
					$wheres2[] 	= "LOWER(a.metakey) LIKE '%$word%'";
					$wheres2[] 	= "LOWER(a.metadesc) LIKE '%$word%'";
					$wheres[] 	= implode( ' OR ', $wheres2 );
				}
				$where = '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheres ) . ')';
				break;
		}
	
		$morder = '';
		$ordering = $oSearch->getOrdering();
		switch ($ordering) {
			case 'oldest':
				$order = 'a.created ASC';
				break;
	
			case 'popular':
				$order = 'a.hits DESC';
				break;
	
			case 'alpha':
				$order = 'a.title ASC';
				break;
	
			case 'category':
				$order = 'b.title ASC, a.title ASC';
				$morder = 'a.title ASC';
				break;
	
			case 'newest':
				default:
				$order = 'a.created DESC';
				break;
		}
	
		$rows = array();
	
		// search content items
		if ( $sContent ) {
			$query = "SELECT a.title AS title,"
			. "\n a.created AS created,"
			. "\n CONCAT(a.introtext, a.fulltext) AS text,"
			. "\n CONCAT_WS( '/', u.title, b.title ) AS section,"
			. "\n CONCAT( 'index.php?option=com_content&task=view&id=', a.id ) AS href,"
			. "\n '2' AS browsernav"
			. "\n FROM #__content AS a"
			. "\n INNER JOIN #__categories AS b ON b.id=a.catid"
			. "\n INNER JOIN #__sections AS u ON u.id = a.sectionid"
			. "\n WHERE ( $where )"
			. "\n AND a.state = 1"
			. "\n AND u.published = 1"
			. "\n AND b.published = 1"
			. "\n AND a.access <= $my->gid"
			. "\n AND b.access <= $my->gid"
			. "\n AND u.access <= $my->gid"
			. "\n AND ( publish_up = '$nullDate' OR publish_up <= '$now' )"
			. "\n AND ( publish_down = '$nullDate' OR publish_down >= '$now' )"
			. "\n GROUP BY a.id"
			. "\n ORDER BY $order"
			;
		
			// just count the result set
			$database->setQuery( $query );
			$database->query();

			$oSearch->addResultCount( $database->getNumRows() );

			$limitstart	= $oSearch->getQueryLimitStart();
			$limit		= $oSearch->getQueryLimit();
			
			if ($limit) {
				$database->setQuery( $query, 0, $limit );
				$list = $database->loadObjectList();

				$oSearch->addResults( $list );
			}
		}

		// search static content
		if ( $sStatic ) {
			$query = "SELECT a.title AS title, a.created AS created,"
			. "\n a.introtext AS text,"
			. "\n CONCAT( 'index.php?option=com_content&task=view&id=', a.id, '&Itemid=', m.id ) AS href,"
			. "\n '2' as browsernav, 'Menu' AS section"
			. "\n FROM #__content AS a"
			. "\n LEFT JOIN #__menu AS m ON m.componentid = a.id"
			. "\n WHERE ($where)"
			. "\n AND a.state = 1"
			. "\n AND a.access <= $my->gid"
			. "\n AND m.type = 'content_typed'"
			. "\n AND ( publish_up = '0000-00-00 00:00:00' OR publish_up <= '$now' )"
			. "\n AND ( publish_down = '0000-00-00 00:00:00' OR publish_down >= '$now' )"
			. "\n ORDER BY ". ($morder ? $morder : $order)
			;

			// just count the result set
			$database->setQuery( $query );
			$database->query();

			$oSearch->addResultCount( $database->getNumRows() );

			$limitstart	= $oSearch->getQueryLimitStart();
			$limit		= $oSearch->getQueryLimit();
			
			if ($limit) {
				$database->setQuery( $query, 0, $limit );
				$list = $database->loadObjectList();

				$oSearch->addResults( $list );
			}
		}
	
		// search archived content
		if ( $sArchived ) {
			$searchArchived = JText::_( 'Archived' );
	
			$query = "SELECT a.title AS title,"
			. "\n a.created AS created,"
			. "\n a.introtext AS text,"
			. "\n CONCAT_WS( '/', '". $searchArchived ." ', u.title, b.title ) AS section,"
			. "\n CONCAT('index.php?option=com_content&task=view&id=',a.id) AS href,"
			. "\n '2' AS browsernav"
			. "\n FROM #__content AS a"
			. "\n INNER JOIN #__categories AS b ON b.id=a.catid AND b.access <='$my->gid'"
			. "\n INNER JOIN #__sections AS u ON u.id = a.sectionid"
			. "\n WHERE ( $where )"
			. "\n AND a.state = -1"
			. "\n AND u.published = 1"
			. "\n AND b.published = 1"
			. "\n AND a.access <= $my->gid"
			. "\n AND b.access <= $my->gid"
			. "\n AND u.access <= $my->gid"
			. "\n AND ( publish_up = '0000-00-00 00:00:00' OR publish_up <= '$now' )"
			. "\n AND ( publish_down = '0000-00-00 00:00:00' OR publish_down >= '$now' )"
			. "\n ORDER BY $order"
			;

			// just count the result set
			$database->setQuery( $query );
			$database->query();

			$oSearch->addResultCount( $database->getNumRows() );

			$limitstart	= $oSearch->getQueryLimitStart();
			$limit		= $oSearch->getQueryLimit();
			
			if ($limit) {
				$database->setQuery( $query, 0, $limit );
				$list = $database->loadObjectList();

				$oSearch->addResults( $list );
			}
		}
	}
}

$mainframe->registerEvent( '', 'JSearchContent' );
?>