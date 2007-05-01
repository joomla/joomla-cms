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

jimport( 'joomla.application.component.view');

/**
 * HTML Article View class for the Content component
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class ContentViewArticle extends JView
{
	function display($tpl = null)
	{
		global $mainframe;

		$user		=& JFactory::getUser();
		$document	=& JFactory::getDocument();
		$dispatcher	=& JEventDispatcher::getInstance();
		$pathway		=& $mainframe->getPathWay();
		$contentConfig = &JComponentHelper::getParams( 'com_content' );

		// Initialize variables
		$article	=& $this->get('Article');
		$params		=& $article->parameters;

		// Get the menu item object
		$menus	= &JMenu::getInstance();
		$menu	= &$menus->getActive();

		// Get the page/component configuration
		$state  = &$this->get('State');
		$pparams = &$state->get('parameters.menu');
		if (!is_object($pparams)) {
			$pparams = &JComponentHelper::getParams('com_content');
		}

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
			$id = JRequest::getVar( 'id' );
			return JError::raiseError( 404, JText::sprintf( 'Article # not found', $id ) );
		}

		$linkOn		= null;
		$linkText	= null;

		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');

		//set breadcrumbs
		if($menu->query['view'] != 'article')
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

		// Handle Page Title
		$document->setTitle($article->title);

		// Handle metadata
		$document->setDescription( $article->metadesc );
		$document->setMetadata('keywords', $article->metakey);

		$mdata = new JParameter($article->metadata);
		$mdata = $mdata->toArray();
		foreach ($mdata as $k => $v) {
			if ($v) {
				$document->setMetadata($k, $v);
			}
		}

		// If there is a pagebreak heading or title, add it to the page title
		if (isset ($article->page_title)) {
			$document->setTitle($article->title.' '.$article->page_title);
		}

		// Create a user access object for the current user
		$access = new stdClass();
		$access->canEdit		= $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn	= $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish	= $user->authorize('action', 'publish', 'content', 'all');

		// Process the content plugins
		JPluginHelper::importPlugin('content');
		$results = $dispatcher->trigger('onPrepareContent', array (& $article, & $params, $limitstart));

		if ($params->get('show_readmore') || $params->get('link_titles'))
		{
			if ($params->get('show_intro'))
			{
				// Check to see if the user has access to view the full article
				if ($article->access <= $user->get('aid', 0))
				{
					$linkOn = JRoute::_("index.php?option=com_content&view=article&id=".$article->slug);

					if (@$article->readmore) {
						// text for the readmore link
						$linkText = JText::_('Read more...');
					}
				}
				else
				{
					$linkOn = JRoute::_("index.php?option=com_user&task=register");


					if (@$article->readmore) {
						// text for the readmore link if accessible only if registered
						$linkText = JText::_('Register to read more...');
					}
				}
			}
		}
		$article->mod_date = '';
		if (intval($article->modified) != 0) {
			$article->mod_date = JHTML::Date($article->modified);
		}
		if (intval($article->created) != 0) {
			$article->created = JHTML::Date($article->created);
		}

		$article->readmore_link = $linkOn;
		$article->readmore_text = $linkText;

		$article->event = new stdClass();
		$results = $dispatcher->trigger('onAfterDisplayTitle', array ($article, &$params, $limitstart));
		$article->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onBeforeDisplayContent', array (& $article, & $params, $limitstart));
		$article->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onAfterDisplayContent', array (& $article, & $params, $limitstart));
		$article->event->afterDisplayContent = trim(implode("\n", $results));

		$print = JRequest::getVar('print', '0');

		$this->assignRef('article', $article);
		$this->assignRef('params' , $params);
		$this->assignRef('user'   , $user);
		$this->assignRef('access' , $access);
		$this->assignRef('print', $print);

		parent::display($tpl);
	}

	function _displayForm($tpl)
	{
		global $mainframe, $Itemid;

		// Initialize variables
		$document	=& JFactory::getDocument();
		$user		=& JFactory::getUser();

		// Make sure you are logged in and have the necessary access rights
		if ($user->get('gid') < 19) {
			JError::raiseError( 403, JText::_('ALERTNOTAUTH') );
			return;
		}

		// Initialize variables
		$article	=& $this->get('Article');
		$params		=& $article->parameters;
		$isNew		= ($article->id < 1);

		// At some point in the future this will come from a request object
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');
		$returnid	= JRequest::getVar('Returnid', $Itemid, '', 'int');

		// Add the Calendar includes to the document <head> section
		JCommonHTML::loadCalendar();

		if ($isNew)
		{
			// TODO: Do we allow non-sectioned articles from the frontend??
			$article->sectionid = JRequest::getVar('sectionid', 0, '', 'int');
			$db = JFactory::getDBO();
			$db->setQuery('SELECT title FROM #__sections WHERE id = '.$article->sectionid);
			$article->section = $db->loadResult();
		}

		// Get the lists
		$lists = $this->_buildEditLists();

		// Load the JEditor object
		$editor =& JFactory::getEditor();

		// Ensure the row data is safe html
		jimport('joomla.filter.output');
		JOutputFilter::objectHTMLSafe( $article);

		// Build the page title string
		$title = $article->id ? JText::_('Edit') : JText::_('New');

		// Set page title
		$document->setTitle($title);

		// get pathway
		$pathway =& $mainframe->getPathWay();
		$pathway->addItem($title, '');

		// Unify the introtext and fulltext fields and separated the fields by the {readmore} tag
		if (JString::strlen($article->fulltext) > 1) {
			$article->text = $article->introtext."<hr id=\"system-readmore\" />".$article->fulltext;
		} else {
			$article->text = $article->introtext;
		}

		$this->set('returnid',	$returnid);
		$this->set('article',	$article);
		$this->set('params',	$params);
		$this->set('lists',		$lists);
		$this->set('editor',	$editor);
		$this->set('user',		$user);

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

		$sections[] = JHTMLSelect::option('-1', '- '.JText::_('Select Section').' -', 'id', 'title');
		$sections[] = JHTMLSelect::option('0', JText::_('Uncategorized'), 'id', 'title');
		$sections = array_merge($sections, $db->loadObjectList());
		$lists['sectionid'] = JHTMLSelect::genericList($sections, 'sectionid', 'class="inputbox" size="1" '.$javascript, 'id', 'title', intval($article->sectionid));

		foreach ($sections as $section)
		{
			$section_list[] = $section->id;
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
		$sectioncategories[-1][] = JHTMLSelect::option('-1', JText::_( 'Select Category' ), 'id', 'title');
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
				$sectioncategories[$section->id][] = JHTMLSelect::option($row2->id, $row2->title, 'id', 'title');
			}
		}

		$categories = array();
		foreach ($cat_list as $cat) {
			if($cat->section == $article->sectionid)
				$categories[] = $cat;
		}

		$categories[] = JHTMLSelect::option('-1', JText::_( 'Select Category' ), 'id', 'title');
		$lists['sectioncategories'] = $sectioncategories;
		$lists['catid'] = JHTMLSelect::genericList($categories, 'catid', 'class="inputbox" size="1"', 'id', 'title', intval($article->catid));

		// Select List: Category Ordering
		$query = 'SELECT ordering AS value, title AS text FROM #__content WHERE catid = '.$article->catid.' ORDER BY ordering';
		require_once(JPATH_ADMINISTRATOR.DS.'includes'.DS.'helper.php');
		$lists['ordering'] = JAdministratorHelper::SpecificOrdering($article, $article->id, $query, 1);

		// Radio Buttons: Should the article be published
		$lists['state'] = JHTMLSelect::yesnoList('state', '', $article->state);

		// Radio Buttons: Should the article be added to the frontpage
		if($article->id) {
			$query = 'SELECT content_id FROM #__content_frontpage WHERE content_id = '. $article->id;
			$db->setQuery($query);
			$article->frontpage = $db->loadResult();
		} else {
			$article->frontpage = 0;
		}

		$lists['frontpage'] = JHTMLSelect::yesnoList('frontpage', '', (boolean) $article->frontpage);

		// Select List: Group Access
		require_once(JPATH_ADMINISTRATOR.DS.'includes'.DS.'helper.php');
		$lists['access'] = JAdministratorHelper::Access($article);

		return $lists;
	}

	function _displayPagebreak($tpl)
	{
		global $mainframe;
		$mainframe->setPageTitle(JText::_('PGB ARTICLE PAGEBRK'));

		parent::display($tpl);
	}
}
?>
