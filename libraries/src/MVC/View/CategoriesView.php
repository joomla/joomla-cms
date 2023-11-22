<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\View;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Categories view base class.
 *
 * @since  3.2
 */
class CategoriesView extends HtmlView
{
    /**
     * State data
     *
     * @var    \Joomla\Registry\Registry
     * @since  3.2
     */
    protected $state;

    /**
     * Category items data
     *
     * @var    array
     * @since  3.2
     */
    protected $items;

    /**
     * Language key for default page heading
     *
     * @var    string
     * @since  3.2
     */
    protected $pageHeading;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void|boolean
     *
     * @since   3.2
     * @throws  \Exception
     */
    public function display($tpl = null)
    {
        $state  = $this->get('State');
        $items  = $this->get('Items');
        $parent = $this->get('Parent');

        $app = Factory::getApplication();

        // Check for errors.
        if (\count($errors = $this->get('Errors'))) {
            $app->enqueueMessage($errors, 'error');

            return false;
        }

        if ($items === false) {
            $app->enqueueMessage(Text::_('JGLOBAL_CATEGORY_NOT_FOUND'), 'error');

            return false;
        }

        if ($parent == false) {
            $app->enqueueMessage(Text::_('JGLOBAL_CATEGORY_NOT_FOUND'), 'error');

            return false;
        }

        $params = &$state->params;

        $items = [$parent->id => $items];

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx', ''), ENT_COMPAT, 'UTF-8');

        $this->maxLevelcat = $params->get('maxLevelcat', -1) < 0 ? PHP_INT_MAX : $params->get('maxLevelcat', PHP_INT_MAX);
        $this->params      = &$params;
        $this->parent      = &$parent;
        $this->items       = &$items;

        $this->prepareDocument();

        parent::display($tpl);
    }

    /**
     * Prepares the document
     *
     * @return  void
     *
     * @since   3.2
     */
    protected function prepareDocument()
    {
        // Because the application sets a default page title, we need to get it from the menu item itself
        $menu = Factory::getApplication()->getMenu()->getActive();

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', Text::_($this->pageHeading));
        }

        $this->setDocumentTitle($this->params->get('page_title', ''));

        if ($this->params->get('menu-meta_description')) {
            $this->getDocument()->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('robots')) {
            $this->getDocument()->setMetaData('robots', $this->params->get('robots'));
        }
    }
}
