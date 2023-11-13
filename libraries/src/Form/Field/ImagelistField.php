<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Supports an HTML select list of image
 *
 * @since  1.7.0
 */
class ImagelistField extends FilelistField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.7.0
     */
    protected $type = 'Imagelist';

    /**
     * Method to get the list of images field options.
     * Use the filter attribute to specify allowable file extensions.
     *
     * @return  object[]  The field option objects.
     *
     * @since   1.7.0
     */
    protected function getOptions()
    {
        // Define the image file type filter.
        $this->fileFilter = '\.png$|\.gif$|\.jpg$|\.bmp$|\.ico$|\.jpeg$|\.psd$|\.eps$|\.avif$|\.webp$|\.heic$|\.wp2$';

        // Get the field options.
        return parent::getOptions();
    }
}
