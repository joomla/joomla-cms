<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Pagination;

/**
 * Pagination object representing a particular item in the pagination lists.
 *
 * @since  1.5
 */
class PaginationObject
{
    /**
     * Class constructor.
     *
     * @param   string   $text    The link text.
     * @param   string   $prefix  The prefix used for request variables.
     * @param   integer  $base    The number of rows as a base offset.
     * @param   string   $link    The link URL.
     * @param   boolean  $active  Flag whether the object is the 'active' page
     *
     * @since   1.5
     */
    public function __construct(public $text, public $prefix = '', public $base = null, public $link = null, public $active = false)
    {
    }
}
