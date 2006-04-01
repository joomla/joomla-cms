<?php
/**
 * @version $Id: frontpage.php 2874 2006-03-22 22:57:55Z webImagery $
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// require the component helper 
require_once (JApplicationHelper::getPath('helper', 'com_content'));

/**
 * Content Component Article Model
 *
 * @author	Louis Landry <louis.landry@joomla.org>
 * @package Joomla
 * @subpackage Content
 * @since 1.5
 */
class JModelArticle extends JModel
{
	/**
	 * Content data in category array
	 *
	 * @var array
	 */
	var $_article = null;

	/**
	 * Method to set the article id
	 *
	 * @access	public
	 * @param	int	Article ID number
	 */
	function setId($id)
	{
		// Set new article ID and wipe data
		$this->_id			= $id;
		$this->_article	= null;
	}

	/**
	 * Overridden set method to pass properties on to the article
	 * 
	 * @access	public
	 * @param	string	$property	The name of the property
	 * @param	mixed	$value		The value of the property to set
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function set( $property, $value=null ) 
	{
		if ($this->_loadContent()) {
			$this->_article->$property = $value;
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Overridden get method to get properties from the article
	 * 
	 * @access	public
	 * @param	string	$property	The name of the property
	 * @param	mixed	$value		The value of the property to set
	 * @return 	mixed 				The value of the property
	 * @since	1.5
	 */
	function get($property, $default=null) 
	{
		if ($this->_loadContent()) {
			if(isset($this->_article->$property)) {
				return $this->_article->$property;
			}
		}
		return $default;
	}

	/**
	 * Method to get content article data for the frontpage
	 *
	 * @since 1.5
	 */
	function getArticle()
	{
		/*
		 * Load the Category data
		 */
		if ($this->_loadArticle())
		{
			$user = & $this->_app->getUser();

			// Is the category published?
			if (!$this->_article->cat_pub && $this->_article->catid) {
				JError::raiseError( 404, JText::_("Resource Not Found") );
			}
			// Is the section published?
			if (!$this->_article->sec_pub && $this->_article->sectionid) {
				JError::raiseError( 404, JText::_("Resource Not Found") );
			}
			// Do we have access to the category?
			if (($this->_article->cat_access > $user->get('gid')) && $this->_article->catid) {
				JError::raiseError( 403, JText::_("ALERTNOTAUTH") );
			}
			// Do we have access to the section?
			if (($this->_article->sec_access > $user->get('gid')) && $this->_article->sectionid) {
				JError::raiseError( 403, JText::_("ALERTNOTAUTH") );
			}

			$this->_loadArticleParams();

			/*
			 * Record the hit on the article if necessary
			 */
			$limitstart	= JRequest::getVar('limitstart',	0, '', 'int');
			if (!$this->_article->parameters->get('intro_only') && ($limitstart == 0)) {
				$this->incrementHit();
			}
			
		}
		return $this->_article;
	}

