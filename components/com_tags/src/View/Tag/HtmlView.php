<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Tags\Site\View\Tag;

use Joomla\CMS\Factory;
use Joomla\CMS\Menu\MenuItem;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\User\User;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTML View class for the Tags component
 *
 * @since  3.1
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The model state
     *
     * @var    CMSObject
     *
     * @since  3.1
     */
    protected $state;

    /**
     * List of items associated with the tag
     *
     * @var    \stdClass[]|false
     *
     * @since  3.1
     */
    protected $items;

    /**
     * Tag data for the current tag or tags (on success, false on failure)
     *
     * @var    CMSObject[]|boolean
     *
     * @since  3.1
     */
    protected $item;

    /**
     * UNUSED
     *
     * @var    null
     *
     * @since  3.1
     */
    protected $children;

    /**
     * UNUSED
     *
     * @var    null
     *
     * @since  3.1
     */
    protected $parent;

    /**
     * The pagination object
     *
     * @var    \Joomla\CMS\Pagination\Pagination
     *
     * @since  3.1
     */
    protected $pagination;

    /**
     * The page parameters
     *
     * @var    Registry
     *
     * @since  3.1
     */
    protected $params;

    /**
     * Array of tags title
     *
     * @var    array
     *
     * @since  3.1
     */
    protected $tags_title;

    /**
     * The page class suffix
     *
     * @var    string
     *
     * @since  4.0.0
     */
    protected $pageclass_sfx = '';

    /**
     * The logged in user
     *
     * @var    User
     *
     * @since  4.0.0
     */
    protected $user;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   3.1
     */
    public function display($tpl = null)
    {
        $app    = Factory::getApplication();

        // Get some data from the models
        $this->state      = $this->get('State');
        $this->items      = $this->get('Items');
        $this->item       = $this->get('Item');
        $this->children   = $this->get('Children');
        $this->parent     = $this->get('Parent');
        $this->pagination = $this->get('Pagination');
        $this->user       = $this->getCurrentUser();

        // Flag indicates to not add limitstart=0 to URL
        $this->pagination->hideEmptyLimitstart = true;

        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->params = $this->state->get('params');
        /** @var MenuItem $active */
        $active       = $app->getMenu()->getActive();
        $query        = $active->query;

        // Merge tag params. If this is single-tag view, menu params override tag params
        // Otherwise, article params override menu item params
        foreach ($this->item as $itemElement) {
            // Prepare the data.
            $temp = new Registry($itemElement->params);

            // If the current view is the active item and a tag view for at least this tag, then the menu item params take priority
            if ($query['option'] == 'com_tags' && $query['view'] == 'tag' && in_array($itemElement->id, $query['id'])) {
                // Merge so that the menu item params take priority
                $itemElement->params = $temp;
                $itemElement->params->merge($this->params);

                // Load layout from active query (in case it is an alternative menu item)
                if (isset($active->query['layout'])) {
                    $this->setLayout($active->query['layout']);
                }
            } else {
                $itemElement->params   = clone $this->params;
                $itemElement->params->merge($temp);

                // Check for alternative layouts (since we are not in a single-tag menu item)
                if ($layout = $itemElement->params->get('tag_layout')) {
                    $this->setLayout($layout);
                }
            }

            $itemElement->metadata = new Registry($itemElement->metadata);
        }

        PluginHelper::importPlugin('content');

        foreach ($this->items as $itemElement) {
            $itemElement->event = new \stdClass();

            // For some plugins.
            $itemElement->text = !empty($itemElement->core_body) ? $itemElement->core_body : '';

            $itemElement->core_params = new Registry($itemElement->core_params);

            $app->triggerEvent('onContentPrepare', ['com_tags.tag', &$itemElement, &$itemElement->core_params, 0]);

            $results = $app->triggerEvent(
                'onContentAfterTitle',
                ['com_tags.tag', &$itemElement, &$itemElement->core_params, 0]
            );
            $itemElement->event->afterDisplayTitle = trim(implode("\n", $results));

            $results = $app->triggerEvent(
                'onContentBeforeDisplay',
                ['com_tags.tag', &$itemElement, &$itemElement->core_params, 0]
            );
            $itemElement->event->beforeDisplayContent = trim(implode("\n", $results));

            $results = $app->triggerEvent(
                'onContentAfterDisplay',
                ['com_tags.tag', &$itemElement, &$itemElement->core_params, 0]
            );
            $itemElement->event->afterDisplayContent = trim(implode("\n", $results));

            // Write the results back into the body
            if (!empty($itemElement->core_body)) {
                $itemElement->core_body = $itemElement->text;
            }

            // Categories store the images differently so lets re-map it so the display is correct
            if ($itemElement->type_alias === 'com_content.category') {
                $itemElement->core_images = json_encode(
                    [
                        'image_intro'     => $itemElement->core_params->get('image', ''),
                        'image_intro_alt' => $itemElement->core_params->get('image_alt', ''),
                    ]
                );
            }
        }

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx', ''));

        $this->_prepareDocument();

        parent::display($tpl);
    }

    /**
     * Prepares the document.
     *
     * @return  void
     */
    protected function _prepareDocument()
    {
        $app              = Factory::getApplication();
        $menu             = $app->getMenu()->getActive();
        $this->tags_title = $this->getTagsTitle();
        $pathway          = $app->getPathway();
        $title            = '';

        // Highest priority for "Browser Page Title".
        if ($menu) {
            $title = $menu->getParams()->get('page_title', '');
        }

        if ($this->tags_title) {
            $this->params->def('page_heading', $this->tags_title);
            $title = $title ?: $this->tags_title;
        } elseif ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
            $title = $title ?: $this->params->get('page_title', $menu->title);
        }

        $this->setDocumentTitle($title);

        if (
            $menu
            && isset($menu->query['option'], $menu->query['view'])
            && $menu->query['option'] === 'com_tags'
            && $menu->query['view'] === 'tag'
        ) {
            // No need to alter pathway if the active menu item links directly to tag view
        } else {
            $pathway->addItem($title);
        }

        foreach ($this->item as $itemElement) {
            if ($itemElement->metadesc) {
                $this->getDocument()->setDescription($itemElement->metadesc);
            } elseif ($this->params->get('menu-meta_description')) {
                $this->getDocument()->setDescription($this->params->get('menu-meta_description'));
            }

            if ($this->params->get('robots')) {
                $this->getDocument()->setMetaData('robots', $this->params->get('robots'));
            }
        }

        if (count($this->item) === 1) {
            foreach ($this->item[0]->metadata->toArray() as $k => $v) {
                if ($v) {
                    $this->getDocument()->setMetaData($k, $v);
                }
            }
        }

        if ($this->params->get('show_feed_link', 1) == 1) {
            $link    = '&format=feed&limitstart=';
            $attribs = ['type' => 'application/rss+xml', 'title' => htmlspecialchars($this->getDocument()->getTitle())];
            $this->getDocument()->addHeadLink(Route::_($link . '&type=rss'), 'alternate', 'rel', $attribs);
            $attribs = ['type' => 'application/atom+xml', 'title' => htmlspecialchars($this->getDocument()->getTitle())];
            $this->getDocument()->addHeadLink(Route::_($link . '&type=atom'), 'alternate', 'rel', $attribs);
        }
    }

    /**
     * Creates the tags title for the output
     *
     * @return  string
     *
     * @since   3.1
     */
    protected function getTagsTitle()
    {
        $tags_title = [];

        foreach ($this->item as $item) {
            $tags_title[] = $item->title;
        }

        return implode(' ', $tags_title);
    }
}
