<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

/**
 * Controller for global configuration
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
    protected $default_view = 'application';


    /**
     * Typical view method for MVC based architecture
     *
     * This function is provide as a default implementation, in most cases
     * you will need to override it in your own controllers.
     *
     * @param   boolean  $cachable   If true, the view output will be cached
     * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link InputFilter::clean()}.
     *
     * @return  static  A \JControllerLegacy object to support chaining.
     *
     * @since   3.0
     * @throws  \Exception
     */
    public function display($cachable = false, $urlparams = array())
    {
        $component = $this->input->get('component', '');

        // Make sure com_joomlaupdate and com_privacy can only be accessed by SuperUser
        if (
            in_array(strtolower($component), array('com_joomlaupdate', 'com_privacy'))
            && !$this->app->getIdentity()->authorise('core.admin')
        ) {
            $this->setRedirect(Route::_('index.php'), Text::_('JERROR_ALERTNOAUTHOR'), 'error');
        }

        return parent::display($cachable, $urlparams);
    }
}
