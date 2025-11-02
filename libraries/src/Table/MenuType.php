<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\CurrentUserInterface;
use Joomla\CMS\User\CurrentUserTrait;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Menu Types table
 *
 * @since  1.6
 */
class MenuType extends Table implements CurrentUserInterface
{
    use CurrentUserTrait;

    /**
     * Constructor
     *
     * @param   DatabaseInterface     $db          Database connector object
     * @param   ?DispatcherInterface  $dispatcher  Event dispatcher for this table
     *
     * @since   1.6
     */
    public function __construct(DatabaseInterface $db, ?DispatcherInterface $dispatcher = null)
    {
        parent::__construct('#__menu_types', 'id', $db, $dispatcher);
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
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('COUNT(id)')
            ->from($db->quoteName('#__menu_types'))
            ->where($db->quoteName('menutype') . ' = :menutype')
            ->where($db->quoteName('id') . ' <> :id')
            ->bind(':menutype', $this->menutype)
            ->bind(':id', $id, ParameterType::INTEGER);
        $db->setQuery($query);

        if ($db->loadResult()) {
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
            $userId = (int) $this->getCurrentUser()->id;
            $notIn  = [0, $userId];

            // Get the old value of the table
            $db    = $this->getDatabase();
            $table = new Menutype($db, $this->getDispatcher());
            $table->load($this->id);

            // Verify that no items are checked out
            $query = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__menu'))
                ->where($db->quoteName('menutype') . ' = :menutype')
                ->whereNotIn($db->quoteName('checked_out'), $notIn)
                ->bind(':menutype', $table->menutype);
            $db->setQuery($query);

            if ($db->loadRowList()) {
                $this->setError(
                    Text::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', \get_class($this), Text::_('JLIB_DATABASE_ERROR_MENUTYPE_CHECKOUT'))
                );

                return false;
            }

            // Verify that no module for this menu are checked out
            $searchParams = '%"menutype":' . json_encode($table->menutype) . '%';
            $query->clear()
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__modules'))
                ->where($db->quoteName('module') . ' = ' . $db->quote('mod_menu'))
                ->where($db->quoteName('params') . ' LIKE :params')
                ->whereNotIn($db->quoteName('checked_out'), $notIn)
                ->bind(':params', $searchParams);
            $db->setQuery($query);

            if ($db->loadRowList()) {
                $this->setError(
                    Text::sprintf('JLIB_DATABASE_ERROR_STORE_FAILED', \get_class($this), Text::_('JLIB_DATABASE_ERROR_MENUTYPE_CHECKOUT'))
                );

                return false;
            }

            // Update the menu items
            $query->clear()
                ->update($db->quoteName('#__menu'))
                ->set($db->quoteName('menutype') . ' = :setmenutype')
                ->where($db->quoteName('menutype') . ' = :menutype')
                ->bind(':setmenutype', $this->menutype)
                ->bind(':menutype', $table->menutype);
            $db->setQuery($query);
            $db->execute();

            // Update the module items
            $whereParams   = '%"menutype":' . json_encode($table->menutype) . '%';
            $searchParams  = '"menutype":' . json_encode($table->menutype);
            $replaceParams = '"menutype":' . json_encode($this->menutype);
            $query->clear()
                ->update($db->quoteName('#__modules'))
                ->set(
                    $db->quoteName('params') . ' = REPLACE(' . $db->quoteName('params') . ', :search, :value)'
                );
            $query->where($db->quoteName('module') . ' = ' . $db->quote('mod_menu'))
                ->where($db->quoteName('params') . ' LIKE :whereparams')
                ->bind(':search', $searchParams)
                ->bind(':value', $replaceParams)
                ->bind(':whereparams', $whereParams);
            $db->setQuery($query);
            $db->execute();
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
        $pk = $pk ?? $this->$k;

        // If no primary key is given, return false.
        if ($pk !== null) {
            // Get the user id
            $userId = (int) $this->getCurrentUser()->id;
            $notIn  = [0, $userId];
            $star   = '*';

            // Get the old value of the table
            $db    = $this->getDatabase();
            $table = new Menutype($db, $this->getDispatcher());
            $table->load($pk);

            // Verify that no items are checked out
            $query = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__menu'))
                ->where($db->quoteName('menutype') . ' = :menutype')
                ->where('(' .
                    $db->quoteName('checked_out') . ' NOT IN (NULL, :id)' .
                    ' OR ' . $db->quoteName('home') . ' = 1' .
                    ' AND ' . $db->quoteName('language') . ' = :star' .
                    ')')
                ->bind(':menutype', $table->menutype)
                ->bind(':id', $userId, ParameterType::INTEGER)
                ->bind(':star', $star);
            $db->setQuery($query);

            if ($db->loadRowList()) {
                $this->setError(Text::sprintf('JLIB_DATABASE_ERROR_DELETE_FAILED', \get_class($this), Text::_('JLIB_DATABASE_ERROR_MENUTYPE')));

                return false;
            }

            // Verify that no module for this menu are checked out
            $searchParams = '%"menutype":' . json_encode($table->menutype) . '%';
            $query->clear()
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__modules'))
                ->where($db->quoteName('module') . ' = ' . $db->quote('mod_menu'))
                ->where($db->quoteName('params') . ' LIKE :menutype')
                ->whereNotIn($db->quoteName('checked_out'), $notIn)
                ->bind(':menutype', $searchParams);
            $db->setQuery($query);

            if ($db->loadRowList()) {
                $this->setError(Text::sprintf('JLIB_DATABASE_ERROR_DELETE_FAILED', \get_class($this), Text::_('JLIB_DATABASE_ERROR_MENUTYPE')));

                return false;
            }

            // Delete the menu items
            $query->clear()
                ->delete('#__menu')
                ->where('menutype=' . $db->quote($table->menutype));
            $db->setQuery($query);
            $db->execute();

            // Update the module items
            $query->clear()
                ->delete('#__modules')
                ->where('module=' . $db->quote('mod_menu'))
                ->where('params LIKE ' . $db->quote('%"menutype":' . json_encode($table->menutype) . '%'));
            $db->setQuery($query);
            $db->execute();
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
     * @param   ?Table    $table  A Table object for the asset parent.
     * @param   ?integer  $id     Id to look up
     *
     * @return  integer
     *
     * @since   3.6
     */
    protected function _getAssetParentId(?Table $table = null, $id = null)
    {
        $assetId = null;
        $asset   = Table::getInstance('asset');

        if ($asset->loadByName('com_menus')) {
            $assetId = $asset->id;
        }

        return $assetId ?? parent::_getAssetParentId($table, $id);
    }
}
