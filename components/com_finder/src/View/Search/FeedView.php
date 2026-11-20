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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\Component\Finder\Site\Model\SearchModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

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
        $app->getInput()->set('limit', $app->get('feed_limit'));

        /** @var SearchModel $model */
        $model   = $this->getModel();
        $state   = $model->getState();
        $params  = $state->get('params');
        $query   = $model->getQuery();
        $results = $model->getItems();

        // If the feed has been disabled, we want to bail out here
        if ($params->get('show_feed_link', 1) == 0) {
            throw new \Exception(Text::_('JGLOBAL_RESOURCE_NOT_FOUND'), 404);
        }

        // Push out the query data.
        $explained = HTMLHelper::_('query.explained', $query);

        // Set the document title.
        $this->setDocumentTitle($params->get('page_title', ''));

        // Configure the document description.
        if (!empty($explained)) {
            $this->getDocument()->setDescription(html_entity_decode(strip_tags($explained), ENT_QUOTES, 'UTF-8'));
        }

        // Set the document link.
        $this->getDocument()->link = Route::_($query->toUri());

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
            $this->getDocument()->addItem($item);
        }
    }
}
