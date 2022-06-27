<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Privacy\Administrator\Model\ExportModel;
use Joomla\Component\Privacy\Administrator\Model\RemoveModel;
use Joomla\Component\Privacy\Administrator\Model\RequestModel;
use Joomla\Component\Privacy\Administrator\Table\RequestTable;

/**
 * Request management controller class.
 *
 * @since  3.9.0
 */
class RequestController extends FormController
{
    /**
     * Method to complete a request.
     *
     * @param   string  $key     The name of the primary key of the URL variable.
     * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return  boolean
     *
     * @since   3.9.0
     */
    public function complete($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        $this->checkToken();

        /** @var RequestModel $model */
        $model = $this->getModel();

        /** @var RequestTable $table */
        $table = $model->getTable();

        // Determine the name of the primary key for the data.
        if (empty($key)) {
            $key = $table->getKeyName();
        }

        // To avoid data collisions the urlVar may be different from the primary key.
        if (empty($urlVar)) {
            $urlVar = $key;
        }

        $recordId = $this->input->getInt($urlVar);

        $item = $model->getItem($recordId);

        // Ensure this record can transition to the requested state
        if (!$this->canTransition($item, '2')) {
            $this->setMessage(Text::_('COM_PRIVACY_ERROR_COMPLETE_TRANSITION_NOT_PERMITTED'), 'error');

            $this->setRedirect(
                Route::_(
                    'index.php?option=com_privacy&view=request&id=' . $recordId,
                    false
                )
            );

            return false;
        }

        // Build the data array for the update
        $data = [
            $key     => $recordId,
            'status' => '2',
        ];

        // Access check.
        if (!$this->allowSave($data, $key)) {
            $this->setMessage(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

            $this->setRedirect(
                Route::_(
                    'index.php?option=com_privacy&view=request&id=' . $recordId,
                    false
                )
            );

            return false;
        }

        // Attempt to save the data.
        if (!$model->save($data)) {
            // Redirect back to the edit screen.
            $this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'error');

            $this->setRedirect(
                Route::_(
                    'index.php?option=com_privacy&view=request&id=' . $recordId,
                    false
                )
            );

            return false;
        }

        // Log the request completed
        $model->logRequestCompleted($recordId);

        $this->setMessage(Text::_('COM_PRIVACY_REQUEST_COMPLETED'));

        $url = 'index.php?option=com_privacy&view=requests';

        // Check if there is a return value
        $return = $this->input->get('return', null, 'base64');

        if (!is_null($return) && Uri::isInternal(base64_decode($return))) {
            $url = base64_decode($return);
        }

        // Redirect to the list screen.
        $this->setRedirect(Route::_($url, false));

        return true;
    }

    /**
     * Method to email the data export for a request.
     *
     * @return  boolean
     *
     * @since   3.9.0
     */
    public function emailexport()
    {
        // Check for request forgeries.
        $this->checkToken('get');

        /** @var ExportModel $model */
        $model = $this->getModel('Export');

        $recordId = $this->input->getUint('id');

        if (!$model->emailDataExport($recordId)) {
            // Redirect back to the edit screen.
            $this->setMessage(Text::sprintf('COM_PRIVACY_ERROR_EXPORT_EMAIL_FAILED', $model->getError()), 'error');
        } else {
            $this->setMessage(Text::_('COM_PRIVACY_EXPORT_EMAILED'));
        }

        $url = 'index.php?option=com_privacy&view=requests';

        // Check if there is a return value
        $return = $this->input->get('return', null, 'base64');

        if (!is_null($return) && Uri::isInternal(base64_decode($return))) {
            $url = base64_decode($return);
        }

        // Redirect to the list screen.
        $this->setRedirect(Route::_($url, false));

        return true;
    }

    /**
     * Method to export the data for a request.
     *
     * @return  $this
     *
     * @since   3.9.0
     */
    public function export()
    {
        $this->input->set('view', 'export');

        return $this->display();
    }

