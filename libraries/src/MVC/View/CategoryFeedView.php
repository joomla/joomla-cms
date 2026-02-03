<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\View;

use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Document\Feed\FeedItem;
use Joomla\CMS\Document\Feed\FeedEnclosure;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\RouteHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

\defined('_JEXEC') or die;

class CategoryFeedView extends AbstractView
{
    
    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $document = $this->getDocument();

        $extension = $app->getInput()->getString('option');
        $contentType = $extension . '.' . $this->viewName;

        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('ct') . '.*')
            ->from($db->quoteName('#__content_types', 'ct'))
            ->where($db->quoteName('ct.type_alias') . ' = :alias')
            ->bind(':alias', $contentType);

        $db->setQuery($query);
        $ucmRow = $db->loadObject();
        $ucmMapCommon = json_decode($ucmRow->field_mappings)->common;
        $createdField = null;
        $titleField = null;

        if (\is_object($ucmMapCommon)) {
            $createdField = $ucmMapCommon->core_created_time;
            $titleField   = $ucmMapCommon->core_title;
        } elseif (\is_array($ucmMapCommon)) {
            $createdField = $ucmMapCommon[0]->core_created_time;
            $titleField   = $ucmMapCommon[0]->core_title;
        }

        $document->link = Route::_(RouteHelper::getCategoryRoute($app->getInput()->getInt('id'), $language = 0, $extension));

        $app->getInput()->set('limit', $app->get('feed_limit'));
        $siteEmail = $app->get('mailfrom');
        $fromName = $app->get('fromname');
        $feedEmail = $app->get('feed_email', 'none');
        $document->editor = $fromName;

        if ($feedEmail !== 'none') {
            $document->editorEmail = $siteEmail;
        }

        // Get some data from the model
        $items = $this->get('Items');
        $category = $this->get('Category');
        $params = $app->getParams();
        $categories = Categories::getInstance('Content');
        $date_type = $params->get('date_feed_type', 1);

        // If the feed has been disabled, we want to bail out here
        if ($params->get('show_feed_link', 1) == 0) {
            throw new \Exception(Text::_('JGLOBAL_RESOURCE_NOT_FOUND'), 404);
        }

        // Don't display feed if category id missing or non existent
        if (!$category || $category->alias === 'root') {
            throw new \Exception(Text::_('JGLOBAL_CATEGORY_NOT_FOUND'), 404);
        }

        foreach ($items as $item) {
            $this->reconcileNames($item);
            $title = '';
            $title = htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8');
            $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');

            $router = new RouteHelper();
            $link = Route::_($router->getRoute($item->id, $contentType, null, null, $item->catid));
            $description = $item->description;
            $author = $item->created_by_alias ? : $item->author;

            $item->category = [];
            for ($item_category = $categories->get($item->catid); $item_category !== null; $item_category = $item_category->getParent())
                if ($item_category->id > 1 && $item_category->title != 'ROOT') $item->category[] = $item_category->title;

            $item->category[] = $siteName;
            $item->category = array_reverse($item->category);
            $item->category = implode(' / ', $item->category);
            $item->category = htmlspecialchars($item->category, ENT_QUOTES, 'UTF-8');

            $feeditem = new FeedItem();
            $feeditem->title = $title;
            $feeditem->link = $link;
            $feeditem->description = $description;
            $feeditem->category = $item->category;
            $feeditem->author = $author;

            switch ($date_type) {
                case 0: $feeditem->date = date('r', strtotime($item->created));
                        break;
                case 1: $feeditem->date = date('r', strtotime($item->publish_up));
                        break;
                case 2: $feeditem->date = date('r', strtotime($item->modified));
                        break;
                default: $feeditem->date = date('r', strtotime($item->created));
            }

            $images = json_decode($item->images, false);
            if (!empty($images->image_intro)) {
                if (preg_match('/^([^#]*)/', $images->image_intro, $matches)) $url_img = $matches[1];
                else $url_img = $images->image_intro;

                $lastDotPos = strrpos($url_img, '.');
                if ($lastDotPos !== false) {
                    $extension = substr($url_img, $lastDotPos + 1);
                    $extension = mb_strtolower($extension); 
                } else $extension = '-';

                // Use of Joomla FeedEnclosure class for items intro images
                $feedEnclosure = new FeedEnclosure();
                $feedEnclosure->url = Uri::root() . $url_img;
                $feedEnclosure->length = filesize($url_img);
                $feedEnclosure->type = 'image/' . $extension;
                $feeditem->enclosure = $feedEnclosure;
            }

            if ($feedEmail === 'site') $feeditem->authorEmail = $siteEmail;
            elseif ($feedEmail === 'author') $feeditem->authorEmail = $item->author_email;
            $document->addItem($feeditem);
        }
    }

    protected function reconcileNames($item)
    {
        if (!property_exists($item, 'title') && property_exists($item, 'name')) {
            $item->title = $item->name;
        }
    }
}
