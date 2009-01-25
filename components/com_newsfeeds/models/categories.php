<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

/**
 * Newsfeeds Component Categories Model
 *
 * @package		Joomla
 * @subpackage	Newsfeeds
 * @since 1.5
 */
class NewsfeedsModelCategories extends JModel
{
	/**
	 * Frontpage data array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Frontpage total
	 *
	 * @var integer
	 */
	var $_total = null;


	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();

	}

	/**
	 * Method to get newsfeed item data for the categories
	 *
	 * @access public
	 * @return array
	 */
	function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query);
		}

		return $this->_data;
	}

	/**
	 * Method to get the total number of newsfeed items for the categories
	 *
	 * @access public
	 * @return integer
	 */
	function getTotal()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_total))
		{
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}

	function _buildQuery()
	{
		$user =& JFactory::getUser();
		$gid = $user->get('aid', 0);

		/* Query to retrieve all categories that belong under the newsfeeds section and that are published. */
		$query = 'SELECT cc.*, a.catid, COUNT(a.id) AS numlinks,'
			. ' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(\':\', cc.id, cc.alias) ELSE cc.id END as slug'
			. ' FROM #__categories AS cc'
			. ' LEFT JOIN #__newsfeeds AS a ON a.catid = cc.id'
			. ' WHERE a.published = 1'
			. ' AND cc.section = \'com_newsfeeds\''
			. ' AND cc.published = 1'
			. ' AND cc.access <= '.(int) $gid
			. ' GROUP BY cc.id'
			. ' ORDER BY cc.ordering'
		;

		return $query;
	}
}
?>