<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Session\Session;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Requests from the frontend
 *
 * @since  4.0.0
 */
class RequestController extends BaseController
{

    /**
     * Method to send the test mail.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
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

}
