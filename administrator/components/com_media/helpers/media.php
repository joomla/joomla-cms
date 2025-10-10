<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Media helper class.
 *
 * @since       1.6
 */
abstract class MediaHelper
{
    /**
     * Generates the URL to the object in the action logs component
     *
     * @param   string    $contentType  The content type
     * @param   integer   $id           The integer id
     * @param   \stdClass  $mediaObject  The media object being uploaded
     *
     * @return  string  The link for the action log
     *
     * @since   3.9.27
     */
    public static function getContentTypeLink($contentType, $id, \stdClass $mediaObject)
    {
        if ($contentType === 'com_media.file') {
            return '';
        }

        $link = 'index.php?option=com_media';

        if (!empty($mediaObject->adapter) && !empty($mediaObject->path)) {
            $link .= '&path=' . $mediaObject->adapter . ':' . $mediaObject->path;
        }

        return $link;
    }
}
