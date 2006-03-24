<?php
/**
 * @version $Id$
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
require_once (JApplicationHelper::getPath('front_html', 'com_content'));

// Require the MVC libraries
require_once (dirname(__FILE__).DS.'app'.DS.'model.php');
require_once (dirname(__FILE__).DS.'app'.DS.'view.php');
require_once (dirname(__FILE__).DS.'app'.DS.'controller.php');


/*
switch (strtolower($task))
{
	case 'section' :
		JContentController::showSection();
		break;

	case 'category' :
		JContentController::showCategory();
		break;

	case 'blogsection' :
		JContentController::showBlogSection();
		break;

	case 'blogcategorymulti' :
	case 'blogcategory' :
		JContentController::showBlogCategory();
		break;

	case 'archivesection' :
		JContentController::showArchiveSection();
		break;

	case 'archivecategory' :
		JContentController::showArchiveCategory();
		break;

	case 'view' :
		JContentController::showItem();
		break;

	case 'viewpdf' :
		JContentController::showItemAsPDF();
		break;

	case 'edit' :
	case 'new' :
		JContentController::editItem();
		break;

	case 'save' :
	case 'apply' :
	case 'apply_new' :
		$cache = & JFactory::getCache();
		$cache->cleanCache('com_content');
		JContentController::saveContent();
		break;

	case 'cancel' :
		JContentController::cancelContent();
		break;

	case 'emailform' :
		JContentController::emailContentForm();
		break;

	case 'emailsend' :
		JContentController::emailContentSend();
		break;

	case 'vote' :
		JContentController::recordVote();
		break;

	case 'findkey' :
		JContentController::findKeyItem();
		break;

	default :
		// Tried to access an unknown task
		JError::raiseError( 404, JText::_("Resource Not Found") );
		break;
}
*/

/**
 * Content Component Controller
 *
 * @package Joomla
 * @subpackage Content
 * @since 1.5
 */
class JContentController extends JController
{
	/**
	 * Method to build data for displaying a content section
	 *
	 * @access	public
	 * @since	1.5
	 */
	function section()
	{
		// Dirty trick for now until we get the menus ready for us
		$this->setViewName( 'section', 'com_content', 'HTML' );

		// Set some parameter defaults
		// TODO: probably this needs to move into the view class
		$this->_menu->parameters->def('page_title', 1);
		$this->_menu->parameters->def('pageclass_sfx', '');
		$this->_menu->parameters->def('other_cat_section', 1);
		$this->_menu->parameters->def('empty_cat_section', 0);
		$this->_menu->parameters->def('other_cat', 1);
		$this->_menu->parameters->def('empty_cat', 0);
		$this->_menu->parameters->def('cat_items', 1);
		$this->_menu->parameters->def('cat_description', 1);
		$this->_menu->parameters->def('back_button', $this->_app->getCfg('back_button'));
		$this->_menu->parameters->def('pageclass_sfx', '');

		// Get the view
		$view = & $this->getView();

		// Create the model
		require_once (dirname(__FILE__).DS.'model'.DS.'section.php');
		$model = & new JModelSection($this->_app, $this->_menu);

		// Get the id of the section to display and set the model
		$id = JRequest::getVar('id', 0, '', 'int');
		$model->setId($id);

		// Push the model into the view (as default)
		$view->setModel($model, true);
		// Display the view
		$view->display();

//		$cache = & JFactory::getCache('com_content', 'output');
//		if (!$cache->start(md5($id.'section'.$Itemid), 'com_content')) {
//			JViewContentHTML::showSection( $model );
//			$cache->end();
//		}
	}

