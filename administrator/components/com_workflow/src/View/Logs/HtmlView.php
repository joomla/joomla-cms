<?php

/**
 * @package Joomla.Administrator
 * @subpackage com_workflow
 *
 * @copyright (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\View\Logs;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Workflow\Administrator\Model\LogsModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Automation log view.
 *
 * @since __DEPLOY_VERSION__
 */
class HtmlView extends BaseHtmlView
{
    protected $items;
    protected $state;
    protected $pagination;
    public $filterForm;
    public $activeFilters;
    protected $extension;

    public function display($tpl = null)
    {
        // The dropdown that links here is core.admin only, because these rows carry failure
        // details. Without this the screen is merely hidden, not protected: the URL still works.
        if (!$this->getCurrentUser()->authorise('core.admin', 'com_workflow')) {
            throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        /** @var LogsModel $model */
        $model = $this->getModel();

        $this->state         = $model->getState();
        $this->items         = $model->getItems();
        $this->pagination    = $model->getPagination();
        $this->filterForm    = $model->getFilterForm();
        $this->activeFilters = $model->getActiveFilters();

        $extension       = (string) $this->state->get('filter.extension');
        $parts           = explode('.', $extension);
        $this->extension = array_shift($parts) ?: 'com_content';

        $this->filterForm->addControlField('extension', $extension);

        $this->addToolbar();

        parent::display($tpl);
    }

    protected function addToolbar()
    {
        /** @var \Joomla\CMS\Document\HtmlDocument $document */
        $document = $this->getDocument();
        $toolbar  = $document->getToolbar();

        ToolbarHelper::title(Text::_('COM_WORKFLOW_LOGS_LIST'), 'list');

        $arrow = $this->getLanguage()->isRtl() ? 'arrow-right' : 'arrow-left';

        $toolbar->link(
            'JTOOLBAR_BACK',
            Route::_('index.php?option=com_workflow&view=workflows&extension=' . $this->escape((string) $this->state->get('filter.extension')))
        )->icon('icon-' . $arrow);
    }
}
