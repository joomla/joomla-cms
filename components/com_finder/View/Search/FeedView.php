<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Finder\Site\View\Search;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * Search feed view class for the Finder package.
 *
 * @since  2.5
 */
class FeedView extends BaseHtmlView
{
	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	public function display($tpl = null)
	{
		// Get the application
		$app = \JFactory::getApplication();
		$params = ComponentHelper::getParams('com_finder');

		// Adjust the list limit to the feed limit.
		$app->input->set('limit', $app->get('feed_limit'));

		// Prevent any output when OpenSearch Support is disabled
		if (!$params->get('opensearch', 1))
		{
			$app->close();
		}

		// Get view data.
		$state = $this->get('State');
		$params = $state->get('params');
		$query = $this->get('Query');
		$results = $this->get('Items');
		$total = $this->get('Total');

		// Push out the query data.
		\JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
		$explained = \JHtml::_('query.explained', $query);

		// Set the document title.
		$title = $params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = \JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = \JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		// Configure the document description.
		if (!empty($explained))
		{
			$this->document->setDescription(html_entity_decode(strip_tags($explained), ENT_QUOTES, 'UTF-8'));
		}

		// Set the document link.
		$this->document->link = \JRoute::_($query->toUri());

		// If we don't have any results, we are done.
		if (empty($results))
		{
			return;
		}

		// Convert the results to feed entries.
		foreach ($results as $result)
		{
			// Convert the result to a feed entry.
			$item              = new \JFeedItem;
			$item->title       = $result->title;
			$item->link        = \JRoute::_($result->route);
			$item->description = $result->description;
			$item->date        = date('r', strtotime($result->start_date));

			// Loads item info into RSS array
			$this->document->addItem($item);
		}
	}
}
