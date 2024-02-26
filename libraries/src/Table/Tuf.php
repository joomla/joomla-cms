<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * TUF map table
 *
 * @since  __DEPLOY_VERSION__
 */
class Tuf extends Table
{
    /**
     * Constructor
     *
     * @param   \Joomla\Database\DatabaseDriver  $db  A database connector object
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct($db)
    {
        parent::__construct('#__tuf_metadata', 'id', $db);
    }
}
