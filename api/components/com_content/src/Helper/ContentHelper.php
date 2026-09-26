<?php

/**
 * @package     Joomla.Api
 * @subpackage  com_content
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Api\Helper;

use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Content api helper.
 *
 * @since  4.0.0
 */
class ContentHelper
{
    /**
     * Fully Qualified Domain name for the image url
     *
     * @param   string  $uri      The uri to resolve
     *
     * @return  string
     */
    public static function resolve(string $uri): string
    {
        // Check if external URL.
        if (stripos($uri, 'http') !== 0) {
            return Uri::root() . $uri;
        }

        return $uri;
    }
}
