<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Model\Exception;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Exception class defining a batch operation that could not be carried out, e.g. no items
 * were selected or insufficient information was supplied to complete the batch request.
 *
 * @since  __DEPLOY_VERSION__
 */
class BatchOperationException extends \RuntimeException implements ModelExceptionInterface
{
}