	/**
	 * Method to increment the hit counter for the article
	 * 
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function incrementHit()
	{
		if ($this->_id)
		{
			$article = & JTable::getInstance('content', $this->_db);
			$article->hit($this->_id);
			return true;
		}
		return false;
	}

	/**
	 * Tests if article is checked out
	 * 
	 * @access	public
	 * @param	int	A user id
	 * @return	boolean	True if checked out
	 * @since	1.5
	 */
	function isCheckedOut( $uid=0 ) {
		if (_loadArticle()) {
			if ($uid) {
				return ($this->_article->checked_out && $this->_article->checked_out != $uid);
			} else {
				return $this->_article->checked_out;
			}
		} else {
			JError::raiseWarning( 0, 'Unable to Load Data');
			return false;
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
			$article = & JTable::getInstance('content', $this->_db);
			return $article->checkin($this->_id);
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
				$user	= & $this->_app->getUser();
				$uid		= $user->get('id');
			}
			// Lets get to it and checkout the thing...
			$article = & JTable::getInstance('content', $this->_db);
			return $article->checkout($uid, $this->_id);
		}
		return false;
	}

	/**
	 * Method to store a user rating for a content article
	 * 
	 * @access	public
	 * @param	int	$rating	Article rating [ 1 - 5 ]
	 * @return	boolean True on success
	 * @since	1.5
	 */
	function storeVote($rating)
	{
		if ( 1 <= $rating && $rating >= 5)
		{
			$userIP 			=  $_SERVER['REMOTE_ADDR'];
			$articleRatings	= null;

			$query = "SELECT *" .
					"\n FROM #__content_rating" .
					"\n WHERE content_id = $this->_id";
			$this->_db->setQuery($query);
			if (!($this->_db->loadObject($articleRatings))) {
				
				// There are no ratings yet, so lets insert our rating
				$query = "INSERT INTO #__content_rating ( content_id, lastip, rating_sum, rating_count )" .
						"\n VALUES ( $this->_id, '$userIP', $rating, 1 )";
				$this->_db->setQuery($query);
					if (!$this->_db->query()) {
						JError::raiseError( 500, $this->_db->stderr());
					}
			}
			else {
				if ($userIP != ($votesdb->lastip)) {
					
					// We weren't the last voter so lets add our vote to the ratings totals for the article
					$query = "UPDATE #__content_rating" .
							"\n SET rating_count = rating_count + 1, rating_sum = rating_sum + $rating, lastip = '$userIP'" .
							"\n WHERE content_id = $this->_id";
					$this->_db->setQuery($query);
					if (!$this->_db->query()) {
						JError::raiseError( 500, $this->_db->stderr());
					}
				}
				else {
					// Cannot vote for the same article twice (at least in a row... this could probably be better)
					return false;
				}
			}
			return true;
		}
		JError::raiseWarning( 'SOME_ERROR_CODE', 'Article Rating:: Invalid Rating: '.$rating, "JModelArticle::storeVote($rating)");
		return false;
	}

	function sendEmail($to, $from, $fromname = null, $subject = null)
	{
		// Build the link to send in the E-Mail
		$_Itemid	= JContentHelper::getItemid($this->_id);
		$link = sefRelToAbs('index.php?option=com_content&task=view&id='.$this->_id.'&Itemid='.$_Itemid);

		// Build the message body to send in the E-Mail
		$SiteName	= $this->_app->getCfg('sitename');
		$body = sprintf(JText::_('EMAIL_MSG'), $SiteName, $fromname, $from, $link);

		// Clean the email data
		$subject			= JMailHelper::cleanSubject($subject);
		$body				= JMailHelper::cleanBody($body);
		$yourename	= JMailHelper::cleanAddress($fromname);

		// Send the email
		josMail($from, $fromname, $to, $subject, $body);
	}

	/**
	 * Method to load content article data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function _loadArticle()
	{
		/*
		 * Lets load the content if it doesn't already exist
		 */
		if (empty($this->_article))
		{
			// If voting is turned on, get voting data as well for the article
			$voting	= JContentHelper::buildVotingQuery();

			// Get the WHERE clause
			$where	= $this->_buildContentWhere();

			$query = "SELECT a.*, u.name AS author, u.usertype, cc.title AS category, s.title AS section," .
					"\n g.name AS groups, s.published AS sec_pub, cc.published AS cat_pub, s.access AS sec_access, cc.access AS cat_access".$voting['select'].
					"\n FROM #__content AS a" .
					"\n LEFT JOIN #__categories AS cc ON cc.id = a.catid" .
					"\n LEFT JOIN #__sections AS s ON s.id = cc.section AND s.scope = 'content'" .
					"\n LEFT JOIN #__users AS u ON u.id = a.created_by" .
					"\n LEFT JOIN #__groups AS g ON a.access = g.id".
					$voting['join'].
					$where;
			$this->_db->setQuery($query);
			return $this->_db->loadObject($this->_article);
		}		
		return true;
	}

	/**
	 * Method to load content article parameters
	 *
	 * @access	private
	 * @return	void
	 * @since	1.5
	 */
	function _loadArticleParams()
	{
		$user	= & $this->_app->getUser();
		$pop	= JRequest::getVar('pop', 0, '', 'int');

		// Create a new parameters object for the article
		$params = new JParameter($this->_article->attribs);

		// Default setting
		$params->set('intro_only', 0);

		// We only want to view navigation if necessary
		if ($this->_article->sectionid == 0) {
			$params->set('item_navigation', 0);
		} else {
			$params->set('item_navigation', $this->_app->getCfg('item_navigation'));
		}

		// Set some metatag information if needed
		if ($this->_app->getCfg('MetaTitle') == '1') {
			$this->_app->addMetaTag('title', $this->_article->title);
		}
		if ($this->_app->getCfg('MetaAuthor') == '1') {
			$this->_app->addMetaTag('author', $this->_article->author);
		}

		// Handle global overides for some article parameters if set
		$params->def('link_titles',	$this->_app->getCfg('link_titles'));
		$params->def('author',		!$this->_app->getCfg('hideAuthor'));
		$params->def('createdate',	!$this->_app->getCfg('hideCreateDate'));
		$params->def('modifydate',	!$this->_app->getCfg('hideModifyDate'));
		$params->def('print',		!$this->_app->getCfg('hidePrint'));
		$params->def('pdf',			!$this->_app->getCfg('hidePdf'));
		$params->def('email',		!$this->_app->getCfg('hideEmail'));
		$params->def('rating',		$this->_app->getCfg('vote'));

		$params->def('back_button', $this->_app->getCfg('back_button'));
		$params->def('icons',		$this->_app->getCfg('icons'));
		$params->def('readmore',	$this->_app->getCfg('readmore'));
		
		// Get some article specific parameter defaults
		$params->def('image',			1);
		$params->def('section',			0);
		$params->def('popup',			$pop);
		$params->def('section_link',	0);
		$params->def('category',		0);
		$params->def('category_link',	0);
		$params->def('introtext',		1);
		$params->def('pageclass_sfx',	'');
		$params->def('item_title',		1);
		$params->def('url',				1);

		// Set the Section name as a link if needed
		if ($params->get('section_link') && $this->_article->sectionid) {
			$this->_article->section = JContentHelper::getSectionLink($this->_article);
		}

		// Set the Category name as a link if needed
		if ($params->get('category_link') && $this->_article->catid) {
			$this->_article->category = JContentHelper::getCategoryLink($this->_article);
		}

		// Show or Hide the introtext column
		if ($params->get('introtext')) {
			$this->_article->text = $this->_article->introtext . ($params->get('intro_only') ? '' : chr(13).chr(13).$this->_article->fulltext);
		}
		else {
			$this->_article->text = $this->_article->fulltext;
		}

		// Set the article object's parameters
		$this->_article->parameters = & $params;
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
		$user		= & $this->_app->getUser();
		$gid			= $user->get('gid');
		$now		=$this->_app->get('requestTime');
		$nullDate	= $this->_db->getNullDate();
	
		/*
		 * First thing we need to do is assert that the content article is the one
		 * we are looking for and we have access to it.
		 */
		$where = "\n WHERE a.id = $this->_id";
		$where .= "\n AND a.access <= $gid";
		
		if (!$user->authorize('action', 'edit', 'content', 'all')) {
			$where .= " AND ( a.state = 1 OR a.state = -1 )" .
					"\n AND ( a.publish_up = '$nullDate' OR a.publish_up <= '$now' )" .
					"\n AND ( a.publish_down = '$nullDate' OR a.publish_down >= '$now' )";
		}
	
		return $where;
	}
}
?>