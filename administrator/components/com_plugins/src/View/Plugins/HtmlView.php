<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Plugins\Administrator\View\Plugins;

use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Plugins\Administrator\Model\PluginsModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class for a list of plugins.
 *
 * @since  1.5
 */
class HtmlView extends BaseHtmlView
{
    /**
     * An array of items
     *
     * @var  array
     */
    protected $items;

    /**
     * The pagination object
     *
     * @var    \Joomla\CMS\Pagination\Pagination
     */
    protected $pagination;

    /**
     * The model state
     *
     * @var   \Joomla\Registry\Registry
     */
    protected $state;

    /**
     * Form object for search filters
     *
     * @var    \Joomla\CMS\Form\Form
     * @since  4.0.0
     */
    public $filterForm;

    /**
     * The active search filters
     *
     * @var    array
     * @since  4.0.0
     */
    public $activeFilters;

    /**
     * Display the view.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        /** @var PluginsModel $model */
        $model = $this->getModel();

        $this->items         = $model->getItems();
        $this->pagination    = $model->getPagination();
        $this->state         = $model->getState();
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function addToolbar()
    {
        $canDo   = ContentHelper::getActions('com_plugins');
        $toolbar = $this->getDocument()->getToolbar();

        ToolbarHelper::title(Text::_('COM_PLUGINS_MANAGER_PLUGINS'), 'plug plugin');

        if ($canDo->get('core.edit.state')) {
            $toolbar->publish('plugins.publish', 'JTOOLBAR_ENABLE')->listCheck(true);
            $toolbar->unpublish('plugins.unpublish', 'JTOOLBAR_DISABLE')->listCheck(true);
            $toolbar->checkin('plugins.checkin');
        }

        if ($canDo->get('core.admin')) {
            $toolbar->preferences('com_plugins');
        }

        $toolbar->help('Plugins');
    }
}
