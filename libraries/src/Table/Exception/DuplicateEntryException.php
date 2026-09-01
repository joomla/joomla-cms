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
 * Exception class defining a unique-key violation, e.g. a duplicate alias, name or username.
 *
 * @since  __DEPLOY_VERSION__
 */
class DuplicateEntryException extends ValidationException
{
    /**
     * The name of the field holding the duplicate value.
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    private string $field;

    /**
     * Constructor.
     *
     * @param   string       $message   The exception message.
     * @param   string       $field     The name of the field holding the duplicate value.
     * @param   integer      $code      The exception code.
     * @param   ?\Throwable  $previous  The previous throwable used for exception chaining.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(string $message, string $field, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, [$field => $message], $code, $previous);

        $this->field = $field;
    }

    /**
     * Get the name of the field holding the duplicate value.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getField(): string
    {
        return $this->field;
    }
}
