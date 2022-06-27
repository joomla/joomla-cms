<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Newsfeeds\Site\Controller;

use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Newsfeeds Component Controller
 *
 * @since  1.5
 */
class DisplayController extends BaseController
{
    /**
     * Method to show a newsfeeds view
     *
     * @param   boolean  $cachable   If true, the view output will be cached
     * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
     *
     * @return  static  This object to support chaining.
     *
     * @since   1.5
     */
    public function display($cachable = false, $urlparams = false)
    {
        $cachable = true;

        // Set the default view name and format from the Request.
        $vName = $this->input->get('view', 'categories');
        $this->input->set('view', $vName);

        if ($this->app->getIdentity()->get('id') || ($this->input->getMethod() === 'POST' && $vName === 'category' )) {
            $cachable = false;
        }

        $safeurlparams = array('id' => 'INT', 'limit' => 'UINT', 'limitstart' => 'UINT',
                                'filter_order' => 'CMD', 'filter_order_Dir' => 'CMD', 'lang' => 'CMD');

        return parent::display($cachable, $safeurlparams);
    }
}
