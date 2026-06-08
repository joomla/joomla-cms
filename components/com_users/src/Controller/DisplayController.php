<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Site\Controller;

use Joomla\CMS\MVC\Controller\BaseController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

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
     * @param   array|boolean  $urlparams  An array of safe URL parameters and their variable types.
     *                         @see        \Joomla\CMS\Filter\InputFilter::clean() for valid values.
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
                case 'remind':
                case 'reset':
                case 'registration':
                case 'login':
                case 'profile':
                    $model = $this->getModel($vName);
                    break;

                case 'captive':
                case 'methods':
                case 'method':
                    $controller = $this->factory->createController($vName, 'Site', [], $this->app, $this->input);
                    $task       = $this->input->get('task', '');

                    return $controller->execute($task);

                default:
                    $model = $this->getModel('Login');
                    break;
            }

            // Make sure we don't send a referer
            if (\in_array($vName, ['remind', 'reset'])) {
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
