<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\QuickIcon;

use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\CMS\Event\Result\ResultTypeArrayAware;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Event class for the onGetIcon event.
 *
 * @since  4.2.0
 */
class GetIconEvent extends AbstractImmutableEvent implements ResultAwareInterface
{
    use ResultAware;
    use ResultTypeArrayAware;

    /**
     * A method to validate the 'context' named parameter.
     *
     * @param   string  $value  The calling context for retrieving icons.
     *
     * @return  string
     *
     * @since   4.4.0
     */
    protected function onSetContext(string $value)
    {
        if (empty($value)) {
            throw new \DomainException(\sprintf("Argument 'context' of event %s must be a non-empty string.", $this->name));
        }

        return $value;
    }
}
