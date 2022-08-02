<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Feed;

/**
 * Feed Person class.
 *
 * @since  3.1.4
 */
class FeedPerson
{
    /**
     * Constructor.
     *
     * @param   string  $name   The full name of the person.
     * @param   string  $email  The email address of the person.
     * @param   string  $uri    The URI for the person.
     * @param   string  $type   The type of person.
     *
     * @since   3.1.4
     */
    public function __construct(public $name = null, public $email = null, public $uri = null, public $type = null)
    {
    }
}
