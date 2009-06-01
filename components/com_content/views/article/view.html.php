<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

require_once (JPATH_COMPONENT.DS.'view.php');

/**
 * HTML Article View class for the Content component
 *
 * @package		Joomla.Site
 * @subpackage	Content
 * @since 1.5
 */
class ContentViewArticle extends ContentView
{
	function display($tpl = null)
	{
		global $mainframe;

		$user		= &JFactory::getUser();
		$groups		= $user->authorisedLevels();
		$document	= &JFactory::getDocument();
		$dispatcher	= &JDispatcher::getInstance();
		$pathway	= &$mainframe->getPathway();
		$params		= &$mainframe->getParams('com_content');

		// Initialize variables
		$article	= &$this->get('Article');
		$aparams		= &$article->parameters;
		$params->merge($aparams);

		if ($this->getLayout() == 'pagebreak') {
			$this->_displayPagebreak($tpl);
			return;
		}

		if ($this->getLayout() == 'form') {
			$this->_displayForm($tpl);
			return;
		}

		if (($article->id == 0))
		{
			$id = JRequest::getVar('id', '', 'default', 'int');
			return JError::raiseError(404, JText::sprintf('Article # not found', $id));
		}

		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');

		if (!$params->get('intro_only') && ($this->getLayout() == 'default') && ($limitstart == 0))
		{
			$model = &$this->getModel();
			$model->hit();
		}

		// Create a user access object for the current user
		$access = new stdClass();
		$access->canEdit	= $user->authorise('com_content.article.edit_article');
		$access->canEditOwn	= $user->authorise('com_content.article.edit_own') && $user->get('id') == $article->created_by;
		$access->canPublish	= $user->authorise('com_content.article.publish');
		$access->canManage	= $user->authorise('com_content.manage');

		// Check to see if the user has access to view the full article
		if (in_array($article->access, $groups)) {
			$article->readmore_link = JRoute::_(ContentHelperRoute::getArticleRoute($article->slug, $article->catslug, $article->sectionid));;
		}
		else if ($user->get('guest'))
		{
			// Redirect to login
			$uri		= JFactory::getURI();
			$return		= $uri->toString();

			$url  = 'index.php?option=com_users&view=login';
			$url .= '&return='.base64_encode($return);;

			//$url	= JRoute::_($url, false);
			$mainframe->redirect($url, JText::_('You must login first'));
		}
		else {
			JError::raiseWarning(403, JText::_('ALERTNOTAUTH'));
			return;
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

		if (is_object($menu) && isset($menu->query['view']) && $menu->query['view'] == 'article' && isset($menu->query['id']) && $menu->query['id'] == $article->id) {
			$menu_params = new JParameter($menu->params);
			if (!$menu_params->get('page_title')) {
				$params->set('page_title',	$article->title);
			}
		} else {
			$params->set('page_title',	$article->title);
		}
		$document->setTitle($params->get('page_title'));

		if ($article->metadesc) {
			$document->setDescription($article->metadesc);
		}
		if ($article->metakey) {
			$document->setMetadata('keywords', $article->metakey);
		}

		if ($mainframe->getCfg('MetaTitle') == '1') {
			$document->setMetaData('title', $article->title);
		}
		if ($mainframe->getCfg('MetaAuthor') == '1') {
			$document->setMetaData('author', $article->author);
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
		if ($menu && $menu->query['view'] != 'article')
		{
			switch ($menu->query['view'])
			{
				case 'section':
					$pathway->addItem($article->category, 'index.php?view=category&id='.$article->catslug);
					$pathway->addItem($article->title, '');
					break;
				case 'category':
					$pathway->addItem($article->title, '');
					break;
			}
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
		if ($print) {
      $document->setMetaData('robots', 'noindex, nofollow');
    }

		$this->assignRef('article', $article);
		$this->assignRef('params' , $params);
		$this->assignRef('user'   , $user);
		$this->assignRef('access' , $access);
		$this->assignRef('print', $print);

		parent::display($tpl);
	}

	function _displayForm($tpl)
	{
		global $mainframe;

		// Initialize variables
		$document	= &JFactory::getDocument();
		$user		= &JFactory::getUser();
		$uri		= &JFactory::getURI();
		$params		= &$mainframe->getParams('com_content');

		// Initialize variables
		$article	= &$this->get('Article');
		$aparams	= &$article->parameters;
		$isNew		= ($article->id < 1);

		// Create a user access object for the user
		$access = new stdClass();
		$access->canEdit	= $user->authorise('com_content.article.edit_article');
		$access->canEditOwn	= $user->authorise('com_content.article.edit_own') && ($article->id == 0 || $user->get('id') == $article->created_by);
		$access->canPublish	= $user->authorise('com_content.article.publish');
		$access->canManage	= $user->authorise('com_content.manage');

		// Check the user's access to edit the article.
		if (!$access->canEdit && !$access->canEditOwn && !$access->canPublish && !$access->canManage) {
			JError::raiseError(403, JText::_('ALERTNOTAUTH'));
			return false;
		}

		$params->merge($aparams);

		// At some point in the future this will come from a request object
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');

		// Add the Calendar includes to the document <head> section
		JHtml::_('behavior.calendar');

		if ($isNew)
		{
			// TODO: Do we allow non-sectioned articles from the frontend??
			$article->sectionid = JRequest::getVar('sectionid', 0, '', 'int');
			$db = JFactory::getDbo();
			$db->setQuery('SELECT title FROM #__sections WHERE id = '.(int) $article->sectionid);
			$article->section = $db->loadResult();
		}

		// Get the lists
		$lists = $this->_buildEditLists();

		// Load the JEditor object
		$editor = &JFactory::getEditor();

		// Build the page title string
		$title = $article->id ? JText::_('Edit') : JText::_('New');

		// Set page title
		// because the application sets a default page title, we need to get it
		// right from the menu item itself
		// Get the menu item object
		$menus = &JSite::getMenu();
		$menu  = $menus->getActive();
		$params->set('page_title', $params->get('page_title'));
		if (is_object($menu)) {
			$menu_params = new JParameter($menu->params);
			if (!$menu_params->get('page_title')) {
				$params->set('page_title',	JText::_('Submit an Article'));
			}
		} else {
			$params->set('page_title', JText::_('Submit an Article'));
		}
		$document->setTitle($params->get('page_title'));

		// get pathway
		$pathway = &$mainframe->getPathWay();
		$pathway->addItem($title, '');

		// Unify the introtext and fulltext fields and separated the fields by the {readmore} tag
		if (JString::strlen($article->fulltext) > 1) {
			$article->text = $article->introtext."<hr id=\"system-readmore\" />".$article->fulltext;
		} else {
			$article->text = $article->introtext;
		}

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
		$db 	 = & JFactory::getDbo();

		$javascript = "onchange=\"changeDynaList('catid', sectioncategories, document.adminForm.sectionid.options[document.adminForm.sectionid.selectedIndex].value, 0, 0);\"";

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
		$sectioncategories[-1][] = JHtml::_('select.option', '-1', JText::_('Select Category'), 'id', 'title');
		$section_list = implode('\', \'', $section_list);

		$query = 'SELECT id, title, section' .
				' FROM #__categories' .
				' WHERE section IN (\''.$section_list.'\')' .
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
			if ($cat->section == $article->sectionid)
				$categories[] = $cat;
		}

		$categories[] = JHtml::_('select.option', '-1', JText::_('Select Category'), 'id', 'title');
		$lists['sectioncategories'] = $sectioncategories;
		$lists['catid'] = JHtml::_('select.genericlist',  $categories, 'catid', 'class="inputbox" size="1"', 'id', 'title', intval($article->catid));

		// Select List: Category Ordering
		$query = 'SELECT ordering AS value, title AS text FROM #__content WHERE catid = '.(int) $article->catid.' AND state > ' .(int) "-1" . ' ORDER BY ordering';
		$lists['ordering'] = JHtml::_('list.specificordering', $article, $article->id, $query, 1);

		// Radio Buttons: Should the article be published
		$lists['state'] = JHtml::_('select.booleanlist', 'state', '', $article->state);

		// Radio Buttons: Should the article be added to the frontpage
		if ($article->id) {
			$query = 'SELECT content_id FROM #__content_frontpage WHERE content_id = '. (int) $article->id;
			$db->setQuery($query);
			$article->frontpage = $db->loadResult();
		} else {
			$article->frontpage = 0;
		}

		$lists['frontpage'] = JHtml::_('select.booleanlist', 'frontpage', '', (boolean) $article->frontpage);

		// Select List: Group Access
		$lists['access'] = JHtml::_('access.assetgroups', 'access', $article->access);;

		return $lists;
	}

	function _displayPagebreak($tpl)
	{
		$document = &JFactory::getDocument();
		$document->setTitle(JText::_('PGB ARTICLE PAGEBRK'));

		parent::display($tpl);
	}
}
?>
