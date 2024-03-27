<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Administrator\View\Request;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Privacy\Administrator\Model\RequestsModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Request view class
 *
 * @since  3.9.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The action logs for the item
     *
     * @var    array
     * @since  3.9.0
     */
    protected $actionlogs;

    /**
     * The form object
     *
     * @var    Form
     * @since  3.9.0
     */
    protected $form;

    /**
     * The item record
     *
     * @var    CMSObject
     * @since  3.9.0
     */
    protected $item;

    /**
     * The state information
     *
     * @var    CMSObject
     * @since  3.9.0
     */
    protected $state;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @see     BaseHtmlView::loadTemplate()
     * @since   3.9.0
     * @throws  \Exception
     */
    public function display($tpl = null)
    {
        /** @var RequestsModel $model */
        $model       = $this->getModel();
        $this->item  = $model->getItem();
        $this->state = $model->getState();

        // Variables only required for the default layout
        if ($this->getLayout() === 'default') {
            /** @var \Joomla\Component\Actionlogs\Administrator\Model\ActionlogsModel $logsModel */
            $logsModel = $this->getModel('actionlogs');

            $this->actionlogs = $logsModel->getLogsForItem('com_privacy.request', $this->item->id);

            // Load the com_actionlogs language strings for use in the layout
            $lang = $this->getLanguage();
            $lang->load('com_actionlogs', JPATH_ADMINISTRATOR)
                || $lang->load('com_actionlogs', JPATH_ADMINISTRATOR . '/components/com_actionlogs');
        }

        // Variables only required for the edit layout
        if ($this->getLayout() === 'edit') {
            $this->form = $this->get('Form');
        }

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
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
     * @since   3.9.0
     */
    protected function addToolbar()
    {
        Factory::getApplication()->getInput()->set('hidemainmenu', true);

        $toolbar = Toolbar::getInstance();

        // Set the title and toolbar based on the layout
        if ($this->getLayout() === 'edit') {
            ToolbarHelper::title(Text::_('COM_PRIVACY_VIEW_REQUEST_ADD_REQUEST'), 'lock');

            $toolbar->save('request.save');
            $toolbar->cancel('request.cancel');
            $toolbar->help('Privacy:_New_Information_Request');
        } else {
            ToolbarHelper::title(Text::_('COM_PRIVACY_VIEW_REQUEST_SHOW_REQUEST'), 'lock');

            // Add transition and action buttons based on item status
            switch ($this->item->status) {
                case '0':
                    $toolbar->standardButton('cancel', 'COM_PRIVACY_TOOLBAR_INVALIDATE', 'request.invalidate')
                        ->listCheck(false)
                        ->icon('icon-cancel-circle');

                    break;

                case '1':
                    $return = '&return=' . base64_encode('index.php?option=com_privacy&view=request&id=' . (int) $this->item->id);

                    $toolbar->standardButton('apply', 'COM_PRIVACY_TOOLBAR_COMPLETE', 'request.complete')
                        ->listCheck(false)
                        ->icon('icon-apply');

                    $toolbar->standardButton('invalidate', 'COM_PRIVACY_TOOLBAR_INVALIDATE', 'request.invalidate')
                        ->listCheck(false)
                        ->icon('icon-cancel-circle');

                    if ($this->item->request_type === 'export') {
                        $toolbar->linkButton('download', 'COM_PRIVACY_ACTION_EXPORT_DATA')
                            ->url(Route::_('index.php?option=com_privacy&task=request.export&format=xml&id=' . (int) $this->item->id . $return));

                        if (Factory::getApplication()->get('mailonline', 1)) {
                            $toolbar->linkButton('mail', 'COM_PRIVACY_ACTION_EMAIL_EXPORT_DATA')
                                ->url(Route::_('index.php?option=com_privacy&task=request.emailexport&id=' . (int) $this->item->id . $return
                                    . '&' . Session::getFormToken() . '=1'));
                        }
                    }

                    if ($this->item->request_type === 'remove') {
                        $toolbar->standardButton('delete', 'COM_PRIVACY_ACTION_DELETE_DATA', 'request.remove')
                            ->listCheck(false)
                            ->icon('icon-delete');
                    }

                    break;

                // Item is in a "locked" state and cannot transition
                default:
                    break;
            }

            $toolbar->cancel('request.cancel');
            $toolbar->help('Privacy:_Review_Information_Request');
        }
    }
}
