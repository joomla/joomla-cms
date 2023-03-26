<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtours
 *
 * @copyright     (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Administrator\Table;

use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Tours table class.
 *
 * @since 4.3.0
 */
class TourTable extends Table
{
    /**
     * Indicates that columns fully support the NULL value in the database
     *
     * @var    boolean
     * @since  4.3.0
     */
    // phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
    protected $_supportNullValue = true;

    /**
     * An array of key names to be json encoded in the bind function
     *
     * @var    array
     * @since  4.3.0
     */
    // phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
    protected $_jsonEncode = ['extensions'];

    /**
     * Constructor
     *
     * @param   DatabaseDriver $db Database connector object
     *
     * @since   4.3.0
     */
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__guidedtours', 'id', $db);
    }

    /**
     * Stores a tour.
     *
     * @param   boolean $updateNulls True to update extensions even if they are null.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   4.3.0
     */
    public function store($updateNulls = true)
    {
        $date   = Factory::getDate()->toSql();
        $userId = Factory::getUser()->id;

        // Set created date if not set.
        if (!(int) $this->created) {
            $this->created = $date;
        }

        if ($this->id) {
            // Existing item
            $this->modified_by = $userId;
            $this->modified    = $date;
        } else {
            // Field created_by field can be set by the user, so we don't touch it if it's set.
            if (empty($this->created_by)) {
                $this->created_by = $userId;
            }

            if (!(int) $this->modified) {
                $this->modified = $date;
            }

            if (empty($this->modified_by)) {
                $this->modified_by = $userId;
            }
        }

        if (empty($this->extensions)) {
            $this->extensions = ["*"];
        }

        return parent::store($updateNulls);
    }

    /**
     * Returns the asset name of the entry as it appears in the {@see Asset} table.
     *
     * @return  string  The asset name.
     *
     * @since   4.3.0
     */
    // phpcs:ignore
    protected function _getAssetName(): string
    {
        $k = $this->_tbl_key;

        return 'com_guidedtours.tour.' . (int) $this->$k;
    }

    /**
     * Method to return the title to use for the asset table.
     *
     * @return  string  The string to use as the title in the asset table.
     *
     * @since   4.3.0
     */
    // phpcs:ignore
    protected function _getAssetTitle()
    {
        return $this->title;
    }
    
    /**
     * Method to get the parent asset under which to register this one.
     *
     * By default, all assets are registered to the ROOT node with ID, which will default to 1 if none exists.
     * The extended class can define a table and id to lookup.  If the asset does not exist it will be created.
     *
     * @param   Table    $table  A Table object for the asset parent.
     * @param   integer  $id     Id to look up
     *
     * @return  integer
     *
     * @since   4.3.0
     */
    // phpcs:ignore
    protected function _getAssetParentId(Table $table = null, $id = null)
    {
        // We retrieve the parent-asset from the Asset-table
        $assetParent = Table::getInstance('Asset');
        
        $assetParent->loadByName('com_guidedtours');
        
        if ($assetParent->id) {
            return $assetParent->id;
        }
        
        return parent::_getAssetParentId($table, $id);
    }
}
