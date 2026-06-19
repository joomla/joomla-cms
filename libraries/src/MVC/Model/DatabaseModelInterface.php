<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\MVC\Model;

use Joomla\Database\DatabaseInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Interface for a database model.
 *
 * @since  4.0.0
 *
 * @deprecated  __DEPLOY_VERSION__ will be removed in 7.0
 *              Use the interface from the database package
 *              Example: \Joomla\Database\DatabaseAwareInterface
 */
interface DatabaseModelInterface
{
    /**
     * Method to get the database driver object.
     *
     * @return  DatabaseInterface
     *
     * @since   4.0.0
     */
    public function getDbo();
}
