<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_login
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Login\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Login Controller.
 *
 * @since  1.5
 */
class DisplayController extends BaseController
{
    /**
     * Method to display a view.
     *
     * @param   boolean  $cachable   If true, the view output will be cached
     * @param   array    $urlparams  An array of safe URL parameters and their variable types.
     *                   @see        \Joomla\CMS\Filter\InputFilter::clean() for valid values.
     *
     * @return  static   This object to support chaining.
     *
     * @since   1.5
     * @throws  \Exception
     */
    public function display($cachable = false, $urlparams = false)
    {
        /*
         * Special treatment is required for this component, as this view may be called
         * after a session timeout. We must reset the view and layout prior to display
         * otherwise an error will occur.
         */
        $this->input->set('view', 'login');
        $this->input->set('layout', 'default');

        // For non-html formats we do not have login view, so just display 403 instead
        if ($this->input->get('format', 'html') !== 'html') {
            throw new \RuntimeException(Text::_('JERROR_ALERTNOAUTHOR'), 403);
        }

        /**
         * To prevent clickjacking, only allow the login form to be used inside a frame in the same origin.
         * So send a X-Frame-Options HTTP Header with the SAMEORIGIN value.
         *
         * @link https://www.owasp.org/index.php/Clickjacking_Defense_Cheat_Sheet
         * @link https://tools.ietf.org/html/rfc7034
         */
        $this->app->setHeader('X-Frame-Options', 'SAMEORIGIN');

        return parent::display();
    }

    /**
     * Method to log in a user.
     *
     * @return  void
     */
    public function login()
    {
        // Check for request forgeries.
        $this->checkToken();

        $app = $this->app;

        $model       = $this->getModel('login');
        $credentials = $model->getState('credentials');
        $return      = $model->getState('return');

        $app->login($credentials, ['action' => 'core.login.admin']);

        if (Uri::isInternal($return) && strpos($return, 'tmpl=component') === false) {
            $app->redirect($return);
        } else {
            $app->redirect('index.php');
        }
    }

    /**
     * Method to log out a user.
     *
     * @return  void
     */
    public function logout()
    {
        $this->checkToken('request');

        $app = $this->app;

        $userid = $this->input->getInt('uid', null);

        if ($app->get('shared_session', '0')) {
            $clientid = null;
        } else {
            $clientid = $userid ? 0 : 1;
        }

        $options = [
            'clientid' => $clientid,
        ];

        $result = $app->logout($userid, $options);

        if (!($result instanceof \Exception)) {
            $model  = $this->getModel('login');
            $return = $model->getState('return');

            // Only redirect to an internal URL.
            if (Uri::isInternal($return)) {
                $app->redirect($return);
            }
        }

        parent::display();
    }
}
