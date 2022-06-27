<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Finder\Site\Controller;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\Component\Finder\Administrator\Helper\LanguageHelper;

/**
 * Finder Component Controller.
 *
 * @since  2.5
 */
class DisplayController extends BaseController
{
    /**
     * Method to display a view.
     *
     * @param   boolean  $cachable   If true, the view output will be cached. [optional]
     * @param   array    $urlparams  An array of safe URL parameters and their variable types,
     *                               for valid values see {@link \JFilterInput::clean()}. [optional]
     *
     * @return  static  This object is to support chaining.
     *
     * @since   2.5
     */
    public function display($cachable = false, $urlparams = array())
    {
        $input = $this->app->input;
        $cachable = true;

        // Load plugin language files.
        LanguageHelper::loadPluginLanguage();

        // Set the default view name and format from the Request.
        $viewName = $input->get('view', 'search', 'word');
        $input->set('view', $viewName);

        // Don't cache view for search queries
        if ($input->get('q', null, 'string') || $input->get('f', null, 'int') || $input->get('t', null, 'array')) {
            $cachable = false;
        }

        $safeurlparams = array(
            'f'    => 'INT',
            'lang' => 'CMD'
        );

        return parent::display($cachable, $safeurlparams);
    }
}