	/**
	 * Method to build data for displaying a content section
	 *
	 * @static
	 * @since 1.0
	 */
	function category()
	{
		// Dirty trick for now until we get the menus ready for us
		$this->setViewName( 'category', 'com_content', 'HTML' );

		// Set some parameter defaults
		// TODO: probably this needs to move into the view class
		$this->_menu->parameters->def('page_title',				1);
		$this->_menu->parameters->def('title',							1);
		$this->_menu->parameters->def('hits',							$this->_app->getCfg('hits'));
		$this->_menu->parameters->def('author',						!$this->_app->getCfg('hideAuthor'));
		$this->_menu->parameters->def('date',							!$this->_app->getCfg('hideCreateDate'));
		$this->_menu->parameters->def('date_format',			JText::_('DATE_FORMAT_LC'));
		$this->_menu->parameters->def('navigation',				2);
		$this->_menu->parameters->def('display',					1);
		$this->_menu->parameters->def('display_num',			$this->_app->getCfg('list_limit'));
		$this->_menu->parameters->def('other_cat',				1);
		$this->_menu->parameters->def('empty_cat',				0);
		$this->_menu->parameters->def('cat_items',				1);
		$this->_menu->parameters->def('cat_description',	0);
		$this->_menu->parameters->def('back_button',			$this->_app->getCfg('back_button'));
		$this->_menu->parameters->def('pageclass_sfx',		'');
		$this->_menu->parameters->def('headings',					1);
		$this->_menu->parameters->def('filter',							1);
		$this->_menu->parameters->def('filter_type',				'title');

		// Get the view
		$view = & $this->getView();

		// Create the model
		require_once (dirname(__FILE__).DS.'model'.DS.'category.php');
		$model = & new JModelCategory($this->_app, $this->_menu);

		// Get the id of the section to display and set the model
		$id = JRequest::getVar('id', 0, '', 'int');
		$model->setId($id);

		// Push the model into the view (as default)
		$view->setModel($model, true);
		// Display the view
		$view->display();
		
//		$cache = & JFactory::getCache('com_content', 'output');
//		if (!$cache->start(md5($id.'category'.$Itemid), 'com_content')) {
//			JViewContentHTML::showCategory($model, $access, $lists, $selected);
//			$cache->end();
//		}
	}

	function blogsection()
	{
		// Dirty trick for now until we get the menus ready for us
		$this->setViewName( 'blog', 'com_content', 'HTML' );

		// Get the view
		$view = & $this->getView();

		require_once (dirname(__FILE__).DS.'model'.DS.'section.php');
		$model = & new JModelSection($this->_app, $this->_menu);

		// Get the id of the section to display and set the model
		$id = JRequest::getVar('id', 0, '', 'int');
		$model->setId($id);

		// Push the model into the view (as default)
		$view->setModel($model, true);
		// Display the view
		$view->display();
		
//		$cache = & JFactory::getCache('com_content', 'output');
//		if (!$cache->start(md5($id.'sectionblog'.$Itemid), 'com_content')) {
//			JViewContentHTML::showblog($model, $access, $menu);
//			$cache->end();
//		}
	}

	function blogcategory()
	{
		// Dirty trick for now until we get the menus ready for us
		$this->setViewName( 'blog', 'com_content', 'HTML' );

		// Get the view
		$view = & $this->getView();

		require_once (dirname(__FILE__).DS.'model'.DS.'category.php');
		$model = & new JModelCategory($this->_app, $this->_menu);

		// Get the id of the section to display and set the model
		$id = JRequest::getVar('id', 0, '', 'int');
		$model->setId($id);

		// Push the model into the view (as default)
		$view->setModel($model, true);
		// Display the view
		$view->display();
		
//		$cache = & JFactory::getCache('com_content', 'output');
//		if (!$cache->start(md5($id.'sectionblog'.$Itemid), 'com_content')) {
//			JViewContentHTML::showblog($model, $access, $menu);
//			$cache->end();
//		}
	}

	function archivesection()
	{
		// Dirty trick for now until we get the menus ready for us
		$this->setViewName( 'archive', 'com_content', 'HTML' );

		// Get the view
		$view = & $this->getView();

		require_once (dirname(__FILE__).DS.'model'.DS.'section.php');
		$model = & new JModelSection($this->_app, $this->_menu);

		// Get the id of the section to display and set the model
		$id = JRequest::getVar('id', 0, '', 'int');
		$model->setId($id);

		// Push the model into the view (as default)
		$view->setModel($model, true);
		// Display the view
		$view->display();
	}

