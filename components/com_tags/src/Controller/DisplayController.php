<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Tags\Site\Controller;

use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Tags Component Controller
 *
 * @since  3.1
 */
class DisplayController extends BaseController
{
    /**
     * Method to display a view.
     *
     * @param   boolean        $cachable   If true, the view output will be cached
     * @param   mixed|boolean  $urlparams  An array of safe URL parameters and their
     *                                     variable types, for valid values see {@link \JFilterInput::clean()}.
     *
     * @return  static  This object to support chaining.
     *
     * @since   3.1
     */
    public function display($cachable = false, $urlparams = false)
    {
        $user = $this->app->getIdentity();

        // Set the default view name and format from the Request.
        $vName = $this->input->get('view', 'tags');
        $this->input->set('view', $vName);

        if ($user->get('id') || ($this->input->getMethod() === 'POST' && $vName === 'tags')) {
            $cachable = false;
        }

        $safeurlparams = array(
            'id'               => 'ARRAY',
            'type'             => 'ARRAY',
            'limit'            => 'UINT',
            'limitstart'       => 'UINT',
            'filter_order'     => 'CMD',
            'filter_order_Dir' => 'CMD',
            'lang'             => 'CMD'
        );

        return parent::display($cachable, $safeurlparams);
    }
}
