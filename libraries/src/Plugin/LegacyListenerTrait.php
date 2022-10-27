<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Plugin;

use Joomla\Event\AbstractEvent;
use Joomla\Event\EventInterface;
use Joomla\Event\SubscriberInterface;

/**
 * Trait to auto-register legacy (Joomla 3.x style) listeners and event handlers based on their name.
 *
 * @deprecated 5.0 Use SubscriberInterface instead
 */
trait LegacyListenerTrait
{
    /**
     * Should the plugin try to register legacy (Joomla! 3.x style) listeners?
     *
     * If enabled it also registers on<Something>(Event $e) event handler methods. This is
     * deprecated since Joomla 5.0. Use the SubscriberInterface to register the event handlers
     * methods instead of relying on their name for automatic registration.
     *
     * @var         bool
     * @since       __DEPLOY_VERSION__
     * @deprecated  5.0 Use the SubscriberInterface instead
     */
    protected $allowLegacyListeners = true;

    /**
     * A list of legacy listeners and event handlers discovered by registerListeners.
     *
     * This is used to implement "magic" late initialisation of the plugin.
     *
     * @var    array
     * @since  __DEPLOY_VERSION__
     * @deprecated 5.0 Use the SubscriberInterface instead
     */
    private $legacyListenersDiscovered = [];

    /**
     * Registers legacy Listeners to the Dispatcher, emulating how plugins worked under Joomla! 3.x and below.
     *
     * By default, this method will look for all public methods whose name starts with "on". It will register
     * lambda functions (closures) which try to unwrap the arguments of the dispatched Event into method call
     * arguments and call your on<Something> method. The result will be passed back to the Event into its 'result'
     * argument.
     *
     * This method additionally supports Joomla\Event\SubscriberInterface and plugins implementing this will be
     * registered to the dispatcher as a subscriber.
     *
     * @return  void
     *
     * @since   4.0.0
     * @deprecated 5.0 Use SubscriberInterface instead
     */
    public function registerListeners()
    {
        // Plugins which are SubscriberInterface implementations are handled without legacy layer support
        if ($this instanceof SubscriberInterface) {
            return;
        }

        $reflectedObject = new \ReflectionObject($this);
        $methods = $reflectedObject->getMethods(\ReflectionMethod::IS_PUBLIC);

        /** @var \ReflectionMethod $method */
        foreach ($methods as $method) {
            if (substr($method->name, 0, 2) !== 'on') {
                continue;
            }

            $this->legacyListenersDiscovered[] = $method->name;

            // Save time if I'm not to detect legacy listeners
            if (!$this->allowLegacyListeners) {
                $this->registerListener($method->name);

                continue;
            }

            /** @var \ReflectionParameter[] $parameters */
            $parameters = $method->getParameters();

            // If the parameter count is not 1 it is by definition a legacy listener
            if (\count($parameters) !== 1) {
                $this->registerLegacyListener($method->name);

                continue;
            }

            /** @var \ReflectionParameter $param */
            $param = array_shift($parameters);
            $paramName = $param->getName();

            // No type hint / type hint class not an event or parameter name is not "event"? It's a legacy listener.
            if ($paramName !== 'event' || !$this->parameterImplementsEventInterface($param)) {
                $this->registerLegacyListener($method->name);

                continue;
            }

            // Everything checks out, this is a proper listener.
            $this->registerListener($method->name);
        }
    }

    /**
     * Registers a legacy event listener, i.e. a method which accepts individual arguments instead of an AbstractEvent
     * in its arguments. This provides backwards compatibility to Joomla! 3.x-style plugins.
     *
     * This method will register lambda functions (closures) which try to unwrap the arguments of the dispatched Event
     * into old style method arguments and call your on<Something> method with them. The result will be passed back to
     * the Event, as an element into an array argument called 'result'.
     *
     * @param   string  $methodName  The method name to register
     *
     * @return  void
     *
     * @since   4.0.0
     * @deprecated 5.0 Use SubscriberInterface instead
     */
    final protected function registerLegacyListener(string $methodName)
    {
        $this->getDispatcher()->addListener(
            $methodName,
            function (AbstractEvent $event) use ($methodName) {
                // Get the event arguments
                $arguments = $event->getArguments();

                // Extract any old results; they must not be part of the method call.
                $allResults = [];

                if (isset($arguments['result'])) {
                    $allResults = $arguments['result'];

                    unset($arguments['result']);
                }

                // Convert to indexed array for unpacking.
                $arguments = \array_values($arguments);

                $result = $this->{$methodName}(...$arguments);

                // Ignore null results
                if ($result === null) {
                    return;
                }

                // Restore the old results and add the new result from our method call
                $allResults[]    = $result;
                $event['result'] = $allResults;
            }
        );
    }

    /**
     * Registers a proper event listener, i.e. a method which accepts an AbstractEvent as its sole argument. This is the
     * preferred way to implement plugins in Joomla! 4.x and will be the only possible method with Joomla! 5.x onwards.
     *
     * @param   string  $methodName  The method name to register
     *
     * @return  void
     *
     * @since   4.0.0
     */
    final protected function registerListener(string $methodName)
    {
        $this->getDispatcher()->addListener($methodName, [$this, $methodName]);
    }

    /**
     * Checks if parameter is typehinted to accept \Joomla\Event\EventInterface.
     *
     * @param   \ReflectionParameter  $parameter
     *
     * @return  boolean
     *
     * @since   4.0.0
     * @deprecated 5.0 Use SubscriberInterface instead
     */
    private function parameterImplementsEventInterface(\ReflectionParameter $parameter): bool
    {
        $reflectionType = $parameter->getType();

        // Parameter is not typehinted.
        if ($reflectionType === null) {
            return false;
        }

        // Parameter is nullable.
        if ($reflectionType->allowsNull()) {
            return false;
        }

        // Handle standard typehints.
        if ($reflectionType instanceof \ReflectionNamedType) {
            return \is_a($reflectionType->getName(), EventInterface::class, true);
        }

        // Handle PHP 8 union types.
        /** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */
        if (version_compare(PHP_VERSION, '8.0.0', 'gt') && $reflectionType instanceof \ReflectionUnionType) {
            foreach ($reflectionType->getTypes() as $type) {
                if (!\is_a($type->getName(), EventInterface::class, true)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }
}
