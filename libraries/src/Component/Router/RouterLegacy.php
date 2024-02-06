<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Component\Router;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Default routing class for missing or legacy component routers
 *
 * @since  3.3
 */
class RouterLegacy implements RouterInterface
{
    /**
     * Name of the component
     *
     * @var    string
     * @since  3.3
     */
    protected $component;

    /**
     * Constructor
     *
     * @param   string  $component  Component name without the com_ prefix this router should react upon
     *
     * @since   3.3
     */
    public function __construct($component)
    {
        $this->component = $component;
    }

    /**
     * Generic preprocess function for missing or legacy component router
     *
     * @param   array  $query  An associative array of URL arguments
     *
     * @return  array  The URL arguments to use to assemble the subsequent URL.
     *
     * @since   3.3
     */
    public function preprocess($query)
    {
        return $query;
    }

    /**
     * Generic build function for missing or legacy component router
     *
     * @param   array  &$query  An array of URL arguments
     *
     * @return  array  The URL arguments to use to assemble the subsequent URL.
     *
     * @since   3.3
     */
    public function build(&$query)
    {
        $function = $this->component . 'BuildRoute';

        if (\function_exists($function)) {
            $segments = $function($query);
            $total    = \count($segments);

            for ($i = 0; $i < $total; $i++) {
                $segments[$i] = str_replace(':', '-', $segments[$i]);
            }

            return $segments;
        }

        return [];
    }

    /**
     * Generic parse function for missing or legacy component router
     *
     * @param   array  &$segments  The segments of the URL to parse.
     *
     * @return  array  The URL attributes to be used by the application.
     *
     * @since   3.3
     */
    public function parse(&$segments)
    {
        $function = $this->component . 'ParseRoute';

        if (\function_exists($function)) {
            $total = \count($segments);

            for ($i = 0; $i < $total; $i++) {
                $segments[$i] = preg_replace('/-/', ':', $segments[$i], 1);
            }

            return $function($segments);
        }

        return [];
    }
}
