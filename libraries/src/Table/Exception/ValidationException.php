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
 * Exception class defining a record that failed validation, e.g. a missing required field
 * or a malformed value, as previously detected by Table::check().
 *
 * @since  __DEPLOY_VERSION__
 */
class ValidationException extends \RuntimeException implements TableExceptionInterface
{
    /**
     * The table object the exception relates to.
     *
     * @var    Table
     * @since  __DEPLOY_VERSION__
     */
    private Table $table;

    /**
     * Validation error messages, keyed by field name where known.
     *
     * @var    string[]
     * @since  __DEPLOY_VERSION__
     */
    private array $fieldErrors;

    /**
     * Constructor.
     *
     * @param   Table        $table        The table object the exception relates to.
     * @param   string       $message      The exception message.
     * @param   string[]     $fieldErrors  Validation error messages, keyed by field name where known.
     * @param   integer      $code         The exception code.
     * @param   ?\Throwable  $previous     The previous throwable used for exception chaining.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(Table $table, string $message, array $fieldErrors = [], int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->table       = $table;
        $this->fieldErrors = $fieldErrors;
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

    /**
     * Get the validation error messages, keyed by field name where known.
     *
     * @return  string[]
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getFieldErrors(): array
    {
        return $this->fieldErrors;
    }
}
