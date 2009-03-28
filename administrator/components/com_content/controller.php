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

jimport('joomla.application.component.controller');

/**
 * Content Component Controller
 *
 * @package		Joomla.Administrator
 * @subpackage	Content
 * @since 1.5
 */
class ContentController extends JController
{
	function __construct($config = array())
	{
		parent::__construct($config);

		// Register Extra tasks
		$this->registerTask('wizard',  'element');
		$this->registerTask('content', 'display');
		$this->registerTask('add',  'display');
		$this->registerTask('new',  'display');
		$this->registerTask('edit', 'display');
		$this->registerTask('ins_pagebreak', 'display');
		$this->registerTask('preview', 'display');
		$this->registerTask('copy', 'display');
		$this->registerTask('movesect', 'display');
		$this->registerTask('go2menu', 'save');
		$this->registerTask('go2menuitem', 'save');
		$this->registerTask('menulink', 'save');
		$this->registerTask('resethits', 'save');
		$this->registerTask('apply', 'save');
	}

	function display()
	{
		switch($this->getTask())
		{
			case 'add':
			{
				JRequest::setVar('hidemainmenu', 1);
				JRequest::setVar('view'  , 'article');
				JRequest::setVar('edit', false);

				// Checkout the section
				$model = $this->getModel('article');
				$model->checkout();
			} break;
			case 'edit':
			{
				JRequest::setVar('hidemainmenu', 1);
				JRequest::setVar('view'  , 'article');
				JRequest::setVar('edit', true);

				// Checkout the section
				$model = $this->getModel('article');
				$model->checkout();
			} break;
			case 'movesect':
			case 'copy':
			{
				JRequest::setVar('view'  , 'copyselect');
			} break;
			case 'ins_pagebreak':
			{
				JRequest::setVar('view'  , 'pagebreak');
			} break;
			case 'preview':
			{
				JRequest::setVar('view'  , 'prevuuw');
			} break;
			case 'content':
			default:
			{
				JRequest::setVar('view'  , 'articles');
			} break;
		}

		parent::display();
	}

	/**
	 * Articles element
	 */
	function element()
	{
		$model	= &$this->getModel('element');
		$view	= &$this->getView('element');
		$view->setModel($model, true);
		$view->display();
	}

	/**
	* Saves the article an edit form submit
	* @param database A database connector object
	*/
	function save()
	{
		global $mainframe;

		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$db		= & JFactory::getDBO();

		// Initialize variables
		$post		= JRequest::get('post');
		$task		= $this->getTask();
		$redirect	= JRequest::getVar('redirect', $sectionid, 'post', 'int');
		$menu		= JRequest::getVar('menu', 'mainmenu', 'post', 'cmd');
		$menuid		= JRequest::getVar('menuid', 0, 'post', 'int');

		$model = $this->getModel('article');

		$success = $model->store($post);
		$article = $model->getData();

		switch ($task)
		{
			case 'go2menu' :
				$this->setRedirect('index.php?option=com_menus&menutype='.$menu);
				break;

			case 'go2menuitem' :
				$this->setRedirect('index.php?option=com_menus&menutype='.$menu.'&task=edit&id='.$menuid);
				break;

			case 'resethits' :
				$model->resetHits();
				$msg = JText::_('Successfully Reset Hit count');
				$this->setRedirect('index.php?option=com_content&task=edit&cid[]='.$article->id, $msg);
				break;

			case 'apply' :
				if ($success)
					$msg = JText::sprintf('SUCCESSFULLY SAVED CHANGES TO ARTICLE', $article->title);
				else
					$msg = JText::_('Error Saving Article');
				$this->setRedirect('index.php?option=com_content&task=edit&cid[]='.$article->id, $msg);
				break;

			case 'save' :
			default :
				if ($success)
					$msg = JText::sprintf('Successfully Saved Article', $article->title);
				else
					$msg = JText::_('Error Saving Article');
				$this->setRedirect('index.php?option=com_content', $msg);
				break;
		}
	}

	function publish()
	{
		$this->changeState(1);
	}

	function unpublish()
	{
		$this->changeState(0);
	}

	function archive()
	{
		$this->changeState(-1);
	}

	function unarchive()
	{
		$this->changeState(0);
	}

	/**
	* Changes the state of one or more content pages
	*
	* @param string The name of the category section
	* @param integer A unique category id (passed from an edit form)
	* @param array An array of unique category id numbers
	* @param integer 0 if unpublishing, 1 if publishing
	* @param string The name of the current user
	*/
	function changeState($state = 0)
	{
		global $mainframe;

		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialize variables
		$db		= & JFactory::getDBO();
		$user	= & JFactory::getUser();

		$cid	= JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);
		$option	= JRequest::getCmd('option');
		$task		= $this->getTask();
		$rtask	= JRequest::getCmd('returntask', '', 'post');
		if ($rtask) {
			$rtask = '&task='.$rtask;
		}

