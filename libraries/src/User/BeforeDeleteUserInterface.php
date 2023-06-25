<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\User;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Description.
 *
 * @since  __DEPLOY_VERSION__
 */
interface BeforeDeleteUserInterface
{
    /**
     * The list of database table and columns, where the user information to change.
     *
     * Expected an array list, like the following example containing the key => value pairs
     * with the table and columns to change.
     * Example:
     * array(
     *      array(
     *          'baseContext' => 'com_content',         // Extension base context
     *          'realName'    => 'com_content',         // Language string
     *          'tableName'   => '#__content',          // Database table name
     *          'primaryKey'  => 'id',                  // Primary or unique key of the table
     *          'userId '     => array(                 // List of column names for the user id
     *                               'created_by',
     *                               'modified_by',
     *                           ),
     *          'userName'    => array(                 // List of column names for the user real name
     *                               'created_by_alias'
     *                           ),
     *      ),
     * )
     *
     * @return  array[]
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getTablesListToChangeUser();
}