	function archivecategory()
	{
		// Dirty trick for now until we get the menus ready for us
		$this->setViewName( 'archive', 'com_content', 'HTML' );

		// Get the view
		$view = & $this->getView();

		require_once (dirname(__FILE__).DS.'model'.DS.'category.php');
		$model = & new JModelCategory($this->_app, $this->_menu);

		// Get the id of the section to display and set the model
		$id = JRequest::getVar('id', 0, '', 'int');
		$model->setId($id);

		// Push the model into the view (as default)
		$view->setModel($model, true);
		// Display the view
		$view->display();
	}

	/**
	 * Method to show a content item as the main page display
	 *
	 * @static
	 * @return void
	 * @since 1.0
	 */
	function view()
	{
		// Create the view
		$view = & $this->getView();
		
		// Create the model
		require_once (dirname(__FILE__).DS.'model'.DS.'article.php');
		$model = & new JModelArticle($this->_app, $this->_menu);

		// Get the id of the article to display and set the model
		$id = JRequest::getVar('id', 0, '', 'int');
		$model->setId($id);

		// Push the model into the view (as default)
		$view->setModel($model, true);
		// Display the view
		$view->display();
		
//		$cache = & JFactory::getCache('com_content', 'output');
//		if (!$cache->start(md5($id.'article'.$Itemid.$page), 'com_content')) {
//			JViewContentHTML::showItem($model, $access, $page);
//			$cache->end();
//		}
	}

	function viewpdf()
	{
		require_once (dirname(__FILE__).DS.'content.pdf.php');
		JViewContentPDF::showItem();
	}

