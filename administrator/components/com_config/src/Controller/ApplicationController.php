<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Administrator\Controller;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Input\Input;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Controller for global configuration
 *
 * @since  1.5
 */
class ApplicationController extends BaseController
{
    /**
     * Constructor.
     *
     * @param   array                $config   An optional associative array of configuration settings.
     * Recognized key values include 'name', 'default_task', 'model_path', and
     * 'view_path' (this list is not meant to be comprehensive).
     * @param   MVCFactoryInterface  $factory  The factory.
     * @param   CMSApplication       $app      The Application for the dispatcher
     * @param   Input                $input    Input
     *
     * @since   3.0
     */
    public function __construct($config = [], MVCFactoryInterface $factory = null, $app = null, $input = null)
    {
        parent::__construct($config, $factory, $app, $input);

        // Map the apply task to the save method.
        $this->registerTask('apply', 'save');
    }

    /**
     * Cancel operation.
     *
     * @return  void
     *
     * @since   3.0.0
     */
    public function cancel()
    {
        $this->setRedirect(Route::_('index.php?option=com_cpanel'));
    }

    /**
     * Saves the form
     *
     * @return  void|boolean  Void on success. Boolean false on fail.
     *
     * @since  4.0.0
     */
    public function save()
    {
        // Check for request forgeries.
        $this->checkToken();

        // Check if the user is authorized to do this.
        if (!$this->app->getIdentity()->authorise('core.admin')) {
            $this->setRedirect('index.php', Text::_('JERROR_ALERTNOAUTHOR'), 'error');

            return false;
        }

        $this->app->setUserState('com_config.config.global.data', null);

        /** @var \Joomla\Component\Config\Administrator\Model\ApplicationModel $model */
        $model = $this->getModel('Application', 'Administrator');

        $data  = $this->input->post->get('jform', [], 'array');

        // Complete data array if needed
        $oldData = $model->getData();
        $data    = array_replace($oldData, $data);

        // Get request type
        $saveFormat = $this->app->getDocument()->getType();

        // Handle service requests
        if ($saveFormat == 'json') {
            $form   = $model->getForm();
            $return = $model->validate($form, $data);

            if ($return === false) {
                $this->app->setHeader('Status', 422, true);

                return false;
            }

            return $model->save($return);
        }

        // Must load after serving service-requests
        $form = $model->getForm();

        // Validate the posted data.
        $return = $model->validate($form, $data);

        // Check for validation errors.
        if ($return === false) {
            // Get the validation messages.
            $errors = $model->getErrors();

            // Push up to three validation messages out to the user.
            for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
                if ($errors[$i] instanceof \Exception) {
                    $this->app->enqueueMessage($errors[$i]->getMessage(), 'warning');
                } else {
                    $this->app->enqueueMessage($errors[$i], 'warning');
                }
            }

            // Save the posted data in the session.
            $this->app->setUserState('com_config.config.global.data', $data);

            // Redirect back to the edit screen.
            $this->setRedirect(Route::_('index.php?option=com_config', false));

            return false;
        }

        // Validate database connection data.
        $data   = $return;
        $return = $model->validateDbConnection($data);

        // Check for validation errors.
        if ($return === false) {
            /*
             * The validateDbConnection method enqueued all messages for us.
             */

            // Save the posted data in the session.
            $this->app->setUserState('com_config.config.global.data', $data);

            // Redirect back to the edit screen.
            $this->setRedirect(Route::_('index.php?option=com_config', false));

            return false;
        }

        // Save the validated data in the session.
        $this->app->setUserState('com_config.config.global.data', $return);

        // Attempt to save the configuration.
        $data   = $return;
        $return = $model->save($data);

        // Check the return value.
        if ($return === false) {
            /*
             * The save method enqueued all messages for us, so we just need to redirect back.
             */

            // Save failed, go back to the screen and display a notice.
            $this->setRedirect(Route::_('index.php?option=com_config', false));

            return false;
        }

        // Set the success message.
        $this->app->enqueueMessage(Text::_('COM_CONFIG_SAVE_SUCCESS'), 'message');

        // Set the redirect based on the task.
        switch ($this->input->getCmd('task')) {
            case 'apply':
                $this->setRedirect(Route::_('index.php?option=com_config', false));
                break;

            case 'save':
            default:
                $this->setRedirect(Route::_('index.php', false));
                break;
        }
    }

    /**
     * Method to remove root in global configuration.
     *
     * @return  boolean
     *
     * @since   3.2
     */
    public function removeroot()
    {
        // Check for request forgeries.
        if (!Session::checkToken('get')) {
            $this->setRedirect('index.php', Text::_('JINVALID_TOKEN'), 'error');

            return false;
        }

        // Check if the user is authorized to do this.
        if (!$this->app->getIdentity()->authorise('core.admin')) {
            $this->setRedirect('index.php', Text::_('JERROR_ALERTNOAUTHOR'), 'error');

            return false;
        }

        // Initialise model.

        /** @var \Joomla\Component\Config\Administrator\Model\ApplicationModel $model */
        $model = $this->getModel('Application', 'Administrator');

        // Attempt to save the configuration and remove root.
        try {
            $model->removeroot();
        } catch (\RuntimeException $e) {
            // Save failed, go back to the screen and display a notice.
            $this->setRedirect('index.php', Text::_('JERROR_SAVE_FAILED', $e->getMessage()), 'error');

            return false;
        }

        // Set the redirect based on the task.
        $this->setRedirect(Route::_('index.php'), Text::_('COM_CONFIG_SAVE_SUCCESS'));

        return true;
    }

    /**
     * Method to send the test mail.
     *
     * @return  void
     *
     * @since   3.5
     */
    public function sendtestmail()
    {
        // Send json mime type.
        $this->app->mimeType = 'application/json';
        $this->app->setHeader('Content-Type', $this->app->mimeType . '; charset=' . $this->app->charSet);
        $this->app->sendHeaders();

        // Check if user token is valid.
        if (!Session::checkToken()) {
            $this->app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
            echo new JsonResponse();
            $this->app->close();
        }

        // Check if the user is authorized to do this.
        if (!$this->app->getIdentity()->authorise('core.admin')) {
            $this->app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
            echo new JsonResponse();
            $this->app->close();
        }

        /** @var \Joomla\Component\Config\Administrator\Model\ApplicationModel $model */
        $model = $this->getModel('Application', 'Administrator');

        echo new JsonResponse($model->sendTestMail());

        $this->app->close();
    }

    /**
     * Method to GET permission value and give it to the model for storing in the database.
     *
     * @return  void
     *
     * @since   3.5
     */
    public function store()
    {
        // Send json mime type.
        $this->app->mimeType = 'application/json';
        $this->app->setHeader('Content-Type', $this->app->mimeType . '; charset=' . $this->app->charSet);
        $this->app->sendHeaders();

        // Check if user token is valid.
        if (!Session::checkToken('get')) {
            $this->app->enqueueMessage(Text::_('JINVALID_TOKEN'), 'error');
            echo new JsonResponse();
            $this->app->close();
        }

        /** @var \Joomla\Component\Config\Administrator\Model\ApplicationModel $model */
        $model = $this->getModel('Application', 'Administrator');
        echo new JsonResponse($model->storePermissions());
        $this->app->close();
    }
}
