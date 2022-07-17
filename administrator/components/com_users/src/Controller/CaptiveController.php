<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Controller;

use Exception;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Event\GenericEvent;
use Joomla\CMS\Event\MultiFactor\NotifyActionLog;
use Joomla\CMS\Event\MultiFactor\Validate;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Component\Users\Administrator\Model\BackupcodesModel;
use Joomla\Component\Users\Administrator\Model\CaptiveModel;
use Joomla\Input\Input;
use ReflectionObject;
use RuntimeException;

/**
 * Captive Multi-factor Authentication page controller
 *
 * @since 4.2.0
 */
class CaptiveController extends BaseController
{
    /**
     * Public constructor
     *
     * @param   array                     $config   Plugin configuration
     * @param   MVCFactoryInterface|null  $factory  MVC Factory for the com_users component
     * @param   CMSApplication|null       $app      CMS application object
     * @param   Input|null                $input    Joomla CMS input object
     *
     * @since 4.2.0
     */
    public function __construct(array $config = [], MVCFactoryInterface $factory = null, ?CMSApplication $app = null, ?Input $input = null)
    {
        parent::__construct($config, $factory, $app, $input);

        $this->registerTask('captive', 'display');
    }

    /**
     * Displays the captive login page
     *
     * @param   boolean        $cachable   Ignored. This page is never cached.
     * @param   boolean|array  $urlparams  Ignored. This page is never cached.
     *
     * @return  void
     * @throws  Exception
     * @since   4.2.0
     */
    public function display($cachable = false, $urlparams = false): void
    {
        $user = $this->app->getIdentity()
            ?: Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById(0);

        // Only allow logged in Users
        if ($user->guest) {
            throw new RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        // Get the view object
        $viewLayout = $this->input->get('layout', 'default', 'string');
        $view       = $this->getView(
            'Captive',
            'html',
            '',
            [
                'base_path' => $this->basePath,
                'layout'    => $viewLayout,
            ]
        );

        $view->document = $this->app->getDocument();

        // If we're already logged in go to the site's home page
        if ((int) $this->app->getSession()->get('com_users.mfa_checked', 0) === 1) {
            $url = Route::_('index.php?option=com_users&task=methods.display', false);

            $this->setRedirect($url);
        }

        // Pass the model to the view
        /** @var CaptiveModel $model */
        $model = $this->getModel('Captive');
        $view->setModel($model, true);

        /** @var BackupcodesModel $codesModel */
        $codesModel = $this->getModel('Backupcodes');
        $view->setModel($codesModel, false);

        try {
            // Suppress all modules on the page except those explicitly allowed
            $model->suppressAllModules();
        } catch (Exception $e) {
            // If we can't kill the modules we can still survive.
        }

        // Pass the MFA record ID to the model
        $recordId = $this->input->getInt('record_id', null);
        $model->setState('record_id', $recordId);

        // Do not go through $this->display() because it overrides the model.
        $view->display();
    }

    /**
     * Validate the MFA code entered by the user
     *
     * @param   bool   $cachable         Ignored. This page is never cached.
     * @param   array  $urlparameters    Ignored. This page is never cached.
     *
     * @return  void
     * @throws  Exception
     * @since   4.2.0
     */
    public function validate($cachable = false, $urlparameters = [])
    {
        // CSRF Check
        $this->checkToken($this->input->getMethod());

        // Get the MFA parameters from the request
        $recordId  = $this->input->getInt('record_id', null);
        $code      = $this->input->get('code', null, 'raw');
        /** @var CaptiveModel $model */
        $model = $this->getModel('Captive');

        // Validate the MFA record
        $model->setState('record_id', $recordId);
        $record = $model->getRecord();

        if (empty($record)) {
            $event = new NotifyActionLog('onComUsersCaptiveValidateInvalidMethod');
            $this->app->getDispatcher()->dispatch($event->getName(), $event);

            throw new RuntimeException(Text::_('COM_USERS_MFA_INVALID_METHOD'), 500);
        }

        // Validate the code
        $user = $this->app->getIdentity()
            ?: Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById(0);

        $event   = new Validate($record, $user, $code);
        $results = $this->app
            ->getDispatcher()
            ->dispatch($event->getName(), $event)
            ->getArgument('result', []);

        $isValidCode = false;

        if ($record->method === 'backupcodes') {
            /** @var BackupcodesModel $codesModel */
            $codesModel = $this->getModel('Backupcodes');
            $results    = [$codesModel->isBackupCode($code, $user)];
            /**
             * This is required! Do not remove!
             *
             * There is a store() call below. It saves the in-memory MFA record to the database. That includes the
             * options key which contains the configuration of the Method. For backup codes, these are the actual codes
             * you can use. When we check for a backup code validity we also "burn" it, i.e. we remove it from the
             * options table and save that to the database. However, this DOES NOT update the $record here. Therefore
             * the call to saveRecord() would overwrite the database contents with a record that _includes_ the backup
             * code we had just burned. As a result the single use backup codes end up being multiple use.
             *
             * By doing a getRecord() here, right after we have "burned" any correct backup codes, we resolve this
             * issue. The loaded record will reflect the database contents where the options DO NOT include the code we
             * just used. Therefore the call to store() will result in the correct database state, i.e. the used backup
             * code being removed.
             */
            $record = $model->getRecord();
        }

        $isValidCode = array_reduce(
            $results,
            function (bool $carry, $result) {
                return $carry || boolval($result);
            },
            false
        );

        if (!$isValidCode) {
            // The code is wrong. Display an error and go back.
            $captiveURL = Route::_('index.php?option=com_users&view=captive&record_id=' . $recordId, false);
            $message    = Text::_('COM_USERS_MFA_INVALID_CODE');
            $this->setRedirect($captiveURL, $message, 'error');

            $event = new NotifyActionLog('onComUsersCaptiveValidateFailed', [$record->title]);
            $this->app->getDispatcher()->dispatch($event->getName(), $event);

            return;
        }

        // Update the Last Used, UA and IP columns
        $jNow = Date::getInstance();

        $record->last_used = $jNow->toSql();
        $record->store();

        // Flag the user as fully logged in
        $session = $this->app->getSession();
        $session->set('com_users.mfa_checked', 1);
        $session->set('com_users.mandatory_mfa_setup', 0);

        // Get the return URL stored by the plugin in the session
        $returnUrl = $session->get('com_users.return_url', '');

        // If the return URL is not set or not internal to this site redirect to the site's front page
        if (empty($returnUrl) || !Uri::isInternal($returnUrl)) {
            $returnUrl = Uri::base();
        }

        $this->setRedirect($returnUrl);

        $event = new NotifyActionLog('onComUsersCaptiveValidateSuccess', [$record->title]);
        $this->app->getDispatcher()->dispatch($event->getName(), $event);
    }
}
