<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Menu Types table
 *
 * @since  1.6
 */
class MenuType extends Table
{
    /**
     * Constructor
     *
     * @param   DatabaseDriver  $db  Database driver object.
     *
     * @since   1.6
     */
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__menu_types', 'id', $db);
    }

    /**
     * Overloaded check function
     *
     * @return  boolean  True on success, false on failure
     *
     * @see     Table::check()
     * @since   1.6
     */
    public function check()
    {
        try {
            parent::check();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        $this->menutype = ApplicationHelper::stringURLSafe($this->menutype);

        if (empty($this->menutype)) {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_MENUTYPE_EMPTY'));

            return false;
        }

        // Sanitise data.
        if (trim($this->title) === '') {
            $this->title = $this->menutype;
        }

        $id = (int) $this->id;

        // Check for unique menutype.
        $query = $this->_db->getQuery(true)
            ->select('COUNT(id)')
            ->from($this->_db->quoteName('#__menu_types'))
            ->where($this->_db->quoteName('menutype') . ' = :menutype')
            ->where($this->_db->quoteName('id') . ' <> :id')
            ->bind(':menutype', $this->menutype)
            ->bind(':id', $id, ParameterType::INTEGER);
        $this->_db->setQuery($query);

        if ($this->_db->loadResult()) {
            $this->setError(Text::sprintf('JLIB_DATABASE_ERROR_MENUTYPE_EXISTS', $this->menutype));

            return false;
        }

        return true;
    }

    /**
     * Method to store a row in the database from the Table instance properties.
     *
     * If a primary key value is set the row with that primary key value will be updated with the instance property values.
     * If no primary key value is set a new row will be inserted into the database with the properties from the Table instance.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success.
     *
     * @since   1.6
     */
    public function store($updateNulls = false)
    {
        if ($this->id) {
            // Get the user id
            $userId = (int) Factory::getUser()->id;
            $notIn  = [0, $userId];

            // Get the old value of the table
            $table = Table::getInstance('Menutype', 'JTable', ['dbo' => $this->getDbo()]);
            $table->load($this->id);

            // Verify that no items are checked out
            $query = $this->_db->getQuery(true)
                ->select($this->_db->quoteName('id'))
                ->from($this->_db->quoteName('#__menu'))
                ->where($this->_db->quoteName('menutype') . ' = :menutype')
                ->whereNotIn($this->_db->quoteName('checked_out'), $notIn)
                ->bind(':menutype', $table->menutype);
            $this->_db->setQuery($query);

            if ($this->_db->loadRowList()) {
                $this->setError(
                    Text::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', \get_class($this), Text::_('JLIB_DATABASE_ERROR_MENUTYPE_CHECKOUT'))
                );

                return false;
            }

            // Verify that no module for this menu are checked out
            $searchParams = '%"menutype":' . json_encode($table->menutype) . '%';
            $query->clear()
                ->select($this->_db->quoteName('id'))
                ->from($this->_db->quoteName('#__modules'))
                ->where($this->_db->quoteName('module') . ' = ' . $this->_db->quote('mod_menu'))
                ->where($this->_db->quoteName('params') . ' LIKE :params')
                ->whereNotIn($this->_db->quoteName('checked_out'), $notIn)
                ->bind(':params', $searchParams);
            $this->_db->setQuery($query);

            if ($this->_db->loadRowList()) {
                $this->setError(
                    Text::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', \get_class($this), Text::_('JLIB_DATABASE_ERROR_MENUTYPE_CHECKOUT'))
                );

                return false;
            }

            // Update the menu items
            $query->clear()
                ->update($this->_db->quoteName('#__menu'))
                ->set($this->_db->quoteName('menutype') . ' = :setmenutype')
                ->where($this->_db->quoteName('menutype') . ' = :menutype')
                ->bind(':setmenutype', $this->menutype)
                ->bind(':menutype', $table->menutype);
            $this->_db->setQuery($query);
            $this->_db->execute();

            // Update the module items
            $whereParams   = '%"menutype":' . json_encode($table->menutype) . '%';
            $searchParams  = '"menutype":' . json_encode($table->menutype);
            $replaceParams = '"menutype":' . json_encode($this->menutype);
            $query->clear()
                ->update($this->_db->quoteName('#__modules'))
                ->set(
                    $this->_db->quoteName('params') . ' = REPLACE(' . $this->_db->quoteName('params') . ', :search, :value)'
                );
            $query->where($this->_db->quoteName('module') . ' = ' . $this->_db->quote('mod_menu'))
                ->where($this->_db->quoteName('params') . ' LIKE :whereparams')
                ->bind(':search', $searchParams)
                ->bind(':value', $replaceParams)
                ->bind(':whereparams', $whereParams);
            $this->_db->setQuery($query);
            $this->_db->execute();
        }

        return parent::store($updateNulls);
    }

    /**
     * Method to delete a row from the database table by primary key value.
     *
     * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
     *
     * @return  boolean  True on success.
     *
     * @since   1.6
     */
    public function delete($pk = null)
    {
        $k  = $this->_tbl_key;
        $pk = $pk === null ? $this->$k : $pk;

        // If no primary key is given, return false.
        if ($pk !== null) {
            // Get the user id
            $userId = (int) Factory::getUser()->id;
            $notIn  = [0, $userId];
            $star   = '*';

            // Get the old value of the table
            $table = Table::getInstance('Menutype', 'JTable', ['dbo' => $this->getDbo()]);
            $table->load($pk);

            // Verify that no items are checked out
            $query = $this->_db->getQuery(true)
                ->select($this->_db->quoteName('id'))
                ->from($this->_db->quoteName('#__menu'))
                ->where($this->_db->quoteName('menutype') . ' = :menutype')
                ->where('(' .
                    $this->_db->quoteName('checked_out') . ' NOT IN (NULL, :id)' .
                    ' OR ' . $this->_db->quoteName('home') . ' = 1' .
                    ' AND ' . $this->_db->quoteName('language') . ' = :star' .
                    ')')
                ->bind(':menutype', $table->menutype)
                ->bind(':id', $userId, ParameterType::INTEGER)
                ->bind(':star', $star);
            $this->_db->setQuery($query);

            if ($this->_db->loadRowList()) {
                $this->setError(Text::sprintf('JLIB_DATABASE_ERROR_DELETE_FAILED', \get_class($this), Text::_('JLIB_DATABASE_ERROR_MENUTYPE')));

                return false;
            }

            // Verify that no module for this menu are checked out
            $searchParams = '%"menutype":' . json_encode($table->menutype) . '%';
            $query->clear()
                ->select($this->_db->quoteName('id'))
                ->from($this->_db->quoteName('#__modules'))
                ->where($this->_db->quoteName('module') . ' = ' . $this->_db->quote('mod_menu'))
                ->where($this->_db->quoteName('params') . ' LIKE :menutype')
                ->whereNotIn($this->_db->quoteName('checked_out'), $notIn)
                ->bind(':menutype', $searchParams);
            $this->_db->setQuery($query);

            if ($this->_db->loadRowList()) {
                $this->setError(Text::sprintf('JLIB_DATABASE_ERROR_DELETE_FAILED', \get_class($this), Text::_('JLIB_DATABASE_ERROR_MENUTYPE')));

                return false;
            }

            // Delete the menu items
            $query->clear()
                ->delete('#__menu')
                ->where('menutype=' . $this->_db->quote($table->menutype));
            $this->_db->setQuery($query);
            $this->_db->execute();

            // Update the module items
            $query->clear()
                ->delete('#__modules')
                ->where('module=' . $this->_db->quote('mod_menu'))
                ->where('params LIKE ' . $this->_db->quote('%"menutype":' . json_encode($table->menutype) . '%'));
            $this->_db->setQuery($query);
            $this->_db->execute();
        }

        return parent::delete($pk);
    }

    /**
     * Method to compute the default name of the asset.
     * The default name is in the form table_name.id
     * where id is the value of the primary key of the table.
     *
     * @return  string
     *
     * @since   3.6
     */
    protected function _getAssetName()
    {
        return 'com_menus.menu.' . $this->id;
    }

    /**
     * Method to return the title to use for the asset table.
     *
     * @return  string
     *
     * @since   3.6
     */
    protected function _getAssetTitle()
    {
        return $this->title;
    }

    /**
     * Method to get the parent asset under which to register this one.
     * By default, all assets are registered to the ROOT node with ID,
     * which will default to 1 if none exists.
     * The extended class can define a table and id to lookup.  If the
     * asset does not exist it will be created.
     *
     * @param   Table    $table  A Table object for the asset parent.
     * @param   integer  $id     Id to look up
     *
     * @return  integer
     *
     * @since   3.6
     */
    protected function _getAssetParentId(Table $table = null, $id = null)
    {
        $assetId = null;
        $asset   = Table::getInstance('asset');

        if ($asset->loadByName('com_menus')) {
            $assetId = $asset->id;
        }

        return $assetId === null ? parent::_getAssetParentId($table, $id) : $assetId;
    }
}
