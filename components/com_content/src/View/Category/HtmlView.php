<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Site\View\Category;

use Joomla\CMS\Event\Content\AfterDisplayEvent;
use Joomla\CMS\Event\Content\AfterTitleEvent;
use Joomla\CMS\Event\Content\BeforeDisplayEvent;
use Joomla\CMS\Event\Content\ContentPrepareEvent;
use Joomla\CMS\Event\Content\ItemsDisplayEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\CategoryView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Event\DispatcherInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTML View class for the Content component
 *
 * @since  1.5
 */
class HtmlView extends CategoryView
{
    /**
     * @var    array  Array of leading items for blog display
     * @since  3.2
     */
    protected $lead_items = [];

    /**
     * @var    array  Array of intro items for blog display
     * @since  3.2
     */
    protected $intro_items = [];

    /**
     * @var    array  Array of links in blog display
     * @since  3.2
     */
    protected $link_items = [];

    /**
     * @var    string  The name of the extension for the category
     * @since  3.2
     */
    protected $extension = 'com_content';

    /**
     * @var    string  Default title to use for page title
     * @since  3.2
     */
    protected $defaultPageTitle = 'JGLOBAL_ARTICLES';

    /**
     * @var    string  The name of the view to link individual items to
     * @since  3.2
     */
    protected $viewName = 'article';

    /**
     * Prepared dispatcher result for category
     * @var  \stdClass
     * @since  __DEPLOY_VERSION__
     */
    protected $eventResult;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        /**
         * Pass the current layout to the model so it can apply special handling for the
         * blog layout. In the blog layout, if the total number of articles (leading +
         * intro + links) is 0, we skip loading any articles to avoid the performance
         * cost of loading all records when the limit is 0.
         */
        $this->getModel()->setState('view.layout', $this->getLayout());

        $this->commonCategoryDisplay();

        // Flag indicates to not add limitstart=0 to URL
        $this->pagination->hideEmptyLimitstart = true;

        // Prepare the data
        // Get the metrics for the structural page layout.
        $params     = $this->params;
        $numLeading = $params->def('num_leading_articles', 1);
        $numIntro   = $params->def('num_intro_articles', 4);
        $numLinks   = $params->def('num_links', 4);
        $this->vote = PluginHelper::isEnabled('content', 'vote');

        $dispatcher = $this->getDispatcher();
        PluginHelper::importPlugin('content', null, true, $dispatcher);

        $app        = Factory::getApplication();
        $dispatcher = Factory::getContainer()->get(DispatcherInterface::class);

        // Compute the article prepare introtext (runs content plugins).
        foreach ($this->items as $item) {
            $item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;

            // No link for ROOT category
            if ($item->parent_alias === 'root') {
                $item->parent_id = null;
            }

            $dispatcher->dispatch(
                'onContentPrepare',
                new ContentPrepareEvent('onContentPrepare', ['context' => 'com_content.category', 'subject' => $item, 'params' => $item->params, 'page'    => 0])
            );
        }

        // Compute the article texts (runs content plugins).
        foreach ($this->items as $item) {

            $contentEventArguments = [
                'context' => 'com_content.category',
                'subject' => $item,
                'params'  => $item->params,
                'page'    => 0,
            ];

            $contentEvents = [
                'afterDisplayTitle'    => new AfterTitleEvent('onContentAfterTitle', $contentEventArguments),
                'beforeDisplayContent' => new BeforeDisplayEvent('onContentBeforeDisplay', $contentEventArguments),
                'afterDisplayContent'  => new AfterDisplayEvent('onContentAfterDisplay', $contentEventArguments),
            ];
            
            $item->event   = new \stdClass();

            foreach ($contentEvents as $resultKey => $event) {
                $results = $dispatcher->dispatch($event->getName(), $event)->getArgument('result', []);

                $item->event->{$resultKey} = trim(implode("\n", $results));
            }
        }

