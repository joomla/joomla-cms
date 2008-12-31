<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.plugin.plugin');

class plgSearchSections extends JPlugin
{
	protected $areas = array('sections'=>'Sections');

	public function __construct(&$subject, $options = array())
	{
		parent::__construct($subject, $options);
		$this->loadLanguage();
	}

	/**
	 * @return array An array of search areas
	 */
	public function &onSearchAreas() {
		return $this->areas;
	}

	/**
	* Sections Search method
	*
	* The sql must return the following fields that are used in a common display
	* routine: href, title, section, created, text, browsernav
	* @param string Target search string
	* @param string mathcing option, exact|any|all
	* @param string ordering option, newest|oldest|popular|alpha|category
	 * @param mixed An array if restricted to areas, null if search all
	*/
	public function onSearch( $text, $phrase='', $ordering='', $areas=null )
	{
		$db		= JFactory::getDBO();
		$user	= JFactory::getUser();

		require_once JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php';

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

		switch ( $ordering ) {
			case 'alpha':
				$order = 'a.name ASC';
				break;

			case 'category':
			case 'popular':
			case 'newest':
			case 'oldest':
			default:
				$order = 'a.name DESC';
		}

		$text	= $db->Quote( '%'.$db->getEscaped( $text, true ).'%', false );
		$query	= 'SELECT a.title AS title, a.description AS text,'
		. ' "" AS created,'
		. ' "2" AS browsernav,'
		. ' a.id AS secid'
		. ' FROM #__sections AS a'
		. ' WHERE ( a.name LIKE '.$text
		. ' OR a.title LIKE '.$text
		. ' OR a.description LIKE '.$text.' )'
		. ' AND a.published = 1'
		. ' AND a.access <= '.(int) $user->get( 'aid' )
		. ' GROUP BY a.id'
		. ' ORDER BY '. $order
		;
		$db->setQuery( $query, 0, $limit );
		$rows = $db->loadObjectList();

		$count = count( $rows );
		for ( $i = 0; $i < $count; $i++ )
		{
			$rows[$i]->href 	= ContentHelperRoute::getSectionRoute($rows[$i]->secid);
			$rows[$i]->section 	= JText::_( 'Section' );
		}

		return $rows;
	}
}
