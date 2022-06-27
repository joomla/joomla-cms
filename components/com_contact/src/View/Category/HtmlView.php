<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contact\Site\View\Category;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\MVC\View\CategoryView;
use Joomla\Component\Contact\Site\Helper\RouteHelper;

/**
 * HTML View class for the Contacts component
 *
 * @since  1.5
 */
class HtmlView extends CategoryView
{
    /**
     * @var    string  The name of the extension for the category
     * @since  3.2
     */
    protected $extension = 'com_contact';

    /**
     * @var    string  Default title to use for page title
     * @since  3.2
     */
    protected $defaultPageTitle = 'COM_CONTACT_DEFAULT_PAGE_TITLE';

    /**
     * @var    string  The name of the view to link individual items to
     * @since  3.2
     */
    protected $viewName = 'contact';

    /**
     * Run the standard Joomla plugins
     *
     * @var    boolean
     * @since  3.5
     */
    protected $runPlugins = true;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        parent::commonCategoryDisplay();

        // Flag indicates to not add limitstart=0 to URL
        $this->pagination->hideEmptyLimitstart = true;

        // Prepare the data.
        // Compute the contact slug.
        foreach ($this->items as $item) {
            $item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
            $temp       = $item->params;
            $item->params = clone $this->params;
            $item->params->merge($temp);

            if ($item->params->get('show_email_headings', 0) == 1) {
                $item->email_to = trim($item->email_to);

                if (!empty($item->email_to) && MailHelper::isEmailAddress($item->email_to)) {
                    $item->email_to = HTMLHelper::_('email.cloak', $item->email_to);
                } else {
                    $item->email_to = '';
                }
            }
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

        parent::addFeed();

        if ($this->menuItemMatchCategory) {
            // If the active menu item is linked directly to the category being displayed, no further process is needed
            return;
        }

        // Get ID of the category from active menu item
        $menu = $this->menu;

        if (
            $menu && $menu->component == 'com_contact' && isset($menu->query['view'])
            && in_array($menu->query['view'], ['categories', 'category'])
        ) {
            $id = $menu->query['id'];
        } else {
            $id = 0;
        }

        $path     = [['title' => $this->category->title, 'link' => '']];
        $category = $this->category->getParent();

        while ($category !== null && $category->id != $id && $category->id !== 'root') {
            $path[]   = ['title' => $category->title, 'link' => RouteHelper::getCategoryRoute($category->id, $category->language)];
            $category = $category->getParent();
        }

        $path = array_reverse($path);

        foreach ($path as $item) {
            $this->pathway->addItem($item['title'], $item['link']);
        }
    }
}
