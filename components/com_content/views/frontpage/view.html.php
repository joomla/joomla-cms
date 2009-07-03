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
 * Frontpage View class
 *
 * @package		Joomla.Site
 * @subpackage	com_content
 * @since		1.5
 */
class ContentViewFrontpage extends JView
{
	protected $state = null;
	protected $item = null;
	protected $items = null;
	protected $pagination = null;

	protected $lead_items = array();
	protected $intro_items = array();
	protected $link_items = array();
	protected $columns = 1;

	/**
	 * Display the view
	 *
	 * @return	mixed	False on error, null otherwise.
	 */
	function display($tpl = null)
	{
		// Initialize variables
		$user		= &JFactory::getUser();
		$app		= &JFactory::getApplication();

		$state		= $this->get('State');
		$items		= $this->get('Items');
		$pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}

		$params = &$state->params;

		// PREPARE THE DATA

		// Get the metrics for the structural page layout.
		$numLeading	= $params->def('num_leading_articles',	1);
		$numIntro	= $params->def('num_intro_articles',	4);
		$numLinks	= $params->def('num_links', 			4);

		// Compute the weblink slug and prepare description (runs content plugins).
		foreach ($items as $i => &$item)
		{
			$item->slug		= $item->alias ? ($item->id.':'.$item->alias) : $item->id;
			$item->catslug	= $item->category_route ? ($item->catid.':'.$item->category_route) : $item->catid;
			$item->event	= new stdClass();

			$dispatcher	= &JDispatcher::getInstance();

			// Ignore content plugins on links.
			if ($i < $numLeading + $numIntro)
			{
				$item->introtext = JHtml::_('content.prepare', $item->introtext);

				$results = $dispatcher->trigger('onAfterDisplayTitle', array (&$item, &$item->params, 0));
				$item->event->afterDisplayTitle = trim(implode("\n", $results));

				$results = $dispatcher->trigger('onBeforeDisplayContent', array (&$item, &$item->params, 0));
				$item->event->beforeDisplayContent = trim(implode("\n", $results));

				$results = $dispatcher->trigger('onAfterDisplayContent', array (&$item, &$item->params, 0));
				$item->event->afterDisplayContent = trim(implode("\n", $results));
			}
		}

		// Preprocess the breakdown of leading, intro and linked articles.
		// This makes it much easier for the designer to just interogate the arrays.
		$max	= count($items);

		// The first group is the leading articles.
		$limit	= $numLeading;
		for ($i = 0; $i < $limit &&$i < $max; $i++)
		{
			$this->lead_items[$i] = &$items[$i];
		}

		// The second group is the intro articles.
		$limit		= $numLeading + $numIntro;
		$this->columns	= max(1, $params->def('num_columns', 1));
		$order		= $params->def('multi_column_order', 1);

		if ($order == 1 || $this->columns == 1)
		{
			// Order articles across, then down (or single column mode)
			for ($i = $numLeading; $i < $limit &&$i < $max; $i++) {
				$this->intro_items[$i] = &$items[$i];
			}
		}
		else
		{
			// Order articles down, then across
			$k = $numLeading;

			// Pass over the second group by the number of columns
			for ($j = 0; $j < $this->columns; $j++)
			{
				for ($i = $numLeading + $j; $i < $limit &&$i < $max; $i += $this->columns, $k++) {
					$this->intro_items[$k] = &$items[$i];
				}
			}
		}

		// The remainder are the links.
		for ($i = $numLeading + $numIntro; $i < $max; $i++) {
			$this->link_items[$i] = &$items[$i];
		}

		$this->assignRef('params',		$params);
		$this->assignRef('items',		$items);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('user',		$user);

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app	= &JFactory::getApplication();
		$menus	= &JSite::getMenu();
		$title	= null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		if ($menu = $menus->getActive())
		{
			$menuParams = new JParameter($menu->params);
			$title = $menuParams->get('page_title');
		}
		if (empty($title)) {
			$title	= htmlspecialchars_decode($app->getCfg('sitename'));
		}
		$this->document->setTitle($title);

		// Add feed links
		if ($this->params->get('show_feed_link', 1))
		{
			$link = '&format=feed&limitstart=';

			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$this->document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);

			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$this->document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);
		}
	}
}
