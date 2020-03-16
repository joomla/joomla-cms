<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Serializer;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\Table;
use Tobscure\JsonApi\AbstractSerializer;
use Tobscure\JsonApi\Relationship;

/**
 * Joomla serializer for core data holders
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
	 * @param   Table|array|\stdClass|CMSObject  $post    The model
	 * @param   array                            $fields  The fields can be array or null
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public function getAttributes($post, array $fields = null)
	{
		if (!($post instanceof Table) && !($post instanceof \stdClass) && !(\is_array($post))
			&& !($post instanceof CMSObject))
		{
			$message = sprintf(
				'Invalid argument for %s. Expected array or %s. Got %s',
				static::class,
				Table::class,
				\gettype($post)
			);

			throw new \InvalidArgumentException($message);
		}

		// The response from a standard ListModel query
		if ($post instanceof \stdClass)
		{
			$post = (array) $post;
		}

		// The response from a standard AdminModel query
		if ($post instanceof CMSObject)
		{
			$post = $post->getProperties();
		}

		// TODO: Find a way to make this an instance of TableInterface instead of the concrete class
		if ($post instanceof Table)
		{
			$post = $post->getProperties();
		}

		$event = new Events\OnGetApiAttributes('onGetApiAttributes', ['attributes' => $post, 'context' => $this->type]);

		/** @var Events\OnGetApiAttributes $eventResult */
		$eventResult = Factory::getApplication()->getDispatcher()->dispatch('onGetApiAttributes', $event);
		$combinedData = array_merge($post, $eventResult->getAttributes());

		return \is_array($fields) ? array_intersect_key($combinedData, array_flip($fields)) : $combinedData;
	}

	/**
	 * Get a relationship.
	 *
	 * @param   mixed   $model  The model of the entity being rendered
	 * @param   string  $name   The name of the relationship to return
	 *
	 * @return \Tobscure\JsonApi\Relationship|void
	 *
	 * @since   4.0.0
	 */
	public function getRelationship($model, $name)
	{
		$result = parent::getRelationship($model, $name);

		// If we found a result in the content type serializer return now. Else trigger plugins.
		if ($result instanceof Relationship)
		{
			return $result;
		}

		$eventData = ['model' => $model, 'field' => $name, 'context' => $this->type];
		$event     = new Events\OnGetApiRelation('onGetApiRelation', $eventData);

		/** @var Events\OnGetApiRelation $eventResult */
		$eventResult = Factory::getApplication()->getDispatcher()->dispatch('onGetApiRelation', $event);

		$relationship = $eventResult->getRelationship();

		if ($relationship instanceof Relationship)
		{
			return $relationship;
		}
	}
}
