<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Tags\Site\View\Tags;

use Joomla\CMS\Document\Feed\FeedItem;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTML View class for the Tags component all tags view
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
     * @return  mixed  A string if successful, otherwise an Error object.
     */
    public function display($tpl = null)
    {
        $app                  = Factory::getApplication();
        $this->document->link = Route::_('index.php?option=com_tags&view=tags');

        $app->input->set('limit', $app->get('feed_limit'));
        $siteEmail = $app->get('mailfrom');
        $fromName  = $app->get('fromname');
        $feedEmail = $app->get('feed_email', 'none');

        $this->document->editor = $fromName;

        if ($feedEmail !== 'none') {
            $this->document->editorEmail = $siteEmail;
        }

        // Get some data from the model
        $items = $this->get('Items');

        foreach ($items as $item) {
            // Strip HTML from feed item title
            $title = $this->escape($item->title);
            $title = html_entity_decode($title, ENT_COMPAT, 'UTF-8');

            // Strip HTML from feed item description text
            $description = $item->description;
            $author      = $item->created_by_alias ?: $item->created_by_user_name;
            $date        = $item->created_time ? date('r', strtotime($item->created_time)) : '';

            // Load individual item creator class
            $feeditem = new FeedItem();
            $feeditem->title       = $title;
            $feeditem->link        = '/index.php?option=com_tags&view=tag&id=' . (int) $item->id;
            $feeditem->description = $description;
            $feeditem->date        = $date;
            $feeditem->category    = 'All Tags';
            $feeditem->author      = $author;

            if ($feedEmail === 'site') {
                $feeditem->authorEmail = $siteEmail;
            }

            if ($feedEmail === 'author') {
                $feeditem->authorEmail = $item->email;
            }

            // Loads item info into RSS array
            $this->document->addItem($feeditem);
        }
    }
}
