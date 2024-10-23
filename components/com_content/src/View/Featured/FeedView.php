<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Site\View\Featured;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Document\Feed\FeedItem;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\AbstractView;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Component\Content\Site\Model\FeaturedModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Frontpage View class
 *
 * @since  1.5
 */
class FeedView extends AbstractView
{
    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        // Parameters
        $app       = Factory::getApplication();
        $params    = $app->getParams();
        $feedEmail = $app->get('feed_email', 'none');
        $siteEmail = $app->get('mailfrom');

        // If the feed has been disabled, we want to bail out here
        if ($params->get('show_feed_link', 1) == 0) {
            throw new \Exception(Text::_('JGLOBAL_RESOURCE_NOT_FOUND'), 404);
        }

        $this->getDocument()->link = Route::_('index.php?option=com_content&view=featured');

        // Get some data from the model
        $app->getInput()->set('limit', $app->get('feed_limit'));
        $categories = Categories::getInstance('Content');

        /** @var FeaturedModel $model */
        $model = $this->getModel();
        $rows  = $model->getItems();

        foreach ($rows as $row) {
            // Strip html from feed item title
            $title = htmlspecialchars($row->title, ENT_QUOTES, 'UTF-8');
            $title = html_entity_decode($title, ENT_COMPAT, 'UTF-8');

            // Compute the article slug
            $row->slug = $row->alias ? ($row->id . ':' . $row->alias) : $row->id;

            // URL link to article
            $link = RouteHelper::getArticleRoute($row->slug, $row->catid, $row->language);

            $description = '';
            $obj         = json_decode($row->images);

            // Ensure alt properties are set
            $obj->image_intro_alt    = $obj->image_intro_alt ?? '';
            $obj->image_fulltext_alt = $obj->image_fulltext_alt ?? '';

            // Set feed image to image_intro or if that's empty, to image_fulltext
            if (!empty($obj->image_intro)) {
                $description = '<p>' . HTMLHelper::_('image', $obj->image_intro, $obj->image_intro_alt) . '</p>';
            } elseif (!empty($obj->image_fulltext)) {
                $description = '<p>' . HTMLHelper::_('image', $obj->image_fulltext, $obj->image_fulltext_alt) . '</p>';
            }

            $description .= ($params->get('feed_summary', 0) ? $row->introtext . $row->fulltext : $row->introtext);
            $author      = $row->created_by_alias ?: $row->author;

            // Load individual item creator class
            $item           = new FeedItem();
            $item->title    = $title;
            $item->link     = Route::_($link);
            $item->date     = $row->publish_up;
            $item->category = [];

            // All featured articles are categorized as "Featured"
            $item->category[] = Text::_('JFEATURED');

            for ($item_category = $categories->get($row->catid); $item_category !== null; $item_category = $item_category->getParent()) {
                // Only add non-root categories
                if ($item_category->id > 1) {
                    $item->category[] = $item_category->title;
                }
            }

            $item->author = $author;

            if ($feedEmail === 'site') {
                $item->authorEmail = $siteEmail;
            } elseif ($feedEmail === 'author') {
                $item->authorEmail = $row->author_email;
            }

            // Add readmore link to description if introtext is shown, show_readmore is true and fulltext exists
            if (!$params->get('feed_summary', 0) && $params->get('feed_show_readmore', 0) && $row->fulltext) {
                $link = Route::_($link, true, $app->get('force_ssl') == 2 ? Route::TLS_FORCE : Route::TLS_IGNORE, true);
                $description .= '<p class="feed-readmore"><a target="_blank" href="' . $link . '" rel="noopener">'
                    . Text::_('COM_CONTENT_FEED_READMORE') . '</a></p>';
            }

            // Load item description and add div
            $item->description = '<div class="feed-description">' . $description . '</div>';

            // Loads item info into rss array
            $this->getDocument()->addItem($item);
        }
    }
}
