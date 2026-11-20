<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

// phpcs:disable PSR1.Files.SideEffects
use Joomla\Database\DatabaseInterface;

\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * TUF map table
 *
 * @since  5.1.0
 */
class Tuf extends Table
{
    /**
     * Constructor
     *
     * @param   DatabaseInterface  $db  A database connector object
     *
     * @since   5.1.0
     */
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__tuf_metadata', 'id', $db);
    }
}
