<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\CustomFields;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for CustomFields events
 *
 * @since  __DEPLOY_VERSION__
 */
class AfterPrepareFieldEvent extends AbstractPrepareFieldEvent
{
    /**
     * The argument names, in order expected by legacy plugins.
     *
     * @var array
     *
     * @since  __DEPLOY_VERSION__
     * @deprecated 5.0 will be removed in 6.0
     */
    protected $legacyArgumentsOrder = ['context', 'item', 'subject', 'value'];

    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  \BadMethodCallException
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct($name, array $arguments = [])
    {
        parent::__construct($name, $arguments);

        if (!\array_key_exists('value', $this->arguments)) {
            throw new \BadMethodCallException("Argument 'value' of event {$name} is required but has not been provided");
        }

        // For backward compatibility make sure the value is referenced
        // TODO: Remove in Joomla 6
        if (key($arguments) === 0 && \count($arguments) >= 4) {
            $this->arguments['value'] = &$arguments[3];
        } elseif (\array_key_exists('value', $arguments)) {
            $this->arguments['value'] = &$arguments['value'];
        }

        @trigger_error(
            sprintf(
                'Passing argument by reference is deprecated, and will not work in Joomla 6. Event %s argument %s. Use %s.',
                \get_class($this),
                'value',
                'updateValue()'
            ),
            E_USER_DEPRECATED
        );
    }

    /**
     * Getter for the value.
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getValue(): mixed
    {
        return $this->arguments['value'];
    }

    /**
     * Update the value.
     *
     * @return  mixed
     *
     * @since  __DEPLOY_VERSION__
     */
    public function updateValue(mixed $value): static
    {
        $this->arguments['value'] = $value;

        return $this;
    }
}
