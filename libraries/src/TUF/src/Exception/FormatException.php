<?php

namespace Tuf\Exception;

/**
 *  Indicates an input was not in the required format to be interpreted.
 */
class FormatException extends TufException
{

    /**
     * Constructs the exception.
     *
     * @param string $malformedValue
     *     The malformed value string that caused the exception.
     * @param string $message
     *     (optional) The exception message to use. If blank, a default message
     *     will be used.
     * @param \Throwable|null $previous
     *     (optional) The previous exception, if any, for exception chaining.
     */
    public function __construct(string $malformedValue, string $message = "", \Throwable $previous = null)
    {
        if (empty($message)) {
            $message = 'Bad format';
        }
        $message = $message . sprintf(": %s", $malformedValue);
        parent::__construct($message, 0, $previous);
    }
}
