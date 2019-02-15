<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Feed;

defined('JPATH_PLATFORM') or die();

use Joomla\CMS\Date\Date;

/**
 * Class to encapsulate a feed for the Joomla Platform.
 *
 * @property  FeedPerson  $author         Person responsible for feed content.
 * @property  array       $categories     Categories to which the feed belongs.
 * @property  array       $contributors   People who contributed to the feed content.
 * @property  string      $copyright      Information about rights, e.g. copyrights, held in and over the feed.
 * @property  string      $description    A phrase or sentence describing the feed.
 * @property  string      $generator      A string indicating the program used to generate the feed.
 * @property  string      $image          Specifies a GIF, JPEG or PNG image that should be displayed with the feed.
 * @property  Date        $publishedDate  The publication date for the feed content.
 * @property  string      $title          A human readable title for the feed.
 * @property  Date        $updatedDate    The last time the content of the feed changed.
 * @property  string      $uri            Universal, permanent identifier for the feed.
 *
 * @since  3.1.4
 */
class Feed implements \ArrayAccess, \Countable
{
	/**
	 * @var    array  The entry properties.
	 * @since  3.1.4
	 */
	protected $properties = array(
		'uri' => '',
		'title' => '',
		'updatedDate' => '',
		'description' => '',
		'categories' => array(),
		'contributors' => array(),
	);

	/**
	 * @var    array  The list of feed entry objects.
	 * @since  3.1.4
	 */
	protected $entries = array();

	/**
	 * Magic method to return values for feed properties.
	 *
	 * @param   string  $name  The name of the property.
	 *
	 * @return  mixed
	 *
	 * @since   3.1.4
	 */
	public function __get($name)
	{
		return isset($this->properties[$name]) ? $this->properties[$name] : null;
	}

	/**
	 * Magic method to set values for feed properties.
	 *
	 * @param   string  $name   The name of the property.
	 * @param   mixed   $value  The value to set for the property.
	 *
	 * @return  void
	 *
	 * @since   3.1.4
	 */
	public function __set($name, $value)
	{
		// Ensure that setting a date always sets a JDate instance.
		if ((($name == 'updatedDate') || ($name == 'publishedDate')) && !($value instanceof Date))
		{
			$value = new Date($value);
		}

		// Validate that any authors that are set are instances of JFeedPerson or null.
		if (($name == 'author') && (!($value instanceof FeedPerson) || ($value === null)))
		{
			throw new \InvalidArgumentException(
				sprintf(
					'%1$s "author" must be an instance of Joomla\\CMS\\Feed\\FeedPerson. %2$s given.',
					get_class($this),
					gettype($value) === 'object' ? get_class($value) : gettype($value)
				)
			);
		}

		// Disallow setting categories or contributors directly.
		if (in_array($name, array('categories', 'contributors')))
		{
			throw new \InvalidArgumentException(
				sprintf(
					'Cannot directly set %1$s property "%2$s".',
					get_class($this),
					$name
				)
			);
		}

		$this->properties[$name] = $value;
	}

	/**
	 * Method to add a category to the feed object.
	 *
	 * @param   string  $name  The name of the category to add.
	 * @param   string  $uri   The optional URI for the category to add.
	 *
	 * @return  Feed
	 *
	 * @since   3.1.4
	 */
	public function addCategory($name, $uri = '')
	{
		$this->properties['categories'][$name] = $uri;

		return $this;
	}

	/**
	 * Method to add a contributor to the feed object.
	 *
	 * @param   string  $name   The full name of the person to add.
	 * @param   string  $email  The email address of the person to add.
	 * @param   string  $uri    The optional URI for the person to add.
	 * @param   string  $type   The optional type of person to add.
	 *
	 * @return  Feed
	 *
	 * @since   3.1.4
	 */
	public function addContributor($name, $email, $uri = null, $type = null)
	{
		$contributor = new FeedPerson($name, $email, $uri, $type);

		// If the new contributor already exists then there is nothing to do, so just return.
		foreach ($this->properties['contributors'] as $c)
		{
			if ($c == $contributor)
			{
				return $this;
			}
		}

		// Add the new contributor.
		$this->properties['contributors'][] = $contributor;

		return $this;
	}

	/**
	 * Method to add an entry to the feed object.
	 *
	 * @param   FeedEntry  $entry  The entry object to add.
	 *
	 * @return  Feed
	 *
	 * @since   3.1.4
	 */
	public function addEntry(FeedEntry $entry)
	{
		// If the new entry already exists then there is nothing to do, so just return.
		foreach ($this->entries as $e)
		{
			if ($e == $entry)
			{
				return $this;
			}
		}

		// Add the new entry.
		$this->entries[] = $entry;

		return $this;
	}

