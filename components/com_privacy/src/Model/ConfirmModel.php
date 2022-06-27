<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Site\Model;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\UserHelper;
use Joomla\Component\Actionlogs\Administrator\Model\ActionlogModel;
use Joomla\Component\Messages\Administrator\Model\MessageModel;
use Joomla\Component\Privacy\Administrator\Table\RequestTable;
use Joomla\Database\Exception\ExecutionFailureException;

/**
 * Request confirmation model class.
 *
 * @since  3.9.0
 */
class ConfirmModel extends AdminModel
{
    /**
     * Confirms the information request.
     *
     * @param   array  $data  The data expected for the form.
     *
     * @return  mixed  Exception | boolean
     *
     * @since   3.9.0
     */
    public function confirmRequest($data)
    {
        // Get the form.
        $form = $this->getForm();

        // Check for an error.
        if ($form instanceof \Exception) {
            return $form;
        }

        // Filter and validate the form data.
        $data = $form->filter($data);
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

        // Get the user email address
        $email = Factory::getUser()->email;

        // Search for the information request
        /** @var RequestTable $table */
        $table = $this->getTable();

        if (!$table->load(['email' => $email, 'status' => 0])) {
            $this->setError(Text::_('COM_PRIVACY_ERROR_NO_PENDING_REQUESTS'));

            return false;
        }

        // A request can only be confirmed if it is in a pending status and has a confirmation token
        if ($table->status != '0' || !$table->confirm_token || $table->confirm_token_created_at === null) {
            $this->setError(Text::_('COM_PRIVACY_ERROR_NO_PENDING_REQUESTS'));

            return false;
        }

        // A request can only be confirmed if the token is less than 24 hours old
        $confirmTokenCreatedAt = new Date($table->confirm_token_created_at);
        $confirmTokenCreatedAt->add(new \DateInterval('P1D'));

        $now = new Date('now');

        if ($now > $confirmTokenCreatedAt) {
            // Invalidate the request
            $table->status = -1;
            $table->confirm_token = '';
            $table->confirm_token_created_at = null;

            try {
                $table->store();
            } catch (ExecutionFailureException $exception) {
                // The error will be logged in the database API, we just need to catch it here to not let things fatal out
            }

            $this->setError(Text::_('COM_PRIVACY_ERROR_CONFIRM_TOKEN_EXPIRED'));

            return false;
        }

        // Verify the token
        if (!UserHelper::verifyPassword($data['confirm_token'], $table->confirm_token)) {
            $this->setError(Text::_('COM_PRIVACY_ERROR_NO_PENDING_REQUESTS'));

            return false;
        }

        // Everything is good to go, transition the request to confirmed
        $saved = $this->save(
            [
                'id'            => $table->id,
                'status'        => 1,
                'confirm_token' => '',
            ]
        );

        if (!$saved) {
            // Error was set by the save method
            return false;
        }

        // Push a notification to the site's super users, deliberately ignoring if this process fails so the below message goes out
        /** @var MessageModel $messageModel */
        $messageModel = Factory::getApplication()->bootComponent('com_messages')->getMVCFactory()->createModel('Message', 'Administrator');

        $messageModel->notifySuperUsers(
            Text::_('COM_PRIVACY_ADMIN_NOTIFICATION_USER_CONFIRMED_REQUEST_SUBJECT'),
            Text::sprintf('COM_PRIVACY_ADMIN_NOTIFICATION_USER_CONFIRMED_REQUEST_MESSAGE', $table->email)
        );

        $message = [
            'action'       => 'request-confirmed',
            'subjectemail' => $table->email,
            'id'           => $table->id,
            'itemlink'     => 'index.php?option=com_privacy&view=request&id=' . $table->id,
        ];

        $this->getActionlogModel()->addLog([$message], 'COM_PRIVACY_ACTION_LOG_CONFIRMED_REQUEST', 'com_privacy.request');

        return true;
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
        $form = $this->loadForm('com_privacy.confirm', 'confirm', ['control' => 'jform']);

        if (empty($form)) {
            return false;
        }

        $input = Factory::getApplication()->input;

        if ($input->getMethod() === 'GET') {
            $form->setValue('confirm_token', '', $input->get->getAlnum('confirm_token'));
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
     * @since   3.9.0
     * @throws  \Exception
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
