<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_banners
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Site\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Banner Helper Class
 *
 * @since  1.6
 *
 * @deprecated  5.1 will be removed in 7.0
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
     *
     * @deprecated  5.1 will be removed in 7.0
     *              When testing the image file, use Joomla\CMS\Helper\MediaHelper::isImage($url) for pixel-based image files
     *              in combination with Joomla\CMS\Helper\MediaHelper::getMimeType($url) === 'image/svg+xml' for vector based image files
     *              Be aware that the image url should first be sanitized with the helper function Joomla\CMS\HTML\HTMLHelper::cleanImageURL($imageurl)
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
