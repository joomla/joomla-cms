<?php

/**
 * Joomla! Content Management System
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Image\Filter;

use Joomla\CMS\Image\ImageFilter;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Image Filter class to make an image appear "sketchy".
 *
 * @since  2.5.0
 */
class Sketchy extends ImageFilter
{
    /**
     * Method to apply a filter to an image resource.
     *
     * @param   array  $options  An array of options for the filter.
     *
     * @return  void
     *
     * @since   2.5.0
     */
    public function execute(array $options = [])
    {
        // Perform the sketchy filter.
        imagefilter($this->handle, IMG_FILTER_MEAN_REMOVAL);
    }
}
