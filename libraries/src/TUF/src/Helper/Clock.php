<?php

namespace Tuf\Helper;

/**
 * Provides time related functions.
 */
class Clock
{
    /**
     * Gets the current time.
     *
     * @return int
     *   The current time.
     *
     * @codeCoverageIgnore
     */
    public function getCurrentTime(): int
    {
        return time();
    }
}
