<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

/**
 * Content Component Category Model
 *
 * @package		Joomla.Administrator
 * @subpackage	Content
 * @since 1.5
 */
class ContentModelArticle extends JModel
{
	/**
	 * Category id
	 *
	 * @var int
	 */
	var $_id = null;

	/**
	 * Category data
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		//parent::__construct(array('name' => 'content'));
		parent::__construct();

		$array = JRequest::getVar('cid', array(0), '', 'array');
		$edit	= JRequest::getVar('edit',true);
		if($edit)
			$this->setId((int)$array[0]);
	}

	/**
	 * Method to set the article identifier
	 *
	 * @access	public
	 * @param	int Category identifier
	 */
	function setId($id)
	{
		// Set article id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}

	/**
	 * Method to get an article
	 *
	 * @since 1.5
	 */
	function &getData()
	{
		// Load the article data
		if (!$this->_loadData())
			$this->_initData();

		return $this->_data;
	}

	/**
	 * Tests if article is checked out
	 *
	 * @access	public
	 * @param	int	A user id
	 * @return	boolean	True if checked out
	 * @since	1.5
	 */
	function isCheckedOut( $uid=0 )
	{
		if ($this->_loadData())
		{
			if ($uid) {
				return ($this->_data->checked_out && $this->_data->checked_out != $uid);
			} else {
				return $this->_data->checked_out;
			}
		}
	}

	/**
	 * Method to checkin/unlock the article
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function checkin()
	{
		if ($this->_id)
		{
			$article = & JTable::getInstance('content');
			if(! $article->checkin($this->_id)) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			return true;
		}
		return false;
	}

	/**
	 * Method to checkout/lock the article
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
			$article = & JTable::getInstance('content');
			if(!$article->checkout($uid, $this->_id)) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			return true;
		}
		return false;
	}

	/**
	 * Method to store the article
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function store($data)
	{
		jimport('joomla.utilities.date');
		$user		= & JFactory::getUser();
		$details	= JRequest::getVar( 'details', array(), 'post', 'array');
		$nullDate	= $this->_db->getNullDate();

		$row = & JTable::getInstance('content');
		if (!$row->bind($data)) {
			JError::raiseError( 500, $this->_db->stderr() );
			return false;
		}
		$row->bind($details);

		// sanitise id field
		$row->id = (int) $row->id;

		// Are we saving from an item edit?
		if ($row->id) {
			$datenow =& JFactory::getDate();
			$row->modified 		= $datenow->toMySQL();
			$row->modified_by 	= $user->get('id');
		}

		$row->created_by 	= $row->created_by ? $row->created_by : $user->get('id');

		if ($row->created && strlen(trim( $row->created )) <= 10) {
			$row->created 	.= ' 00:00:00';
		}

		$config =& JFactory::getConfig();
		$tzoffset = $config->getValue('config.offset');
		$date =& JFactory::getDate($row->created, $tzoffset);
		$row->created = $date->toMySQL();

		// Append time if not added to publish date
		if (strlen(trim($row->publish_up)) <= 10) {
			$row->publish_up .= ' 00:00:00';
		}

		$date =& JFactory::getDate($row->publish_up, $tzoffset);
		$row->publish_up = $date->toMySQL();

		// Handle never unpublish date
		if (trim($row->publish_down) == JText::_('Never') || trim( $row->publish_down ) == '')
		{
			$row->publish_down = $nullDate;
		}
		else
		{
			if (strlen(trim( $row->publish_down )) <= 10) {
				$row->publish_down .= ' 00:00:00';
			}
			$date =& JFactory::getDate($row->publish_down, $tzoffset);
			$row->publish_down = $date->toMySQL();
		}

		// Get a state and parameter variables from the request
		$row->state	= JRequest::getVar( 'state', 0, '', 'int' );
		$params		= JRequest::getVar( 'params', null, 'post', 'array' );

		// Build parameter INI string
		if (is_array($params))
		{
			$row->attribs = json_encode($params);
		}

		// Get metadata string
		$metadata = JRequest::getVar( 'meta', null, 'post', 'array');
		if (is_array($metadata))
		{
			$txt = array();
			foreach ($metadata as $k => $v) {
				if ($k == 'description') {
					$row->metadesc = $v;
				} elseif ($k == 'keywords') {
					$row->metakey = $v;
				} else {
					$txt[] = "$k=$v";
				}
			}
			$row->metadata = implode("\n", $txt);
		}

		// Prepare the content for saving to the database
		$this->saveContentPrep( $row );

		// Make sure the data is valid
		if (!$row->check()) {
			JError::raiseError( 500, $this->_db->stderr() );
			return false;
		}

		// Increment the content version number
		$row->version++;

		// Store the content to the database
		if (!$row->store()) {
			JError::raiseError( 500, $this->_db->stderr() );
			return false;
		}

		// Check the article and update item order
		$row->checkin();
		$row->reorder('catid = '.(int) $row->catid.' AND state >= 0');

		// Save ID so controller can find data
		$this->setId($row->id);

		/*
		 * We need to update frontpage status for the article.
		 *
		 * First we include the frontpage table and instantiate an instance of it.
		 */
		$fp = & JTable::getInstance('frontpage', 'Table');

		// Is the article viewable on the frontpage?
		if ($data['frontpage'])
		{
			// Is the item already viewable on the frontpage?
			if (!$fp->load($row->id))
			{
				// Insert the new entry
				$query = 'INSERT INTO #__content_frontpage' .
						' VALUES ( '. (int) $row->id .', 1 )';
				$this->_db->setQuery($query);
				if (!$this->_db->query())
				{
					JError::raiseError( 500, $this->_db->stderr() );
					return false;
				}
				$fp->ordering = 1;
			}
		}
		else
		{
			// Delete the item from frontpage if it exists
			if (!$fp->delete($article->id)) {
				$msg .= $fp->stderr();
			}
			$fp->ordering = 0;
		}
		$fp->reorder();

		return true;
	}

