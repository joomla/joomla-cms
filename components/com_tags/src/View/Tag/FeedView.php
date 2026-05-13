<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Tags\Site\View\Tag;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Document\Feed\FeedItem;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\Component\Tags\Site\Model\TagModel;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTML View class for the Tags component
 *
 * @since  3.1
 */
class FeedView extends BaseHtmlView
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
        $app    = Factory::getApplication();
        $ids    = (array) $app->getInput()->get('id', [], 'int');
        $i      = 0;
        $tagIds = '';
        $params = $app->getParams();

        // If the feed has been disabled, we want to bail out here
        if ($params->get('show_feed_link', 1) == 0) {
            throw new \Exception(Text::_('JGLOBAL_RESOURCE_NOT_FOUND'), 404);
        }

        // Remove zero values resulting from input filter
        $ids = array_filter($ids);

        foreach ($ids as $id) {
            if ($i !== 0) {
                $tagIds .= '&';
            }

            $tagIds .= 'id[' . $i . ']=' . $id;

            $i++;
        }

        $this->getDocument()->link = Route::_('index.php?option=com_tags&view=tag&' . $tagIds);

        $app->getInput()->set('limit', $app->get('feed_limit'));
        $siteEmail = $app->get('mailfrom');
        $fromName  = $app->get('fromname');
        $feedEmail = $app->get('feed_email', 'none');

        $this->getDocument()->editor = $fromName;

        if ($feedEmail !== 'none') {
            $this->getDocument()->editorEmail = $siteEmail;
        }

        /** @var TagModel $model */
        $model = $this->getModel();
        $items = $model->getItems();

        if ($items !== false) {
            // Pre-fetch fulltext and attribs for all com_content.article items in one query
            $articleIds = [];

            foreach ($items as $item) {
                if ($item->type_alias === 'com_content.article') {
                    $articleIds[] = (int) $item->content_item_id;
                }
            }

            $articleData = [];

            if ($articleIds) {
                $db           = Factory::getContainer()->get(DatabaseInterface::class);
                $articleQuery = $db->getQuery(true)
                    ->select([$db->quoteName('id'), $db->quoteName('fulltext'), $db->quoteName('attribs')])
                    ->from($db->quoteName('#__content'))
                    ->whereIn($db->quoteName('id'), $articleIds);
                $db->setQuery($articleQuery);
                $articleData = $db->loadObjectList('id');
            }

            $contentParams = ComponentHelper::getParams('com_content');
            $feedSummary   = $contentParams->get('feed_summary', 0);

            foreach ($items as $item) {
                // Strip HTML from feed item title
                $title = $this->escape($item->core_title);
                $title = html_entity_decode($title, ENT_COMPAT, 'UTF-8');

                // Strip HTML from feed item description text
                $description = $item->core_body;
                $author      = $item->core_created_by_alias ?: $item->author;
                $date        = ($item->displayDate ? date('r', strtotime($item->displayDate)) : '');

                if ($item->type_alias === 'com_content.article' && isset($articleData[$item->content_item_id])) {
                    $row           = $articleData[$item->content_item_id];
                    $articleParams = new Registry($row->attribs);
                    $showIntro     = $articleParams->get('show_intro', $contentParams->get('show_intro', 1));
                    $description   = ($showIntro ? $item->core_body : '') . ($feedSummary ? $row->fulltext : '');
                }

                // Load individual item creator class
                $feeditem              = new FeedItem();
                $feeditem->title       = $title;
                $feeditem->link        = Route::_($item->link);
                $feeditem->description = $description;
                $feeditem->date        = $date;
                $feeditem->category    = $item->core_category_title;
                $feeditem->author      = $author;

                if ($feedEmail === 'site') {
                    $item->authorEmail = $siteEmail;
                } elseif ($feedEmail === 'author') {
                    $item->authorEmail = $item->author_email;
                }

                // Loads item info into RSS array
                $this->getDocument()->addItem($feeditem);
            }
        }
    }
}