    /**
     * Method to invalidate a request.
     *
     * @param   string  $key     The name of the primary key of the URL variable.
     * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return  boolean
     *
     * @since   3.9.0
     */
    public function invalidate($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        $this->checkToken();

        /** @var RequestModel $model */
        $model = $this->getModel();

        /** @var RequestTable $table */
        $table = $model->getTable();

        // Determine the name of the primary key for the data.
        if (empty($key)) {
            $key = $table->getKeyName();
        }

        // To avoid data collisions the urlVar may be different from the primary key.
        if (empty($urlVar)) {
            $urlVar = $key;
        }

        $recordId = $this->input->getInt($urlVar);

        $item = $model->getItem($recordId);

        // Ensure this record can transition to the requested state
        if (!$this->canTransition($item, '-1')) {
            $this->setMessage(Text::_('COM_PRIVACY_ERROR_INVALID_TRANSITION_NOT_PERMITTED'), 'error');

            $this->setRedirect(
                Route::_(
                    'index.php?option=com_privacy&view=request&id=' . $recordId,
                    false
                )
            );

            return false;
        }

        // Build the data array for the update
        $data = [
            $key     => $recordId,
            'status' => '-1',
        ];

        // Access check.
        if (!$this->allowSave($data, $key)) {
            $this->setMessage(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

            $this->setRedirect(
                Route::_(
                    'index.php?option=com_privacy&view=request&id=' . $recordId,
                    false
                )
            );

            return false;
        }

        // Attempt to save the data.
        if (!$model->save($data)) {
            // Redirect back to the edit screen.
            $this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'error');

            $this->setRedirect(
                Route::_(
                    'index.php?option=com_privacy&view=request&id=' . $recordId,
                    false
                )
            );

            return false;
        }

        // Log the request invalidated
        $model->logRequestInvalidated($recordId);

        $this->setMessage(Text::_('COM_PRIVACY_REQUEST_INVALIDATED'));

        $url = 'index.php?option=com_privacy&view=requests';

        // Check if there is a return value
        $return = $this->input->get('return', null, 'base64');

        if (!is_null($return) && Uri::isInternal(base64_decode($return))) {
            $url = base64_decode($return);
        }

        // Redirect to the list screen.
        $this->setRedirect(Route::_($url, false));

        return true;
    }

    /**
     * Method to remove the user data for a privacy remove request.
     *
     * @return  boolean
     *
     * @since   3.9.0
     */
    public function remove()
    {
        // Check for request forgeries.
        $this->checkToken('request');

        /** @var RemoveModel $model */
        $model = $this->getModel('Remove');

        $recordId = $this->input->getUint('id');

        if (!$model->removeDataForRequest($recordId)) {
            // Redirect back to the edit screen.
            $this->setMessage(Text::sprintf('COM_PRIVACY_ERROR_REMOVE_DATA_FAILED', $model->getError()), 'error');

            $this->setRedirect(
                Route::_(
                    'index.php?option=com_privacy&view=request&id=' . $recordId,
                    false
                )
            );

            return false;
        }

        $this->setMessage(Text::_('COM_PRIVACY_DATA_REMOVED'));

        $url = 'index.php?option=com_privacy&view=requests';

        // Check if there is a return value
        $return = $this->input->get('return', null, 'base64');

        if (!is_null($return) && Uri::isInternal(base64_decode($return))) {
            $url = base64_decode($return);
        }

        // Redirect to the list screen.
        $this->setRedirect(Route::_($url, false));

        return true;
    }

    /**
     * Function that allows child controller access to model data after the data has been saved.
     *
     * @param   BaseDatabaseModel  $model      The data model object.
     * @param   array              $validData  The validated data.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    protected function postSaveHook(BaseDatabaseModel $model, $validData = [])
    {
        // This hook only processes new items
        if (!$model->getState($model->getName() . '.new', false)) {
            return;
        }

        if (!$model->logRequestCreated($model->getState($model->getName() . '.id'))) {
            if ($error = $model->getError()) {
                $this->app->enqueueMessage($error, 'warning');
            }
        }

        if (!$model->notifyUserAdminCreatedRequest($model->getState($model->getName() . '.id'))) {
            if ($error = $model->getError()) {
                $this->app->enqueueMessage($error, 'warning');
            }
        } else {
            $this->app->enqueueMessage(Text::_('COM_PRIVACY_MSG_CONFIRM_EMAIL_SENT_TO_USER'));
        }
    }

    /**
     * Method to determine if an item can transition to the specified status.
     *
     * @param   object  $item       The item being updated.
     * @param   string  $newStatus  The new status of the item.
     *
     * @return  boolean
     *
     * @since   3.9.0
     */
    private function canTransition($item, $newStatus)
    {
        switch ($item->status) {
            case '0':
                // A pending item can only move to invalid through this controller due to the requirement for a user to confirm the request
                return $newStatus === '-1';

            case '1':
                // A confirmed item can be marked completed or invalid
                return in_array($newStatus, ['-1', '2'], true);

            // An item which is already in an invalid or complete state cannot transition, likewise if we don't know the state don't change anything
            case '-1':
            case '2':
            default:
                return false;
        }
    }
}
