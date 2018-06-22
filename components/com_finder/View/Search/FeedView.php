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

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Document\Feed\FeedItem;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

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

		// Adjust the list limit to the feed limit.
		$app->input->set('limit', $app->get('feed_limit'));

		// Get view data.
		$state = $this->get('State');
		$params = $state->get('params');
		$query = $this->get('Query');
		$results = $this->get('Results');

		// Push out the query data.
		HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
		$explained = HTMLHelper::_('query.explained', $query);

		// Set the document title.
		$title = $params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		// Configure the document description.
		if (!empty($explained))
		{
			$this->document->setDescription(html_entity_decode(strip_tags($explained), ENT_QUOTES, 'UTF-8'));
		}

		// Set the document link.
		$this->document->link = Route::_($query->toUri());

		// If we don't have any results, we are done.
		if (empty($results))
		{
			return;
		}

		// Convert the results to feed entries.
		foreach ($results as $result)
		{
			// Convert the result to a feed entry.
			$item              = new FeedItem;
			$item->title       = $result->title;
			$item->link        = Route::_($result->route);
			$item->description = $result->description;
			$item->date        = (int) $result->start_date ? HTMLHelper::_('date', $result->start_date, 'l d F Y') : $result->indexdate;

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
