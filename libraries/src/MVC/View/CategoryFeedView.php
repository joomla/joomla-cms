<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\View;

use Joomla\CMS\Document\Document;
use Joomla\CMS\Document\FeedDocument;
use Joomla\CMS\Document\Feed\FeedItem;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\RouteHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\UCM\UCMType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base feed View class for a category
 *
 * @since  3.2
 */
class CategoryFeedView extends AbstractView
{
    /**
     * Method to set the document object
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @throws  \InvalidArgumentException
     */
    public function setDocument(Document $document): void
    {
        if (!$document instanceof FeedDocument) {
            throw new \InvalidArgumentException(sprintf('%s requires an instance of %s', static::class, FeedDocument::class));
        }

        parent::setDocument($document);
    }

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   3.2
     * @throws  \Exception
     */
    public function display($tpl = null)
    {
        $app      = Factory::getApplication();

        $extension      = $app->getInput()->getString('option');
        $contentType    = $extension . '.' . $this->viewName;

        $ucmType      = new UCMType();
        $ucmRow       = $ucmType->getTypeByAlias($contentType);
        $ucmMapCommon = json_decode($ucmRow->field_mappings)->common;
        $createdField = null;
        $titleField   = null;

        if (\is_object($ucmMapCommon)) {
            $createdField = $ucmMapCommon->core_created_time;
            $titleField   = $ucmMapCommon->core_title;
        } elseif (\is_array($ucmMapCommon)) {
            $createdField = $ucmMapCommon[0]->core_created_time;
            $titleField   = $ucmMapCommon[0]->core_title;
        }

        $this->getDocument()->link = Route::_(RouteHelper::getCategoryRoute($app->getInput()->getInt('id'), $language = 0, $extension));

        $app->getInput()->set('limit', $app->get('feed_limit'));
        $siteEmail        = $app->get('mailfrom');
        $fromName         = $app->get('fromname');
        $feedEmail        = $app->get('feed_email', 'none');
        $this->getDocument()->editor = $fromName;

        if ($feedEmail !== 'none') {
            $this->getDocument()->editorEmail = $siteEmail;
        }

        // Get some data from the model
        $items    = $this->get('Items');
        $category = $this->get('Category');
        $params   = $app->getParams();

        // If the feed has been disabled, we want to bail out here
        if ($params->get('show_feed_link', 1) == 0) {
            throw new \Exception(Text::_('JGLOBAL_RESOURCE_NOT_FOUND'), 404);
        }

        // Don't display feed if category id missing or non existent
        if ($category == false || $category->alias === 'root') {
            throw new \Exception(Text::_('JGLOBAL_CATEGORY_NOT_FOUND'), 404);
        }

        foreach ($items as $item) {
            $this->reconcileNames($item);

            // Strip html from feed item title
            if ($titleField) {
                $title = htmlspecialchars($item->$titleField, ENT_QUOTES, 'UTF-8');
                $title = html_entity_decode($title, ENT_QUOTES, 'UTF-8');
            } else {
                $title = '';
            }

            // URL link to article
            $router = new RouteHelper();
            $link   = Route::_($router->getRoute($item->id, $contentType, null, null, $item->catid));

            // Strip HTML from feed item description text.
            $description   = $item->description;
            $author        = $item->created_by_alias ?: $item->author;
            $categoryTitle = $item->category_title ?? $category->title;

            if ($createdField) {
                $date = isset($item->$createdField) ? date('r', strtotime($item->$createdField)) : '';
            } else {
                $date = '';
            }

            // Load individual item creator class.
            $feeditem              = new FeedItem();
            $feeditem->title       = $title;
            $feeditem->link        = $link;
            $feeditem->description = $description;
            $feeditem->date        = $date;
            $feeditem->category    = $categoryTitle;
            $feeditem->author      = $author;

            // We don't have the author email so we have to use site in both cases.
            if ($feedEmail === 'site') {
                $feeditem->authorEmail = $siteEmail;
            } elseif ($feedEmail === 'author') {
                $feeditem->authorEmail = $item->author_email;
            }

            // Loads item information into RSS array
            $this->getDocument()->addItem($feeditem);
        }
    }

    /**
     * Method to reconcile non standard names from components to usage in this class.
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
        if (!property_exists($item, 'title') && property_exists($item, 'name')) {
            $item->title = $item->name;
        }
    }
}
