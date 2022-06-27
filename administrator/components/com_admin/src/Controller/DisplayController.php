<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Admin\Administrator\Controller;

use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Admin Controller
 *
 * @since  1.6
 */
class DisplayController extends BaseController
{
    /**
     * View method
     *
     * @param   boolean  $cachable   If true, the view output will be cached
     * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
     *
     * @return  static  Supports chaining.
     *
     * @since   3.9
     */
    public function display($cachable = false, $urlparams = array())
    {
        $viewName = $this->input->get('view', $this->default_view);
        $format   = $this->input->get('format', 'html');

        // Check CSRF token for sysinfo export views
        if ($viewName === 'sysinfo' && ($format === 'text' || $format === 'json')) {
            // Check for request forgeries.
            $this->checkToken('GET');
        }

        return parent::display($cachable, $urlparams);
    }
}