	/**
	 * Method to remove a article
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function delete($cid = array())
	{
		$result = false;

		if (count( $cid ))
		{
			JArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );
			$query = 'DELETE FROM #__content'
				. ' WHERE id IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			if(!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to move an article to the trash
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function trash($cid = array())
	{
		$result = false;

		if (count( $cid ))
		{
			// Removed content gets put in the trash [state = -2] and ordering is always set to 0
			JArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );
			$nullDate	= $this->_db->getNullDate();
			$state		= '-2';
			$ordering	= '0';
			$query = 'UPDATE #__content' .
					' SET state = '.(int) $state .
					', ordering = '.(int) $ordering .
					', checked_out = 0, checked_out_time = '.$this->_db->Quote($nullDate).
					' WHERE id IN ( '. $cids. ' )';
			$this->_db->setQuery( $query );
			if(!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to set article state
	 * Published = 1
	 * Not published = 0
	 * Archived = -1
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function setArticleState($cid = array(), $new_state = 1)
	{
		$user 	=& JFactory::getUser();

		if (count( $cid ))
		{
			JArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );

			$query = 'UPDATE #__content'
				. ' SET state = '.(int) $new_state
				. ' WHERE id IN ( '.$cids.' )'
				. ' AND ( checked_out = 0 OR ( checked_out = '.(int) $user->get('id').' ) )'
			;
			$this->_db->setQuery( $query );
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to move a article
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function move($direction)
	{
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );

		$row = & JTable::getInstance('content');
		if (!$row->load( (int) $cid[0] )) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		if (!$row->move( $direction, ' catid = '.(int) $row->catid.' AND state >= 0 ' )) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}

	/**
	 * Method to move a article
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function saveorder($cid = array(), $order)
	{
		$row = & JTable::getInstance('content');
		$conditions	= array ();

		// Update the ordering for items in the cid array
		for ($i = 0; $i < count($cid); $i ++)
		{
			$row->load( (int) $cid[$i] );
			if ($row->ordering != $order[$i]) {
				$row->ordering = $order[$i];
				if (!$row->store()) {
					JError::raiseError( 500, $this->_db->getErrorMsg() );
					return false;
				}
				// remember to updateOrder this group
				$condition = 'catid = '.(int) $row->catid.' AND state >= 0';
				$found = false;
				foreach ($conditions as $cond)
					if ($cond[1] == $condition) {
						$found = true;
						break;
					}
				if (!$found)
					$conditions[] = array ($row->id, $condition);
			}
		}

		// execute updateOrder for each group
		foreach ($conditions as $cond)
		{
			$row->load($cond[0]);
			$row->reorder($cond[1]);
		}

		return true;
	}

	/**
	 * Method to load article data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = 'SELECT c.* '.
					' FROM #__content AS c' .
					' WHERE c.id = '.(int) $this->_id;
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();

			if (!$this->_data) {
				return false;
			}

			$query = 'SELECT name' .
					' FROM #__users'.
					' WHERE id = '. (int) $this->_data->created_by;
			$this->_db->setQuery($query);
			$this->_data->creator = $this->_db->loadResult();

			// test to reduce unneeded query
			if ($this->_data->created_by == $this->_data->modified_by) {
				$this->_data->modifier = $this->_data->creator;
			} else {
				$query = 'SELECT name' .
						' FROM #__users' .
						' WHERE id = '. (int) $this->_data->modified_by;
				$this->_db->setQuery($query);
				$this->_data->modifier = $this->_db->loadResult();
			}

			$query = 'SELECT COUNT(content_id)' .
					' FROM #__content_frontpage' .
					' WHERE content_id = '. (int) $this->_id;
			$this->_db->setQuery($query);
			$this->_data->frontpage = $this->_db->loadResult();
			if (!$this->_data->frontpage) {
				$this->_data->frontpage = 0;
			}

			return (boolean) $this->_data;
		}
		return true;
	}

	/**
	 * Method to initialise the article data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function _initData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$article = new stdClass();
			$article->id 				= null;
			$article->name 				= null;
			$article->alias				= null;
			$article->title 				= null;
			$article->title_alias		= null;
			$article->introtext 			= null;
			$article->fulltext			= null;
			$article->sectionid	 		= null;
			$article->catid				= 0;
			$article->state				= 0;
			$article->mask				= 0;
			$article->created_by 		= 0;
			$article->created_by_alias	= null;
			$article->modified	 		= 0;
			$article->modified_by	 	= 0;
			$article->checked_out 		= 0;
			$article->checked_out_time 	= 0;
			$article->publish_up			= 0;
			$article->publish_down		= 0;
			$article->images				= null;
			$article->urls 				= null;
			$article->attribs			= null;
			$article->version 			= 1;
			$article->parent_id 			= null;
			$article->ordering 			= null;
			$article->metakey			= null;
			$article->metadesc			= null;
			$article->metadata			= null;
			$article->access 			= null;
			$article->hits 				= null;

			$this->_data				= $article;
			return (boolean) $this->_data;
		}
		return true;
	}

	/**
	 * Method to set the article access
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function setAccess($cid = array(), $access = 0)
	{
		if (count( $cid ))
		{
			$user 	=& JFactory::getUser();

			JArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );

			$query = 'UPDATE #__content'
				. ' SET access = '.(int) $access
				. ' WHERE id IN ( '.$cids.' )'
				. ' AND ( checked_out = 0 OR ( checked_out = '.(int) $user->get('id').' ) )'
			;
			$this->_db->setQuery( $query );
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		return true;
	}

	function saveContentPrep( &$row )
	{
		// Get submitted text from the request variables
		$text = JRequest::getVar( 'text', '', 'post', 'string', JREQUEST_ALLOWRAW );

		// Clean text for xhtml transitional compliance
		$text		= str_replace( '<br>', '<br />', $text );

		// Search for the {readmore} tag and split the text up accordingly.
		$tagPos	= JString::strpos( $text, '<hr id="system-readmore" />' );

		if ( $tagPos === false )
		{
			$row->introtext	= $text;
		} else
		{
			$row->introtext	= JString::substr($text, 0, $tagPos);
			$row->fulltext	= JString::substr($text, $tagPos + 27 );
		}

		// Filter settings
		jimport( 'joomla.application.component.helper' );
		$config	= JComponentHelper::getParams( 'com_content' );
		$user	= &JFactory::getUser();
		$gid	= $user->get( 'gid' );

		$filterGroups	= (array) $config->get( 'filter_groups' );
		if (in_array( $gid, $filterGroups ))
		{
			$filterType		= $config->get( 'filter_type' );
			$filterTags		= preg_split( '#[,\s]+#', trim( $config->get( 'filter_tags' ) ) );
			$filterAttrs	= preg_split( '#[,\s]+#', trim( $config->get( 'filter_attritbutes' ) ) );
			switch ($filterType)
			{
				case 'NH':
					$filter	= new JFilterInput();
					break;
				case 'WL':
					$filter	= new JFilterInput( $filterTags, $filterAttrs, 0, 0 );
					break;
				case 'BL':
				default:
					$filter	= new JFilterInput( $filterTags, $filterAttrs, 1, 1 );
					break;
			}
			$row->introtext	= $filter->clean( $row->introtext );
			$row->fulltext	= $filter->clean( $row->fulltext );
		}

		return true;
	}

	/**
	* Function to reset Hit count of an article
	*
	*/
	function resetHits()
	{
		// Instantiate and load an article table
		$row = & JTable::getInstance('content');
		$row->Load($this->_id);
		$row->hits = 0;
		$row->store();
		$row->checkin();
	}
}
