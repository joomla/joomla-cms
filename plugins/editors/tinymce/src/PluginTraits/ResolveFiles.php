<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Editors\TinyMCE\PluginTraits;

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Uri\Uri;

/**
 * Handles the editor.css files.
 *
 * @since  4.1.0
 */
trait ResolveFiles
{
    use ActiveSiteTemplate;

    /**
     * Compute the file paths to be included
     *
     * @param   string   $folder  Folder name to search in (i.e. images, css, js).
     * @param   string   $file    Path to file.
     *
     * @return  array    files to be included.
     *
     * @since   4.1.0
     */
    protected function includeRelativeFiles($folder, $file)
    {
        $fallback = Uri::root(true) . '/media/system/css/editor' . (JDEBUG ? '' : '.min') . '.css';
        $template = $this->getActiveSiteTemplate();

        if (!(array) $template) {
            return $fallback;
        }

        // Extract extension and strip the file
        $file       = File::stripExt($file) . '.' . File::getExt($file);
        $templaPath = $template->inheritable || (isset($template->parent) && $template->parent !== '')
            ? JPATH_ROOT . '/media/templates/site'
            : JPATH_ROOT . '/templates';

        if (isset($template->parent) && $template->parent !== '') {
            $found = static::resolveFileUrl("$templaPath/$template->template/$folder/$file");

            if (empty($found)) {
                $found = static::resolveFileUrl("$templaPath/$template->parent/$folder/$file");
            }
        } else {
            $found = static::resolveFileUrl("$templaPath/$template->template/$folder/$file");
        }

        if (empty($found)) {
            return $fallback;
        }

        return $found;
    }

    /**
     * Method that searches if file exists in given path and returns the relative path.
     * If a minified version exists it will be preferred.
     *
     * @param   string   $path          The actual path of the file
     *
     * @return  string  The relative path of the file
     *
     * @since   4.1.0
     */
    protected static function resolveFileUrl($path = '')
    {
        $position = strrpos($path, '.min.');

        // We are handling a name.min.ext file:
        if ($position !== false) {
            $minifiedPath    = $path;
            $nonMinifiedPath = substr_replace($path, '', $position, 4);

            if (JDEBUG && is_file($nonMinifiedPath)) {
                return Uri::root(true) . str_replace(JPATH_ROOT, '', $nonMinifiedPath);
            }

            if (is_file($minifiedPath)) {
                return Uri::root(true) . str_replace(JPATH_ROOT, '', $minifiedPath);
            }

            return '';
        }

        $minifiedPath = pathinfo($path, PATHINFO_DIRNAME) . '/' . pathinfo($path, PATHINFO_FILENAME) . '.min.' . pathinfo($path, PATHINFO_EXTENSION);

        if (JDEBUG && is_file($path)) {
            return Uri::root(true) . str_replace(JPATH_ROOT, '', $path);
        }

        if (is_file($minifiedPath)) {
            return Uri::root(true) . str_replace(JPATH_ROOT, '', $minifiedPath);
        }

        return '';
    }
}
