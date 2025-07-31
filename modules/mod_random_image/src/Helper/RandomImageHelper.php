<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_random_image
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\RandomImage\Site\Helper;

use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_random_image
 *
 * @since  1.5
 */
class RandomImageHelper
{
    /**
     * Retrieves a random image
     *
     * @param   Registry  &$params  module parameters object
     * @param   array     $images   list of images
     *
     * @return  mixed
     *
     * @since   5.4.0
     */
    public function getImage(Registry &$params, array $images): mixed
    {
        $width  = $params->get('width', 100);
        $height = $params->get('height', null);

        $i = \count($images);

        if ($i === 0) {
            return null;
        }

        $random = mt_rand(0, $i - 1);
        $image  = $images[$random];
        $size   = getimagesize(JPATH_BASE . '/' . $image->folder . '/' . $image->name);

        if ($size[0] < $width) {
            $width = $size[0];
        }

        $coeff = $size[0] / $size[1];

        if ($height === null) {
            $height = (int) ($width / $coeff);
        } else {
            $newheight = min($height, (int) ($width / $coeff));

            if ($newheight < $height) {
                $height = $newheight;
            } else {
                $width = $height * $coeff;
            }
        }

        $image->width  = $width;
        $image->height = $height;
        $image->folder = str_replace('\\', '/', $image->folder);

        return $image;
    }

    /**
     * Retrieves images from a specific folder
     *
     * @param   Registry  &$params  module params
     * @param   string    $folder   folder to get the images from
     *
     * @return  array
     *
     * @since   5.4.0
     */
    public function getImagesFromFolder(Registry &$params, string $folder): array
    {
        $type   = $params->get('type', 'jpg');
        $files  = [];
        $images = [];

        $dir = JPATH_BASE . '/' . $folder;

        // Check if directory exists
        if (is_dir($dir)) {
            if ($handle = opendir($dir)) {
                while (false !== ($file = readdir($handle))) {
                    if ($file !== '.' && $file !== '..' && $file !== 'CVS' && $file !== 'index.html') {
                        $files[] = $file;
                    }
                }
            }

            closedir($handle);

            $i = 0;

            foreach ($files as $img) {
                if (!is_dir($dir . '/' . $img) && preg_match('/' . $type . '/', $img)) {
                    $images[$i] = new \stdClass();

                    $images[$i]->name   = $img;
                    $images[$i]->folder = $folder;
                    $i++;
                }
            }
        }

        return $images;
    }

    /**
     * Get sanitized folder
     *
     * @param   Registry  &$params  module params objects
     *
     * @return  mixed
     *
     * @since   5.4.0
     */
    public function getSanitizedFolder(Registry &$params): mixed
    {
        $folder   = $params->get('folder');
        $liveSite = Uri::base();

        // If folder includes livesite info, remove
        if (StringHelper::strpos($folder, $liveSite) === 0) {
            $folder = str_replace($liveSite, '', $folder);
        }

        // If folder includes absolute path, remove
        if (StringHelper::strpos($folder, JPATH_SITE) === 0) {
            $folder = str_replace(JPATH_BASE, '', $folder);
        }

        return str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $folder);
    }

    /**
     * Retrieves a random image
     *
     * @param   Registry  &$params  module parameters object
     * @param   array     $images   list of images
     *
     * @return  mixed
     *
     * @deprecated 5.4.0 will be removed in 7.0
     *             Use the non-static method getImage
     *             Example: Factory::getApplication()->bootModule('mod_random_image', 'site')
     *                            ->getHelper('RandomImageHelper')
     *                            ->getImage($params, $images)
     */
    public static function getRandomImage(&$params, $images)
    {
        return (new self())->getImage($params, $images);
    }

    /**
     * Retrieves images from a specific folder
     *
     * @param   Registry  &$params  module params
     * @param   string    $folder   folder to get the images from
     *
     * @return  array
     *
     * @deprecated 5.4.0 will be removed in 7.0
     *             Use the non-static method getImagesFromFolder
     *             Example: Factory::getApplication()->bootModule('mod_random_image', 'site')
     *                            ->getHelper('RandomImageHelper')
     *                            ->getImagesFromFolder($params, $folder)
     */
    public static function getImages(&$params, $folder)
    {
        return (new self())->getImagesFromFolder($params, $folder);
    }

    /**
     * Get sanitized folder
     *
     * @param   Registry  &$params  module params objects
     *
     * @return  mixed
     *
     * @deprecated 5.4.0 will be removed in 7.0
     *             Use the non-static method getSanitizedFolder
     *             Example: Factory::getApplication()->bootModule('mod_random_image', 'site')
     *                            ->getHelper('RandomImageHelper')
     *                            ->getSanitizedFolder($params)
     */
    public static function getFolder(&$params)
    {
        return (new self())->getSanitizedFolder($params);
    }
}
