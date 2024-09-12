<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Model\Exception;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface that all exceptions stemming from the model should implement for processing by the controller.
 * It is expected that the controller should catch all exceptions that implement this interface and then
 * make a decision as to whether the exception can be recovered from or not.
 *
 * @since  4.4.0
 */
interface ModelExceptionInterface extends \Throwable
{
}