	/**
	 * Returns a count of the number of entries in the feed.
	 *
	 * This method is here to implement the Countable interface.
	 * You can call it by doing count($feed) rather than $feed->count();
	 *
	 * @return  integer number of entries in the feed.
	 */
	public function count()
	{
		return count($this->entries);
	}

	/**
	 * Whether or not an offset exists.  This method is executed when using isset() or empty() on
	 * objects implementing ArrayAccess.
	 *
	 * @param   mixed  $offset  An offset to check for.
	 *
	 * @return  boolean
	 *
	 * @see     ArrayAccess::offsetExists()
	 * @since   3.1.4
	 */
	public function offsetExists($offset)
	{
		return isset($this->entries[$offset]);
	}

	/**
	 * Returns the value at specified offset.
	 *
	 * @param   mixed  $offset  The offset to retrieve.
	 *
	 * @return  mixed  The value at the offset.
	 *
	 * @see     ArrayAccess::offsetGet()
	 * @since   3.1.4
	 */
	public function offsetGet($offset)
	{
		return $this->entries[$offset];
	}

	/**
	 * Assigns a value to the specified offset.
	 *
	 * @param   mixed      $offset  The offset to assign the value to.
	 * @param   FeedEntry  $value   The JFeedEntry to set.
	 *
	 * @return  boolean
	 *
	 * @see     ArrayAccess::offsetSet()
	 * @since   3.1.4
	 * @throws  \InvalidArgumentException
	 */
	public function offsetSet($offset, $value)
	{
		if (!($value instanceof FeedEntry))
		{
			throw new \InvalidArgumentException(
				sprintf(
					'%1$s entries must be an instance of Joomla\\CMS\\Feed\\FeedPerson. %2$s given.',
					get_class($this),
					gettype($value) === 'object' ? get_class($value) : gettype($value)
				)
			);
		}

		$this->entries[$offset] = $value;

		return true;
	}

	/**
	 * Unsets an offset.
	 *
	 * @param   mixed  $offset  The offset to unset.
	 *
	 * @return  void
	 *
	 * @see     ArrayAccess::offsetUnset()
	 * @since   3.1.4
	 */
	public function offsetUnset($offset)
	{
		unset($this->entries[$offset]);
	}

	/**
	 * Method to remove a category from the feed object.
	 *
	 * @param   string  $name  The name of the category to remove.
	 *
	 * @return  Feed
	 *
	 * @since   3.1.4
	 */
	public function removeCategory($name)
	{
		unset($this->properties['categories'][$name]);

		return $this;
	}

	/**
	 * Method to remove a contributor from the feed object.
	 *
	 * @param   FeedPerson  $contributor  The person object to remove.
	 *
	 * @return  Feed
	 *
	 * @since   3.1.4
	 */
	public function removeContributor(FeedPerson $contributor)
	{
		// If the contributor exists remove it.
		foreach ($this->properties['contributors'] as $k => $c)
		{
			if ($c == $contributor)
			{
				unset($this->properties['contributors'][$k]);
				$this->properties['contributors'] = array_values($this->properties['contributors']);

				return $this;
			}
		}

		return $this;
	}

	/**
	 * Method to remove an entry from the feed object.
	 *
	 * @param   FeedEntry  $entry  The entry object to remove.
	 *
	 * @return  Feed
	 *
	 * @since   3.1.4
	 */
	public function removeEntry(FeedEntry $entry)
	{
		// If the entry exists remove it.
		foreach ($this->entries as $k => $e)
		{
			if ($e == $entry)
			{
				unset($this->entries[$k]);
				$this->entries = array_values($this->entries);

				return $this;
			}
		}

		return $this;
	}

	/**
	 * Shortcut method to set the author for the feed object.
	 *
	 * @param   string  $name   The full name of the person to set.
	 * @param   string  $email  The email address of the person to set.
	 * @param   string  $uri    The optional URI for the person to set.
	 * @param   string  $type   The optional type of person to set.
	 *
	 * @return  Feed
	 *
	 * @since   3.1.4
	 */
	public function setAuthor($name, $email, $uri = null, $type = null)
	{
		$author = new FeedPerson($name, $email, $uri, $type);

		$this->properties['author'] = $author;

		return $this;
	}

	/**
	 * Method to reverse the items if display is set to 'oldest first'
	 *
	 * @return  Feed
	 *
	 * @since   3.1.4
	 */
	public function reverseItems()
	{
		if (is_array($this->entries) && !empty($this->entries))
		{
			$this->entries = array_reverse($this->entries);
		}

		return $this;
	}
}
