<?php

/**
 * Joomla! Content Management System
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Image\Exception;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Exception thrown when an image has no known properties.
 *
 * @since  4.0.0
 */
class UnparsableImageException extends \RuntimeException
{
}
