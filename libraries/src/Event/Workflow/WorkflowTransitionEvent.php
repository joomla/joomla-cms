<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event\Workflow;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Event class for Workflow Functionality Used events
 *
 * @since  4.0.0
 */
class WorkflowTransitionEvent extends AbstractEvent
{
    /**
     * Constructor.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  \BadMethodCallException
     *
     * @since   4.0.0
     */
    public function __construct($name, array $arguments = [])
    {
        $arguments['stopTransition'] = false;

        parent::__construct($name, $arguments);
    }

    /**
     * Set used parameter to true
     *
     * @param   bool  $value  The value to set
     *
     * @return void
     *
     * @since   4.0.0
     */
    public function setStopTransition($value = true)
    {
        $this->arguments['stopTransition'] = $value;

        if ($value === true) {
            $this->stopPropagation();
        }
    }

    /**
     * Pre-process the pks argument.
     *
     * @param   mixed  $value  The raw argument value.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function onGetPks($value): array
    {
        return (array) $value;
    }

    /**
     * Get the primary keys of the items being transitioned.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getPks(): array
    {
        return $this->getArgument('pks');
    }

    /**
     * Pre-process the extension argument.
     *
     * @param   mixed  $value  The raw argument value.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function onGetExtension($value): string
    {
        return (string) $value;
    }

    /**
     * Get the extension context (e.g. com_content.article).
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getExtension(): string
    {
        return $this->getArgument('extension');
    }

    /**
     * Pre-process the transition argument.
     *
     * @param   mixed  $value  The raw argument value.
     *
     * @return  object
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function onGetTransition($value): object
    {
        return (object) $value;
    }

    /**
     * Get the transition object being executed.
     *
     * @return  object
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getTransition(): object
    {
        return $this->getArgument('transition');
    }

    /**
     * Pre-process the triggeredBy argument.
     *
     * @param mixed $value The raw argument value
     *
     * @return string
     *
     * @since __DEPLOY_VERSION__
     */
    protected function onGetTriggeredBy($value): string
    {
        return (string) ($value ?? 'manual');
    }

    /**
     * Get the trigger source - 'manual' or 'automation'
     *
     * @return string
     *
     * @since __DEPLOY_VERSION__
     */
    public function getTriggeredBy(): string
    {
        return $this->getArgument('triggeredBy');
    }
}
