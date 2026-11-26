<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table\Exception;

use Joomla\CMS\Table\Table;

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
    /**
     * The table object the exception relates to.
     *
     * @var    Table
     * @since  __DEPLOY_VERSION__
     */
    private Table $table;

    /**
     * Constructor.
     *
     * @param   Table        $table     The table object the exception relates to.
     * @param   string       $message   The exception message.
     * @param   integer      $code      The exception code.
     * @param   ?\Throwable  $previous  The previous throwable used for exception chaining.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(Table $table, string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->table = $table;
    }

    /**
     * Get the table object the exception relates to.
     *
     * @return  Table
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getTable(): Table
    {
        return $this->table;
    }
}
