<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Router;

use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class to create and parse routes
 *
 * @since  1.5
 */
class AdministratorRouter extends Router
{
    /**
     * Function to convert a route to an internal URI.
     *
     * @param   Uri   &$uri     The uri.
     * @param   bool  $setVars  Set the parsed data in the internal
     *                          storage for current-request-URLs
     *
     * @return  array
     *
     * @since   1.5
     */
    public function parse(&$uri, $setVars = false)
    {
        return [];
    }

    /**
     * Function to convert an internal URI to a route
     *
     * @param   string  $url  The internal URL
     *
     * @return  Uri  The absolute search engine friendly URL
     *
     * @since   1.5
     */
    public function build($url)
    {
        // Create the URI object
        $uri = parent::build($url);

        // Get the path data
        $route = $uri->getPath();

        // Add basepath to the uri
        $uri->setPath(Uri::root(true) . '/' . basename(JPATH_ADMINISTRATOR) . '/' . $route);

        return $uri;
    }
}
