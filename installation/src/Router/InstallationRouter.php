<?php

/**
 * @package    Joomla.Installation
 *
 * @copyright  (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Router;

use Joomla\CMS\Router\Router;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class to create and parse routes.
 *
 * @since  1.5
 */
class InstallationRouter extends Router
{
    /**
     * Function to convert a route to an internal URI
     *
     * @param   Uri   $uri      The uri.
     * @param   bool  $setVars  Set the parsed data in the internal
     *                             storage for current-request-URLs
     *
     * @return  boolean
     *
     * @since   1.5
     */
    public function parse(&$uri, $setVars = false)
    {
        return true;
    }

    /**
     * Function to convert an internal URI to a route
     *
     * @param   string  $url  The internal URL
     *
     * @return  string  The absolute search engine friendly URL
     *
     * @since   1.5
     */
    public function build($url)
    {
        $url = str_replace('&amp;', '&', $url);

        return new Uri($url);
    }
}
