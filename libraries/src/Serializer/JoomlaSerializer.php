<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Serializer;

\defined('_JEXEC') or die;

use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\Table;
use Tobscure\JsonApi\AbstractSerializer;

/**
 * Temporary serializer
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
	 * @param   Table|array|\stdClass|CMSobject  $post    The model
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
				'Invalid argument for TableSerializer. Expected array or %s. Got %s',
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

		return \is_array($fields) ? array_intersect_key($post, array_flip($fields)) : $post;
	}
}
