<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Mail\Exception;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Exception class defining an error for disabled mail functionality.
 *
 * @since  4.0.0
 */
final class MailDisabledException extends \RuntimeException
{
    /**
     * Send Mail option is disabled by the user.
     *
     * @var    string
     * @since  4.0.0
     */
    public const REASON_USER_DISABLED = 'user_disabled';

    /**
     * Mail() function is not available on the system.
     *
     * @var    string
     * @since  4.0.0
     */
    public const REASON_MAIL_FUNCTION_NOT_AVAILABLE = 'mail_function_not_available';

    /**
     * Reason mail is disabled.
     *
     * @var    string
     * @since  4.0.0
     */
    private $reason;

    /**
     * Constructor.
     *
     * @param   string       $reason    The reason why mail is disabled.
     * @param   string       $message   The Exception message to throw.
     * @param   integer      $code      The Exception code.
     * @param   ?\Throwable  $previous  The previous exception used for the exception chaining.
     *
     * @since   4.0.0
     */
    public function __construct(string $reason, string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->reason = $reason;
    }

    /**
     * Method to return the reason why mail is disabled.
     *
     * @return  string
     *
     * @since   4.0.0
     */
    public function getReason(): string
    {
        return $this->reason;
    }
}
