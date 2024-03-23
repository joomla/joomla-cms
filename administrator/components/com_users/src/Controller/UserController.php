<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Controller;

use Joomla\CMS\Access\Access;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * User controller class.
 *
 * @since  1.6
 */
class UserController extends FormController
{
    /**
     * @var    string  The prefix to use with controller messages.
     * @since  1.6
     */
    protected $text_prefix = 'COM_USERS_USER';

    /**
     * Overrides Joomla\CMS\MVC\Controller\FormController::allowEdit
     *
     * Checks that non-Super Admins are not editing Super Admins.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key.
     *
     * @return  boolean  True if allowed, false otherwise.
     *
     * @since   1.6
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        // Check if this person is a Super Admin
        if (Access::check($data[$key], 'core.admin')) {
            // If I'm not a Super Admin, then disallow the edit.
            if (!$this->app->getIdentity()->authorise('core.admin')) {
                return false;
            }
        }

        // Allow users to edit their own account
        if (isset($data[$key]) && (int) $this->app->getIdentity()->id === (int) $data[$key]) {
            return true;
        }

        return parent::allowEdit($data, $key);
    }

    /**
     * Override parent cancel to redirect when using status edit account.
     *
     * @param   string  $key  The name of the primary key of the URL variable.
     *
     * @return  boolean  True if access level checks pass, false otherwise.
     *
     * @since  4.0.0
     */
    public function cancel($key = null)
    {
        $result = parent::cancel();

        if ($return = $this->input->get('return', '', 'BASE64')) {
            $return = base64_decode($return);

            // Don't redirect to an external URL.
            if (!Uri::isInternal($return)) {
                $return = Uri::base();
            }

            $this->app->redirect($return);
        }

        return $result;
    }

    /**
     * Override parent save to redirect when using status edit account.
     *
     * @param   string  $key     The name of the primary key of the URL variable.
     * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return  boolean  True if successful, false otherwise.
     *
     * @since   4.0.0
     */
    public function save($key = null, $urlVar = null)
    {
        $result = parent::save($key, $urlVar);

        $task   = $this->getTask();

        if ($task === 'save' && $return = $this->input->get('return', '', 'BASE64')) {
            $return = base64_decode($return);

            // Don't redirect to an external URL.
            if (!Uri::isInternal($return)) {
                $return = Uri::base();
            }

            $this->setRedirect($return);
        }

        // If a user has to renew a password but has no permission for users
        if ($task === 'save' && !$this->app->getIdentity()->authorise('core.manage', 'com_users')) {
            $this->setRedirect(Uri::base());
        }

        return $result;
    }

    /**
     * Method to run batch operations.
     *
     * @param   object  $model  The model.
     *
     * @return  boolean  True on success, false on failure
     *
     * @since   2.5
     */
    public function batch($model = null)
    {
        $this->checkToken();

        // Set the model
        $model = $this->getModel('User', 'Administrator', []);

        // Preset the redirect
        $this->setRedirect(Route::_('index.php?option=com_users&view=users' . $this->getRedirectToListAppend(), false));

        return parent::batch($model);
    }

    /**
     * Function that allows child controller access to model data after the data has been saved.
     *
     * @param   BaseDatabaseModel  $model      The data model object.
     * @param   array              $validData  The validated data.
     *
     * @return  void
     *
     * @since   3.1
     */
    protected function postSaveHook(BaseDatabaseModel $model, $validData = [])
    {
    }
}
