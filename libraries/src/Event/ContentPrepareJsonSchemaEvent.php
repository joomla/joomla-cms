<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event;

\defined('JPATH_PLATFORM') or die;

use Spatie\SchemaOrg\Schema;

/**
 * Event class for representing the application's `onBeforeExecute` event
 *
 * @since  4.0.0
 */
class ContentPrepareJsonSchemaEvent extends AbstractEvent
{
	/**
	 * Get the schema object from the event
	 *
	 * @return  Schema
	 *
	 * @since  4.0.0
	 */
	public function getSchema(): Schema
	{
		return $this->getArgument('schema');
	}

	/**
	 * Get the item having a schema object prepared
	 *
	 * @return  mixed
	 *
	 * @since  4.0.0
	 */
	public function getItem()
	{
		return $this->getArgument('item');
	}

	/**
	 * Ensure that the item is readonly
	 *
	 * @return  mixed
	 *
	 * @since  4.0.0
	 * @throws \BadMethodCallException
	 */
	public function setItem()
	{
		throw new \BadMethodCallException(
			'You are not allowed to modify the content item when preparing' .
			'json schema. It is read only'
		);
	}

	/**
	 * Set's a modified schema object to the event. Method just returns what is passed in however
	 * adds type hinting to the object to be safe!
	 *
	 * @param   Schema  The schema object
	 *
	 * @return  $schema
	 *
	 * @since  4.0.0
	 */
	public function setSchema(Schema $schema)
	{
		return $schema;
	}
}
