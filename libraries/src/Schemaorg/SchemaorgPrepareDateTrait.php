<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Schemaorg;

use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Prepare dates to be valid for JSON-LD output
 *
 * @since  __DEPLOY_VERSION__
 */
trait SchemaorgPrepareDateTrait
{
    /**
     * Prepare date
     *
     * @param string|array $image
     *
     * @return string|null
     */
    protected function prepareDate($date)
    {
        if (is_array($date)) {
            // We don't handle references, they should be ok
            if (count($date) === 1 && isset($date['@id'])) {
                return $date;
            }

            $newDates = [];

            foreach ($date as $d) {
                $newDates[] = $this->prepareImage($d);
            }

            return $newDates;
        }

        return Factory::getDate($date)->format('Y-m-d');
    }
}
