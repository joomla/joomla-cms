<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Search feed view class for the Finder package.
 *
 * @package     Joomla.Site
 * @subpackage  com_finder
 * @since       2.5
 */
class FinderViewSearch extends JViewLegacy
{
	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  mixed  JError object on failure, void on success.
	 *
	 * @since   2.5
	 */
	public function display($tpl = null)
	{
		// Get the application
		$app = JFactory::getApplication();
		// Adjust the list limit to the feed limit.
		$app->input->set('limit', $app->getCfg('feed_limit'));

		// Get view data.
		$state = $this->get('State');
		$params = $state->get('params');
		$query = $this->get('Query');
		$results = $this->get('Results');

		// Push out the query data.
		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
		$suggested = JHtml::_('query.suggested', $query);
		$explained = JHtml::_('query.explained', $query);

		// Set the document title.
		$title = $params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}

		$this->document->setTitle($title);

		// Configure the document description.
		if (!empty($explained))
		{
			$this->document->setDescription(html_entity_decode(strip_tags($explained), ENT_QUOTES, 'UTF-8'));
		}

		// Set the document link.
		$this->document->link = JRoute::_($query->toURI());

		// Convert the results to feed entries.
		foreach ($results as $result)
		{
			// Convert the result to a feed entry.
			$item = new JFeedItem;
			$item->title = $result->title;
			$item->link = JRoute::_($result->route);
			$item->description = $result->description;
			$item->date = (int) $result->start_date ? JHtml::date($result->start_date, 'l d F Y') : $result->indexdate;

			// Get the taxonomy data.
			$taxonomy = $result->getTaxonomy();

			// Add the category to the feed if available.
			if (isset($taxonomy['Category']))
			{
				$node = array_pop($taxonomy['Category']);
				$item->category = $node->title;
			}

			// loads item info into rss array
			$this->document->addItem($item);
		}
	}
}
