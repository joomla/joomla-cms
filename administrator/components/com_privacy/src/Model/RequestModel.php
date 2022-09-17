<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Administrator\Model;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\Exception\MailDisabledException;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;
use Joomla\Component\Actionlogs\Administrator\Model\ActionlogModel;
use Joomla\Component\Privacy\Administrator\Table\RequestTable;
use Joomla\Database\Exception\ExecutionFailureException;
use PHPMailer\PHPMailer\Exception as phpmailerException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Request item model class.
 *
 * @since  3.9.0
 */
class RequestModel extends AdminModel
{
    /**
     * Clean the cache
     *
     * @param   string  $group  The cache group
     *
     * @return  void
     *
     * @since   3.9.0
     */
    protected function cleanCache($group = 'com_privacy')
    {
        parent::cleanCache('com_privacy');
    }

    /**
     * Method for getting the form from the model.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form|boolean  A Form object on success, false on failure
     *
     * @since   3.9.0
     */
    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_privacy.request', 'request', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $name     The table name. Optional.
     * @param   string  $prefix   The class prefix. Optional.
     * @param   array   $options  Configuration array for model. Optional.
     *
     * @return  Table  A Table object
     *
     * @throws  \Exception
     * @since   3.9.0
     */
    public function getTable($name = 'Request', $prefix = 'Administrator', $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  array  The default data is an empty array.
     *
     * @since   3.9.0
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_privacy.edit.request.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * Log the completion of a request to the action log system.
     *
     * @param   integer  $id  The ID of the request to process.
     *
     * @return  boolean
     *
     * @since   3.9.0
     */
    public function logRequestCompleted($id)
    {
        /** @var RequestTable $table */
        $table = $this->getTable();

        if (!$table->load($id)) {
            $this->setError($table->getError());

            return false;
        }

        $user = Factory::getUser();

        $message = [
            'action'       => 'request-completed',
            'requesttype'  => $table->request_type,
            'subjectemail' => $table->email,
            'id'           => $table->id,
            'itemlink'     => 'index.php?option=com_privacy&view=request&id=' . $table->id,
            'userid'       => $user->id,
            'username'     => $user->username,
            'accountlink'  => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
        ];

        $this->getActionlogModel()->addLog([$message], 'COM_PRIVACY_ACTION_LOG_ADMIN_COMPLETED_REQUEST', 'com_privacy.request', $user->id);

        return true;
    }

    /**
     * Log the creation of a request to the action log system.
     *
     * @param   integer  $id  The ID of the request to process.
     *
     * @return  boolean
     *
     * @since   3.9.0
     */
    public function logRequestCreated($id)
    {
        /** @var RequestTable $table */
        $table = $this->getTable();

        if (!$table->load($id)) {
            $this->setError($table->getError());

            return false;
        }

        $user = Factory::getUser();

        $message = [
            'action'       => 'request-created',
            'requesttype'  => $table->request_type,
            'subjectemail' => $table->email,
            'id'           => $table->id,
            'itemlink'     => 'index.php?option=com_privacy&view=request&id=' . $table->id,
            'userid'       => $user->id,
            'username'     => $user->username,
            'accountlink'  => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
        ];

        $this->getActionlogModel()->addLog([$message], 'COM_PRIVACY_ACTION_LOG_ADMIN_CREATED_REQUEST', 'com_privacy.request', $user->id);

        return true;
    }

    /**
     * Log the invalidation of a request to the action log system.
     *
     * @param   integer  $id  The ID of the request to process.
     *
     * @return  boolean
     *
     * @since   3.9.0
     */
    public function logRequestInvalidated($id)
    {
        /** @var RequestTable $table */
        $table = $this->getTable();

        if (!$table->load($id)) {
            $this->setError($table->getError());

            return false;
        }

        $user = Factory::getUser();

        $message = [
            'action'       => 'request-invalidated',
            'requesttype'  => $table->request_type,
            'subjectemail' => $table->email,
            'id'           => $table->id,
            'itemlink'     => 'index.php?option=com_privacy&view=request&id=' . $table->id,
            'userid'       => $user->id,
            'username'     => $user->username,
            'accountlink'  => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
        ];

        $this->getActionlogModel()->addLog([$message], 'COM_PRIVACY_ACTION_LOG_ADMIN_INVALIDATED_REQUEST', 'com_privacy.request', $user->id);

        return true;
    }

    /**
     * Notifies the user that an information request has been created by a site administrator.
     *
     * Because confirmation tokens are stored in the database as a hashed value, this method will generate a new confirmation token
     * for the request.
     *
     * @param   integer  $id  The ID of the request to process.
     *
     * @return  boolean
     *
     * @since   3.9.0
     */
    public function notifyUserAdminCreatedRequest($id)
    {
        /** @var RequestTable $table */
        $table = $this->getTable();

        if (!$table->load($id)) {
            $this->setError($table->getError());

            return false;
        }

        /*
         * If there is an associated user account, we will attempt to send this email in the user's preferred language.
         * Because of this, it is expected that Language::_() is directly called and that the Text class is NOT used
         * for translating all messages.
         *
         * Error messages will still be displayed to the administrator, so those messages should continue to use the Text class.
         */

        $lang = Factory::getLanguage();

        $db = $this->getDatabase();

        $userId = (int) $db->setQuery(
            $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__users'))
                ->where('LOWER(' . $db->quoteName('email') . ') = LOWER(:email)')
                ->bind(':email', $table->email)
                ->setLimit(1)
        )->loadResult();

        if ($userId) {
            $receiver = User::getInstance($userId);

            /*
             * We don't know if the user has admin access, so we will check if they have an admin language in their parameters,
             * falling back to the site language, falling back to the currently active language
             */

            $langCode = $receiver->getParam('admin_language', '');

            if (!$langCode) {
                $langCode = $receiver->getParam('language', $lang->getTag());
            }

            $lang = Language::getInstance($langCode, $lang->getDebug());
        }

        // Ensure the right language files have been loaded
        $lang->load('com_privacy', JPATH_ADMINISTRATOR)
            || $lang->load('com_privacy', JPATH_ADMINISTRATOR . '/components/com_privacy');

        // Regenerate the confirmation token
        $token       = ApplicationHelper::getHash(UserHelper::genRandomPassword());
        $hashedToken = UserHelper::hashPassword($token);

        $table->confirm_token            = $hashedToken;
        $table->confirm_token_created_at = Factory::getDate()->toSql();

        try {
            $table->store();
        } catch (ExecutionFailureException $exception) {
            $this->setError($exception->getMessage());

            return false;
        }

        // The mailer can be set to either throw Exceptions or return boolean false, account for both
        try {
            $app = Factory::getApplication();

            $linkMode = $app->get('force_ssl', 0) == 2 ? Route::TLS_FORCE : Route::TLS_IGNORE;

            $templateData = [
                'sitename' => $app->get('sitename'),
                'url'      => Uri::root(),
                'tokenurl' => Route::link('site', 'index.php?option=com_privacy&view=confirm&confirm_token=' . $token, false, $linkMode, true),
                'formurl'  => Route::link('site', 'index.php?option=com_privacy&view=confirm', false, $linkMode, true),
                'token'    => $token,
            ];

            switch ($table->request_type) {
                case 'export':
                    $mailer = new MailTemplate('com_privacy.notification.admin.export', $app->getLanguage()->getTag());

                    break;

                case 'remove':
                    $mailer = new MailTemplate('com_privacy.notification.admin.remove', $app->getLanguage()->getTag());

                    break;

                default:
                    $this->setError(Text::_('COM_PRIVACY_ERROR_UNKNOWN_REQUEST_TYPE'));

                    return false;
            }

            $mailer->addTemplateData($templateData);
            $mailer->addRecipient($table->email);

            $mailer->send();

            return true;
        } catch (MailDisabledException | phpmailerException $exception) {
            $this->setError($exception->getMessage());

            return false;
        }
    }

    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  True on success, False on error.
     *
     * @since   3.9.0
     */
    public function save($data)
    {
        $table = $this->getTable();
        $key   = $table->getKeyName();
        $pk    = !empty($data[$key]) ? $data[$key] : (int) $this->getState($this->getName() . '.id');

        if (!$pk && !Factory::getApplication()->get('mailonline', 1)) {
            $this->setError(Text::_('COM_PRIVACY_ERROR_CANNOT_CREATE_REQUEST_WHEN_SENDMAIL_DISABLED'));

            return false;
        }

        return parent::save($data);
    }

    /**
     * Method to validate the form data.
     *
     * @param   Form    $form   The form to validate against.
     * @param   array   $data   The data to validate.
     * @param   string  $group  The name of the field group to validate.
     *
     * @return  array|boolean  Array of filtered data if valid, false otherwise.
     *
     * @see     \Joomla\CMS\Form\FormRule
     * @see     JFilterInput
     * @since   3.9.0
     */
    public function validate($form, $data, $group = null)
    {
        $validatedData = parent::validate($form, $data, $group);

        // If parent validation failed there's no point in doing our extended validation
        if ($validatedData === false) {
            return false;
        }

        // Make sure the status is always 0
        $validatedData['status'] = 0;

        // The user cannot create a request for their own account
        if (strtolower(Factory::getUser()->email) === strtolower($validatedData['email'])) {
            $this->setError(Text::_('COM_PRIVACY_ERROR_CANNOT_CREATE_REQUEST_FOR_SELF'));

            return false;
        }

        // Check for an active request for this email address
        $db = $this->getDatabase();

        $query = $db->getQuery(true)
            ->select('COUNT(id)')
            ->from($db->quoteName('#__privacy_requests'))
            ->where($db->quoteName('email') . ' = :email')
            ->where($db->quoteName('request_type') . ' = :requesttype')
            ->whereIn($db->quoteName('status'), [0, 1])
            ->bind(':email', $validatedData['email'])
            ->bind(':requesttype', $validatedData['request_type']);

        $activeRequestCount = (int) $db->setQuery($query)->loadResult();

        if ($activeRequestCount > 0) {
            $this->setError(Text::_('COM_PRIVACY_ERROR_ACTIVE_REQUEST_FOR_EMAIL'));

            return false;
        }

        return $validatedData;
    }

    /**
     * Method to fetch an instance of the action log model.
     *
     * @return  ActionlogModel
     *
     * @since   4.0.0
     */
    private function getActionlogModel(): ActionlogModel
    {
        return Factory::getApplication()->bootComponent('com_actionlogs')
            ->getMVCFactory()->createModel('Actionlog', 'Administrator', ['ignore_request' => true]);
    }
}
