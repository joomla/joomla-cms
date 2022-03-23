<?php

namespace Tuf\Exception;

/**
 * Indicates that an item was not found in the repository data.
 */
class NotFoundException extends TufException
{

    /**
     * Constructs the exception.
     *
     * @param string $key
     *     (optional) The unique identifier, if any, for the item that was not
     *     found.
     * @param string $itemType
     *     (optional) The type of item (signing key, file, etc.) that was not
     *     found. Used to construct the exception message. If left blank, the
     *     word 'item' will be used by default.
     * @param \Throwable|null $previous
     *     (optional) The previous exception, if any, for exception chaining.
     */
    public function __construct(string $key = '', string $itemType = 'Item', \Throwable $previous = null)
    {
        $message = "$itemType not found";
        if ($key != "") {
            $message = "$itemType not found: $key";
        }
        parent::__construct($message, 0, $previous);
    }
}
