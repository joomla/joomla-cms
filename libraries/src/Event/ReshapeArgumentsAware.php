<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * A Trait to reshape arguments maintaining b/c with legacy plugin events.
 *
 * Old plugin event handlers expect positional arguments, not named arguments, since they are simple
 * PHP methods, e.g.
 * public onExample($foo, $bar, $baz).
 * Concrete Event classes, however, use named arguments which can be passed in any order. The
 * following two examples are equivalent:
 * $event1 = new ConcreteEventClass('onExample', ['foo' => 1, 'bar' => 2, 'baz' => 3];
 * $event2 = new ConcreteEventClass('onExample', ['bar' => 2, 'baz' => 3, 'foo' => 1,];
 * However, this means that the internal $arguments property of the event object holds the named
 * arguments in a **different** order in each case.
 *
 * When the event handler is aware of the ConcreteEventClass it can retrieve named arguments and
 * all is good in the world. However, when you have a legacy plugin listener registered through
 * CMSPlugin::registerLegacyListener you have a major problem! The legacy listener is passing the
 * arguments **positionally**, in the order they were added to the Event object.
 *
 * In the previous example, $event1 would work as expected because the foo, bar, and baz arguments
 * were given in the same order legacy listeners expected them. On the other hand, $event2 would
 * fail miserably because the call order would be $bar, $baz, $foo which is NOT what the legacy
 * listener expected.
 *
 * The only way to fix that is to *reshape the argument* in the concrete event's constructor so that
 * the order of arguments is guaranteed to be the same as expected by legacy listeners. Moreover,
 * since Joomla is passing all arguments (except the 'result' argument) blindly to the legacy
 * listener we must ensure that a. all necessary arguments are set and b. any other named arguments
 * do NOT exist. Otherwise our legacy listeners would receive the wrong number of positional
 * arguments and break.
 *
 * All this is achieved by the reshapeArguments() method in this trait which has to be called in the
 * constructor of the concrete event class.
 *
 * This trait is marked as deprecated with a removal target of 6.0 because in Joomla 6 we will only
 * be using concrete event classes with named arguments, removing legacy listeners and their
 * positional arguments headaches.
 *
 * @since  4.2.0
 *
 * @deprecated  4.3 will be removed in 6.0
 *              Will be removed without replacement
 */
trait ReshapeArgumentsAware
{
    /**
     * Reshape the arguments array to preserve b/c with legacy listeners
     *
     * @param   array  $arguments      The named arguments array passed to the constructor.
     * @param   array  $argumentNames  The allowed argument names (mandatory AND optional).
     * @param   array  $defaults       Default values for optional arguments.
     *
     * @return  array  The reshaped arguments.
     *
     * @since   4.2.0
     */
    protected function reshapeArguments(array $arguments, array $argumentNames, array $defaults = [])
    {
        $reconstructed = [];

        // Check when the source is non-associative, example [$context, $item, $isNew, $data]
        if (key($arguments) === 0) {
            foreach ($argumentNames as $i => $name) {
                $reconstructed[$name] = $arguments[$i] ?? $defaults[$name] ?? null;
            }

            // Return the reconstructed arguments array
            return $reconstructed;
        }

        // Reconstruct the arguments in the order specified in $argumentNames
        foreach ($argumentNames as $key) {
            $reconstructed[$key] = $arguments[$key] ?? $defaults[$key] ?? null;
        }

        // Return the reconstructed arguments array
        return $reconstructed;
    }
}
