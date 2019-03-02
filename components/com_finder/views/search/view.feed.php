<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Search feed view class for the Finder package.
 *
 * @since  2.5
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
		$app->input->set('limit', $app->get('feed_limit'));

		// Get view data.
		$state = $this->get('State');
		$params = $state->get('params');
		$query = $this->get('Query');
		$results = $this->get('Results');

		// Push out the query data.
		JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
		$explained = JHtml::_('query.explained', $query);

		// Set the document title.
		$title = $params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		// Configure the document description.
		if (!empty($explained))
		{
			$this->document->setDescription(html_entity_decode(strip_tags($explained), ENT_QUOTES, 'UTF-8'));
		}

		// Set the document link.
		$this->document->link = JRoute::_($query->toUri());

		// If we don't have any results, we are done.
		if (empty($results))
		{
			return;
		}

		// Convert the results to feed entries.
		foreach ($results as $result)
		{
			// Convert the result to a feed entry.
			$item              = new JFeedItem;
			$item->title       = $result->title;
			$item->link        = JRoute::_($result->route);
			$item->description = $result->description;

			// Use Unix date to cope for non-english languages
			$item->date        = (int) $result->start_date ? JHtml::_('date', $result->start_date, 'U') : $result->indexdate;

			// Get the taxonomy data.
			$taxonomy = $result->getTaxonomy();

			// Add the category to the feed if available.
			if (isset($taxonomy['Category']))
			{
				$node           = array_pop($taxonomy['Category']);
				$item->category = $node->title;
			}

			// Loads item info into RSS array
			$this->document->addItem($item);
		}
	}
}
