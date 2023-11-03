<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Component\Privacy\Administrator\Model\RequestsModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Privacy Controller
 *
 * @since  3.9.0
 */
class DisplayController extends BaseController
{
    /**
     * The default view.
     *
     * @var    string
     * @since  3.9.0
     */
    protected $default_view = 'requests';

    /**
     * Method to display a view.
     *
     * @param   boolean  $cachable   If true, the view output will be cached
     * @param   array    $urlparams  An array of safe URL parameters and their variable types.
     *                   @see        \Joomla\CMS\Filter\InputFilter::clean() for valid values.
     *
     * @return  $this
     *
     * @since   3.9.0
     */
    public function display($cachable = false, $urlparams = [])
    {
        // Get the document object.
        $document = $this->app->getDocument();

        // Set the default view name and format from the Request.
        $vName   = $this->input->get('view', $this->default_view);
        $vFormat = $document->getType();
        $lName   = $this->input->get('layout', 'default', 'string');

        // Get and render the view.
        if ($view = $this->getView($vName, $vFormat)) {
            $model = $this->getModel($vName);
            $view->setModel($model, true);

            if ($vName === 'request') {
                // For the default layout, we need to also push the action logs model into the view
                if ($lName === 'default') {
                    $logsModel = $this->app->bootComponent('com_actionlogs')
                        ->getMVCFactory()->createModel('Actionlogs', 'Administrator', ['ignore_request' => true]);

                    // Set default ordering for the context
                    $logsModel->setState('list.fullordering', 'a.log_date DESC');

                    // And push the model into the view
                    $view->setModel($logsModel, false);
                }

                // For the edit layout, if mail sending is disabled then redirect back to the list view as the form is unusable in this state
                if ($lName === 'edit' && !$this->app->get('mailonline', 1)) {
                    $this->setRedirect(
                        Route::_('index.php?option=com_privacy&view=requests', false),
                        Text::_('COM_PRIVACY_WARNING_CANNOT_CREATE_REQUEST_WHEN_SENDMAIL_DISABLED'),
                        'warning'
                    );

                    return $this;
                }
            }

            $view->setLayout($lName);

            // Push document object into the view.
            $view->document = $document;

            $view->display();
        }

        return $this;
    }

    /**
     * Fetch and report number urgent privacy requests in JSON format, for AJAX requests
     *
     * @return  void
     *
     * @since   3.9.0
     */
    public function getNumberUrgentRequests()
    {
        // Check for a valid token. If invalid, send a 403 with the error message.
        if (!Session::checkToken('get')) {
            $this->app->setHeader('status', 403, true);
            $this->app->sendHeaders();
            echo new JsonResponse(new \Exception(Text::_('JINVALID_TOKEN'), 403));
            $this->app->close();
        }

        /** @var RequestsModel $model */
        $model                = $this->getModel('requests');
        $numberUrgentRequests = $model->getNumberUrgentRequests();

        echo new JsonResponse(['number_urgent_requests' => $numberUrgentRequests]);
    }
}
