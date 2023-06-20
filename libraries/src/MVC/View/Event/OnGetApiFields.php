<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\View\Event;

use BadMethodCallException;
use Joomla\CMS\Event\AbstractImmutableEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Event for getting extra API Fields and Relations to render with an entity
 *
 * @since  4.0.0
 */
final class OnGetApiFields extends AbstractImmutableEvent
{
    /**
     * List of types of view supported
     *
     * @since  4.0.0
     */
    public const LIST = 'list';

    /**
     * List of types of view supported
     *
     * @since  4.0.0
     */
    public const ITEM = 'item';

    /**
     * List of names of properties that will be rendered as relations
     *
     * @var    string[]
     * @since  4.0.0
     */
    private $extraRelations = [];

    /**
     * List of names of properties that will be rendered as data
     *
     * @var    string[]
     * @since  4.0.0
     */
    private $extraAttributes = [];

    /**
     * Constructor.
     *
     * Mandatory arguments:
     * type         string          The type of the field. Should be a constant from static::VIEW_TYPE
     * fields       fields          The list of fields that will be rendered in the API.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @throws  BadMethodCallException
     */
    public function __construct($name, array $arguments = [])
    {
        if (!\array_key_exists('type', $arguments)) {
            throw new BadMethodCallException("Argument 'type' is required for event $name");
        }

        if (!\array_key_exists('fields', $arguments)) {
            throw new BadMethodCallException("Argument 'fields' is required for event $name");
        }

        if (!\array_key_exists('context', $arguments)) {
            throw new BadMethodCallException("Argument 'context' is required for event $name");
        }

        parent::__construct($name, $arguments);
    }

    /**
     * Setter for the type argument
     *
     * @param   integer  $value  The constant from VIEW_TYPE
     *
     * @return  mixed
     *
     * @throws  BadMethodCallException  if the argument is not of the expected type
     */
    protected function setType($value)
    {
        if (!in_array($value, [static::ITEM, static::LIST])) {
            throw new BadMethodCallException("Argument 'type' of event {$this->name} must be a valid value");
        }

        return $value;
    }

    /**
     * Setter for the fields argument
     *
     * @param   mixed  $value  The value to set
     *
     * @return  array
     *
     * @throws  BadMethodCallException  if the argument is not a non-empty array
     */
    protected function setFields($value)
    {
        if (!\is_array($value) || is_array($value) && empty($value)) {
            throw new BadMethodCallException("Argument 'fields' of event {$this->name} must be be an array and not empty");
        }

        return $value;
    }

    /**
     * Setter for the relations argument
     *
     * @param   mixed  $value  The value to set
     *
     * @return  array
     *
     * @throws  BadMethodCallException  if the argument is not a non-empty array
     */
    protected function setRelations($value)
    {
        if (!\is_array($value)) {
            throw new BadMethodCallException("Argument 'relations' of event {$this->name} must be be an array");
        }

        return $value;
    }

    /**
     * Allows the user to add names of properties that will be interpreted as relations
     * Note that if there is an existing data property it will also be displayed as well
     * as the relation due to the internal implementation (this behaviour is not part of this API
     * however and should not be guaranteed)
     *
     * @param   string[]  $fields  The array of additional fields to add to the data of the attribute
     *
     * @return  void
     */
    public function addFields(array $fields): void
    {
        $this->extraAttributes = array_merge($this->extraAttributes, $fields);
    }

    /**
     * Allows the user to add names of properties that will be interpreted as relations
     * Note that if there is an existing data property it will also be displayed as well
     * as the relation due to the internal implementation (this behaviour is not part of this API
     * however and should not be guaranteed)
     *
     * @param   string[]  $fields  The array of additional fields to add as relations
     *
     * @return  void
     */
    public function addRelations(array $fields): void
    {
        $this->extraRelations = array_merge($this->extraRelations, $fields);
    }

    /**
     * Get properties to render.
     *
     * @return  array
     */
    public function getAllPropertiesToRender(): array
    {
        return array_merge($this->getArgument('fields'), $this->extraAttributes);
    }

    /**
     * Get properties to render.
     *
     * @return  array
     */
    public function getAllRelationsToRender(): array
    {
        return array_merge($this->getArgument('relations'), $this->extraRelations);
    }
}
