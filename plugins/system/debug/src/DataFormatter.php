<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Debug;

use DebugBar\DataFormatter\DataFormatter as DebugBarDataFormatter;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * DataFormatter
 *
 * @since  4.0.0
 */
class DataFormatter extends DebugBarDataFormatter
{
    /**
     * Strip the root path.
     *
     * @param   string  $path         The path.
     * @param   string  $replacement  The replacement
     *
     * @return string
     *
     * @since 4.0.0
     */
    public function formatPath($path, $replacement = ''): string
    {
        return str_replace(JPATH_ROOT, $replacement, $path);
    }

    /**
     * Format a string from back trace.
     *
     * @param   array  $call  The array to format
     *
     * @return string
     *
     * @since 4.0.0
     */
    public function formatCallerInfo(array $call): string
    {
        $string = '';

        if (isset($call['class'])) {
            // If entry has Class/Method print it.
            $string .= htmlspecialchars($call['class'] . $call['type'] . $call['function']) . '()';
        } elseif (isset($call['args'][0]) && \is_array($call['args'][0])) {
            $string .= htmlspecialchars($call['function']) . ' (';

            foreach ($call['args'][0] as $arg) {
                // Check if the arguments can be used as string
                if (\is_object($arg) && !method_exists($arg, '__toString')) {
                    $arg = \get_class($arg);
                }

                // Keep only the size of array
                if (\is_array($arg)) {
                    $arg = 'Array(count=' . \count($arg) . ')';
                }

                $string .= htmlspecialchars($arg) . ', ';
            }

            $string = rtrim($string, ', ') . ')';
        } elseif (isset($call['args'][0])) {
            $string .= htmlspecialchars($call['function']) . '(';

            if (is_scalar($call['args'][0])) {
                $string .= $call['args'][0];
            } elseif (\is_object($call['args'][0])) {
                $string .= \get_class($call['args'][0]);
            } else {
                $string .= gettype($call['args'][0]);
            }
            $string .= ')';
        } else {
            // It's a function.
            $string .= htmlspecialchars($call['function']) . '()';
        }

        return $string;
    }
}
