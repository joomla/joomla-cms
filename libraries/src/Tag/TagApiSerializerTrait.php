<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Tag;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Serializer\JoomlaSerializer;
use Joomla\CMS\Uri\Uri;
use Tobscure\JsonApi\Collection;
use Tobscure\JsonApi\Relationship;
use Tobscure\JsonApi\Resource;

\defined('JPATH_PLATFORM') or die;

/**
 * Trait for implementing tags in an API Serializer
 *
 * @since  4.0.0
 */
trait TagApiSerializerTrait
{
	/**
	 * Build tags relationship
	 *
	 * @param   \stdClass  $model  Item model
	 *
	 * @return  Relationship
	 *
	 * @since 4.0.0
	 */
	public function tags($model)
	{
		$resources = [];

		$serializer = new JoomlaSerializer('tags');

		foreach ($model->tags as $id => $tagName)
		{
			$resources[] = (new Resource($id, $serializer))
				->addLink('self', Route::link('site', Uri::root() . 'api/index.php/v1/tags/' . $id));
		}

		$collection = new Collection($resources, $serializer);

		return new Relationship($collection);
	}
}
