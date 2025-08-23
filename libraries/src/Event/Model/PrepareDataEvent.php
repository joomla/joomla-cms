<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Model event.
 * Example:
 *  new PrepareDataEvent('onEventName', ['context' => 'com_example.example', 'subject' => $data]);
 *
 * @since  5.0.0
 */
class PrepareDataEvent extends ModelEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  5.0.0
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['context', 'data', 'subject'];

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
        // This event has a dummy subject for now
        $this->arguments['subject'] ??= new \stdClass();

        parent::__construct($name, $arguments);

        if (!\array_key_exists('data', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'data' of event {$name} is required but has not been provided");
        }

        // For backward compatibility make sure the content is referenced
        // @todo: Remove in Joomla 6
        // @deprecated: Passing argument by reference is deprecated, and will not work in Joomla 6
        if (key($arguments) === 0) {
            $this->arguments['data'] = &$arguments[1];
        } elseif (\array_key_exists('data', $arguments)) {
            $this->arguments['data'] = &$arguments['data'];
        }
    }

    /**
     * Setter for the data argument.
     *
     * @param   object|array  $value  The value to set
     *
     * @return  object|array
     *
     * @since  5.0.0
     */
    protected function onSetData(object|array $value): object|array
    {
        return $value;
    }

    /**
     * Getter for the data.
     *
     * @return  object|array
     *
     * @since  5.0.0
     */
    public function getData(): object|array
    {
        return $this->arguments['data'];
    }

    /**
     * Update the data.
     *
     * @param   object|array  $value  The value to set
     *
     * @return  static
     *
     * @since  5.0.0
     */
    public function updateData(object|array $value): static
    {
        $this->arguments['data'] = $this->onSetData($value);

        return $this;
    }
}
