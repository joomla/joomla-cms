<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

/**
 * Content Component Controller
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class ContentController extends JController
{
	/**
	 * Method to show an article as the main page display
	 *
	 * @access	public
	 * @since	1.5
	 */
	function display()
	{
		// View caching logic -- simple... are we logged in?
		$user = &JFactory::getUser();
		if (!$user->get('gid')) {
			parent::display(true);
		} else {
			parent::display();
		}
	}

	/**
	* Edits an article
	*
	* @access	public
	* @since	1.5
	*/
	function edit()
	{
		// Create the view
		$view = & $this->getView('article', 'html');

		// Get/Create the model
		$model = & $this->getModel('Article');

		// Get the id of the article to display and set the model
		$id = JRequest::getVar('id', 0, '', 'int');
		$model->setId($id);

		// Push the model into the view (as default)
		$view->setModel($model, true);

		// Set the layout
		$view->setLayout('form');

		// Display the view
		$view->display();
	}

	/**
	* Saves the content item an edit form submit
	*
	* @todo
	*/
	function save()
	{
		global $mainframe;

		// Initialize variables
		$db			= & JFactory::getDBO();
		$user		= & JFactory::getUser();
		$task		= JRequest::getVar('task', null, 'default', 'cmd');

		// Make sure you are logged in and have the necessary access rights
		if ($user->get('gid') < 19) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}

		// Create a user access object for the user
		$access					= new stdClass();
		$access->canEdit		= $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn		= $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish		= $user->authorize('action', 'publish', 'content', 'all');
		
		//get data from the request
		$model = $this->getModel('article');
		
		//get data from request
		$post = JRequest::get('post');
		$post['text'] = JRequest::getVar('text', '', 'post', 'string', JREQUEST_ALLOWRAW);
		
		//preform access checks
		$isNew = ((int) $post['id'] < 1);
		if ($isNew)
		{
			// new record
			if (!($access->canEdit || $access->canEditOwn)) {
				JError::raiseError( 403, JText::_("ALERTNOTAUTH") );
			}
		}
		else
		{
			// existing record
			if (!($access->canEdit || ($access->canEditOwn && $row->created_by == $user->get('id')))) {
				JError::raiseError( 403, JText::_("ALERTNOTAUTH") );
			}
		}

		if ($model->store($post)) {
			$msg = JText::_( 'Article Saved' );
		} else {
			$msg = JText::_( 'Error Saving Article' );
		}

		// manage frontpage items
		//TODO : Move this into a frontpage model
		require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_frontpage'.DS.'tables'.DS.'frontpage.php');
		$fp = new TableFrontPage($db);

		if (JRequest::getVar('frontpage', false, '', 'boolean'))
		{
			// toggles go to first place
			if (!$fp->load($post['id']))
			{
				// new entry
				$query = 'INSERT INTO #__content_frontpage' .
						' VALUES ( '.$post['id'].', 1 )';
				$db->setQuery($query);
				if (!$db->query()) {
					JError::raiseError( 500, $db->stderr());
				}
				$fp->ordering = 1;
			}
		}
		else
		{
			// no frontpage mask
			if (!$fp->delete($post['id'])) {
				$msg .= $fp->stderr();
			}
			$fp->ordering = 0;
		}
		$fp->reorder();

		$model->checkin();

		// gets section name of item
		$query = 'SELECT s.title' .
				' FROM #__sections AS s' .
				' WHERE s.scope = "content"' .
				' AND s.id = ' . (int) $post['sectionid'];
		$db->setQuery($query);
		// gets category name of item
		$section = $db->loadResult();

		$query = 'SELECT c.title' .
				' FROM #__categories AS c' .
				' WHERE c.id = ' . (int) $post['catid'];
		$db->setQuery($query);
		$category = $db->loadResult();

		if ($isNew)
		{
			// messaging for new items
			require_once (JPATH_ADMINISTRATOR.DS.'components'.DS.'com_messages'.DS.'tables'.DS.'message.php');
			$query = 'SELECT id' .
					' FROM #__users' .
					' WHERE sendEmail = 1';
			$db->setQuery($query);
			$users = $db->loadResultArray();
			foreach ($users as $user_id)
			{
				$msg = new TableMessage($db);
				$msg->send($user->get('id'), $user_id, "New Item", JText::sprintf('ON_NEW_CONTENT', $user->get('username'), $post['title'], $section, $category));
			}
		} else {
			// If the article isn't new, then we need to clean the cache so that our changes appear realtime :)
			$cache = &JFactory::getCache('com_content');
			$cache->clean();
		}

		if ($access->canPublish)
		{
			// Publishers, admins, etc just get the stock msg
			$msg = JText::_('Item successfully saved.');
		}
		else
		{
			$msg = $isNew ? JText::_('THANK_SUB') : JText::_('Item successfully saved.');
		}

		switch ($task)
		{
			case 'apply' :
				$link = $_SERVER['HTTP_REFERER'];
				break;

			case 'apply_new' :
				$link = JRoute::_('index.php?task=edit&id='.$post['id']);
				break;

			case 'save' :
			default :
				$link = JRoute::_('index.php?view=article&id='.$post['id']);
				break;
		}
		$this->setRedirect($link, $msg);
	}

	/**
	* Cancels an edit article operation
	*
	* @access	public
	* @since	1.5
	*/
	function cancel()
	{
		// Initialize some variables
		$db		= & JFactory::getDBO();
		$user	= & JFactory::getUser();

		// At some point in the future these will be in a request object
		$Itemid	= JRequest::getVar('Returnid', '0', 'post', 'int');

		// Get an article table object and bind post variabes to it [We don't need a full model here]
		$article = & JTable::getInstance('content');
		$article->bind(JRequest::get('post'));

		if ($user->authorize('action', 'edit', 'content', 'all') || ($user->authorize('action', 'edit', 'content', 'own') && $article->created_by == $user->get('id'))) {
			$article->checkin();
		}

		// If the task was edit or cancel, we go back to the content item
		if (($this->_task == 'edit' || $this->_task == 'cancel') && $article->id) {
			$referer = 'index.php?option=com_content&view=article&id='.$article->id.'&Itemid='.$Itemid;
		} else {
			$referer = JRequest::getVar('referer', JURI::Base() .'index.php', 'post');
		}

		// If the task was not new, we go back to the referrer
		if ($referer) {
			$this->setRedirect($referer);
		}
		else {
			$this->setRedirect('index.php');
		}
	}

	/**
	* Rates an article
	*
	* @access	public
	* @since	1.5
	*/
	function vote()
	{
		$url	= JRequest::getVar('url', '', 'default', 'string');
		$rating	= JRequest::getVar('user_rating', 0, '', 'int');
		$id		= JRequest::getVar('cid', 0, '', 'int');

		// Get/Create the model
		$model = & $this->getModel('Article' );

		$model->setId($id);
		if ($model->storeVote($rating)) {
			$this->setRedirect($url, JText::_('Thanks for rating!'));
		} else {
			$this->setRedirect($url, JText::_('You already rated this article today!'));
		}
	}

	/**
	 * Searches for an item by a key parameter
	 *
	 * @access	public
	 * @since	1.5
	 */
	function findkey()
	{
		// Initialize variables
		$db		= & JFactory::getDBO();
		$keyref	= JRequest::getVar('keyref', null, 'default', 'string');
		$keyref	= preg_replace('/[^A-Z0-9.-_]/i', '', $keyref);
		$keyref	= JRequest::setVar('keyref', $keyref);
		$keyref	= $db->getEscaped($keyref);

		// If no keyref left, throw 404
		if( empty($keyref) === true ) {
			JError::raiseError( 404, JText::_("Key Not Found") );
		}

		$query =	'SELECT id' .
				' FROM #__content' .
				' WHERE attribs LIKE "%keyref='.$keyref.'%"';
		$db->setQuery($query);
		$id = (int) $db->loadResult();

		if ($id > 0)
		{
			// Create the view
			$view =& $this->getView('article', 'html');

			// Get/Create the model
			$model =& $this->getModel('Article' );

			// Set the id of the article to display
			$model->setId($id);

			// Push the model into the view (as default)
			$view->setModel($model, true);

			// Display the view
			$view->display();
		}
		else {
			JError::raiseError( 404, JText::_("Key Not Found") );
		}
	}

	/**
	 * Output the pagebreak dialog
	 *
	 * @access 	public
	 * @since 	1.5
	 */
	function ins_pagebreak()
	{
		// Create the view
		$view = & $this->getView('article', 'html');

		// Set the layout
		$view->setLayout('pagebreak');

		// Display the view
		$view->display();
	}
}
?>