        // For blog layouts, preprocess the breakdown of leading, intro and linked articles.
        // This makes it much easier for the designer to just interrogate the arrays.
        if ($params->get('layout_type') === 'blog' || $this->getLayout() === 'blog') {
            foreach ($this->items as $i => $item) {
                if ($i < $numLeading) {
                    $this->lead_items[] = $item;
                } elseif ($i >= $numLeading && $i < $numLeading + $numIntro) {
                    $this->intro_items[] = $item;
                } elseif ($i < $numLeading + $numIntro + $numLinks) {
                    $this->link_items[] = $item;
                }
            }
        }

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $active = $app->getMenu()->getActive();

        if ($this->menuItemMatchCategory) {
            $this->params->def('page_heading', $this->params->get('page_title', $active->title));
            $title = $this->params->get('page_title', $active->title);
        } else {
            $this->params->def('page_heading', $this->category->title);
            $title = $this->category->title;
            $this->params->set('page_title', $title);
        }

        if (empty($title)) {
            $title = $this->category->title;
        }

        $this->setDocumentTitle($title);

        if ($this->category->metadesc) {
            $this->getDocument()->setDescription($this->category->metadesc);
        } elseif ($this->params->get('menu-meta_description')) {
            $this->getDocument()->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('robots')) {
            $this->getDocument()->setMetaData('robots', $this->params->get('robots'));
        }

        if (!\is_object($this->category->metadata)) {
            $this->category->metadata = new Registry($this->category->metadata);
        }

        $mdata = $this->category->metadata->toArray();

        foreach ($mdata as $k => $v) {
            if ($v) {
                $this->getDocument()->setMetaData($k, $v);
            }
        }

        $this->category->text = $this->category->description;

        $contentEventArguments = [
            'context' => $this->category->extension . '.categories',
            'subject' => $this->category,
            'params'  => $this->params,
            'page'    => 0,
        ];

        $dispatcher->dispatch('onContentPrepare', new ContentPrepareEvent('onContentPrepare', $contentEventArguments));
        $this->category->description = $this->category->text;

        $this->eventResult = new \stdClass();

        $results = $dispatcher->dispatch(
            'onContentAfterTitle',
            new AfterTitleEvent('onContentAfterTitle', $contentEventArguments)
        )->getArgument('result', []);
        $this->eventResult->afterDisplayTitle = trim(implode("\n", $results));

        $results = $dispatcher->dispatch(
            'onContentBeforeDisplay',
            new BeforeDisplayEvent('onContentBeforeDisplay', $contentEventArguments)
        )->getArgument('result', []);
        $this->eventResult->beforeDisplayContent = trim(implode("\n", $results));

        $results = $dispatcher->dispatch(
            'onContentAfterDisplay',
            new AfterDisplayEvent('onContentAfterDisplay', $contentEventArguments)
        )->getArgument('result', []);
        $this->eventResult->afterDisplayContent = trim(implode("\n", $results));

        $contentEventArguments['subject'] = $this;
        $results                          = $dispatcher->dispatch(
            'onContentAfterItems',
            new ItemsDisplayEvent('onContentAfterItems', $contentEventArguments)
        )->getArgument('result', []);
        $this->eventResult->afterDisplayItems = trim(implode("\n", $results));

        parent::display($tpl);
    }

    /**
     * Prepares the document
     *
     * @return  void
     */
    protected function prepareDocument()
    {
        parent::prepareDocument();

        $this->addFeed();

        if ($this->menuItemMatchCategory) {
            // If the active menu item is linked directly to the category being displayed, no further process is needed
            return;
        }

        // Get ID of the category from active menu item
        $menu = $this->menu;

        if (
            $menu && $menu->component == 'com_content' && isset($menu->query['view'])
            && \in_array($menu->query['view'], ['categories', 'category'])
        ) {
            $id = $menu->query['id'];
        } else {
            $id = 0;
        }

        $path     = [['title' => $this->category->title, 'link' => '']];
        $category = $this->category->getParent();

        while ($category !== null && $category->id !== 'root' && $category->id != $id) {
            $path[]   = ['title' => $category->title, 'link' => RouteHelper::getCategoryRoute($category->id, $category->language)];
            $category = $category->getParent();
        }

        $path = array_reverse($path);

        foreach ($path as $item) {
            $this->pathway->addItem($item['title'], $item['link']);
        }
    }
}
