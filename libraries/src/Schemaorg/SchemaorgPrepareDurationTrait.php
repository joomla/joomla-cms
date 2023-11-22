<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Schemaorg;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Prepare duration to be valid for JSON-LD output
 *
 * @since  5.0.0
 */
trait SchemaorgPrepareDurationTrait
{
    /**
     * Prepare duration
     *
     * @param   array $duration
     *
     * @return  ?string
     *
     * @since   5.0.0
     */
    protected function prepareDuration($duration)
    {
        if (!\is_array($duration)) {
            return null;
        }

        $newDuration = 'PT' . (!empty($duration['hour']) ? (int) $duration['hour'] . 'H' : '') . (!empty($duration['min']) ? (int) $duration['min'] . 'M' : '');

        return $newDuration !== 'PT' ? $newDuration : null;
    }
}
