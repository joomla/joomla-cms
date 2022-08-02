<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Feed;

/**
 * Feed Link class.
 *
 * @since  3.1.4
 */
class FeedLink
{
    /**
     * The length of the resource in bytes.
     *
     * @var    integer
     * @since  3.1.4
     */
    public $length;

    /**
     * Constructor.
     *
     * @param   string   $uri       The URI to the linked resource.
     * @param   string   $relation  The relationship between the feed and the linked resource.
     * @param   string   $type      The resource type.
     * @param   string   $language  The language of the resource found at the given URI.
     * @param   string   $title     The title of the resource.
     * @param   integer  $length    The length of the resource in bytes.
     *
     * @since   3.1.4
     * @throws  \InvalidArgumentException
     */
    public function __construct(public $uri = null, public $relation = null, public $type = null, public $language = null, public $title = null, $length = null)
    {
        // Validate the length input.
        if (isset($length) && !is_numeric($length)) {
            throw new \InvalidArgumentException('Length must be numeric.');
        }

        $this->length = (int) $length;
    }
}
