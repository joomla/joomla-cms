<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\QuickIcon;

use BadMethodCallException;
use DomainException;
use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Event\ReshapeArgumentsAware;
use Joomla\CMS\Event\Result\ResultAware;
use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\CMS\Event\Result\ResultTypeArrayAware;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
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
    use ReshapeArgumentsAware;

    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @since   4.2.0
     * @throws  BadMethodCallException
     */
    public function __construct(string $name, array $arguments = [])
    {
        $this->reshapeArguments($arguments, ['context']);

        parent::__construct($name, $arguments);
    }

    /**
     * A method to validate the 'context' named parameter.
     *
     * @param   string  $value  The calling context for retrieving icons.
     *
     * @return  void
     *
     * @since   4.2.0
     */
    public function setContext(string $value)
    {
        if (empty($value)) {
            throw new DomainException(sprintf("Argument 'context' of event %s must be a non-empty string.", $this->name));
        }
    }
}
