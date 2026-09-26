<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Dispatcher;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Input\Input;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Base class for a Joomla Dispatcher
 *
 * @since  4.0.0
 */
abstract class Dispatcher implements DispatcherInterface
{
    /**
     * The application instance
     *
     * @var    CMSApplicationInterface
     * @since  4.0.0
     */
    protected $app;

    /**
     * The input instance
     *
     * @var    Input
     * @since  4.0.0
     */
    protected $input;

    /**
     * Constructor for Dispatcher
     *
     * @param   CMSApplicationInterface  $app    The application instance
     * @param   Input                    $input  The input instance
     *
     * @since   4.0.0
     */
    public function __construct(CMSApplicationInterface $app, Input $input)
    {
        $this->app   = $app;
        $this->input = $input;
    }

    /**
     * The application the dispatcher is working with.
     *
     * @return  CMSApplicationInterface
     *
     * @since   4.0.0
     */
    protected function getApplication(): CMSApplicationInterface
    {
        return $this->app;
    }
}
