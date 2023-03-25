<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Jtchuserbeforedel
 *
 * @author      Guido De Gobbis <support@joomtools.de>
 * @copyright   Copyright JoomTools.de - All rights reserved.
 * @license     GNU General Public License version 3 or later
 */

namespace Joomla\Plugin\User\BeforeDelete;

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
     * The extensions real name language string.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getExtensionRealNameLanguageString();

    /**
     * The extensions first/base part of the context.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getExtensionBaseContext();

    /**
     * The database table and columns about the user information to change.
     *
     * Expected array keys, if there is a table and column to change:
     * Example:
     * array(
     *      'tableName' => '#__content',
     *      'uniqueId'  => 'id',
     *      'author'    => 'created_by',
     *      'alias'     => 'created_by_alias',
     * )
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getColumsToChange();
}
