<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Site\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

/**
 * Reset controller class for Users.
 *
 * @since  1.6
 */
class ResetController extends BaseController
{
    /**
     * Method to request a password reset.
     *
     * @return  boolean
     *
     * @since   1.6
     */
    public function request()
    {
        // Check the request token.
        $this->checkToken('post');

        $app   = $this->app;

        /** @var \Joomla\Component\Users\Site\Model\ResetModel $model */
        $model = $this->getModel('Reset', 'Site');
        $data  = $this->input->post->get('jform', array(), 'array');

        // Submit the password reset request.
        $return = $model->processResetRequest($data);

        // Check for a hard error.
        if ($return instanceof \Exception && JDEBUG) {
            // Get the error message to display.
            if ($app->get('error_reporting')) {
                $message = $return->getMessage();
            } else {
                $message = Text::_('COM_USERS_RESET_REQUEST_ERROR');
            }

            // Go back to the request form.
            $this->setRedirect(Route::_('index.php?option=com_users&view=reset', false), $message, 'error');

            return false;
        } elseif ($return === false && JDEBUG) {
            // The request failed.
            // Go back to the request form.
            $message = Text::sprintf('COM_USERS_RESET_REQUEST_FAILED', $model->getError());
            $this->setRedirect(Route::_('index.php?option=com_users&view=reset', false), $message, 'notice');

            return false;
        }

        // To not expose if the user exists or not we send a generic message.
        $message = Text::_('COM_USERS_RESET_REQUEST');
        $this->setRedirect(Route::_('index.php?option=com_users&view=reset&layout=confirm', false), $message, 'notice');

        return true;
    }

    /**
     * Method to confirm the password request.
     *
     * @return  boolean
     *
     * @access  public
     * @since   1.6
     */
    public function confirm()
    {
        // Check the request token.
        $this->checkToken('request');

        $app   = $this->app;

        /** @var \Joomla\Component\Users\Site\Model\ResetModel $model */
        $model = $this->getModel('Reset', 'Site');
        $data  = $this->input->get('jform', array(), 'array');

        // Confirm the password reset request.
        $return = $model->processResetConfirm($data);

        // Check for a hard error.
        if ($return instanceof \Exception) {
            // Get the error message to display.
            if ($app->get('error_reporting')) {
                $message = $return->getMessage();
            } else {
                $message = Text::_('COM_USERS_RESET_CONFIRM_ERROR');
            }

            // Go back to the confirm form.
            $this->setRedirect(Route::_('index.php?option=com_users&view=reset&layout=confirm', false), $message, 'error');

            return false;
        } elseif ($return === false) {
            // Confirm failed.
            // Go back to the confirm form.
            $message = Text::sprintf('COM_USERS_RESET_CONFIRM_FAILED', $model->getError());
            $this->setRedirect(Route::_('index.php?option=com_users&view=reset&layout=confirm', false), $message, 'notice');

            return false;
        } else {
            // Confirm succeeded.
            // Proceed to step three.
            $this->setRedirect(Route::_('index.php?option=com_users&view=reset&layout=complete', false));

            return true;
        }
    }

    /**
     * Method to complete the password reset process.
     *
     * @return  boolean
     *
     * @since   1.6
     */
    public function complete()
    {
        // Check for request forgeries
        $this->checkToken('post');

        $app   = $this->app;

        /** @var \Joomla\Component\Users\Site\Model\ResetModel $model */
        $model = $this->getModel('Reset', 'Site');
        $data  = $this->input->post->get('jform', array(), 'array');

        // Complete the password reset request.
        $return = $model->processResetComplete($data);

        // Check for a hard error.
        if ($return instanceof \Exception) {
            // Get the error message to display.
            if ($app->get('error_reporting')) {
                $message = $return->getMessage();
            } else {
                $message = Text::_('COM_USERS_RESET_COMPLETE_ERROR');
            }

            // Go back to the complete form.
            $this->setRedirect(Route::_('index.php?option=com_users&view=reset&layout=complete', false), $message, 'error');

            return false;
        } elseif ($return === false) {
            // Complete failed.
            // Go back to the complete form.
            $message = Text::sprintf('COM_USERS_RESET_COMPLETE_FAILED', $model->getError());
            $this->setRedirect(Route::_('index.php?option=com_users&view=reset&layout=complete', false), $message, 'notice');

            return false;
        } else {
            // Complete succeeded.
            // Proceed to the login form.
            $message = Text::_('COM_USERS_RESET_COMPLETE_SUCCESS');
            $this->setRedirect(Route::_('index.php?option=com_users&view=login', false), $message);

            return true;
        }
    }
}
