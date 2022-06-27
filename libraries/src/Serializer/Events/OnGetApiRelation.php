<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Serializer\Events;

use Joomla\CMS\Event\AbstractImmutableEvent;
use Tobscure\JsonApi\Relationship;

/**
 * Event for getting information on an API Relationship
 *
 * @since  4.0.0
 */
final class OnGetApiRelation extends AbstractImmutableEvent
{
    /**
     * The relationship
     *
     * @var     Relationship
     * @since   4.0.0
     */
    private $relationship;

    /**
     * Constructor.
     *
     * Mandatory arguments:
     * model        mixed           The model being used to render the resource.
     * field        string          The field name we wish to obtain a relationship for.
     * context      string          The content type of the api resource.
     *
     * @param   string  $name       The event name.
     * @param   array   $arguments  The event arguments.
     *
     * @since   4.0.0
     * @throws  \BadMethodCallException
     */
    public function __construct($name, array $arguments = array())
    {
        if (!\array_key_exists('model', $arguments)) {
            throw new \BadMethodCallException("Argument 'model' is required for event $name");
        }

        if (!\array_key_exists('field', $arguments)) {
            throw new \BadMethodCallException("Argument 'field' is required for event $name");
        }

        if (!\array_key_exists('context', $arguments)) {
            throw new \BadMethodCallException("Argument 'context' is required for event $name");
        }

        parent::__construct($name, $arguments);
    }

    /**
     * Get properties to render.
     *
     * @return  Relationship
     *
     * @since   4.0.0
     */
    public function getRelationship(): ?Relationship
    {
        return $this->relationship;
    }

    /**
     * Set relationship object that should be rendered.
     *
     * @param   Relationship  $relationship  The relationship object that should be rendered.
     *
     * @return  void
     * @since   4.0.0
     */
    public function setRelationship(Relationship $relationship): void
    {
        $this->relationship = $relationship;
    }
}
