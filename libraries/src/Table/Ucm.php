<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * UCM map table
 *
 * @since  3.1
 */
class Ucm extends Table
{
    /**
     * Constructor
     *
     * @param   \Joomla\Database\DatabaseDriver  $db  A database connector object
     *
     * @since   3.1
     */
    public function __construct($db)
    {
        parent::__construct('#__ucm_base', 'ucm_id', $db);
    }
}
