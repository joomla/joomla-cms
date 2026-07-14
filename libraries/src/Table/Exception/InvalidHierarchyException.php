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
 * Exception class defining a violation of a nested-set/parent-child invariant, e.g. moving
 * a node to be a child of itself or an otherwise structurally inconsistent tree operation.
 *
 * Extends \LogicException rather than \RuntimeException: these conditions indicate a
 * programming error or an invalid invocation, not a runtime data/user-input problem.
 *
 * @since  __DEPLOY_VERSION__
 */
class InvalidHierarchyException extends \LogicException implements TableExceptionInterface
{
}