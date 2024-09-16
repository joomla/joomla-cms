<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Table;

use Joomla\CMS\Access\Rules;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Tag\TaggableTableInterface;
use Joomla\CMS\Tag\TaggableTableTrait;
use Joomla\CMS\User\CurrentUserInterface;
use Joomla\CMS\User\CurrentUserTrait;
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Event\DispatcherInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Category table
 *
 * @since  1.5
 */
class Category extends Nested implements VersionableTableInterface, TaggableTableInterface, CurrentUserInterface
{
    use TaggableTableTrait;
    use CurrentUserTrait;

    /**
     * Indicates that columns fully support the NULL value in the database
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $_supportNullValue = true;

    /**
     * Constructor
     *
     * @param   DatabaseInterface     $db          Database connector object
     * @param   ?DispatcherInterface  $dispatcher  Event dispatcher for this table
     *
     * @since   1.5
     */
    public function __construct(DatabaseInterface $db, ?DispatcherInterface $dispatcher = null)
    {
        /**
         * @deprecated  4.0 will be removed in 6.0
         *              This format was used by tags and versioning before 4.0 before
         *              the introduction of the getTypeAlias function.
         */
        $this->typeAlias = '{extension}.category';
        parent::__construct('#__categories', 'id', $db, $dispatcher);
        $this->access = (int) Factory::getApplication()->get('access');
    }

    /**
     * Method to compute the default name of the asset.
     * The default name is in the form table_name.id
     * where id is the value of the primary key of the table.
     *
     * @return  string
     *
     * @since   1.6
     */
    protected function _getAssetName()
    {
        $k = $this->_tbl_key;

        return $this->extension . '.category.' . (int) $this->$k;
    }

    /**
     * Method to return the title to use for the asset table.
     *
     * @return  string
     *
     * @since   1.6
     */
    protected function _getAssetTitle()
    {
        return $this->title;
    }

    /**
     * Get the parent asset id for the record
     *
     * @param   ?Table    $table  A Table object for the asset parent.
     * @param   ?integer  $id     The id for the asset
     *
     * @return  integer  The id of the asset's parent
     *
     * @since   1.6
     */
    protected function _getAssetParentId(?Table $table = null, $id = null)
    {
        $assetId = null;

        // This is a category under a category.
        if ($this->parent_id > 1) {
            // Build the query to get the asset id for the parent category.
            $query = $this->_db->getQuery(true)
                ->select($this->_db->quoteName('asset_id'))
                ->from($this->_db->quoteName('#__categories'))
                ->where($this->_db->quoteName('id') . ' = :parentId')
                ->bind(':parentId', $this->parent_id, ParameterType::INTEGER);

            // Get the asset id from the database.
            $this->_db->setQuery($query);

            if ($result = $this->_db->loadResult()) {
                $assetId = (int) $result;
            }
        } elseif ($assetId === null) {
            // This is a category that needs to parent with the extension.
            // Build the query to get the asset id for the parent category.
            $query = $this->_db->getQuery(true)
                ->select($this->_db->quoteName('id'))
                ->from($this->_db->quoteName('#__assets'))
                ->where($this->_db->quoteName('name') . ' = :extension')
                ->bind(':extension', $this->extension);

            // Get the asset id from the database.
            $this->_db->setQuery($query);

            if ($result = $this->_db->loadResult()) {
                $assetId = (int) $result;
            }
        }

        // Return the asset id.
        if ($assetId) {
            return $assetId;
        }

        return parent::_getAssetParentId($table, $id);
    }

    /**
     * Override check function
     *
     * @return  boolean
     *
     * @see     Table::check()
     * @since   1.5
     */
    public function check()
    {
        try {
            parent::check();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        // Check for a title.
        if (trim($this->title) == '') {
            $this->setError(Text::_('JLIB_DATABASE_ERROR_MUSTCONTAIN_A_TITLE_CATEGORY'));

            return false;
        }

        $this->alias = trim($this->alias);

        if (empty($this->alias)) {
            $this->alias = $this->title;
        }

        $this->alias = ApplicationHelper::stringURLSafe($this->alias, $this->language);

        if (trim(str_replace('-', '', $this->alias)) == '') {
            $this->alias = Factory::getDate()->format('Y-m-d-H-i-s');
        }

        return true;
    }

    /**
     * Overloaded bind function.
     *
     * @param   array   $array   named array
     * @param   string  $ignore  An optional array or space separated list of properties
     *                           to ignore while binding.
     *
     * @return  mixed   Null if operation was satisfactory, otherwise returns an error
     *
     * @see     Table::bind()
     * @since   1.6
     */
    public function bind($array, $ignore = '')
    {
        if (isset($array['params']) && \is_array($array['params'])) {
            $registry        = new Registry($array['params']);
            $array['params'] = (string) $registry;
        }

        if (isset($array['metadata']) && \is_array($array['metadata'])) {
            $registry          = new Registry($array['metadata']);
            $array['metadata'] = (string) $registry;
        }

        // Bind the rules.
        if (isset($array['rules']) && \is_array($array['rules'])) {
            $rules = new Rules($array['rules']);
            $this->setRules($rules);
        }

        return parent::bind($array, $ignore);
    }

    /**
     * Overridden Table::store to set created/modified and user id.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success.
     *
     * @since   1.6
     */
    public function store($updateNulls = true)
    {
        $date = Factory::getDate()->toSql();
        $user = $this->getCurrentUser();

        // Set created date if not set.
        if (!(int) $this->created_time) {
            $this->created_time = $date;
        }

        if ($this->id) {
            // Existing category
            $this->modified_user_id = $user->id;
            $this->modified_time    = $date;
        } else {
            if (!(int) ($this->modified_time)) {
                $this->modified_time = $this->created_time;
            }

            // Field created_user_id can be set by the user, so we don't touch it if it's set.
            if (empty($this->created_user_id)) {
                $this->created_user_id = $user->id;
            }

            if (empty($this->modified_user_id)) {
                $this->modified_user_id = $this->created_user_id;
            }
        }

        // Verify that the alias is unique
        $table = new Category($this->getDbo(), $this->getDispatcher());

        if (
            $table->load(['alias' => $this->alias, 'parent_id' => (int) $this->parent_id, 'extension' => $this->extension])
            && ($table->id != $this->id || $this->id == 0)
        ) {
            // Is the existing category trashed?
            $this->setError(Text::_('JLIB_DATABASE_ERROR_CATEGORY_UNIQUE_ALIAS'));

            if ($table->published === -2) {
                $this->setError(Text::_('JLIB_DATABASE_ERROR_CATEGORY_UNIQUE_ALIAS_TRASHED'));
            }

            return false;
        }

        return parent::store($updateNulls);
    }

    /**
     * Get the type alias for the history table
     *
     * @return  string  The alias as described above
     *
     * @since   4.0.0
     */
    public function getTypeAlias()
    {
        return $this->extension . '.category';
    }
}
