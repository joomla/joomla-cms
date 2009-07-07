<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML Article View class for the Content component
 *
 * @package		Joomla.Site
 * @subpackage	com_content
 * @since		1.5
 */
class ContentViewArticle extends JView
{
	protected $state;
	protected $item;
	protected $print;

	function display($tpl = null)
	{
		// Initialise variables.
		$app		= &JFactory::getApplication();
		$user		= &JFactory::getUser();
		$dispatcher	= &JDispatcher::getInstance();

		// Get view related request variables.
		$print	= JRequest::getBool('print');

		// Get model data.
		$state	= $this->get('State');
		$item	= $this->get('Item');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

		// Add router helpers.
		$item->slug		= $item->alias ? ($item->id.':'.$item->alias) : $item->id;
		$item->catslug	= $item->category_alias ? ($item->catid.':'.$item->category_alias) : $item->catid;

		// TODO: Change based on shownoauth
		$item->rlink	= JRoute::_(ContentRoute::article($item->slug, $item->catslug));

		// Create a shortcut to the paramemters.
		$params	= &$state->params;
		$offset	= $state->get('page.offset');

		// If a guest user, they may be able to log in to view the full article
		// TODO: Does this satisfy the show not auth setting?
		if (!$item->params->get('access-view'))
		{
			if ($user->get('guest'))
			{
				// Redirect to login
				$uri		= JFactory::getURI();
				$app->redirect(
					'index.php?option=com_users&view=login&return='.base64_encode($uri),
					JText::_('Content_Error_Login_to_view_article')
				);
				return;
			}
			else {
				JError::raiseWarning(403, JText::_('Content_Error_Not_auth'));
				return;
			}
		}

		//
		// Process the content plugins.
		//
		JPluginHelper::importPlugin('content');
		//$results = $dispatcher->trigger('onPrepareContent', array (& $article, & $params, $limitstart));

		$item->text = JHtml::_('content.prepare', $item->introtext.$item->fulltext);

		$item->event = new stdClass();
		$results = $dispatcher->trigger('onAfterDisplayTitle', array(&$item, &$params, $offset));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onBeforeDisplayContent', array(&$item, &$params, $offset));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onAfterDisplayContent', array(&$item, &$params, $offset));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->assignRef('state',	$state);
		$this->assignRef('item',	$item);
		$this->assignRef('user',	$user);
		$this->assign('print',		$print);

		// Override the layout.
		if ($layout = $params->get('layout')) {
			$this->setLayout($layout);
		}

		// Increment the hit counter of the article.
		if (!$params->get('intro_only') && $offset == 0)
		{
			$model = &$this->getModel();
			$model->hit();
		}

		$this->_prepareDocument();
		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app		= &JFactory::getApplication();
		$pathway	= &$app->getPathway();
		$menus		= &JSite::getMenu();
		$title		= null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		if ($menu = $menus->getActive())
		{
			if (isset($menu->query['view']) && isset($menu->query['id']))
			{
				if ($menu->query['view'] == 'article' && $menu->query['id'] == $this->item->id)
				{
					$menuParams = new JParameter($menu->params);
					$title = $menuParams->get('page_title');
				}
			}
		}
		if (empty($title)) {
			$title	= $this->item->title;
		}
		$this->document->setTitle($title);

		if ($this->item->metadesc) {
			$this->document->setDescription($this->item->metadesc);
		}

		if ($this->item->metakey) {
			$this->document->setMetadata('keywords', $this->item->metakey);
		}

		if ($app->getCfg('MetaTitle') == '1') {
			$this->document->setMetaData('title', $this->item->title);
		}

		if ($app->getCfg('MetaAuthor') == '1') {
			$this->document->setMetaData('author', $this->item->author);
		}

		$mdata = $this->item->metadata->toArray();
		foreach ($mdata as $k => $v)
		{
			if ($v) {
				$this->document->setMetadata($k, $v);
			}
		}

		// If there is a pagebreak heading or title, add it to the page title
		if (!empty($this->item->page_title))
		{
			$article->title = $article->title .' - '. $article->page_title;
			$this->document->setTitle($article->page_title.' - '.JText::sprintf('Page %s', $this->state->get('page.offset') + 1));
		}

		//
		// Handle the breadcrumbs
		//
		if ($menu && $menu->query['view'] != 'article')
		{
			switch ($menu->query['view'])
			{
				case 'category':
					$pathway->addItem($this->item->title, '');
					break;
			}
		}

		if ($this->print) {
			$this->document->setMetaData('robots', 'noindex, nofollow');
		}
	}
}