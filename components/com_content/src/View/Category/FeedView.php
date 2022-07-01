<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Site\View\Category;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\CategoryFeedView;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;

/**
 * HTML View class for the Content component
 *
 * @since  1.5
 */
class FeedView extends CategoryFeedView
{
    /**
     * @var    string  The name of the view to link individual items to
     *
     * @since  3.2
     */
    protected $viewName = 'article';

    /**
     * Method to reconcile non-standard names from components to usage in this class.
     * Typically overridden in the component feed view class.
     *
     * @param   object  $item  The item for a feed, an element of the $items array.
     *
     * @return  void
     *
     * @since   3.2
     */
    protected function reconcileNames($item)
    {
        // Get description, intro_image, author and date
        $app               = Factory::getApplication();
        $params            = $app->getParams();
        $item->description = '';
        $obj = json_decode($item->images);

        if (!empty($obj->image_intro)) {
            $item->description = '<p>' . HTMLHelper::_('image', $obj->image_intro, $obj->image_intro_alt) . '</p>';
        }

        $item->description .= ($params->get('feed_summary', 0) ? $item->introtext . $item->fulltext : $item->introtext);

        // Add readmore link to description if introtext is shown, show_readmore is true and fulltext exists
        if (!$item->params->get('feed_summary', 0) && $item->params->get('feed_show_readmore', 0) && $item->fulltext) {
            // Compute the article slug
            $item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;

            // URL link to article
            $link = Route::_(
                RouteHelper::getArticleRoute($item->slug, $item->catid, $item->language),
                true,
                $app->get('force_ssl') == 2 ? Route::TLS_FORCE : Route::TLS_IGNORE,
                true
            );

            $item->description .= '<p class="feed-readmore"><a target="_blank" href="' . $link . '" rel="noopener">'
                . Text::_('COM_CONTENT_FEED_READMORE') . '</a></p>';
        }

        $item->author = $item->created_by_alias ?: $item->author;
    }
}
