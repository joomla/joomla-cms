<?php
/**
 * @version $Id: article.php 5379 2006-10-09 22:39:40Z Jinx $
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport('joomla.application.component.model');

/**
 * Weblinks Component Weblink Model
 *
 * @author Johan Janssens <johan.janssens@joomla.org>
 * @package Joomla
 * @subpackage Weblinks
 * @since 1.5
 */
class WeblinksModelWeblink extends JModel
{
	/**
	 * Weblink id
	 *
	 * @var int
	 */
	var $_id = null;
	
	/**
	 * Weblink data
	 *
	 * @var array
	 */
	var $_weblink = null;
	
	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();
		
		global $Itemid;

		// Get the paramaters of the active menu item
		$params =& JSiteHelper::getMenuParams();

		$id = JRequest::getVar('id', $params->get( 'weblink_id', 0 ), '', 'int');
		$this->setId($id);

	}
	
	/**
	 * Method to set the weblink identifier
	 *
	 * @access	public
	 * @param	int Weblink identifier
	 */
	function setId($id)
	{
		// Set weblink id and wipe data
		$this->_id	    = $id;
		$this->_weblink = null;
	}
	
	/**
	 * Method to get a weblink
	 *
	 * @since 1.5
	 */
	function &getWeblink()
	{
		// Load the Category data
		if ($this->_loadWeblink())
		{
			// Initialize some variables
			$user = &JFactory::getUser();
			
			// Make sure the category is published
			if (!$this->_weblink->published) {
				JError::raiseError(404, JText::_("Resource Not Found"));
				return false;
			}
			
			// Check to see if the category is published
			if (!$this->_weblink->cat_pub) {
				JError::raiseError( 404, JText::_("Resource Not Found") );
				return;
			}

			// Check whether category access level allows access
			if ($this->_weblink->cat_access > $user->get('gid')) {
				JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
				return;
			}
		}
		
		return $this->_weblink;
	}
	
	/**
	 * Method to increment the hit counter for the weblink
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function incrementHit()
	{
		global $mainframe;

		if ($this->_id)
		{
			$weblink = & JTable::getInstance('weblink', $this->_db, 'Table');
			$weblink->hit($this->_id, $mainframe->getCfg('enable_log_items'));
			return true;
		}
		return false;
	}
	
	/**
	 * Tests if weblink is checked out
	 *
	 * @access	public
	 * @param	int	A user id
	 * @return	boolean	True if checked out
	 * @since	1.5
	 */
	function isCheckedOut( $uid=0 )
	{
		if ($this->_loadWeblink()) 
		{
			if ($uid) {
				return ($this->_weblink->checked_out && $this->_weblink->checked_out != $uid);
			} else {
				return $this->_weblink->checked_out;
			}
		} else {
			JError::raiseWarning( 0, 'Unable to Load Data');
			return false;
		}
	}
	
	/**
	 * Method to checkin/unlock the weblink
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function checkin()
	{
		if ($this->_id)
		{
			$weblink = & JTable::getInstance('weblinks', $this->_db, 'Table');
			return $weblink->checkin($this->_id);
		}
		return false;
	}

	/**
	 * Method to checkout/lock the weblink
	 *
	 * @access	public
	 * @param	int	$uid	User ID of the user checking the article out
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function checkout($uid = null)
	{
		if ($this->_id)
		{
			// Make sure we have a user id to checkout the article with
			if (is_null($uid)) {
				$user	=& JFactory::getUser();
				$uid	= $user->get('id');
			}
			// Lets get to it and checkout the thing...
			$weblink = & JTable::getInstance('weblinks', $this->_db, 'Table');
			return $weblink->checkout($uid, $this->_id);
		}
		return false;
	}

	/**
	 * Method to load content weblink data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function _loadWeblink()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_weblink))
		{
			// Get the WHERE clause
			$where	= $this->_buildContentWhere();

			$query = "SELECT w.*, cc.title AS category," .
					"\n cc.published AS cat_pub, cc.access AS cat_access".
					"\n FROM #__weblinks AS w" .
					"\n LEFT JOIN #__categories AS cc ON cc.id = w.catid" .
					$where;
			$this->_db->setQuery($query);
			$this->_weblink = $this->_db->loadObject();
			return (boolean) $this->_weblink;
		}
		return true;
	}
	
	/**
	 * Method to build the WHERE clause of the query to select a content article
	 *
	 * @access	private
	 * @return	string	WHERE clause
	 * @since	1.5
	 */
	function _buildContentWhere()
	{
		global $mainframe;
		
		// Make sure that the weblink is the one we are looking for.
		$where = "\n WHERE w.id = $this->_id";

		return $where;
	}
	
}
?>