		$total = count($cid);
		if ($total < 1) {
			$redirect	= JRequest::getVar('redirect', '', 'post', 'int');
			$action		= ($state == 1) ? 'publish' : ($state == -1 ? 'archive' : 'unpublish');
			$msg		= JText::_('Select an item to') . ' ' . JText::_($action);
			$this->setRedirect('index.php?option='.$option.$rtask, $msg, 'error');
			return;
		}

		$model = $this->getModel('article');
		if (!$model->setArticleState($cid, $state)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		switch ($state)
		{
			case -1 :
				$msg = JText::sprintf('Item(s) successfully Archived', $total);
				break;

			case 1 :
				$msg = JText::sprintf('Item(s) successfully Published', $total);
				break;

			case 0 :
			default :
				if ($task == 'unarchive') {
					$msg = JText::sprintf('Item(s) successfully Unarchived', $total);
				} else {
					$msg = JText::sprintf('Item(s) successfully Unpublished', $total);
				}
				break;
		}

		$cache = & JFactory::getCache('com_content');
		$cache->clean();	

		$this->setRedirect('index.php?option='.$option.$rtask, $msg);
	}

	/**
	* Changes the frontpage state of one or more articles
	*
	*/
	function toggle_frontpage()
	{
		global $mainframe;

		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialize variables
		$db		=& JFactory::getDBO();

		$cid	= JRequest::getVar('cid', array(), 'post', 'array');
		$option	= JRequest::getCmd('option');
		$msg	= null;

		JArrayHelper::toInteger($cid);

		if (count($cid) < 1) {
			$msg = JText::_('Select an item to toggle');
			$this->setRedirect('index.php?option='.$option, $msg, 'error');
			return;
		}

		$model = $this->getModel('frontpage');
		if (!$model->toggle($cid))
			$msg = JText::_('Error toggling frontpage flag');

		$cache = & JFactory::getCache('com_content');
		$cache->clean();

		$this->setRedirect('index.php?option='.$option, $msg);
	}

	function remove()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		if (count($cid) < 1) {
			JError::raiseError(500, JText::_('Select an item to delete'));
		}

		$model = $this->getModel('article');
		if (!$model->trash($cid)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$cache = & JFactory::getCache('com_content');
		$cache->clean();

		$msg = JText::sprintf('Item(s) sent to the Trash', count($cid));
		$this->setRedirect('index.php?option=com_content', $msg);
	}

	/**
	* Cancels an edit operation
	*/
	function cancel()
	{
		// Checkin the section
		$model = $this->getModel('article');
		$model->checkin();

		$this->setRedirect('index.php?option=com_content');
	}

	function orderup()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('article');
		$model->move(-1);

		$cache = & JFactory::getCache('com_content');
		$cache->clean();

