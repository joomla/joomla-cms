<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Feed;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Feed Person class.
 *
 * @since  3.1.4
 */
class FeedPerson
{
    /**
     * The email address of the person.
     *
     * @var    string
     * @since  3.1.4
     */
    public $email;

    /**
     * The full name of the person.
     *
     * @var    string
     * @since  3.1.4
     */
    public $name;

    /**
     * The type of person.
     *
     * @var    string
     * @since  3.1.4
     */
    public $type;

    /**
     * The URI for the person.
     *
     * @var    string
     * @since  3.1.4
     */
    public $uri;

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
    public function __construct($name = null, $email = null, $uri = null, $type = null)
    {
        $this->name = $name;
        $this->email = $email;
        $this->uri = $uri;
        $this->type = $type;
    }
}
