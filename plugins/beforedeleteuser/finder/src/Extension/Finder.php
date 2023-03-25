<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.Jtchuserbeforedel
 *
 * @author      Guido De Gobbis <support@joomtools.de>
 * @copyright   Copyright JoomTools.de - All rights reserved.
 * @license     GNU General Public License version 3 or later
 */

namespace Joomla\Plugin\BeforeDeleteUser\Finder\Extension;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Plugin\User\BeforeDelete\BeforeDeleteUserInterface;

/**
 * Class to support the core extension 'com_finder'.
 *
 * @since  1.0.0
 */
class Finder extends CMSPlugin implements BeforeDeleteUserInterface
{
    /**
     * The extensions real name language string.
     *
     * @return  string
     *
     * @since   1.0.0
     */
    public function getExtensionRealNameLanguageString()
    {
        return $this->getExtensionBaseContext();
    }

    /**
     * The extensions first/base part of the context.
     *
     * @return  string
     *
     * @since   1.0.0
     */
    public function getExtensionBaseContext()
    {
        return 'com_finder';
    }

    /**
     * The database table and columns about the user information to change.
     *
     * @return  array
     *
     * @since   1.0.0
     * @see     BeforeDeleteUserInterface
     */
    public function getColumsToChange()
    {
        return array(
            array(
                'tableName' => '#__finder_filters',
                'uniqueId'  => 'filter_id',
                'author'    => 'created_by',
                'alias'     => 'created_by_alias',
            ),
        );
    }
}
