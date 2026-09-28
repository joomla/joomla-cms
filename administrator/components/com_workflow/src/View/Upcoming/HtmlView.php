<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\View\Upcoming;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Workflow\Administrator\Model\UpcomingModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Upcoming automated transitions across every workflow of an extension.
 *
 * @since  __DEPLOY_VERSION__
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The upcoming transitions to display.
     *
     * @var    \Joomla\Component\Workflow\Administrator\Automation\UpcomingTransition[]
     * @since  __DEPLOY_VERSION__
     */
    protected $items = [];

    /**
     * The model state.
     *
     * @var    \Joomla\Registry\Registry
     * @since  __DEPLOY_VERSION__
     */
    protected $state;

    /**
     * The pagination object.
     *
     * @var    \Joomla\CMS\Pagination\Pagination
     * @since  __DEPLOY_VERSION__
     */
    protected $pagination;

    /**
     * The filter form.
     *
     * @var    \Joomla\CMS\Form\Form
     * @since  __DEPLOY_VERSION__
     */
    public $filterForm;

    /**
     * The active filters.
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     */
    public $activeFilters = [];

    /**
     * Display the view.
     *
     * @param   string  $tpl  The name of the template file to parse.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function display($tpl = null)
    {
        // The dropdown that links here is core.admin only, because this lists every queued item
        // across the site. Without this the screen is merely hidden, not protected.
        if (!$this->getCurrentUser()->authorise('core.admin', 'com_workflow')) {
            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        /** @var UpcomingModel $model */
        $model = $this->getModel();

        $this->state         = $model->getState();
        $this->items         = $model->getItems();
        $this->pagination    = $model->getPagination();
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();

        // Keeps the extension on the form when the filter bar submits it.
        $this->filterForm->addControlField('extension', (string) $this->state->get('filter.extension'));

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function addToolbar()
    {
        $toolbar = $this->getDocument()->getToolbar();

        ToolbarHelper::title(Text::_('COM_WORKFLOW_UPCOMING_LIST'), 'clock');

        $arrow = $this->getLanguage()->isRtl() ? 'arrow-right' : 'arrow-left';

        $toolbar->link(
            'JTOOLBAR_BACK',
            Route::_('index.php?option=com_workflow&view=workflows&extension=' . $this->escape((string) $this->state->get('filter.extension')))
        )->icon('icon-' . $arrow);
    }
}
