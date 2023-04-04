<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Access;

use Joomla\CMS\Object\CMSObject;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Access rules class.
 *
 * @since  2.5.0
 */
class Rules
{
    /**
     * A named array.
     *
     * @var    array
     * @since  1.7.0
     */
    protected $data = [];

    /**
     * Constructor.
     *
     * The input array must be in the form: array('action' => array(-42 => true, 3 => true, 4 => false))
     * or an equivalent JSON encoded string, or an object where properties are arrays.
     *
     * @param   mixed  $input  A JSON format string (probably from the database) or a nested array.
     *
     * @since   1.7.0
     */
    public function __construct($input = '')
    {
        // Convert in input to an array.
        if (\is_string($input)) {
            $input = json_decode($input, true);
        } elseif (\is_object($input)) {
            $input = (array) $input;
        }

        if (\is_array($input)) {
            // Top level keys represent the actions.
            foreach ($input as $action => $identities) {
                $this->mergeAction($action, $identities);
            }
        }
    }

    /**
     * Get the data for the action.
     *
     * @return  array  A named array of Rule objects.
     *
     * @since   1.7.0
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Method to merge a collection of Rules.
     *
     * @param   mixed  $input  Rule or array of Rules
     *
     * @return  void
     *
     * @since   1.7.0
     */
    public function mergeCollection($input)
    {
        // Check if the input is an array.
        if (\is_array($input)) {
            foreach ($input as $actions) {
                $this->merge($actions);
            }
        }
    }

    /**
     * Method to merge actions with this object.
     *
     * @param   mixed  $actions  Rule object, an array of actions or a JSON string array of actions.
     *
     * @return  void
     *
     * @since   1.7.0
     */
    public function merge($actions)
    {
        if (\is_string($actions)) {
            $actions = json_decode($actions, true);
        }

        if (\is_array($actions)) {
            foreach ($actions as $action => $identities) {
                $this->mergeAction($action, $identities);
            }
        } elseif ($actions instanceof Rules) {
            $data = $actions->getData();

            foreach ($data as $name => $identities) {
                $this->mergeAction($name, $identities);
            }
        }
    }

    /**
     * Merges an array of identities for an action.
     *
     * @param   string  $action      The name of the action.
     * @param   array   $identities  An array of identities
     *
     * @return  void
     *
     * @since   1.7.0
     */
    public function mergeAction($action, $identities)
    {
        if (isset($this->data[$action])) {
            // If exists, merge the action.
            $this->data[$action]->mergeIdentities($identities);
        } else {
            // If new, add the action.
            $this->data[$action] = new Rule($identities);
        }
    }

    /**
     * Checks that an action can be performed by an identity.
     *
     * The identity is an integer where +ve represents a user group,
     * and -ve represents a user.
     *
     * @param   string  $action    The name of the action.
     * @param   mixed   $identity  An integer representing the identity, or an array of identities
     *
     * @return  mixed   Object or null if there is no information about the action.
     *
     * @since   1.7.0
     */
    public function allow($action, $identity)
    {
        // Check we have information about this action.
        if (isset($this->data[$action])) {
            return $this->data[$action]->allow($identity);
        }
    }

    /**
     * Get the allowed actions for an identity.
     *
     * @param   mixed  $identity  An integer representing the identity or an array of identities
     *
     * @return  CMSObject  Allowed actions for the identity or identities
     *
     * @since   1.7.0
     */
    public function getAllowed($identity)
    {
        // Sweep for the allowed actions.
        $allowed = new CMSObject();

        foreach ($this->data as $name => &$action) {
            if ($action->allow($identity)) {
                $allowed->set($name, true);
            }
        }

        return $allowed;
    }

    /**
     * Magic method to convert the object to JSON string representation.
     *
     * @return  string  JSON representation of the actions array
     *
     * @since   1.7.0
     */
    public function __toString()
    {
        $temp = [];

        foreach ($this->data as $name => $rule) {
            if ($data = $rule->getData()) {
                $temp[$name] = $data;
            }
        }

        return json_encode($temp, JSON_FORCE_OBJECT);
    }
}
