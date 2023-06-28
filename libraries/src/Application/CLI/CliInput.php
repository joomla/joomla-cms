<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Application\CLI;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class CliInput
 *
 * @since       4.0.0
 *
 * @deprecated  4.3 will be removed in 6.0
 *              Use the `joomla/console` package instead
 */
class CliInput
{
    /**
     * Get a value from standard input.
     *
     * @return  string  The input string from standard input.
     *
     * @codeCoverageIgnore
     * @since   4.0.0
     */
    public function in()
    {
        return rtrim(fread(STDIN, 8192), "\n\r");
    }
}
