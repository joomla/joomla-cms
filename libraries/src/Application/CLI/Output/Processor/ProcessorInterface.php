<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Application\CLI\Output\Processor;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface for a command line output processor
 *
 * @since       4.0.0
 *
 * @deprecated  4.3 will be removed in 6.0
 *              Use the `joomla/console` package instead
 */
interface ProcessorInterface
{
    /**
     * Process the provided output into a string.
     *
     * @param   string  $output  The string to process.
     *
     * @return  string
     *
     * @since   4.0.0
     */
    public function process($output);
}
