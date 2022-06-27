<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Site\Controller;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

/**
 * Base controller class for Users.
 *
 * @since  1.5
 */
class DisplayController extends BaseController
{
    /**
     * Method to display a view.
     *
     * @param   boolean        $cachable   If true, the view output will be cached
     * @param   array|boolean  $urlparams  An array of safe URL parameters and their variable types,
     *                                     for valid values see {@link \Joomla\CMS\Filter\InputFilter::clean()}.
     *
     * @return  void
     *
     * @since   1.5
     * @throws  \Exception
     */
    public function display($cachable = false, $urlparams = false)
    {
        // Get the document object.
        $document = $this->app->getDocument();

        // Set the default view name and format from the Request.
        $vName   = $this->input->getCmd('view', 'login');
        $vFormat = $document->getType();
        $lName   = $this->input->getCmd('layout', 'default');

        if ($view = $this->getView($vName, $vFormat)) {
            // Do any specific processing by view.
            switch ($vName) {
                case 'registration':
                    // If the user is already logged in, redirect to the profile page.
                    $user = $this->app->getIdentity();

                    if ($user->get('guest') != 1) {
                        // Redirect to profile page.
                        $this->setRedirect(Route::_('index.php?option=com_users&view=profile', false));

                        return;
                    }

                    // Check if user registration is enabled
                    if (ComponentHelper::getParams('com_users')->get('allowUserRegistration') == 0) {
                        // Registration is disabled - Redirect to login page.
                        $this->setRedirect(Route::_('index.php?option=com_users&view=login', false));

                        return;
                    }

                    // The user is a guest, load the registration model and show the registration page.
                    $model = $this->getModel('Registration');
                    break;

                // Handle view specific models.
                case 'profile':
                    // If the user is a guest, redirect to the login page.
                    $user = $this->app->getIdentity();

                    if ($user->get('guest') == 1) {
                        // Redirect to login page.
                        $this->setRedirect(Route::_('index.php?option=com_users&view=login', false));

                        return;
                    }

                    $model = $this->getModel($vName);
                    break;

                // Handle the default views.
                case 'login':
                    $model = $this->getModel($vName);
                    break;

                case 'remind':
                case 'reset':
                    // If the user is already logged in, redirect to the profile page.
                    $user = $this->app->getIdentity();

                    if ($user->get('guest') != 1) {
                        // Redirect to profile page.
                        $this->setRedirect(Route::_('index.php?option=com_users&view=profile', false));

                        return;
                    }

                    $model = $this->getModel($vName);
                    break;

                case 'captive':
                case 'methods':
                case 'method':
                    $controller = $this->factory->createController($vName, 'Site', [], $this->app, $this->input);
                    $task       = $this->input->get('task', '');

                    return $controller->execute($task);

                    break;

                default:
                    $model = $this->getModel('Login');
                    break;
            }

            // Make sure we don't send a referer
            if (in_array($vName, array('remind', 'reset'))) {
                $this->app->setHeader('Referrer-Policy', 'no-referrer', true);
            }

            // Push the model into the view (as default).
            $view->setModel($model, true);
            $view->setLayout($lName);

            // Push document object into the view.
            $view->document = $document;

            $view->display();
        }
    }
}