		$option	= JRequest::getCmd('option');
		$this->setRedirect('index.php?option='.$option);
	}

	function orderdown()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('article');
		$model->move(1);

		$cache = & JFactory::getCache('com_content');
		$cache->clean();

		$option	= JRequest::getCmd('option');
		$this->setRedirect('index.php?option='.$option);
	}

	/**
	* Save the changes to move item(s) to a different section and category
	*/
	function movesectsave()
	{
		global $mainframe;

		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialize variables
		$db			= & JFactory::getDBO();
		$user		= & JFactory::getUser();

		$cid		= JRequest::getVar('cid', array(0), 'post', 'array');
		$option		= JRequest::getCmd('option');

		JArrayHelper::toInteger($cid, array(0));

		$sectcat = JRequest::getVar('sectcat', '', 'post', 'string');
		$sectcat = explode(',', $sectcat);
		$newsect = (int) @$sectcat[0];
		$newcat = (int) @$sectcat[1];

		if ((!$newsect || !$newcat) && ($sectcat !== array('0', '0'))) {
			$this->setRedirect("index.php?option=com_content", JText::_('An error has occurred'));
			return;
		}

		// find category name
		$query = 'SELECT a.title' .
				' FROM #__categories AS a' .
				' WHERE a.id = '. (int) $newcat;
		$db->setQuery($query);
		$category = $db->loadResult();

		$total	= count($cid);
		$cids		= implode(',', $cid);
		$uid		= $user->get('id');

		$content = & JTable::getInstance('content');
		// update old orders - put existing items in last place
		foreach ($cid as $id)
		{
			$content->load(intval($id));
			$content->ordering = 0;
			$content->store();
			$content->reorder('catid = '.(int) $content->catid.' AND state >= 0');
		}

		$query = 'UPDATE #__content SET catid = '.(int) $newcat.
				' WHERE id IN ('.$cids.')' .
				' AND (checked_out = 0 OR (checked_out = '.(int) $uid.'))';
		$db->setQuery($query);
		if (!$db->query())
		{
			JError::raiseError(500, $db->getErrorMsg());
			return false;
		}

		// update new orders - put items in last place
		foreach ($cid as $id)
		{
			$content->load(intval($id));
			$content->ordering = 0;
			$content->store();
			$content->reorder('catid = '.(int) $content->catid.' AND state >= 0');
		}

		if ($category) {
			$msg = JText::sprintf('Item(s) successfully moved to Section', $total, $category);
		} else {
			$msg = JText::sprintf('ITEM(S) SUCCESSFULLY MOVED TO UNCATEGORIZED', $total);
		}

		$this->setRedirect('index.php?option='.$option, $msg);
	}

	/**
	* saves Copies of items
	**/
	function copysave()
	{
		global $mainframe;

		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialize variables
		$db			= & JFactory::getDBO();

		$cid		= JRequest::getVar('cid', array(), 'post', 'array');
		$option		= JRequest::getCmd('option');

		JArrayHelper::toInteger($cid);

		$item	= null;
		$sectcat = JRequest::getVar('sectcat', '-1,-1', 'post', 'string');
		//seperate sections and categories from selection
		$sectcat = explode(',', $sectcat);
		$newsect = (int) @$sectcat[0];
		$newcat = (int) @$sectcat[1];

		if (($newsect == -1) || ($newcat == -1)) {
			$this->setRedirect('index.php?option=com_content', JText::_('An error has occurred'));
			return;
		}

		// find category name
		$query = 'SELECT a.title' .
				' FROM #__categories AS a' .
				' WHERE a.id = '. (int) $newcat;
		$db->setQuery($query);
		$category = $db->loadResult();

		if (($newsect == 0) && ($newcat == 0))
		{
			$section	= JText::_('UNCATEGORIZED');
			$category	= JText::_('UNCATEGORIZED');
		}

		$total = count($cid);
		$content = & JTable::getInstance('content');

		for ($i = 0; $i < $total; $i ++)
		{
			$id = $cid[$i];
			$content->load($id);
			$content->id 		= NULL;
			$content->catid		= $newcat;
			$content->title 	= JText::sprintf('Copy of', $content->title);
			$content->hits 		= 0;
			$content->ordering	= 0;

			if (!$content->check()) {
				JError::raiseError(500, $content->getError());
			}

			if (!$content->store()) {
				JError::raiseError(500, $content->getError());
			}

			$content->checkin();
			$content->reorder('catid='.(int) $content->catid.' AND state >= 0');
		}

		$msg = JText::sprintf('Item(s) successfully copied to Section', $total, $category);
		$this->setRedirect('index.php?option='.$option, $msg);
	}

	function accesspublic()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		$msg = '';
		$model = $this->getModel('article');
		if (!$model->setAccess($cid, 0)) {
			$msg = $model->getError();
		}

		$cache = & JFactory::getCache('com_content');
		$cache->clean();

		$option	= JRequest::getCmd('option');
		$this->setRedirect('index.php?option='.$option, $msg);
	}

	function accessregistered()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		$msg = '';
		$model = $this->getModel('article');
		if (!$model->setAccess($cid, 1)) {
			$msg = $model->getError();
		}

		$cache = & JFactory::getCache('com_content');
		$cache->clean();

		$option	= JRequest::getCmd('option');
		$this->setRedirect('index.php?option='.$option, $msg);
	}

	function accessspecial()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		JArrayHelper::toInteger($cid);

		$msg = '';
		$model = $this->getModel('article');
		if (!$model->setAccess($cid, 2)) {
			$msg = $model->getError();
		}

		$cache = & JFactory::getCache('com_content');
		$cache->clean();

		$option	= JRequest::getCmd('option');
		$this->setRedirect('index.php?option='.$option, $msg);
	}

	function saveorder()
	{
		global $mainframe;

		// Check for request forgeries
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialize variables
		$db			= & JFactory::getDBO();

		$cid		= JRequest::getVar('cid', array(0), 'post', 'array');
		$order		= JRequest::getVar('order', array (0), 'post', 'array');
		$redirect	= JRequest::getVar('redirect', 0, 'post', 'int');

		JArrayHelper::toInteger($cid, array(0));
		JArrayHelper::toInteger($order, array(0));

		$model = $this->getModel('article');
		$model->saveorder($cid, $order);

		$cache = & JFactory::getCache('com_content');
		$cache->clean();

		$msg = JText::_('New ordering saved');
		$this->setRedirect('index.php?option=com_content', $msg);
	}
}