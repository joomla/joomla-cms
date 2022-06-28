<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_wrapper
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Wrapper\Site\Service;

use Joomla\CMS\Component\Router\RouterBase;

/**
 * Routing class from com_wrapper
 *
 * @since  3.3
 */
class Router extends RouterBase
{
    /**
     * Build the route for the com_wrapper component
     *
     * @param   array  $query  An array of URL arguments
     *
     * @return  array  The URL arguments to use to assemble the subsequent URL.
     *
     * @since   3.3
     */
    public function build(&$query)
    {
        if (isset($query['view'])) {
            unset($query['view']);
        }

        return array();
    }

    /**
     * Parse the segments of a URL.
     *
     * @param   array  $segments  The segments of the URL to parse.
     *
     * @return  array  The URL attributes to be used by the application.
     *
     * @since   3.3
     */
    public function parse(&$segments)
    {
        return array('view' => 'wrapper');
    }
}