	function editItem()
	{
		global $mainframe, $Itemid;

		/*
		 * Initialize variables
		 */
		$db				= & $mainframe->getDBO();
		$user			= & $mainframe->getUser();
		$breadcrumbs	= & $mainframe->getPathWay();
		$nullDate		= $db->getNullDate();
		$id			= JRequest::getVar('id', 0, '', 'int');
		$sectionid		= JRequest::getVar('sectionid', 0, '', 'int');
		$task			= JRequest::getVar('task');

		/*
		 * Create a user access object for the user
		 */
		$access					= new stdClass();
		$access->canEdit		= $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn		= $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish		= $user->authorize('action', 'publish', 'content', 'all');

		if ($Itemid) {
			$menu = JMenu::getInstance();
			$menu = $menu->getItem($Itemid);
			$params = new JParameter($menu->params);
		} else {
			$menu = null;
			$params = new JParameter();
		}

		/*
		 * Get the content data object
		 */
		require_once (dirname(__FILE__).DS.'model'.DS.'article.php');
		$model = & new JModelArticle($db, $params, $id);

		// fail if checked out not by 'me'
		if ($model->isCheckedOut($user->get('id'))) {
			JViewContentHTML::userInputError(JText::_('The module')." [ ".$row->title." ] ".JText::_('DESCBEINGEDITTEDBY'));
		}

		if ($id) {
			// existing record
			if (!($access->canEdit || ($access->canEditOwn && $model->get('created_by') == $user->get('gid')))) {
				JError::raiseError( 403, JText::_("Access Forbidden") );
			}
			$sectionid = $model->get('sectionid');
			$row->checkout($user->get('id'));
			if (trim($row->publish_down) == $nullDate) {
				$row->publish_down = 'Never';
			}
			if (trim($row->images)) {
				$row->images = explode("\n", $row->images);
			} else {
				$row->images = array ();
			}
			$query = "SELECT name" .
					"\n FROM #__users" .
					"\n WHERE id = $row->created_by";
			$db->setQuery($query);
			$row->creator = $db->loadResult();

			// test to reduce unneeded query
			if ($row->created_by == $row->modified_by) {
				$row->modifier = $row->creator;
			} else {
				$query = "SELECT name" .
						"\n FROM #__users" .
						"\n WHERE id = $row->modified_by";
				$db->setQuery($query);
				$row->modifier = $db->loadResult();
			}

			$query = "SELECT content_id" .
					"\n FROM #__content_frontpage" .
					"\n WHERE content_id = $row->id";
			$db->setQuery($query);
			$row->frontpage = $db->loadResult();

			$title = JText::_('Edit');
		} else {
			// new record
			if (!($access->canEdit || $access->canEditOwn)) {
				JError::raiseError( 403, JText::_("Access Forbidden") );
			}
			$model->set('catid', 0);
			$row->sectionid			= $sectionid;
			$row->version				= 0;
			$row->state				= 0;
			$row->ordering			= 0;
			$row->images				= array ();
			$row->publish_up		= date('Y-m-d', time());
			$row->publish_down	= 'Never';
			$row->creator				= 0;
			$row->modifier			= 0;
			$row->frontpage		= 0;

			$title = JText::_('New');
		}

		$lists = array ();

		// get the type name - which is a special category
		$query = "SELECT name FROM #__sections" .
				"\n WHERE id = $sectionid";
		$db->setQuery($query);
		$section = $db->loadResult();

		// calls function to read image from directory
		$pathA			= 'images/stories';
		$pathL			= 'images/stories';
		$images		= array ();
		$folders		= array ();
		$folders[]		= mosHTML::makeOption('/');
		mosAdminMenus::ReadImages($pathA, '/', $folders, $images);
		// list of folders in images/stories/
		$lists['folders'] = mosAdminMenus::GetImageFolders($folders, $pathL);
		// list of images in specfic folder in images/stories/
		$lists['imagefiles'] = mosAdminMenus::GetImages($images, $pathL);
		// list of saved images
		$lists['imagelist'] = mosAdminMenus::GetSavedImages($row, $pathL);

		// build the html select list for ordering
		$query = "SELECT ordering AS value, title AS text" .
				"\n FROM #__content" .
				"\n WHERE catid = $row->catid" .
				"\n ORDER BY ordering";
		$lists['ordering'] = mosAdminMenus::SpecificOrdering($row, $uid, $query, 1);

		// build list of categories
		$lists['catid'] = mosAdminMenus::ComponentCategory('catid', $sectionid, intval($row->catid));
		// build the select list for the image positions
		$lists['_align'] = mosAdminMenus::Positions('_align');
		// build the html select list for the group access
		$lists['access'] = mosAdminMenus::Access($row);

		// build the select list for the image caption alignment
		$lists['_caption_align'] = mosAdminMenus::Positions('_caption_align');
		// build the html select list for the group access
		// build the select list for the image caption position
		$pos[] = mosHTML::makeOption('bottom', JText::_('Bottom'));
		$pos[] = mosHTML::makeOption('top', JText::_('Top'));
		$lists['_caption_position'] = mosHTML::selectList($pos, '_caption_position', 'class="inputbox" size="1"', 'value', 'text');

		// build the html radio buttons for published
		$lists['state'] = mosHTML::yesnoradioList('state', '', $row->state);
		// build the html radio buttons for frontpage
		$lists['frontpage'] = mosHTML::yesnoradioList('frontpage', '', $row->frontpage);

		$title = $title.' '.JText::_('Content');

		// Set page title
		$mainframe->setPageTitle($title);

		// Add pathway item
		$breadcrumbs->addItem($title, '');

		JViewContentHTML::editContent($row, $section, $lists, $images, $access, $user->get('id'), $sectionid, $task, $Itemid);
	}

