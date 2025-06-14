<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Plugin;

use Joomla\Application\AbstractApplication;
use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\Event\Result\ResultAwareInterface;
use Joomla\CMS\Event\Result\ResultTypeMixedAware;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for onAjax... events
 *
 * @since  5.0.0
 */
class AjaxEvent extends AbstractImmutableEvent implements ResultAwareInterface
{
    use ResultTypeMixedAware;

    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  \BadMethodCallException
     *
     * @since   5.0.0
     */
    public function __construct($name, array $arguments = [])
    {
        parent::__construct($name, $arguments);

        if (!\array_key_exists('subject', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'subject' of event {$name} is required but has not been provided");
        }
    }

    /**
     * Setter for the subject argument.
     *
     * @param   AbstractApplication  $value  The value to set
     *
     * @return  AbstractApplication
     *
     * @since  5.0.0
     */
    protected function onSetSubject(AbstractApplication $value): AbstractApplication
    {
        return $value;
    }

    /**
     * Get the event's application object
     *
     * @return  AbstractApplication
     *
     * @since  5.0.0
     */
    public function getApplication(): AbstractApplication
    {
        return $this->arguments['subject'];
    }

    /**
     * Appends data to the result of the event.
     *
     * @param   mixed  $data  What to add to the result.
     *
     * @return  void
     * @since   5.0.0
     */
    public function addResult($data): void
    {
        $this->arguments['result'] ??= [];

        if (\is_array($this->arguments['result'])) {
            $this->arguments['result'][] = $data;
        } elseif (\is_scalar($this->arguments['result']) && \is_scalar($data)) {
            $this->arguments['result'] .= $data;
        } else {
            throw new \UnexpectedValueException('Mixed data in the result for the event ' . $this->getName());
        }
    }

    /**
     * Update the result of the event.
     *
     * @param   mixed  $data  What to add to the result.
     *
     * @return  static
     * @since   5.0.0
     */
    public function updateEventResult($data): static
    {
        $this->arguments['result'] = $data;

        return $this;
    }

    /**
     * Get the event result.
     *
     * @return  mixed
     * @since   5.0.0
     */
    public function getEventResult(): mixed
    {
        return $this->arguments['result'] ?? null;
    }
}
