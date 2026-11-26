<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Component\Exception;

use Joomla\CMS\Router\Exception\RouteNotFoundException;

\defined('_JEXEC') or die;

/**
 * Exception class defining an error for a missing component
 *
 * @since  3.7.0
 */
class MissingComponentException extends RouteNotFoundException
{
}
