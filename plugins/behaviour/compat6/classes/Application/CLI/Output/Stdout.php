<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Application\CLI\Output;

use Joomla\CMS\Application\CLI\CliOutput;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Output handler for writing command line output to the stdout interface
 *
 * @since       4.0.0
 *
 * @deprecated  4.3 will be removed in 6.0
 *              Use the `joomla/console` package instead
 */
class Stdout extends CliOutput
{
    /**
     * Write a string to standard output
     *
     * @param   string   $text  The text to display.
     * @param   boolean  $nl    True (default) to append a new line at the end of the output string.
     *
     * @return  $this
     *
     * @codeCoverageIgnore
     * @since   4.0.0
     */
    public function out($text = '', $nl = true)
    {
        fwrite(STDOUT, $this->getProcessor()->process($text) . ($nl ? "\n" : null));

        return $this;
    }
}
