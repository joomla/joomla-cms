<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Site\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Privacy\Site\Model\ConfirmModel;
use Joomla\Component\Privacy\Site\Model\RequestModel;

/**
 * Request action controller class.
 *
 * @since  3.9.0
 */
class RequestController extends BaseController
{
    /**
     * Method to confirm the information request.
     *
     * @return  boolean
     *
     * @since   3.9.0
     */
    public function confirm()
    {
        // Check the request token.
        $this->checkToken('post');

        /** @var ConfirmModel $model */
        $model = $this->getModel('Confirm', 'Site');
        $data  = $this->input->post->get('jform', [], 'array');

        $return = $model->confirmRequest($data);

        // Check for a hard error.
        if ($return instanceof \Exception) {
            // Get the error message to display.
            if ($this->app->get('error_reporting')) {
                $message = $return->getMessage();
            } else {
                $message = Text::_('COM_PRIVACY_ERROR_CONFIRMING_REQUEST');
            }

            // Go back to the confirm form.
            $this->setRedirect(Route::_('index.php?option=com_privacy&view=confirm', false), $message, 'error');

            return false;
        } elseif ($return === false) {
            // Confirm failed.
            // Go back to the confirm form.
            $message = Text::sprintf('COM_PRIVACY_ERROR_CONFIRMING_REQUEST_FAILED', $model->getError());
            $this->setRedirect(Route::_('index.php?option=com_privacy&view=confirm', false), $message, 'notice');

            return false;
        } else {
            // Confirm succeeded.
            $this->setRedirect(Route::_(Uri::root()), Text::_('COM_PRIVACY_CONFIRM_REQUEST_SUCCEEDED'), 'info');

            return true;
        }
    }

    /**
     * Method to submit an information request.
     *
     * @return  boolean
     *
     * @since   3.9.0
     */
    public function submit()
    {
        // Check the request token.
        $this->checkToken('post');

        /** @var RequestModel $model */
        $model = $this->getModel('Request', 'Site');
        $data  = $this->input->post->get('jform', [], 'array');

        $return = $model->createRequest($data);

        // Check for a hard error.
        if ($return instanceof \Exception) {
            // Get the error message to display.
            if ($this->app->get('error_reporting')) {
                $message = $return->getMessage();
            } else {
                $message = Text::_('COM_PRIVACY_ERROR_CREATING_REQUEST');
            }

            // Go back to the confirm form.
            $this->setRedirect(Route::_('index.php?option=com_privacy&view=request', false), $message, 'error');

            return false;
        } elseif ($return === false) {
            // Confirm failed.
            // Go back to the confirm form.
            $message = Text::sprintf('COM_PRIVACY_ERROR_CREATING_REQUEST_FAILED', $model->getError());
            $this->setRedirect(Route::_('index.php?option=com_privacy&view=request', false), $message, 'notice');

            return false;
        } else {
            // Confirm succeeded.
            $this->setRedirect(Route::_(Uri::root()), Text::_('COM_PRIVACY_CREATE_REQUEST_SUCCEEDED'), 'info');

            return true;
        }
    }

    /**
     * Method to extend the privacy consent.
     *
     * @return  boolean
     *
     * @since   3.9.0
     */
    public function remind()
    {
        // Check the request token.
        $this->checkToken('post');

        /** @var ConfirmModel $model */
        $model = $this->getModel('Remind', 'Site');
        $data  = $this->input->post->get('jform', [], 'array');

        $return = $model->remindRequest($data);

        // Check for a hard error.
        if ($return instanceof \Exception) {
            // Get the error message to display.
            if ($this->app->get('error_reporting')) {
                $message = $return->getMessage();
            } else {
                $message = Text::_('COM_PRIVACY_ERROR_REMIND_REQUEST');
            }

            // Go back to the confirm form.
            $this->setRedirect(Route::_('index.php?option=com_privacy&view=remind', false), $message, 'error');

            return false;
        } elseif ($return === false) {
            // Confirm failed.
            // Go back to the confirm form.
            $message = Text::sprintf('COM_PRIVACY_ERROR_CONFIRMING_REMIND_FAILED', $model->getError());
            $this->setRedirect(Route::_('index.php?option=com_privacy&view=remind', false), $message, 'notice');

            return false;
        } else {
            // Confirm succeeded.
            $this->setRedirect(Route::_(Uri::root()), Text::_('COM_PRIVACY_CONFIRM_REMIND_SUCCEEDED'), 'info');

            return true;
        }
    }
}