	/**
	* Saves the content item an edit form submit
	*/
	function saveContent()
	{
		global $mainframe, $Itemid;

		/*
		 * Initialize variables
		 */
		$db			= & $mainframe->getDBO();
		$user		= & $mainframe->getUser();
		$nullDate	= $db->getNullDate();
		$task		= JRequest::getVar('task');

		/*
		 * Create a user access object for the user
		 */
		$access					= new stdClass();
		$access->canEdit		= $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn		= $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish		= $user->authorize('action', 'publish', 'content', 'all');

		$row = & JTable::getInstance('content', $db);
		if (!$row->bind($_POST))
		{
			JError::raiseError( 500, $row->getError());
		}

		$isNew = ($row->id < 1);
		if ($isNew)
		{
			// new record
			if (!($access->canEdit || $access->canEditOwn))
			{
				JError::raiseError( 403, JText::_("Access Forbidden") );
			}
			$row->created = date('Y-m-d H:i:s');
			$row->created_by = $user->get('id');
		}
		else
		{
			// existing record
			if (!($access->canEdit || ($access->canEditOwn && $row->created_by == $user->get('id'))))
			{
				JError::raiseError( 403, JText::_("Access Forbidden") );
			}
			$row->modified = date('Y-m-d H:i:s');
			$row->modified_by = $user->get('id');
		}
		if (trim($row->publish_down) == 'Never')
		{
			$row->publish_down = $nullDate;
		}

		// code cleaner for xhtml transitional compliance
		$row->introtext = str_replace('<br>', '<br />', $row->introtext);
		$row->fulltext = str_replace('<br>', '<br />', $row->fulltext);

		// remove <br /> take being automatically added to empty fulltext
		$length = strlen($row->fulltext) < 9;
		$search = strstr($row->fulltext, '<br />');
		if ($length && $search)
		{
			$row->fulltext = NULL;
		}

		$row->title = ampReplace($row->title);

		// Publishing state hardening for Authors
		if (!$access->canPublish)
		{
			if ($isNew)
			{
				// For new items - author is not allowed to publish - prevent them from doing so
				$row->state = 0;
			}
			else
			{
				// For existing items keep existing state - author is not allowed to change status
				$query = "SELECT state" .
						"\n FROM #__content" .
						"\n WHERE id = $row->id";
				$db->setQuery($query);
				$state = $db->loadResult();

				if ($state)
				{
					$row->state = 1;
				}
				else
				{
					$row->state = 0;
				}
			}
		}

		if (!$row->check())
		{
			JError::raiseError( 500, $row->getError());
		}
		$row->version++;
		if (!$row->store())
		{
			JError::raiseError( 500, $row->getError());
		}

		// manage frontpage items
		require_once (JApplicationHelper::getPath('class', 'com_frontpage'));
		$fp = new JTableFrontPage($db);

		if (JRequest::getVar('frontpage', false, '', 'boolean'))
		{

			// toggles go to first place
			if (!$fp->load($row->id))
			{
				// new entry
				$query = "INSERT INTO #__content_frontpage" .
						"\n VALUES ( $row->id, 1 )";
				$db->setQuery($query);
				if (!$db->query())
				{
					JError::raiseError( 500, $db->stderror());
				}
				$fp->ordering = 1;
			}
		}
		else
		{
			// no frontpage mask
			if (!$fp->delete($row->id))
			{
				$msg .= $fp->stderr();
			}
			$fp->ordering = 0;
		}
		$fp->reorder();

		$row->checkin();
		$row->reorder("catid = $row->catid");

		// gets section name of item
		$query = "SELECT s.title" .
				"\n FROM #__sections AS s" .
				"\n WHERE s.scope = 'content'" .
				"\n AND s.id = $row->sectionid";
		$db->setQuery($query);
		// gets category name of item
		$section = $db->loadResult();

		$query = "SELECT c.title" .
				"\n FROM #__categories AS c" .
				"\n WHERE c.id = $row->catid";
		$db->setQuery($query);
		$category = $db->loadResult();

		if ($isNew)
		{
			// messaging for new items
			require_once (JApplicationHelper::getPath('class', 'com_messages'));
			$query = "SELECT id" .
					"\n FROM #__users" .
					"\n WHERE sendEmail = 1";
			$db->setQuery($query);
			$users = $db->loadResultArray();
			foreach ($users as $user_id)
			{
				$msg = new mosMessage($db);
				$msg->send($user->get('id'), $user_id, "New Item", sprintf(JText::_('ON_NEW_CONTENT'), $user->get('username'), $row->title, $section, $category));
			}
		}

		$msg = $isNew ? JText::_('THANK_SUB') : JText::_('Item successfully saved.');
		$msg = $user->get('usertype') == 'Publisher' ? JText::_('THANK_SUB') : $msg;
		switch ($task)
		{
			case 'apply' :
				$link = $_SERVER['HTTP_REFERER'];
				break;

			case 'apply_new' :
				$Itemid = JRequest::getVar('Returnid', $Itemid, 'post');
				$link = 'index.php?option=com_content&task=edit&id='.$row->id.'&Itemid='.$Itemid;
				break;

			case 'save' :
			default :
				$Itemid = JRequest::getVar('Returnid', '', 'post');
				if ($Itemid)
				{
					$link = 'index.php?option=com_content&task=view&id='.$row->id.'&Itemid='.$Itemid;
				}
				else
				{
					$link = JRequest::getVar('referer', '', 'post');
				}
				break;
		}
		josRedirect($link, $msg);
	}

