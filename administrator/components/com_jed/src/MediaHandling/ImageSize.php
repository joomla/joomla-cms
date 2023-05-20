<?php

namespace Jed\Component\Jed\Administrator\MediaHandling;

// No direct access.
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

enum ImageSize
{
    /**
     * Original size (no dimensions specified)
     *
     * @since 4.0.0
     */
    case ORIGINAL;

    /**
     * Small size (up to 400x175)
     *
     * @since 4.0.0
     */
    case SMALL;

    /**
     * Large size (up to 1200x525)
     *
     * @since 4.0.0
     */
    case LARGE;

    /**
     *
     * @return array
     *
     * @since version
     */
    public function getMaximumDimensions(): array
    {
        return match ($this) {
            self::ORIGINAL => [null, null],
            self::SMALL    => [400, 175],
            self::LARGE    => [1200, 525]
        };
    }
}
