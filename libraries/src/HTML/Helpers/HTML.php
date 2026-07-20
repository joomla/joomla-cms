<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Provides utility methods for HTML handling.
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class HTML
{
    /**
     * Build a string of HTML attributes from an array.
     *
     * @param   array  $attribs  The array of attributes.
     *
     * @return  string  The string of HTML attributes.
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function attributes(array $attribs): string
    {
        $result = [];

        foreach ($attribs as $key => $value) {
            // Skip invalid attribute names
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_:.-]*$/', $key)) {
                continue;
            }

            // Escape the attribute value to prevent XSS
            $result[] = $key . '="' . htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8') . '"';
        }

        return implode(' ', $result);
    }
}
