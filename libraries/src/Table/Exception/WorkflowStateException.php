<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table\Exception;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Exception class defining an invalid workflow/publish-state transition, e.g. trying to
 * disable a default workflow stage or transition an item that must first be published.
 *
 * @since  __DEPLOY_VERSION__
 */
class WorkflowStateException extends \RuntimeException implements TableExceptionInterface
{
}