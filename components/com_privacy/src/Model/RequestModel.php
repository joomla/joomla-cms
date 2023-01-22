<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Site\Model;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mail\Exception\MailDisabledException;
use Joomla\CMS\Mail\MailTemplate;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserHelper;
use Joomla\Component\Actionlogs\Administrator\Model\ActionlogModel;
use Joomla\Component\Messages\Administrator\Model\MessageModel;
use Joomla\Component\Privacy\Administrator\Table\RequestTable;
use Joomla\Database\Exception\ExecutionFailureException;
use PHPMailer\PHPMailer\Exception as phpmailerException;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Request model class.
 *
 * @since  3.9.0
 */
class RequestModel extends AdminModel
{
    /**
     * Creates an information request.
     *
     * @param   array  $data  The data expected for the form.
     *
     * @return  mixed  Exception | boolean
     *
     * @since   3.9.0
     */
    public function createRequest($data)
    {
        $app = Factory::getApplication();

        // Creating requests requires the site's email sending be enabled
        if (!$app->get('mailonline', 1)) {
            $this->setError(Text::_('COM_PRIVACY_ERROR_CANNOT_CREATE_REQUEST_WHEN_SENDMAIL_DISABLED'));

            return false;
        }

        // Get the form.
        $form = $this->getForm();

        // Check for an error.
        if ($form instanceof \Exception) {
            return $form;
        }

        // Filter and validate the form data.
        $data   = $form->filter($data);
        $return = $form->validate($data);

        // Check for an error.
        if ($return instanceof \Exception) {
            return $return;
        }

        // Check the validation results.
        if ($return === false) {
            // Get the validation messages from the form.
            foreach ($form->getErrors() as $formError) {
                $this->setError($formError->getMessage());
            }

            return false;
        }

        $data['email'] = Factory::getUser()->email;

        // Search for an open information request matching the email and type
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(id)')
            ->from($db->quoteName('#__privacy_requests'))
            ->where($db->quoteName('email') . ' = :email')
            ->where($db->quoteName('request_type') . ' = :requesttype')
            ->whereIn($db->quoteName('status'), [0, 1])
            ->bind(':email', $data['email'])
            ->bind(':requesttype', $data['request_type']);

        try {
            $result = (int) $db->setQuery($query)->loadResult();
        } catch (ExecutionFailureException $exception) {
            // Can't check for existing requests, so don't create a new one
            $this->setError(Text::_('COM_PRIVACY_ERROR_CHECKING_FOR_EXISTING_REQUESTS'));

            return false;
        }

        if ($result > 0) {
            $this->setError(Text::_('COM_PRIVACY_ERROR_PENDING_REQUEST_OPEN'));

            return false;
        }

        // Everything is good to go, create the request
        $token       = ApplicationHelper::getHash(UserHelper::genRandomPassword());
        $hashedToken = UserHelper::hashPassword($token);

        $data['confirm_token']            = $hashedToken;
        $data['confirm_token_created_at'] = Factory::getDate()->toSql();

        if (!$this->save($data)) {
            // The save function will set the error message, so just return here
            return false;
        }

        // Push a notification to the site's super users, deliberately ignoring if this process fails so the below message goes out
        /** @var MessageModel $messageModel */
        $messageModel = $app->bootComponent('com_messages')->getMVCFactory()->createModel('Message', 'Administrator');

        $messageModel->notifySuperUsers(
            Text::_('COM_PRIVACY_ADMIN_NOTIFICATION_USER_CREATED_REQUEST_SUBJECT'),
            Text::sprintf('COM_PRIVACY_ADMIN_NOTIFICATION_USER_CREATED_REQUEST_MESSAGE', $data['email'])
        );

        // The mailer can be set to either throw Exceptions or return boolean false, account for both
        try {
            $linkMode = $app->get('force_ssl', 0) == 2 ? Route::TLS_FORCE : Route::TLS_IGNORE;

            $templateData = [
                'sitename' => $app->get('sitename'),
                'url'      => Uri::root(),
                'tokenurl' => Route::link('site', 'index.php?option=com_privacy&view=confirm&confirm_token=' . $token, false, $linkMode, true),
                'formurl'  => Route::link('site', 'index.php?option=com_privacy&view=confirm', false, $linkMode, true),
                'token'    => $token,
            ];

            switch ($data['request_type']) {
                case 'export':
                    $mailer = new MailTemplate('com_privacy.notification.export', $app->getLanguage()->getTag());

                    break;

                case 'remove':
                    $mailer = new MailTemplate('com_privacy.notification.remove', $app->getLanguage()->getTag());

                    break;

                default:
                    $this->setError(Text::_('COM_PRIVACY_ERROR_UNKNOWN_REQUEST_TYPE'));

                    return false;
            }

            $mailer->addTemplateData($templateData);
            $mailer->addRecipient($data['email']);

            $mailer->send();

            /** @var RequestTable $table */
            $table = $this->getTable();

            if (!$table->load($this->getState($this->getName() . '.id'))) {
                $this->setError($table->getError());

                return false;
            }

            // Log the request's creation
            $message = [
                'action'       => 'request-created',
                'requesttype'  => $table->request_type,
                'subjectemail' => $table->email,
                'id'           => $table->id,
                'itemlink'     => 'index.php?option=com_privacy&view=request&id=' . $table->id,
            ];

            $this->getActionlogModel()->addLog([$message], 'COM_PRIVACY_ACTION_LOG_CREATED_REQUEST', 'com_privacy.request');

            // The email sent and the record is saved, everything is good to go from here
            return true;
        } catch (MailDisabledException | phpmailerException $exception) {
            $this->setError($exception->getMessage());

            return false;
        }
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
        return $this->loadForm('com_privacy.request', 'request', ['control' => 'jform']);
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
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    protected function populateState()
    {
        // Get the application object.
        $params = Factory::getApplication()->getParams('com_privacy');

        // Load the parameters.
        $this->setState('params', $params);
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
