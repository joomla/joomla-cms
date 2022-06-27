<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_banners
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Site\Helper;

/**
 * Banner Helper Class
 *
 * @since  1.6
 */
abstract class BannerHelper
{
    /**
     * Checks if a URL is an image
     *
     * @param   string  $url  The URL path to the potential image
     *
     * @return  boolean  True if an image of type bmp, gif, jp(e)g, png or webp, false otherwise
     *
     * @since   1.6
     */
    public static function isImage($url)
    {
        $urlCheck = explode('?', $url);

        if (preg_match('#\.(?:bmp|gif|jpe?g|png|webp)$#i', $urlCheck[0])) {
            return true;
        }

        return false;
    }
}
