<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Site\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\User\UserHelper;
use Joomla\Component\Privacy\Administrator\Table\ConsentTable;
use Joomla\Database\Exception\ExecutionFailureException;

/**
 * Remind confirmation model class.
 *
 * @since  3.9.0
 */
class RemindModel extends AdminModel
{
    /**
     * Confirms the remind request.
     *
     * @param   array  $data  The data expected for the form.
     *
     * @return  mixed  \Exception | JException | boolean
     *
     * @since   3.9.0
     */
    public function remindRequest($data)
    {
        // Get the form.
        $form = $this->getForm();
        $data['email'] = PunycodeHelper::emailToPunycode($data['email']);

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

        /** @var ConsentTable $table */
        $table = $this->getTable();

        $db = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['r.id', 'r.user_id', 'r.token']));
        $query->from($db->quoteName('#__privacy_consents', 'r'));
        $query->join(
            'LEFT',
            $db->quoteName('#__users', 'u'),
            $db->quoteName('u.id') . ' = ' . $db->quoteName('r.user_id')
        );
        $query->where($db->quoteName('u.email') . ' = :email')
            ->bind(':email', $data['email']);
        $query->where($db->quoteName('r.remind') . ' = 1');
        $db->setQuery($query);

        try {
            $remind = $db->loadObject();
        } catch (ExecutionFailureException $e) {
            $this->setError(Text::_('COM_PRIVACY_ERROR_NO_PENDING_REMIND'));

            return false;
        }

        if (!$remind) {
            $this->setError(Text::_('COM_PRIVACY_ERROR_NO_PENDING_REMIND'));

            return false;
        }

        // Verify the token
        if (!UserHelper::verifyPassword($data['remind_token'], $remind->token)) {
            $this->setError(Text::_('COM_PRIVACY_ERROR_NO_REMIND_REQUESTS'));

            return false;
        }

        // Everything is good to go, transition the request to extended
        $saved = $this->save(
            [
                'id'      => $remind->id,
                'remind'  => 0,
                'token'   => '',
                'created' => Factory::getDate()->toSql(),
            ]
        );

        if (!$saved) {
            // Error was set by the save method
            return false;
        }

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
        $form = $this->loadForm('com_privacy.remind', 'remind', ['control' => 'jform']);

        if (empty($form)) {
            return false;
        }

        $input = Factory::getApplication()->input;

        if ($input->getMethod() === 'GET') {
            $form->setValue('remind_token', '', $input->get->getAlnum('remind_token'));
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
    public function getTable($name = 'Consent', $prefix = 'Administrator', $options = [])
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
}
