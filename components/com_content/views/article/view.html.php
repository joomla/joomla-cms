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

require_once JPATH_COMPONENT.DS.'view.php';

/**
 * HTML Article View class for the Content component
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class ContentViewArticle extends ContentView
{
	protected $article = null;
	protected $params = null;
	protected $user = null;
	protected $access = null;
	protected $print = null;
	protected $action = null;
	protected $lists = null;
	protected $editor = null;


	function display($tpl = null)
	{
		$app			=& JFactory::getApplication();
		$user		=& JFactory::getUser();
		$document	=& JFactory::getDocument();
		$dispatcher	=& JDispatcher::getInstance();
		$pathway	=& $app->getPathway();
		$params		= JComponentHelper::getParams('com_content');

		// Initialize variables
		$article	=& $this->get('Article');
		$aparams	= $article->parameters;
		$params->merge($aparams);

		if($this->getLayout() == 'pagebreak') {
			$this->_displayPagebreak($tpl);
			return;
		}

		if($this->getLayout() == 'form') {
			$this->_displayForm($tpl);
			return;
		}

		if (($article->id == 0))
		{
			$id = JRequest::getVar( 'id', '', 'default', 'int' );
			return JError::raiseError( 404, JText::sprintf( 'Article # not found', $id ) );
		}

		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');

		// Create a user access object for the current user
		$access = new stdClass();
		$access->canEdit	= $user->authorize('com_content', 'edit', 'content', 'all');
		$access->canEditOwn	= $user->authorize('com_content', 'edit', 'content', 'own');
		$access->canPublish	= $user->authorize('com_content', 'publish', 'content', 'all');

		// Check to see if the user has access to view the full article
		if ($article->access <= $user->get('aid', 0)) {
			$article->readmore_link = JRoute::_(ContentHelperRoute::getArticleRoute($article->slug, $article->catslug, $article->sectionid));;
		} else {
			$article->readmore_link = JRoute::_("index.php?option=com_user&task=register");
		}

		/*
		 * Process the prepare content plugins
		 */
		JPluginHelper::importPlugin('content');
		$results = $dispatcher->trigger('onPrepareContent', array (& $article, & $params, $limitstart));

		/*
		 * Handle the metadata
		 */
		// because the application sets a default page title, we need to get it
		// right from the menu item itself
		// Get the menu item object
		$menus = &JSite::getMenu();
		$menu  = $menus->getActive();

		if (is_object( $menu ) && isset($menu->query['view']) && $menu->query['view'] == 'article' && isset($menu->query['id']) && $menu->query['id'] == $article->id) {
			$menu_params = new JParameter( $menu->params );
			if (!$menu_params->get( 'page_title')) {
				$params->set('page_title',	$article->title);
			}
		} else {
			$params->set('page_title',	$article->title);
		}
		$document->setTitle( $params->get( 'page_title' ) );

		if ($article->metadesc) {
			$document->setDescription( $article->metadesc );
		}
		if ($article->metakey) {
			$document->setMetadata('keywords', $article->metakey);
		}

		if ($app->getCfg('MetaTitle') == '1') {
			$document->setMetadata('title', $article->title);
		}
		if ($app->getCfg('MetaAuthor') == '1') {
			$document->setMetadata('author', $article->author);
		}

		$mdata = new JParameter($article->metadata);
		$mdata = $mdata->toArray();
		foreach ($mdata as $k => $v)
		{
			if ($v) {
				$document->setMetadata($k, $v);
			}
		}

		// If there is a pagebreak heading or title, add it to the page title
		if (!empty($article->page_title))
		{
			$article->title = $article->title .' - '. $article->page_title;
			$document->setTitle($article->page_title.' - '.JText::sprintf('Page %s', $limitstart + 1));
		}

		/*
		 * Handle the breadcrumbs
		 */
		jimport('joomla.application.categorytree');
		$categorytree = JCategoryTree::getInstance('com_content');
		$pathwaycat = $categorytree->get($article->catid);
		$path = array();
		if(is_object($menu) && $menu->query['view'] != 'article' && $menu->query['id'] != $pathwaycat->id)
		{
			while($pathwaycat->id != $menu->query['id'])
			{
				$path[] = array($pathwaycat->title, $pathwaycat->slug);
				$pathwaycat = $pathwaycat->getParent();	
			}
			$path = array_reverse($path);
			foreach($path as $element)
			{
				if(isset($element[1]))
				{
					$pathway->addItem($element[0], 'index.php?option=com_content&view=category&id='.$element[1]);
				} else {
					$pathway->addItem($element[0], '');
				}
			}
			$pathway->addItem($article->title, '');
		}

		/*
		 * Handle display events
		 */
		$article->event = new stdClass();
		$results = $dispatcher->trigger('onAfterDisplayTitle', array ($article, &$params, $limitstart));
		$article->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onBeforeDisplayContent', array (& $article, & $params, $limitstart));
		$article->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onAfterDisplayContent', array (& $article, & $params, $limitstart));
		$article->event->afterDisplayContent = trim(implode("\n", $results));

		$print = JRequest::getBool('print');

		$this->assignRef('article', $article);
		$this->assignRef('params' , $params);
		$this->assignRef('user'   , $user);
		$this->assignRef('access' , $access);
		$this->assignRef('print', $print);

		parent::display($tpl);
	}

	function _displayForm($tpl)
	{
		// Initialize variables
		$app		=& JFactory::getApplication();
		$document	=& JFactory::getDocument();
		$user		=& JFactory::getUser();
		$uri		=& JFactory::getURI();
		$params		= JComponentHelper::getParams('com_content');

		// Make sure you are logged in and have the necessary access rights
		if ($user->get('gid') < 19) {
			JResponse::setHeader('HTTP/1.0 403',true);
			JError::raiseWarning( 403, JText::_('ALERTNOTAUTH') );
			return;
		}

		// Initialize variables
		$article	=& $this->get('Article');
		$aparams	= $article->parameters;
		$isNew		= ($article->id < 1);

		$params->merge($aparams);

		// At some point in the future this will come from a request object
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');

		// Add the Calendar includes to the document <head> section
		JHtml::_('behavior.calendar');

		if ($isNew)
		{
			// TODO: Do we allow non-sectioned articles from the frontend??
			$article->sectionid = JRequest::getVar('sectionid', 0, '', 'int');
			$db = JFactory::getDBO();
			$db->setQuery('SELECT title FROM #__sections WHERE id = '.(int) $article->sectionid);
			$article->section = $db->loadResult();
		}

		// Get the lists
		$lists = $this->_buildEditLists();

		// Load the JEditor object
		$editor =& JFactory::getEditor();

		// Build the page title string
		$title = $article->id ? JText::_('Edit') : JText::_('New');

		// Set page title
		// because the application sets a default page title, we need to get it
		// right from the menu item itself
		// Get the menu item object
		$menus = &JSite::getMenu();
		$menu  = $menus->getActive();
		$params->set( 'page_title', $params->get( 'page_title' ) );
		if (is_object( $menu )) {
			$menu_params = new JParameter( $menu->params );
			if (!$menu_params->get( 'page_title')) {
				$params->set('page_title',	JText::_( 'Submit an Article' ));
			}
		} else {
			$params->set('page_title', JText::_( 'Submit an Article' ));
		}
		$document->setTitle( $params->get( 'page_title' ) );

		// get pathway
		$pathway =& $app->getPathway();
		$pathway->addItem($title, '');

		// Unify the introtext and fulltext fields and separated the fields by the {readmore} tag
		if (JString::strlen($article->fulltext) > 1) {
			$article->text = $article->introtext."<hr id=\"system-readmore\" />".$article->fulltext;
		} else {
			$article->text = $article->introtext;
		}

		// Ensure the row data is safe html
		JFilterOutput::objectHTMLSafe( $article);

		$this->assign('action', 	$uri->toString());

		$this->assignRef('article',	$article);
		$this->assignRef('params',	$params);
		$this->assignRef('lists',	$lists);
		$this->assignRef('editor',	$editor);
		$this->assignRef('user',	$user);


		parent::display($tpl);
	}

	function _buildEditLists()
	{
		// Get the article and database connector from the model
		$article = & $this->get('Article');
		$db 	 = & JFactory::getDBO();

		$javascript = "onchange=\"changeDynaList( 'catid', sectioncategories, document.adminForm.sectionid.options[document.adminForm.sectionid.selectedIndex].value, 0, 0);\"";

		$query = 'SELECT s.id, s.title' .
				' FROM #__sections AS s' .
				' ORDER BY s.ordering';
		$db->setQuery($query);

		$sections[] = JHtml::_('select.option', '-1', '- '.JText::_('Select Section').' -', 'id', 'title');
		$sections[] = JHtml::_('select.option', '0', JText::_('Uncategorized'), 'id', 'title');
		$sections = array_merge($sections, $db->loadObjectList());
		$lists['sectionid'] = JHtml::_('select.genericlist',  $sections, 'sectionid', 'class="inputbox" size="1" '.$javascript, 'id', 'title', intval($article->sectionid));

		foreach ($sections as $section)
		{
			$section_list[] = (int) $section->id;
			// get the type name - which is a special category
			if ($article->sectionid) {
				if ($section->id == $article->sectionid) {
					$contentSection = $section->title;
				}
			} else {
				if ($section->id == $article->sectionid) {
					$contentSection = $section->title;
				}
			}
		}

		$sectioncategories = array ();
		$sectioncategories[-1] = array ();
		$sectioncategories[-1][] = JHtml::_('select.option', '-1', JText::_( 'Select Category' ), 'id', 'title');
		$section_list = implode('\', \'', $section_list);

		$query = 'SELECT id, title, section' .
				' FROM #__categories' .
				' WHERE section IN ( \''.$section_list.'\' )' .
				' ORDER BY ordering';
		$db->setQuery($query);
		$cat_list = $db->loadObjectList();

		// Uncategorized category mapped to uncategorized section
		$uncat = new stdClass();
		$uncat->id = 0;
		$uncat->title = JText::_('Uncategorized');
		$uncat->section = 0;
		$cat_list[] = $uncat;
		foreach ($sections as $section)
		{
			$sectioncategories[$section->id] = array ();
			$rows2 = array ();
			foreach ($cat_list as $cat)
			{
				if ($cat->section == $section->id) {
					$rows2[] = $cat;
				}
			}
			foreach ($rows2 as $row2) {
				$sectioncategories[$section->id][] = JHtml::_('select.option', $row2->id, $row2->title, 'id', 'title');
			}
		}

		$categories = array();
		foreach ($cat_list as $cat) {
			if($cat->section == $article->sectionid)
				$categories[] = $cat;
		}

		$categories[] = JHtml::_('select.option', '-1', JText::_( 'Select Category' ), 'id', 'title');
		$lists['sectioncategories'] = $sectioncategories;
		$lists['catid'] = JHtml::_('select.genericlist',  $categories, 'catid', 'class="inputbox" size="1"', 'id', 'title', intval($article->catid));

		// Select List: Category Ordering
		$query = 'SELECT ordering AS value, title AS text FROM #__content WHERE catid = '.(int) $article->catid.' ORDER BY ordering';
		$lists['ordering'] = JHtml::_('list.specificordering', $article, $article->id, $query, 1);

		// Radio Buttons: Should the article be published
		$lists['state'] = JHtml::_('select.booleanlist', 'state', '', $article->state);

		// Radio Buttons: Should the article be added to the frontpage
		if($article->id) {
			$query = 'SELECT content_id FROM #__content_frontpage WHERE content_id = '. (int) $article->id;
			$db->setQuery($query);
			$article->frontpage = $db->loadResult();
		} else {
			$article->frontpage = 0;
		}

		$lists['frontpage'] = JHtml::_('select.booleanlist', 'frontpage', '', (boolean) $article->frontpage);

		// Select List: Group Access
		$lists['access'] = JHtml::_('list.accesslevel', $article);

		return $lists;
	}

	function _displayPagebreak($tpl)
	{
		$document =& JFactory::getDocument();
		$document->setTitle(JText::_('PGB ARTICLE PAGEBRK'));

		parent::display($tpl);
	}
}
?>
