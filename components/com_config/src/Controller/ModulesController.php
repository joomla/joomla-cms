<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Site\Controller;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Client\ClientHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Modules\Administrator\Controller\ModuleController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Component Controller
 *
 * @since  1.5
 */
class ModulesController extends BaseController
{
    /**
     * @param   array                         $config   An optional associative array of configuration settings.
     *                                                  Recognized key values include 'name', 'default_task', 'model_path', and
     *                                                  'view_path' (this list is not meant to be comprehensive).
     * @param   MVCFactoryInterface|null      $factory  The factory.
     * @param   CMSApplication|null           $app      The Application for the dispatcher
     * @param   \Joomla\CMS\Input\Input|null  $input    The Input object for the request
     *
     * @since   1.6
     */
    public function __construct($config = [], MVCFactoryInterface $factory = null, $app = null, $input = null)
    {
        parent::__construct($config, $factory, $app, $input);

        $this->registerTask('apply', 'save');
    }

    /**
     * Method to handle cancel
     *
     * @return  void
     *
     * @since   3.2
     */
    public function cancel()
    {
        // Redirect back to home(base) page
        $this->setRedirect(Uri::base());
    }

    /**
     * Method to save module editing.
     *
     * @return  void
     *
     * @since   3.2
     */
    public function save()
    {
        // Check for request forgeries.
        $this->checkToken();

        // Check if the user is authorized to do this.
        $user = $this->app->getIdentity();

        if (!$user->authorise('module.edit.frontend', 'com_modules.module.' . $this->input->get('id'))) {
            $this->app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
            $this->app->redirect('index.php');
        }

        // Set FTP credentials, if given.
        ClientHelper::setCredentialsFromRequest('ftp');

        // Get submitted module id
        $moduleId = '&id=' . $this->input->getInt('id');

        // Get returnUri
        $returnUri = $this->input->post->get('return', null, 'base64');
        $redirect = '';

        if (!empty($returnUri)) {
            $redirect = '&return=' . $returnUri;
        }

        /** @var AdministratorApplication $app */
        $app = Factory::getContainer()->get(AdministratorApplication::class);

        // Reset Uri cache.
        Uri::reset();

        // Get a document object
        $document = $this->app->getDocument();

        // Load application dependencies.
        $app->loadLanguage($this->app->getLanguage());
        $app->loadDocument($document);
        $app->loadIdentity($user);

        /** @var \Joomla\CMS\Dispatcher\ComponentDispatcher $dispatcher */
        $dispatcher = $app->bootComponent('com_modules')->getDispatcher($app);

        /** @var ModuleController $controllerClass */
        $controllerClass = $dispatcher->getController('Module');

        // Set backend required params
        $document->setType('json');

        // Execute backend controller
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_modules/forms');
        $return = $controllerClass->save();

        // Reset params back after requesting from service
        $document->setType('html');

        // Check the return value.
        if ($return === false) {
            // Save the data in the session.
            $data = $this->input->post->get('jform', [], 'array');

            $this->app->setUserState('com_config.modules.global.data', $data);

            // Save failed, go back to the screen and display a notice.
            $this->app->enqueueMessage(Text::_('JERROR_SAVE_FAILED'));
            $this->app->redirect(Route::_('index.php?option=com_config&view=modules' . $moduleId . $redirect, false));
        }

        // Redirect back to com_config display
        $this->app->enqueueMessage(Text::_('COM_CONFIG_MODULES_SAVE_SUCCESS'), 'success');

        // Set the redirect based on the task.
        switch ($this->input->getCmd('task')) {
            case 'apply':
                $this->app->redirect(Route::_('index.php?option=com_config&view=modules' . $moduleId . $redirect, false));
                break;

            case 'save':
            default:
                if (!empty($returnUri)) {
                    $redirect = base64_decode(urldecode($returnUri));

                    // Don't redirect to an external URL.
                    if (!Uri::isInternal($redirect)) {
                        $redirect = Uri::base();
                    }
                } else {
                    $redirect = Uri::base();
                }

                $this->setRedirect($redirect);
                break;
        }
    }
}
