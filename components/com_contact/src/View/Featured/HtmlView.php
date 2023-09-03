<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contact\Site\View\Featured;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\MailHelper;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Featured View class
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The item model state
     *
     * @var    \Joomla\Registry\Registry
     *
     * @since  1.6.0
     */
    protected $state;

    /**
     * The item details
     *
     * @var    \Joomla\CMS\Object\CMSObject
     *
     * @since  1.6.0
     */
    protected $items;

    /**
     * The pagination object
     *
     * @var    \Joomla\CMS\Pagination\Pagination
     *
     * @since  1.6.0
     */
    protected $pagination;

    /**
     * The page parameters
     *
     * @var    \Joomla\Registry\Registry|null
     *
     * @since  4.0.0
     */
    protected $params = null;

    /**
     * The page class suffix
     *
     * @var    string
     *
     * @since  4.0.0
     */
    protected $pageclass_sfx = '';

    /**
     * Method to display the view.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function display($tpl = null)
    {
        $app    = Factory::getApplication();
        $params = $app->getParams();

        // Get some data from the models
        $state      = $this->get('State');
        $items      = $this->get('Items');
        $category   = $this->get('Category');
        $children   = $this->get('Children');
        $parent     = $this->get('Parent');
        $pagination = $this->get('Pagination');

        // Flag indicates to not add limitstart=0 to URL
        $pagination->hideEmptyLimitstart = true;

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Prepare the data.
        // Compute the contact slug.
        for ($i = 0, $n = count($items); $i < $n; $i++) {
            $item         = &$items[$i];
            $item->slug   = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
            $temp         = $item->params;
            $item->params = clone $params;
            $item->params->merge($temp);

            if ($item->params->get('show_email', 0) == 1) {
                $item->email_to = trim($item->email_to);

                if (!empty($item->email_to) && MailHelper::isEmailAddress($item->email_to)) {
                    $item->email_to = HTMLHelper::_('email.cloak', $item->email_to);
                } else {
                    $item->email_to = '';
                }
            }
        }

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx', ''), ENT_COMPAT, 'UTF-8');

        $maxLevel         = $params->get('maxLevel', -1);
        $this->maxLevel   = &$maxLevel;
        $this->state      = &$state;
        $this->items      = &$items;
        $this->category   = &$category;
        $this->children   = &$children;
        $this->params     = &$params;
        $this->parent     = &$parent;
        $this->pagination = &$pagination;

        $this->_prepareDocument();

        parent::display($tpl);
    }

    /**
     * Prepares the document
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function _prepareDocument()
    {
        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = Factory::getApplication()->getMenu()->getActive();

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', Text::_('COM_CONTACT_DEFAULT_PAGE_TITLE'));
        }

        $this->setDocumentTitle($this->params->get('page_title', ''));

        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetaData('robots', $this->params->get('robots'));
        }
    }
}
