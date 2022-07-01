<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Image;

/**
 * Class to manipulate an image.
 *
 * @since  1.7.3
 */
abstract class ImageFilter
{
    /**
     * @var    resource  The image resource handle.
     * @since  2.5.0
     */
    protected $handle;

    /**
     * Class constructor.
     *
     * @param   resource  $handle  The image resource on which to apply the filter.
     *
     * @since   1.7.3
     * @throws  \InvalidArgumentException
     * @throws  \RuntimeException
     */
    public function __construct($handle)
    {
        // Verify that image filter support for PHP is available.
        if (!\function_exists('imagefilter')) {
            throw new \RuntimeException('The imagefilter function for PHP is not available.');
        }

        /**
         * Make sure the file handle is valid.
         * @todo: Remove check for resource when we only support PHP 8
         */
        if (
            !((\is_object($handle) && get_class($handle) == 'GdImage')
            || (\is_resource($handle) && get_resource_type($handle) == 'gd'))
        ) {
            throw new \InvalidArgumentException('The image handle is invalid for the image filter.');
        }

        $this->handle = $handle;
    }

    /**
     * Method to apply a filter to an image resource.
     *
     * @param   array  $options  An array of options for the filter.
     *
     * @return  void
     *
     * @since   2.5.0
     */
    abstract public function execute(array $options = []);
}
