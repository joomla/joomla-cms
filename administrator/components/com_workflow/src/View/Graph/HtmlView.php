<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\View\Graph;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Workflow\Administrator\Model\GraphModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View class to display the entire workflow graph
 *
 * @since  __DEPLOY_VERSION__
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The model state
     *
     * @var    object
     * @since  __DEPLOY_VERSION__
     */
    protected $state;

    /**
     * Items array
     *
     * @var    object
     * @since  __DEPLOY_VERSION__
     */
    protected $item;

    /**
     * The name of current extension
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $extension;

    /**
     * The ID of current workflow
     *
     * @var    integer
     * @since  __DEPLOY_VERSION__
     */
    protected $workflow;

    /**
     * The section of the current extension
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $section;

    /**
     * Display the view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    public function display($tpl = null)
    {
        /** @var GraphModel $model */
        $model = $this->getModel();

        // Get the data
        try {
            $this->state = $model->getState();
            $this->item  = $model->getItem();
        } catch (\Exception $e) {
            throw new GenericDataException(Text::_('COM_WORKFLOW_GRAPH_ERROR_FETCHING_MODEL') . $e->getMessage(), 500, $e);
        }

        $extension = $this->state->get('filter.extension');

        $parts = explode('.', $extension);

        $this->extension = array_shift($parts);

        if (!empty($parts)) {
            $this->section = array_shift($parts);
        }

        // Prepare workflow data for frontend
        $options = [
            'apiBaseUrl' => Route::_('index.php?option=com_workflow'),
            'extension'  => $this->escape($this->extension),
            'workflowId' => $this->item->id,
        ];


        // Set the toolbar
        $this->addToolbar();

        // Inject workflow data as JS options for frontend
        $this->getDocument()->addScriptOptions('com_workflow', $options);

        // Display the template
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since  __DEPLOY_VERSION__
     */
    protected function addToolbar()
    {
        Factory::getApplication()->getInput()->set('hidemainmenu', true);

        $user     = $this->getCurrentUser();
        $userId   = $user->id;
        $toolbar  = $this->getDocument()->getToolbar();

        $canDo     = ContentHelper::getActions($this->extension, 'workflow', $this->item->id);

        ToolbarHelper::title(Text::_('COM_WORKFLOW_GRAPH_WORKFLOWS_EDIT'), 'file-alt contact');

        // Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
        $itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId);

        $toolbar->link(
            'JTOOLBAR_CLOSE',
            Route::_('index.php?option=com_workflow&view=workflows&extension=' . $this->escape($this->item->extension))
        )
            ->icon('icon-cancel')->buttonClass('btn btn-danger');

        if ($itemEditable) {
            $toolbar->help('Workflow');
            $shortcutsLayout = new FileLayout('toolbar.shortcuts', JPATH_ADMINISTRATOR . '/components/com_workflow/layouts');
            $toolbar->customButton('Shortcuts')
                ->html($shortcutsLayout->render([]));
        }
    }
}
