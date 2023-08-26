<?php

/**
 * Joomla! Content Management System
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\View;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Exception class defining an error getting data from a model into a view
 *
 * @since  4.0.0
 */
class GenericDataException extends \RuntimeException
{
}
