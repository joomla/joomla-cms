<?php
/**
 * @version		$Id: contactdirectory.php 11163 2008-10-18 15:42:00Z chantal.bisson $
 * @package		Joomla
 * @copyright	Copyright (C) 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.plugin.plugin');

class plgSearchContactdirectory extends JPlugin 
{
	protected $areas = array('contactdirectory'=>'Contact Directory');

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
	* ContactDirectory Search method
	*
	* The sql must return the following fields that are used in a common display
	* routine: href, title, section, created, text, browsernav
	* @param string Target search string
	* @param string mathcing option, exact|any|all
	* @param string ordering option, newest|oldest|popular|alpha|category
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

		$section = JText::_( 'Contact Directory' );
	
		switch ( $ordering ) {
			case 'alpha':
				$order = 'c.name ASC';
				break;
	
			case 'category':
				$order = 'cat.title ASC, c.name ASC';
				break;
	
			case 'popular':
			case 'newest':
			case 'oldest':
			default:
				$order = 'c.name DESC';
		}
	
		$text	= $db->Quote( '%'.$db->getEscaped( $text, true ).'%', false );
	
		$query	= 'SELECT DISTINCT c.name AS title, "" AS created,'
		. ' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(\':\', c.id, c.alias) ELSE c.id END as slug, '
		. ' CASE WHEN CHAR_LENGTH(cat.alias) THEN CONCAT_WS(\':\', cat.id, cat.alias) ELSE cat.id END AS catslug, '
		. ' "" AS text,'
		. ' CONCAT_WS( " / ", '.$db->Quote($section).', cat.title ) AS section,'
		. ' "2" AS browsernav'
		. ' FROM #__contactdirectory_contacts AS c'
		. ' LEFT JOIN #__contactdirectory_con_cat_map AS map ON map.contact_id = c.id '
		. ' LEFT JOIN #__categories AS cat ON cat.id = map.category_id '
		. ' LEFT JOIN #__contactdirectory_details AS d ON d.contact_id = c.id '
		. ' WHERE ( d.data LIKE ' . $text . ' OR c.name LIKE ' . $text . ' ) '
		. ' AND c.published = 1'
		. ' AND cat.published = 1'
		. ' AND c.access <= '.(int) $user->get( 'aid' )
		. ' AND cat.access <= '.(int) $user->get( 'aid' )
		. ' GROUP BY c.id'
		. ' ORDER BY '. $order
		;
		$db->setQuery( $query, 0, $limit );
		$rows = $db->loadObjectList();
	
		foreach($rows as $key => $row) {
			$rows[$key]->href = 'index.php?option=com_contactdirectory&view=contact&id='.$row->slug;
		}
	
		return $rows;
	}
}
