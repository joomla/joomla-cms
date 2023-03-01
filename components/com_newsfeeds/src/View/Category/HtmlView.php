<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Newsfeeds\Site\View\Category;

use Joomla\CMS\MVC\View\CategoryView;
use Joomla\Component\Newsfeeds\Site\Helper\RouteHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTML View class for the Newsfeeds component
 *
 * @since  1.0
 */
class HtmlView extends CategoryView
{
    /**
     * @var    string  Default title to use for page title
     * @since  3.2
     */
    protected $defaultPageTitle = 'COM_NEWSFEEDS_DEFAULT_PAGE_TITLE';

    /**
     * @var    string  The name of the extension for the category
     * @since  3.2
     */
    protected $extension = 'com_newsfeeds';

    /**
     * @var    string  The name of the view to link individual items to
     * @since  3.2
     */
    protected $viewName = 'newsfeed';

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        $this->commonCategoryDisplay();

        // Flag indicates to not add limitstart=0 to URL
        $this->pagination->hideEmptyLimitstart = true;

        // Prepare the data.
        // Compute the newsfeed slug.
        foreach ($this->items as $item) {
            $item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
            $temp       = $item->params;
            $item->params = clone $this->params;
            $item->params->merge($temp);
        }

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

        $menu = $this->menu;
        $id = (int) @$menu->query['id'];

        if (
            $menu && (!isset($menu->query['option']) || $menu->query['option'] !== 'com_newsfeeds' || $menu->query['view'] === 'newsfeed'
            || $id != $this->category->id)
        ) {
            $path = [['title' => $this->category->title, 'link' => '']];
            $category = $this->category->getParent();

            while (
                (!isset($menu->query['option']) || $menu->query['option'] !== 'com_newsfeeds' || $menu->query['view'] === 'newsfeed'
                || $id != $category->id) && $category->id > 1
            ) {
                $path[] = ['title' => $category->title, 'link' => RouteHelper::getCategoryRoute($category->id, $category->language)];
                $category = $category->getParent();
            }

            $path = array_reverse($path);

            foreach ($path as $item) {
                $this->pathway->addItem($item['title'], $item['link']);
            }
        }
    }
}
