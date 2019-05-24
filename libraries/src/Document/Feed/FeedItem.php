<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Document\Feed;

defined('JPATH_PLATFORM') or die;

/**
 * Data object representing a feed item
 *
 * @since  1.7.0
 */
class FeedItem
{
	/**
	 * Title item element
	 *
	 * required
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	public $title;

	/**
	 * Link item element
	 *
	 * required
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	public $link;

	/**
	 * Description item element
	 *
	 * required
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	public $description;

	/**
	 * Author item element
	 *
	 * optional
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	public $author;

	/**
	 * Author email element
	 *
	 * optional
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	public $authorEmail;

	/**
	 * Category element
	 *
	 * optional
	 *
	 * @var    array or string
	 * @since  1.7.0
	 */
	public $category;

	/**
	 * Comments element
	 *
	 * optional
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	public $comments;

	/**
	 * Enclosure element
	 *
	 * @var    FeedEnclosure
	 * @since  1.7.0
	 */
	public $enclosure = null;

	/**
	 * Guid element
	 *
	 * optional
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	public $guid;

	/**
	 * Published date
	 *
	 * optional
	 *
	 * May be in one of the following formats:
	 *
	 * RFC 822:
	 * "Mon, 20 Jan 03 18:05:41 +0400"
	 * "20 Jan 03 18:05:41 +0000"
	 *
	 * ISO 8601:
	 * "2003-01-20T18:05:41+04:00"
	 *
	 * Unix:
	 * 1043082341
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	public $date;

	/**
	 * Source element
	 *
	 * optional
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	public $source;

	/**
	 * Set the FeedEnclosure for this item
	 *
	 * @param   FeedEnclosure  $enclosure  The FeedEnclosure to add to the feed.
	 *
	 * @return  FeedItem instance of $this to allow chaining
	 *
	 * @since   1.7.0
	 */
	public function setEnclosure(FeedEnclosure $enclosure)
	{
		$this->enclosure = $enclosure;

		return $this;
	}
}
