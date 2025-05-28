<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Feed;

use Joomla\CMS\Date\Date;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class to encapsulate a feed entry for the Joomla Platform.
 *
 * @property  FeedPerson  $author         Person responsible for feed entry content.
 * @property  array       $categories     Categories to which the feed entry belongs.
 * @property  string      $content        The content of the feed entry.
 * @property  array       $contributors   People who contributed to the feed entry content.
 * @property  string      $copyright      Information about rights, e.g. copyrights, held in and over the feed entry.
 * @property  array       $links          Links associated with the feed entry.
 * @property  Date        $publishedDate  The publication date for the feed entry.
 * @property  Feed        $source         The feed from which the entry is sourced.
 * @property  string      $title          A human readable title for the feed entry.
 * @property  Date        $updatedDate    The last time the content of the feed entry changed.
 * @property  string      $uri            Universal, permanent identifier for the feed entry.
 *
 * @since  3.1.4
 */
class FeedEntry
{
    /**
     * @var    array  The entry properties.
     * @since  3.1.4
     */
    protected $properties = [
        'uri'          => '',
        'title'        => '',
        'updatedDate'  => '',
        'content'      => '',
        'categories'   => [],
        'contributors' => [],
        'links'        => [],
    ];

    /**
     * Magic method to return values for feed entry properties.
     *
     * @param   string  $name  The name of the property.
     *
     * @return  mixed
     *
     * @since   3.1.4
     */
    public function __get($name)
    {
        return $this->properties[$name] ?? null;
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
        // Ensure that setting a date always sets a Date instance.
        if ((($name === 'updatedDate') || ($name === 'publishedDate')) && !($value instanceof Date)) {
            $value = new Date($value);
        }

        // Validate that any authors that are set are instances of JFeedPerson or null.
        if (($name === 'author') && (!($value instanceof FeedPerson) || ($value === null))) {
            throw new \InvalidArgumentException(
                \sprintf(
                    '%1$s "author" must be an instance of Joomla\\CMS\\Feed\\FeedPerson. %2$s given.',
                    \get_class($this),
                    \is_object($value) ? \get_class($value) : \gettype($value)
                )
            );
        }

        // Validate that any sources that are set are instances of JFeed or null.
        if (($name === 'source') && (!($value instanceof Feed) || ($value === null))) {
            throw new \InvalidArgumentException(
                \sprintf(
                    '%1$s "source" must be an instance of Joomla\\CMS\\Feed\\Feed. %2$s given.',
                    \get_class($this),
                    \is_object($value) ? \get_class($value) : \gettype($value)
                )
            );
        }

        // Disallow setting categories, contributors, or links directly.
        if (\in_array($name, ['categories', 'contributors', 'links'])) {
            throw new \InvalidArgumentException(
                \sprintf(
                    'Cannot directly set %1$s property "%2$s".',
                    \get_class($this),
                    $name
                )
            );
        }

        $this->properties[$name] = $value;
    }

    /**
     * Method to add a category to the feed entry object.
     *
     * @param   string  $name  The name of the category to add.
     * @param   string  $uri   The optional URI for the category to add.
     *
     * @return  FeedEntry
     *
     * @since   3.1.4
     */
    public function addCategory($name, $uri = '')
    {
        $this->properties['categories'][$name] = $uri;

        return $this;
    }

    /**
     * Method to add a contributor to the feed entry object.
     *
     * @param   string  $name   The full name of the person to add.
     * @param   string  $email  The email address of the person to add.
     * @param   string  $uri    The optional URI for the person to add.
     * @param   string  $type   The optional type of person to add.
     *
     * @return  FeedEntry
     *
     * @since   3.1.4
     */
    public function addContributor($name, $email, $uri = null, $type = null)
    {
        $contributor = new FeedPerson($name, $email, $uri, $type);

        // If the new contributor already exists then there is nothing to do, so just return.
        foreach ($this->properties['contributors'] as $c) {
            if ($c == $contributor) {
                return $this;
            }
        }

        // Add the new contributor.
        $this->properties['contributors'][] = $contributor;

        return $this;
    }

    /**
     * Method to add a link to the feed entry object.
     *
     * @param   FeedLink  $link  The link object to add.
     *
     * @return  FeedEntry
     *
     * @since   3.1.4
     */
    public function addLink(FeedLink $link)
    {
        // If the new link already exists then there is nothing to do, so just return.
        foreach ($this->properties['links'] as $l) {
            if ($l == $link) {
                return $this;
            }
        }

        // Add the new link.
        $this->properties['links'][] = $link;

        return $this;
    }

    /**
     * Method to remove a category from the feed entry object.
     *
     * @param   string  $name  The name of the category to remove.
     *
     * @return  FeedEntry
     *
     * @since   3.1.4
     */
    public function removeCategory($name)
    {
        unset($this->properties['categories'][$name]);

        return $this;
    }

    /**
     * Method to remove a contributor from the feed entry object.
     *
     * @param   FeedPerson  $contributor  The person object to remove.
     *
     * @return  FeedEntry
     *
     * @since   3.1.4
     */
    public function removeContributor(FeedPerson $contributor)
    {
        // If the contributor exists remove it.
        foreach ($this->properties['contributors'] as $k => $c) {
            if ($c == $contributor) {
                unset($this->properties['contributors'][$k]);
                $this->properties['contributors'] = array_values($this->properties['contributors']);

                return $this;
            }
        }

        return $this;
    }

    /**
     * Method to remove a link from the feed entry object.
     *
     * @param   FeedLink  $link  The link object to remove.
     *
     * @return  FeedEntry
     *
     * @since   3.1.4
     */
    public function removeLink(FeedLink $link)
    {
        // If the link exists remove it.
        foreach ($this->properties['links'] as $k => $l) {
            if ($l == $link) {
                unset($this->properties['links'][$k]);
                $this->properties['links'] = array_values($this->properties['links']);

                return $this;
            }
        }

        return $this;
    }

    /**
     * Shortcut method to set the author for the feed entry object.
     *
     * @param   string  $name   The full name of the person to set.
     * @param   string  $email  The email address of the person to set.
     * @param   string  $uri    The optional URI for the person to set.
     * @param   string  $type   The optional type of person to set.
     *
     * @return  FeedEntry
     *
     * @since   3.1.4
     */
    public function setAuthor($name, $email, $uri = null, $type = null)
    {
        $author = new FeedPerson($name, $email, $uri, $type);

        $this->properties['author'] = $author;

        return $this;
    }
}