	/**
	* Cancels an edit content item operation
	*
	* @static
	* @since 1.0
	*/
	function cancelContent()
	{
		global $mainframe;

		/*
		 * Initialize variables
		 */
		$db			= & $mainframe->getDBO();
		$user		= & $mainframe->getUser();
		$task		= JRequest::getVar('task');
		$Itemid	= JRequest::getVar('Returnid', '0', 'post');
		$referer	= JRequest::getVar('referer', '', 'post');
		$query		= null;

		/*
		 * Create a user access object for the user
		 */
		$access							= new stdClass();
		$access->canEdit			= $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn		= $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish		= $user->authorize('action', 'publish', 'content', 'all');

		$row = & JTable::getInstance('content', $db);
		$row->bind($_POST);

		if ($access->canEdit || ($access->canEditOwn && $row->created_by == $user->get('id')))
		{
			$row->checkin();
		}

		/*
		 * If the task was edit or cancel, we go back to the content item
		 */
		if ($task == 'edit' || $task == 'cancel')
		{
			$referer = 'index.php?option=com_content&task=view&id='.$row->id.'&Itemid='.$Itemid;
		}

		echo $task;

		/*
		 * If the task was not new, we go back to the referrer
		 */
		if ($referer && $row->id)
		{
			josRedirect($referer);
		}
		else
		{
			josRedirect('index.php');
		}
	}

	/**
	 * Shows the send email form for a content item
	 *
	 * @static
	 * @since 1.0
	 */
	function emailContentForm()
	{
		global $mainframe;

		/*
		 * Initialize variables
		 */
		$db		= & $mainframe->getDBO();
		$user	= & $mainframe->getUser();
		$uid		= JRequest::getVar('id', 0, '', 'int');

		/*
		 * Create a user access object for the user
		 */
		$access							= new stdClass();
		$access->canEdit			= $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn		= $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish		= $user->authorize('action', 'publish', 'content', 'all');

		$row = & JTable::getInstance('content', $db);
		$row->load($uid);

		if ($row->id === null || $row->access > $user->get('gid'))
		{
			JError::raiseError( 403, JText::_("Access Forbidden") );
		}
		else
		{
			$query = "SELECT template" .
					"\n FROM #__templates_menu" .
					"\n WHERE client_id = 0" .
					"\n AND menuid = 0";
			$db->setQuery($query);
			$template = $db->loadResult();
			JViewContentHTML::emailForm($row->id, $row->title, $template);
		}

	}

