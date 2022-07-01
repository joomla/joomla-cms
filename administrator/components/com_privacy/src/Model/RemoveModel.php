<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Administrator\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\User;
use Joomla\Component\Actionlogs\Administrator\Model\ActionlogModel;
use Joomla\Component\Privacy\Administrator\Removal\Status;
use Joomla\Component\Privacy\Administrator\Table\RequestTable;

/**
 * Remove model class.
 *
 * @since  3.9.0
 */
class RemoveModel extends BaseDatabaseModel
{
    /**
     * Remove the user data.
     *
     * @param   integer  $id  The request ID to process
     *
     * @return  boolean
     *
     * @since   3.9.0
     */
    public function removeDataForRequest($id = null)
    {
        $id = !empty($id) ? $id : (int) $this->getState($this->getName() . '.request_id');

        if (!$id) {
            $this->setError(Text::_('COM_PRIVACY_ERROR_REQUEST_ID_REQUIRED_FOR_REMOVE'));

            return false;
        }

        /** @var RequestTable $table */
        $table = $this->getTable();

        if (!$table->load($id)) {
            $this->setError($table->getError());

            return false;
        }

        if ($table->request_type !== 'remove') {
            $this->setError(Text::_('COM_PRIVACY_ERROR_REQUEST_TYPE_NOT_REMOVE'));

            return false;
        }

        if ($table->status != 1) {
            $this->setError(Text::_('COM_PRIVACY_ERROR_CANNOT_REMOVE_UNCONFIRMED_REQUEST'));

            return false;
        }

        // If there is a user account associated with the email address, load it here for use in the plugins
        $db = $this->getDatabase();

        $userId = (int) $db->setQuery(
            $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__users'))
                ->where('LOWER(' . $db->quoteName('email') . ') = LOWER(:email)')
                ->bind(':email', $table->email)
                ->setLimit(1)
        )->loadResult();

        $user = $userId ? User::getInstance($userId) : null;

        $canRemove = true;

        PluginHelper::importPlugin('privacy');

        /** @var Status[] $pluginResults */
        $pluginResults = Factory::getApplication()->triggerEvent('onPrivacyCanRemoveData', [$table, $user]);

        foreach ($pluginResults as $status) {
            if (!$status->canRemove) {
                $this->setError($status->reason ?: Text::_('COM_PRIVACY_ERROR_CANNOT_REMOVE_DATA'));

                $canRemove = false;
            }
        }

        if (!$canRemove) {
            $this->logRemoveBlocked($table, $this->getErrors());

            return false;
        }

        // Log the removal
        $this->logRemove($table);

        Factory::getApplication()->triggerEvent('onPrivacyRemoveData', [$table, $user]);

        return true;
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
     * Log the data removal to the action log system.
     *
     * @param   RequestTable  $request  The request record being processed
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function logRemove(RequestTable $request)
    {
        $user = Factory::getUser();

        $message = [
            'action'      => 'remove',
            'id'          => $request->id,
            'itemlink'    => 'index.php?option=com_privacy&view=request&id=' . $request->id,
            'userid'      => $user->id,
            'username'    => $user->username,
            'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
        ];

        $this->getActionlogModel()->addLog([$message], 'COM_PRIVACY_ACTION_LOG_REMOVE', 'com_privacy.request', $user->id);
    }

    /**
     * Log the data removal being blocked to the action log system.
     *
     * @param   RequestTable  $request  The request record being processed
     * @param   string[]      $reasons  The reasons given why the record could not be removed.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function logRemoveBlocked(RequestTable $request, array $reasons)
    {
        $user = Factory::getUser();

        $message = [
            'action'      => 'remove-blocked',
            'id'          => $request->id,
            'itemlink'    => 'index.php?option=com_privacy&view=request&id=' . $request->id,
            'userid'      => $user->id,
            'username'    => $user->username,
            'accountlink' => 'index.php?option=com_users&task=user.edit&id=' . $user->id,
            'reasons'     => implode('; ', $reasons),
        ];

        $this->getActionlogModel()->addLog([$message], 'COM_PRIVACY_ACTION_LOG_REMOVE_BLOCKED', 'com_privacy.request', $user->id);
    }

    /**
     * Method to auto-populate the model state.
     *
     * @return  void
     *
     * @since   3.9.0
     */
    protected function populateState()
    {
        // Get the pk of the record from the request.
        $this->setState($this->getName() . '.request_id', Factory::getApplication()->input->getUint('id'));

        // Load the parameters.
        $this->setState('params', ComponentHelper::getParams('com_privacy'));
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
