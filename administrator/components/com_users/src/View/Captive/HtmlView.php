<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\View\Captive;

use Joomla\CMS\Event\MultiFactor\BeforeDisplayMethods;
use Joomla\CMS\Event\MultiFactor\NotifyActionLog;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\Button\BasicButton;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Users\Administrator\Helper\Mfa as MfaHelper;
use Joomla\Component\Users\Administrator\Model\BackupcodesModel;
use Joomla\Component\Users\Administrator\Model\CaptiveModel;
use Joomla\Component\Users\Administrator\View\SiteTemplateTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View for Multi-factor Authentication captive page
 *
 * @since 4.2.0
 */
class HtmlView extends BaseHtmlView
{
    use SiteTemplateTrait;

    /**
     * The MFA Method records for the current user which correspond to enabled plugins
     *
     * @var   array
     * @since 4.2.0
     */
    public $records = [];

    /**
     * The currently selected MFA Method record against which we'll be authenticating
     *
     * @var   null|\stdClass
     * @since 4.2.0
     */
    public $record = null;

    /**
     * The Captive MFA page's rendering options
     *
     * @var   array|null
     * @since 4.2.0
     */
    public $renderOptions = null;

    /**
     * The title to display at the top of the page
     *
     * @var   string
     * @since 4.2.0
     */
    public $title = '';

    /**
     * Is this an administrator page?
     *
     * @var   boolean
     * @since 4.2.0
     */
    public $isAdmin = false;

    /**
     * Does the currently selected Method allow authenticating against all of its records?
     *
     * @var   boolean
     * @since 4.2.0
     */
    public $allowEntryBatching = false;

    /**
     * All enabled MFA Methods (plugins)
     *
     * @var   array
     * @since 4.2.0
     */
    public $mfaMethods;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void  A string if successful, otherwise an Error object.
     *
     * @throws  \Exception
     * @since 4.2.0
     */
    public function display($tpl = null)
    {
        $this->setSiteTemplateStyle();

        $app  = Factory::getApplication();
        $user = $this->getCurrentUser();

        PluginHelper::importPlugin('multifactorauth');
        $event = new BeforeDisplayMethods($user);
        $app->getDispatcher()->dispatch($event->getName(), $event);

        /** @var CaptiveModel $model */
        $model = $this->getModel();

        // Load data from the model
        $this->isAdmin    = $app->isClient('administrator');
        $this->records    = $this->get('records');
        $this->record     = $this->get('record');
        $this->mfaMethods = MfaHelper::getMfaMethods();

        if (!empty($this->records)) {
            /** @var BackupcodesModel $codesModel */
            $codesModel        = $this->getModel('Backupcodes');
            $backupCodesRecord = $codesModel->getBackupCodesRecord();

            if (!\is_null($backupCodesRecord)) {
                $backupCodesRecord->title = Text::_('COM_USERS_USER_BACKUPCODES');
                $this->records[]          = $backupCodesRecord;
            }
        }

        // If we only have one record there's no point asking the user to select a MFA Method
        if (empty($this->record) && !empty($this->records)) {
            // Default to the first record
            $this->record = reset($this->records);

            // If we have multiple records try to make this record the default
            if (\count($this->records) > 1) {
                foreach ($this->records as $record) {
                    if ($record->default) {
                        $this->record = $record;

                        break;
                    }
                }
            }
        }

        // Set the correct layout based on the availability of a MFA record
        $this->setLayout('default');

        // If we have no record selected or explicitly asked to run the 'select' task use the correct layout
        if (\is_null($this->record) || ($model->getState('task') == 'select')) {
            $this->setLayout('select');
        }

        switch ($this->getLayout()) {
            case 'select':
                $this->allowEntryBatching = 1;

                $event = new NotifyActionLog('onComUsersCaptiveShowSelect', []);
                Factory::getApplication()->getDispatcher()->dispatch($event->getName(), $event);
                break;

            case 'default':
            default:
                $this->renderOptions      = $model->loadCaptiveRenderOptions($this->record);
                $this->allowEntryBatching = $this->renderOptions['allowEntryBatching'] ?? 0;

                $event = new NotifyActionLog(
                    'onComUsersCaptiveShowCaptive',
                    [
                        $this->escape($this->record->title),
                    ]
                );
                Factory::getApplication()->getDispatcher()->dispatch($event->getName(), $event);
                break;
        }

        // Which title should I use for the page?
        $this->title = $this->get('PageTitle');

        // Back-end: always show a title in the 'title' module position, not in the page body
        if ($this->isAdmin) {
            ToolbarHelper::title(Text::_('COM_USERS_USER_MULTIFACTOR_AUTH'), 'users user-lock');
            $this->title = '';
        }

        if ($this->isAdmin && $this->getLayout() === 'default') {
            $bar    = $this->getDocument()->getToolbar();
            $button = (new BasicButton('user-mfa-submit'))
                ->text($this->renderOptions['submit_text'])
                ->icon($this->renderOptions['submit_icon']);
            $bar->appendButton($button);

            $button = (new BasicButton('user-mfa-logout'))
                ->text('COM_USERS_MFA_LOGOUT')
                ->buttonClass('btn btn-danger')
                ->icon('icon icon-lock');
            $bar->appendButton($button);

            if (\count($this->records) > 1) {
                $arrow  = Factory::getApplication()->getLanguage()->isRtl() ? 'arrow-right' : 'arrow-left';
                $button = (new BasicButton('user-mfa-choose-another'))
                    ->text('COM_USERS_MFA_USE_DIFFERENT_METHOD')
                    ->icon('icon-' . $arrow);
                $bar->appendButton($button);
            }
        }

        // Display the view
        parent::display($tpl);
    }
}
