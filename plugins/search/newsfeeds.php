<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.plugin.plugin');

class plgsearchNewsfeeds extends JPlugin
{
	protected $areas = array('newsfeeds' => 'Newsfeeds');

	public function __construct(&$subject, $options = array())
	{
		parent::__construct($subject, $options);
		$this->loadLanguage();
	}

	/**
	 * @return array An array of search areas
	 */
	public function &onSearchAreas()
	{
		return $this->areas;
	}

	/**
	* Contacts Search method
	*
	* The sql must return the following fields that are used in a common display
	* routine: href, title, section, created, text, browsernav
	* @param string Target search string
	* @param string mathcing option, exact|any|all
	* @param string ordering option, newest|oldest|popular|alpha|category
	 * @param mixed An array if the search it to be restricted to areas, null if search all
	*/
	public function onSearch( $text, $phrase='', $ordering='', $areas=null )
	{
		$db		= JFactory::getDBO();
		$user	= JFactory::getUser();

		if (is_array( $areas )) {
			if (!array_intersect( $areas, array_keys( $this->areas ) )) {
				return array();
			}
		}

		$limit = $this->params->def( 'search_limit', 50 );

		$text = trim( $text );
		if ($text == '') {
			return array();
		}

		$wheres = array();
		switch ($phrase) {
			case 'exact':
				$text		= $db->Quote( '%'.$db->getEscaped( $text, true ).'%', false );
				$wheres2 	= array();
				$wheres2[] 	= 'LOWER(a.name) LIKE '.$text;
				$wheres2[] 	= 'LOWER(a.link) LIKE '.$text;
				$where 		= '(' . implode( ') OR (', $wheres2 ) . ')';
				break;

			case 'all':
			case 'any':
			default:
				$words 	= explode( ' ', $text );
				$wheres = array();
				foreach ($words as $word)
				{
					$word		= $db->Quote( '%'.$db->getEscaped( $word, true ).'%', false );
					$wheres2 	= array();
					$wheres2[] 	= 'LOWER(a.name) LIKE '.$word;
					$wheres2[] 	= 'LOWER(a.link) LIKE '.$word;
					$wheres[] 	= implode( ' OR ', $wheres2 );
				}
				$where = '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheres ) . ')';
				break;
		}

		switch ( $ordering ) {
			case 'alpha':
				$order = 'a.name ASC';
				break;

			case 'category':
				$order = 'b.title ASC, a.name ASC';
				break;

			case 'oldest':
			case 'popular':
			case 'newest':
			default:
				$order = 'a.name ASC';
		}

		$searchNewsfeeds = JText::_( 'Newsfeeds' );

		$query = 'SELECT a.name AS title, "" AS created, a.link AS text,'
		. ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug, '
		. ' CASE WHEN CHAR_LENGTH(b.alias) THEN CONCAT_WS(\':\', b.id, b.alias) ELSE b.id END as catslug, '
		. ' CONCAT_WS( " / ", '. $db->Quote($searchNewsfeeds) .', b.title )AS section,'
		. ' "1" AS browsernav'
		. ' FROM #__newsfeeds AS a'
		. ' INNER JOIN #__categories AS b ON b.id = a.catid'
		. ' WHERE ( '. $where .' )'
		. ' AND a.published = 1'
		. ' AND b.published = 1'
		. ' AND b.access <= '. (int) $user->get( 'aid' )
		. ' ORDER BY '. $order
		;
		$db->setQuery( $query, 0, $limit );
		$rows = $db->loadObjectList();

		foreach($rows as $key => $row) {
			$rows[$key]->href = 'index.php?option=com_newsfeeds&view=newsfeed&catid='.$row->catslug.'&id='.$row->slug;
		}

		return $rows;
	}
}
