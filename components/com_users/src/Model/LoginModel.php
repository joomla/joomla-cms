<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Site\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\MVC\Model\FormModel;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Login model class for Users.
 *
 * @since  1.6
 */
class LoginModel extends FormModel
{
    /**
     * Method to get the login form.
     *
     * The base form is loaded from XML and then an event is fired
     * for users plugins to extend the form with extra fields.
     *
     * @param   array    $data      An optional array of data for the form to interrogate.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form    A Form object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_users.login', 'login', ['load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  array  The default data is an empty array.
     *
     * @since   1.6
     * @throws  \Exception
     */
    protected function loadFormData()
    {
        // Check the session for previously entered login form data.
        $app  = Factory::getApplication();
        $data = $app->getUserState('users.login.form.data', []);

        $input = $app->getInput()->getInputForRequestMethod();

        // Check for return URL from the request first
        if ($return = $input->get('return', '', 'BASE64')) {
            $data['return'] = base64_decode($return);

            if (!Uri::isInternal($data['return'])) {
                $data['return'] = '';
            }
        }

        $app->setUserState('users.login.form.data', $data);

        $this->preprocessData('com_users.login', $data);

        return $data;
    }

    /**
     * Method to auto-populate the model state.
     *
     * Calling getState in this method will result in recursion.
     *
     * @return  void
     *
     * @since   1.6
     * @throws  \Exception
     */
    protected function populateState()
    {
        // Get the application object.
        $params = Factory::getApplication()->getParams('com_users');

        // Load the parameters.
        $this->setState('params', $params);
    }

    /**
     * Override Joomla\CMS\MVC\Model\AdminModel::preprocessForm to ensure the correct plugin group is loaded.
     *
     * @param   Form    $form   A Form object.
     * @param   mixed   $data   The data expected for the form.
     * @param   string  $group  The name of the plugin group to import (defaults to "content").
     *
     * @return  void
     *
     * @since   1.6
     * @throws  \Exception if there is an error in the form event.
     */
    protected function preprocessForm(Form $form, $data, $group = 'user')
    {
        parent::preprocessForm($form, $data, $group);
    }

    /**
     * Returns the language for the given menu id.
     *
     * @param  int  $id  The menu id
     *
     * @return string
     *
     * @since  4.2.0
     */
    public function getMenuLanguage(int $id): string
    {
        if (!Multilanguage::isEnabled()) {
            return '';
        }

        $db    = $this->getDatabase();
        $query = $db->createQuery()
            ->select($db->quoteName('language'))
            ->from($db->quoteName('#__menu'))
            ->where($db->quoteName('client_id') . ' = 0')
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);

        $db->setQuery($query);

        try {
            return $db->loadResult();
        } catch (\RuntimeException) {
            return '';
        }
    }
}
