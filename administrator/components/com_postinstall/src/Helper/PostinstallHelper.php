<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_postinstall
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Postinstall\Administrator\Helper;

/**
 * Helper class for postinstall messages
 *
 * @since  3.6
 */
class PostinstallHelper
{
    /**
     * Method for parsing ini files.
     *
     * @param   string  $path  Fancy path.
     *
     * @return  string  Parsed path.
     *
     * @since   3.6
     */
    public function parsePath($path)
    {
        if (str_contains($path, 'site://')) {
            $path = JPATH_ROOT . str_replace('site://', '/', $path);
        } elseif (str_contains($path, 'admin://')) {
            $path = JPATH_ADMINISTRATOR . str_replace('admin://', '/', $path);
        }

        return $path;
    }
}
