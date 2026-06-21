<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_wrapper
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Wrapper\Site\Helper;

use Joomla\CMS\Application\SiteApplication;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_wrapper
 *
 * @since  1.5
 */
class WrapperHelper
{
    /**
     * Gets the parameters for the wrapper
     *
     * @param   Registry        $params     The module parameters.
     * @param   SiteApplication $app        The current application.
     *
     * @return  mixed  $params  The modified parameters
     *
     * @since   5.1.0
     */
    public function getParamsWrapper(Registry $params, SiteApplication $app)
    {
        $params->def('url', '');
        $params->def('scrolling', 'auto');
        $params->def('height', '200');
        $params->def('height_auto', 0);
        $params->def('width', '100%');
        $params->def('add', 1);
        $params->def('name', 'wrapper');

        $url = $params->get('url');

        if ($params->get('add')) {
            // Adds 'http://' if none is set
            if (str_starts_with($url, '/')) {
                // Relative URL in component. use server http_host.
                $url = 'http://' . $app->getInput()->server->get('HTTP_HOST') . $url;
            } elseif (!str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
                $url = 'http://' . $url;
            }
        }

        $load = '';

        // Auto height control
        if ($params->def('height_auto')) {
            $load = 'onload="iFrameHeight(this)"';
        }

        $params->set('load', $load);
        $params->set('url', $url);

        return $params;
    }
}
