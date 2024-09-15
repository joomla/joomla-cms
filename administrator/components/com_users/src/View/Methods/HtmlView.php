<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\View\Methods;

use Joomla\CMS\Event\MultiFactor\NotifyActionLog;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\User\User;
use Joomla\Component\Users\Administrator\DataShape\MethodDescriptor;
use Joomla\Component\Users\Administrator\Model\BackupcodesModel;
use Joomla\Component\Users\Administrator\Model\MethodsModel;
use Joomla\Component\Users\Administrator\View\SiteTemplateTrait;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View for Multi-factor Authentication methods list page
 *
 * @since 4.2.0
 */
class HtmlView extends BaseHtmlView
{
    use SiteTemplateTrait;

    /**
     * Is this an administrator page?
     *
     * @var   boolean
     * @since 4.2.0
     */
    public $isAdmin = false;

    /**
     * The MFA Methods available for this user
     *
     * @var   array
     * @since 4.2.0
     */
    public $methods = [];

    /**
     * The return URL to use for all links and forms
     *
     * @var   string
     * @since 4.2.0
     */
    public $returnURL = null;

    /**
     * Are there any active MFA Methods at all?
     *
     * @var   boolean
     * @since 4.2.0
     */
    public $mfaActive = false;

    /**
     * Which Method has the default record?
     *
     * @var   string
     * @since 4.2.0
     */
    public $defaultMethod = '';

    /**
     * The user object used to display this page
     *
     * @var   User
     * @since 4.2.0
     */
    public $user = null;

    /**
     * Is this page part of the mandatory Multi-factor Authentication setup?
     *
     * @var   boolean
     * @since 4.2.0
     */
    public $isMandatoryMFASetup = false;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @throws  \Exception
     * @see     \JViewLegacy::loadTemplate()
     * @since   4.2.0
     */
    public function display($tpl = null): void
    {
        $this->setSiteTemplateStyle();

        $app = Factory::getApplication();

        if (empty($this->user)) {
            $this->user = $this->getCurrentUser();
        }

        /** @var MethodsModel $model */
        $model = $this->getModel();

        if ($this->getLayout() !== 'firsttime') {
            $this->setLayout('default');
        }

        $this->methods = $model->getMethods($this->user);
        $this->isAdmin = $app->isClient('administrator');
        $activeRecords = 0;

        foreach ($this->methods as $methodName => $method) {
            $methodActiveRecords = \count($method['active']);

            if (!$methodActiveRecords) {
                continue;
            }

            $activeRecords += $methodActiveRecords;
            $this->mfaActive = true;

            foreach ($method['active'] as $record) {
                if ($record->default) {
                    $this->defaultMethod = $methodName;

                    break;
                }
            }
        }

        // If there are no backup codes yet we should create new ones
        /** @var BackupcodesModel $model */
        $model       = $this->getModel('backupcodes');
        $backupCodes = $model->getBackupCodes($this->user);

        if ($activeRecords && empty($backupCodes)) {
            $model->regenerateBackupCodes($this->user);
        }

        $backupCodesRecord = $model->getBackupCodesRecord($this->user);

        if (!\is_null($backupCodesRecord)) {
            $this->methods = array_merge(
                [
                    'backupcodes' => new MethodDescriptor(
                        [
                            'name'       => 'backupcodes',
                            'display'    => Text::_('COM_USERS_USER_BACKUPCODES'),
                            'shortinfo'  => Text::_('COM_USERS_USER_BACKUPCODES_DESC'),
                            'image'      => 'media/com_users/images/emergency.svg',
                            'canDisable' => false,
                            'active'     => [$backupCodesRecord],
                        ]
                    ),
                ],
                $this->methods
            );
        }

        $this->isMandatoryMFASetup = $activeRecords === 0 && $app->getSession()->get('com_users.mandatory_mfa_setup', 0) === 1;

        // Back-end: always show a title in the 'title' module position, not in the page body
        if ($this->isAdmin) {
            ToolbarHelper::title(Text::_('COM_USERS_MFA_LIST_PAGE_HEAD'), 'users user-lock');

            if ($this->getCurrentUser()->authorise('core.manage', 'com_users')) {
                $toolbar = $this->getDocument()->getToolbar();
                $arrow   = Factory::getApplication()->getLanguage()->isRtl() ? 'arrow-right' : 'arrow-left';
                $toolbar->link('JTOOLBAR_BACK', 'index.php?option=com_users')
                    ->icon('icon-' . $arrow);
            }
        }

        // Display the view
        parent::display($tpl);

        $event = new NotifyActionLog('onComUsersViewMethodsAfterDisplay', [$this]);
        Factory::getApplication()->getDispatcher()->dispatch($event->getName(), $event);

        Text::script('JGLOBAL_CONFIRM_DELETE');
    }
}
