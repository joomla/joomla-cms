<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Serializer;

use Joomla\CMS\Factory;
use Joomla\CMS\Object\CMSObject;
use Tobscure\JsonApi\AbstractSerializer;
use Tobscure\JsonApi\Relationship;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * This class does the messy job of sanitising all the classes Joomla has that contain data and converting them
 * into a standard array that can be consumed by the Tobscure library. It also throws appropriate plugin events
 * to allow 3rd party extensions to add custom data and relations into these properties before they are rendered
 *
 * @since  4.0.0
 */
class JoomlaSerializer extends AbstractSerializer
{
    /**
     * Constructor.
     *
     * @param   string  $type  The content type to be loaded
     *
     * @since 4.0.0
     */
    public function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * Get the attributes array.
     *
     * @param   array|\stdClass|CMSObject  $post    The data container
     * @param   array|null                 $fields  The requested fields to be rendered
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public function getAttributes($post, array $fields = null)
    {
        if (!($post instanceof \stdClass) && !(\is_array($post)) && !($post instanceof CMSObject)) {
            $message = sprintf(
                'Invalid argument for %s. Expected array or %s. Got %s',
                static::class,
                CMSObject::class,
                \gettype($post)
            );

            throw new \InvalidArgumentException($message);
        }

        // The response from a standard ListModel query
        if ($post instanceof \stdClass) {
            $post = (array) $post;
        }

        // The response from a standard AdminModel query also works for Table which extends CMSObject
        if ($post instanceof CMSObject) {
            $post = $post->getProperties();
        }

        $event = new Events\OnGetApiAttributes('onGetApiAttributes', ['attributes' => $post, 'context' => $this->type]);

        /** @var Events\OnGetApiAttributes $eventResult */
        $eventResult  = Factory::getApplication()->getDispatcher()->dispatch('onGetApiAttributes', $event);
        $combinedData = array_merge($post, $eventResult->getAttributes());

        return \is_array($fields) ? array_intersect_key($combinedData, array_flip($fields)) : $combinedData;
    }

    /**
     * Get a relationship.
     *
     * @param   mixed   $model  The model of the entity being rendered
     * @param   string  $name   The name of the relationship to return
     *
     * @return \Tobscure\JsonApi\Relationship|null
     *
     * @since   4.0.0
     */
    public function getRelationship($model, $name)
    {
        $result = parent::getRelationship($model, $name);

        // If we found a result in the content type serializer return now. Else trigger plugins.
        if ($result instanceof Relationship) {
            return $result;
        }

        $eventData = ['model' => $model, 'field' => $name, 'context' => $this->type];
        $event     = new Events\OnGetApiRelation('onGetApiRelation', $eventData);

        /** @var Events\OnGetApiRelation $eventResult */
        $eventResult = Factory::getApplication()->getDispatcher()->dispatch('onGetApiRelation', $event);

        $relationship = $eventResult->getRelationship();

        if ($relationship instanceof Relationship) {
            return $relationship;
        }

        return null;
    }
}
