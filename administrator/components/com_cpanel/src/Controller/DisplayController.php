<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Cpanel\Administrator\Controller;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Cpanel Controller
 *
 * @since  1.5
 */
class DisplayController extends BaseController
{
    /**
     * The default view.
     *
     * @var    string
     * @since  1.6
     */
    protected $default_view = 'cpanel';

    /**
     * Typical view method for MVC based architecture
     *
     * This function is provide as a default implementation, in most cases
     * you will need to override it in your own controllers.
     *
     * @param   boolean  $cachable   If true, the view output will be cached
     * @param   array    $urlparams  An array of safe url parameters and their variable types.
     *                   @see        \Joomla\CMS\Filter\InputFilter::clean() for valid values.
     *
     * @return  static  An instance of the current object to support chaining.
     *
     * @since   3.0
     */
    public function display($cachable = false, $urlparams = [])
    {
        /*
         * Set the template - this will display cpanel.php
         * from the selected admin template.
         */
        $this->input->set('tmpl', 'cpanel');

        return parent::display($cachable, $urlparams);
    }

    /**
     * Method to add a module to a dashboard
     *
     * @since   4.0.0
     *
     * @return  void
     */
    public function addModule()
    {
        $position = $this->input->get('position', 'cpanel');
        $function = $this->input->get('function');

        $appendLink = '';

        if ($function) {
            $appendLink .= '&function=' . $function;
        }

        if (substr($position, 0, 6) != 'cpanel') {
            $position = 'cpanel';
        }

        // Administrator
        $clientId = (\Joomla\CMS\Application\ApplicationHelper::getClientInfo('administrator', true))->id;

        $this->app->setUserState('com_modules.modules.' . $clientId . '.filter.position', $position);
        $this->app->setUserState('com_modules.modules.client_id', (string) $clientId);

        $this->setRedirect(Route::_('index.php?option=com_modules&view=select&tmpl=component&layout=modal' . $appendLink, false));
    }
}
