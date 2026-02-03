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
use Joomla\CMS\Factory;
use Joomla\CMS\Document\Feed\FeedItem;
use Joomla\CMS\Document\Feed\FeedEnclosure;
use Joomla\CMS\Document\Feed\FeedView as BaseFeedView;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\AbstractView;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Component\Content\Site\Model\FeaturedModel;
use Joomla\CMS\Uri\Uri;


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
        $date_type = $params->get('date_feed_type', 1);

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
            $title = htmlspecialchars($row->title, ENT_QUOTES, 'UTF-8');
            $title = html_entity_decode($title, ENT_COMPAT, 'UTF-8'); 
            $row->slug = $row->alias ? ($row->id . ':' . $row->alias) : $row->id;

            $link = RouteHelper::getArticleRoute($row->slug, $row->catid, $row->language);
            $description = '';

            $description = ($params->get('feed_summary', 0) ? $row->introtext . $row->fulltext : $row->introtext);
            $author      = $row->created_by_alias ?: $row->author;
            
            $feedItem           = new FeedItem();
            $feedItem->title    = $title;
            $feedItem->link     = Route::_($link);

            // Global article configuration for date type
            switch ($date_type) {
                case 0:   $feedItem->date = $row->created;    break;
                case 1:   $feedItem->date = $row->publish_up; break;
                case 2:   $feedItem->date = $row->modified;   break;
                default:  $feedItem->date = $row->created;
            }

            $images = json_decode($row->images, false);
            if (!empty($images->image_intro)) {

                if (preg_match('/^([^#]*)/', $images->image_intro, $matches)) $url_img = $matches[1]; else $url_img = $images->image_intro;
                
                $lastDotPos = strrpos($url_img, '.');
                if ($lastDotPos !== false) { $extension = substr($url_img, $lastDotPos + 1); $extension = mb_strtolower($extension); } else $extension = '-';

                // Use of Joomla FeedEnclosure class for items intro images
                $feedEnclosure          = new FeedEnclosure();
                $feedEnclosure->url     = Uri::root().$url_img;
                $feedEnclosure->length  = filesize($url_img);
                $feedEnclosure->type    = 'image/'.$extension;
                $feedItem->enclosure    = $feedEnclosure;
            }
            
            $feedItem->category = [];
            for ($item_category = $categories->get($row->catid); $item_category !== null; $item_category = $item_category->getParent()) 
                if ($item_category->id > 1 && $item_category->title != 'ROOT') $feedItem->category[] = $item_category->title;

            $feedItem->category[] = Text::_('JFEATURED');
            $feedItem->category = array_reverse($feedItem->category);
            $feedItem->category = implode(' / ', $feedItem->category);
            
            $feedItem->author = $author;
            if ($feedEmail === 'site') { $feedItem->authorEmail = $siteEmail; } elseif ($feedEmail === 'author') { $feedItem->authorEmail = $row->author_email; }

            if (!$params->get('feed_summary', 0) && $params->get('feed_show_readmore', 0) && $row->fulltext) {
                $link = Route::_($link, true, $app->get('force_ssl') == 2 ? Route::TLS_FORCE : Route::TLS_IGNORE, true);
                $description .= '<p class="feed-readmore"><a target="_blank" href="' . $link . '" rel="noopener">' . Text::_('COM_CONTENT_FEED_READMORE') . '</a></p>';
            }

            $feedItem->description = '<div class="feed-description">' . $description . '</div>';
            $this->getDocument()->addItem($feedItem);
        }
    }
}
