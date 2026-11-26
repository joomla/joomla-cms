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
 * Exception class defining data that failed a validation check in the model, e.g. a missing
 * or malformed value in the submitted data or a request that is not valid in the current state.
 *
 * Use the Table layer's ValidationException instead when the check belongs to a Table object.
 *
 * @since  __DEPLOY_VERSION__
 */
class ValidationException extends \RuntimeException implements ModelExceptionInterface
{
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
     * @param   string       $message      The exception message.
     * @param   string[]     $fieldErrors  Validation error messages, keyed by field name where known.
     * @param   integer      $code         The exception code.
     * @param   ?\Throwable  $previous     The previous throwable used for exception chaining.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(string $message, array $fieldErrors = [], int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->fieldErrors = $fieldErrors;
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
