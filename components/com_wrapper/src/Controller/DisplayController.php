<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_wrapper
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Wrapper\Site\Controller;

use Joomla\CMS\MVC\Controller\BaseController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Content Component Controller
 *
 * @since  1.5
 */
class DisplayController extends BaseController
{
    /**
     * Method to display a view.
     *
     * @param   boolean  $cachable   If true, the view output will be cached
     * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
     *
     * @return  BaseController  This object to support chaining.
     *
     * @since   1.5
     */
    public function display($cachable = false, $urlparams = [])
    {
        $cachable = true;

        // Set the default view name and format from the Request.
        $vName = $this->input->get('view', 'wrapper');
        $this->input->set('view', $vName);

        return parent::display($cachable, ['Itemid' => 'INT']);
    }
}
