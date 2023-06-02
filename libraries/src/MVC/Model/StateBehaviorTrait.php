<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Model;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Trait which supports state behavior
 *
 * @since  4.0.0
 */
trait StateBehaviorTrait
{
    /**
     * Indicates if the internal state has been set
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $__state_set = null;

    /**
     * A state object
     *
     * @var    State
     * @since  4.0.0
     */
    protected $state = null;

    /**
     * Method to get state variables.
     *
     * @param   string  $property  Optional parameter name
     * @param   mixed   $default   Optional default value
     *
     * @return  mixed  The property where specified, the state object where omitted
     *
     * @since   4.0.0
     */
    public function getState($property = null, $default = null)
    {
        if ($this->state === null) {
            $this->state = new State();
        }

        if (!$this->__state_set) {
            // Protected method to auto-populate the state
            $this->populateState();

            // Set the state set flag to true.
            $this->__state_set = true;
        }

        return $property === null ? $this->state : $this->state->get($property, $default);
    }

    /**
     * Method to set state variables.
     *
     * @param   string  $property  The name of the property
     * @param   mixed   $value     The value of the property to set or null
     *
     * @return  mixed  The previous value of the property or null if not set
     *
     * @since   4.0.0
     */
    public function setState($property, $value = null)
    {
        if ($this->state === null) {
            $this->state = new State();
        }

        return $this->state->set($property, $value);
    }

    /**
     * Method to auto-populate the state.
     *
     * This method should only be called once per instantiation and is designed
     * to be called on the first call to the getState() method unless the
     * configuration flag to ignore the request is set.
     *
     * @return  void
     *
     * @note    Calling getState in this method will result in recursion.
     * @since   4.0.0
     */
    protected function populateState()
    {
    }
}
