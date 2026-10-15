<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Cache;

use Joomla\CMS\Event\AbstractImmutableEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Cache events
 *
 * @since  5.0.0
 */
class AfterPurgeEvent extends AbstractImmutableEvent
{
    /**
     * Setter for the subject argument.
     *
     * @param   string  $value  The value to set
     *
     * @return  string
     *
     * @since  5.0.0
     */
    protected function onSetSubject(string $value): string
    {
        return $value;
    }

    /**
     * Getter for the group.
     *
     * @return  string
     *
     * @since  5.0.0
     */
    public function getGroup(): string
    {
        return $this->arguments['subject'] ?? '';
    }
}
