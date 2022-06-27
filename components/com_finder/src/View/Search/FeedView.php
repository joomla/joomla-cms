<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Site\View\Search;

use Joomla\CMS\Document\Feed\FeedItem;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;

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
        $app = Factory::getApplication();

        // Adjust the list limit to the feed limit.
        $app->input->set('limit', $app->get('feed_limit'));

        // Get view data.
        $state = $this->get('State');
        $params = $state->get('params');
        $query = $this->get('Query');
        $results = $this->get('Items');
        $total = $this->get('Total');

        // Push out the query data.
        $explained = HTMLHelper::_('query.explained', $query);

        // Set the document title.
        $this->setDocumentTitle($params->get('page_title', ''));

        // Configure the document description.
        if (!empty($explained)) {
            $this->document->setDescription(html_entity_decode(strip_tags($explained), ENT_QUOTES, 'UTF-8'));
        }

        // Set the document link.
        $this->document->link = Route::_($query->toUri());

        // If we don't have any results, we are done.
        if (empty($results)) {
            return;
        }

        // Convert the results to feed entries.
        foreach ($results as $result) {
            // Convert the result to a feed entry.
            $item              = new FeedItem();
            $item->title       = $result->title;
            $item->link        = Route::_($result->route);
            $item->description = $result->description;

            // Use Unix date to cope for non-english languages
            $item->date        = (int) $result->start_date ? HTMLHelper::_('date', $result->start_date, 'U') : $result->indexdate;

            // Loads item info into RSS array
            $this->document->addItem($item);
        }
    }
}