	/**
	 * Builds and sends an email to a content item
	 *
	 * @static
	 * @since 1.0
	 */
	function emailContentSend()
	{
		global $mainframe;

		$db			= & $mainframe->getDBO();
		$id			= JRequest::getVar('id', 0, '', 'int');
		$validate	= JRequest::getVar(mosHash('validate'), 0, 'post');

		if (!$validate)
		{
			JError::raiseError( 403, JText::_("Access Forbidden") );
		}

		/*
		 * This obviously won't catch all attempts, but it does not hurt to make
		 * sure the request came from a client with a user agent string.
		 */
		if (!isset ($_SERVER['HTTP_USER_AGENT']))
		{
			JError::raiseError( 403, JText::_("Access Forbidden") );
		}

		/*
		 * This obviously won't catch all attempts either, but we ought to check
		 * to make sure that the request was posted as well.
		 */
		if (!$_SERVER['REQUEST_METHOD'] == 'POST')
		{
			JError::raiseError( 403, JText::_("Access Forbidden") );
		}

		// An array of e-mail headers we do not want to allow as input
		$headers = array ('Content-Type:', 'MIME-Version:', 'Content-Transfer-Encoding:', 'bcc:', 'cc:');

		// An array of the input fields to scan for injected headers
		$fields = array ('email', 'yourname', 'youremail', 'subject',);

		/*
		 * Here is the meat and potatoes of the header injection test.  We
		 * iterate over the array of form input and check for header strings.
		 * If we fine one, send an unauthorized header and die.
		 */
		foreach ($fields as $field)
		{
			foreach ($headers as $header)
			{
				if (strpos($_POST[$field], $header) !== false)
				{
					JError::raiseError( 403, JText::_("Access Forbidden") );
				}
			}
		}

		// Free up memory
		unset ($headers, $fields);

		$to				= JRequest::getVar('email', '', 'post');
		$from			= JRequest::getVar('youremail', $mainframe->getCfg('mailfrom'), 'post');
		$fromname	= JRequest::getVar('yourname', $mainframe->getCfg('fromname'), 'post');
		$subject		= JRequest::getVar('subject', sprintf(JText::_('Item sent by'), $fromname), 'post');

		jimport('joomla.utilities.mail');
		if (!JMailHelper::isEmailAddress($to) || !JMailHelper::isEmailAddress($from)) {
			JViewContentHTML::userInputError(JText::_('INALID_EMAIL_ADDRESS'));
			return false;
		}
		if (!JMailHelper::cleanAddress($to) || !JMailHelper::cleanAddress($from)) {
			JError::raiseError( 403, JText::_("Access Forbidden") );
			return false;
		}

		$query = "SELECT template" .
				"\n FROM #__templates_menu" .
				"\n WHERE client_id = 0" .
				"\n AND menuid = 0";
		$db->setQuery($query);
		$template = $db->loadResult();

		// Get the content article model
		require_once (dirname(__FILE__).DS.'model'.DS.'article.php');
		$params	=& new JParameters();
		$model		=& new JModelArticle($db, $params, $id);

		// Send mail via the model
		$email = $model->sendEmail($to, $from, $fromname, $subject);

		if (!$email) {
			JViewContentHTML::userInputError(JText::_('EMAIL_ERR_NOINFO'));
		} else {
			JViewContentHTML::emailSent($email, $template);
		}
	}

	function recordVote()
	{
		global $mainframe;

		$db		= & $mainframe->getDBO();
		$url		= JRequest::getVar('url', '');
		$rating	= JRequest::getVar('user_rating', 0, '', 'int');
		$id		= JRequest::getVar('cid', 0, '', 'int');

		// Get the content article model
		require_once (dirname(__FILE__).DS.'model'.DS.'article.php');
		$params	=& new JParameters();
		$model		=& new JModelArticle($db, $params, $id);

		if ($model->storeVote($rating)) {
			josRedirect($url, JText::_('Thanks for your vote!'));
		} else {
			josRedirect($url, JText::_('You already voted for this poll today!'));
		}
	}

	/**
	 * Searches for an item by a key parameter
	 *
	 * @static
	 * @return void
	 * @since 1.0
	 */
	function findKeyItem()
	{
		global $mainframe;

		/*
		 * Initialize variables
		 */
		$db			= & $mainframe->getDBO();
		$user		= & $mainframe->getUser();
		$now		= $mainframe->get('requestTime');
		$keyref	= $db->getEscaped(JRequest::getVar('keyref'));
		$pop		= JRequest::getVar('pop', 0, '', 'int');
		$option	= JRequest::getVar('option');

		$query = "SELECT id" .
				"\n FROM #__content" .
				"\n WHERE attribs LIKE '%keyref=$keyref%'";
		$db->setQuery($query);
		$id = $db->loadResult();
		if ($id > 0)
		{
			showItem($id, $user->get('gid'), $pop, $option, $now);
		}
		else
		{
			JError::raiseError( 404, JText::_("Key Not Found") );
		}
	}
}

// get the view from the request - set the default
// note - alternatively we can get it from the menu params

$view = JRequest::getVar( 'view', 'article' );

// Create the controller
$controller = & new JContentController( $mainframe, 'view' );

// need to tell the controller where to look for views
$controller->setViewPath( dirname( __FILE__ ) . DS . 'view' );

// set the view name from the Request
$controller->setViewName( $view, 'com_content', 'HTML' );

// Register Extra tasks
$controller->registerTask( 'blogcategorymulti', 'blogcategory' );

// perform the Request task
$controller->performTask( $task );

// redirect if set by the controller
$controller->redirect();
?>