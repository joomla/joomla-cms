<?php

/**
 * @package        JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Administrator\Table;

// No direct access
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Jed\Component\Jed\Administrator\Helper\JedHelper;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Table\Table as Table;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;

/**
 * Extension table
 *
 * @since 4.0.0
 */
class ExtensionTable extends Table
{
    /**
     * Constructor
     *
     * @param   DatabaseDriver  $db  A database connector object
     *
     * @since 4.0.0
     */
    public function __construct(DatabaseDriver $db)
    {
        $this->typeAlias = 'com_jed.extension';
        parent::__construct('#__jed_extensions', 'id', $db);
        $this->setColumnAlias('published', 'state');
    }

    /**
     * Define a namespaced asset name for inclusion in the #__assets table
     *
     * @return string The asset name
     *
     * @see Table::_getAssetName
     *
     * @since 4.0.0
     */
    protected function _getAssetName(): string
    {
        $k = $this->_tbl_key;

        return $this->typeAlias . '.' . (int) $this->$k;
    }

    /**
     * Overloaded bind function to pre-process the params.
     *
     * @param   array  $src     Named array
     * @param   mixed  $ignore  Optional array or list of parameters to ignore
     *
     * @return  null|string  null is operation was satisfactory, otherwise returns an error
     *
     * @see     Table:bind
     * @throws Exception
     * @since   4.0.0
     */
    public function bind($src, $ignore = ''): ?string
    {
        $date = Factory::getDate();
        $task = Factory::getApplication()->input->get('task');


        // Support for alias field: alias
        if (empty($src['alias'])) {
            if (empty($src['title'])) {
                $src['alias'] = OutputFilter::stringURLSafe(date('Y-m-d H:i:s'));
            } else {
                if (Factory::getConfig()->get('unicodeslugs') == 1) {
                    $src['alias'] = OutputFilter::stringURLUnicodeSlug(trim($src['title']));
                } else {
                    $src['alias'] = OutputFilter::stringURLSafe(trim($src['title']));
                }
            }
        }


        // Support for checkbox field: published
        if (!isset($src['published'])) {
            $src['published'] = 0;
        }

        // Support for checkbox field: checked_out
        if (!isset($src['checked_out'])) {
            $src['checked_out'] = 0;
        }

        if ($src['id'] == 0 && empty($src['created_by'])) {
            $src['created_by'] = JedHelper::getUser()->id;
        }

        if ($src['id'] == 0 && empty($src['modified_by'])) {
            $src['modified_by'] = JedHelper::getUser()->id;
        }

        if ($task == 'apply' || $task == 'save') {
            $src['modified_by'] = JedHelper::getUser()->id;
        }

        if ($src['id'] == 0) {
            $src['created_on'] = $date->toSql();
        }

        if ($task == 'apply' || $task == 'save') {
            $src['modified_on'] = $date->toSql();
        }

        // Support for checkbox field: popular
        if (!isset($src['popular'])) {
            $src['popular'] = 0;
        }

        // Support for checkbox field: requires_registration
        if (!isset($src['requires_registration'])) {
            $src['requires_registration'] = 0;
        }

        // Support for checkbox field: can_update
        if (!isset($src['can_update'])) {
            $src['can_update'] = 0;
        }

        // Support for multiple field: uses_updater
        if (isset($src['uses_updater'])) {
            if (is_array($src['uses_updater'])) {
                $src['uses_updater'] = implode(',', $src['uses_updater']);
            } elseif (strpos($src['uses_updater'], ',') != false) {
                $src['uses_updater'] = explode(',', $src['uses_updater']);
            } elseif (strlen($src['uses_updater']) == 0) {
                $src['uses_updater'] = '';
            }
        } else {
            $src['uses_updater'] = '';
        }

        // Support for checkbox field: approved
        if (!isset($src['approved'])) {
            $src['approved'] = 0;
        }

        // Support for checkbox field: jed_checked
        if (!isset($src['jed_checked'])) {
            $src['jed_checked'] = 0;
        }

        // Support for checkbox field: uses_third_party
        if (!isset($src['uses_third_party'])) {
            $src['uses_third_party'] = 0;
        }

        // Support for multiple field: primary_category_id
        if (isset($src['primary_category_id'])) {
            if (is_array($src['primary_category_id'])) {
                $src['primary_category_id'] = implode(',', $src['primary_category_id']);
            } elseif (strpos($src['primary_category_id'], ',') != false) {
                $src['primary_category_id'] = explode(',', $src['primary_category_id']);
            } elseif (strlen($src['primary_category_id']) == 0) {
                $src['primary_category_id'] = '';
            }
        } else {
            $src['primary_category_id'] = '';
        }
        $input = Factory::getApplication()->input;
        $task  = $input->getString('task', '');

        if (isset($src['params']) && is_array($src['params'])) {
            $registry = new Registry();
            $registry->loadArray($src['params']);
            $src['params'] = (string) $registry;
        }

        if (isset($src['metadata']) && is_array($src['metadata'])) {
            $registry = new Registry();
            $registry->loadArray($src['metadata']);
            $src['metadata'] = (string) $registry;
        }

        if (!JedHelper::getUser()->authorise('core.admin', 'com_jed.extension.' . $src['id'])) {
            $actions         = Access::getActionsFromFile(
                JPATH_ADMINISTRATOR . '/components/com_jed/access.xml',
                "/access/section[@name='extension']/"
            );
            $default_actions = Access::getAssetRules('com_jed.extension.' . $src['id'])->getData();
            $array_jaccess   = [];

            foreach ($actions as $action) {
                if (key_exists($action->name, $default_actions)) {
                    $array_jaccess[$action->name] = $default_actions[$action->name];
                }
            }

            $src['rules'] = $this->JAccessRulestoArray($array_jaccess);
        }

        // Bind the rules for ACL where supported.
        if (isset($src['rules']) && is_array($src['rules'])) {
            $this->setRules($src['rules']);
        }

        return parent::bind($src, $ignore);
    }

    /**
     * Overloaded check function
     *
     * @return bool
     *
     * @since 4.0.0
     */
    public function check(): bool
    {
        // If there is an ordering column and this is a new row then get the next ordering value
        if (property_exists($this, 'ordering') && $this->get('id') == 0) {
            $this->ordering = self::getNextOrder();
        }

        // Check if alias is unique
        if (!$this->isUnique('alias')) {
            $count        = 0;
            $currentAlias = $this->get('alias');
            while (!$this->isUnique($this->get('alias'))) {
            }
            {
                $this->set('alias', $currentAlias . '-' . $count++);
            }
        }


        return parent::check();
    }

    /**
     * Delete a record by id
     *
     * @param   mixed  $pk  Primary key value to delete. Optional
     *
     * @return bool
     *
     * @since 4.0.0
     */
    public function delete($pk = null): bool
    {
        $this->load($pk);
        return parent::delete($pk);
    }

    /**
     * Get the type alias for the history table
     *
     * @return  string  The alias as described above
     *
     * @since   4.0.0
     */
    public function getTypeAlias(): string
    {
        return $this->typeAlias;
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
     * @since   4.0.0
     */
    public function store($updateNulls = true): bool
    {
        return parent::store($updateNulls);
    }

    /**
     * This function convert an array of Access objects into an rules array.
     *
     * @param   array  $jaccessrules  An array of Access objects.
     *
     * @return  array
     *
     * @since 4.0.0
     */
    private function JAccessRulestoArray(array $jaccessrules): array
    {
        $rules = [];

        foreach ($jaccessrules as $action => $jaccess) {
            $actions = [];

            if ($jaccess) {
                foreach ($jaccess->getData() as $group => $allow) {
                    $actions[$group] = ((bool) $allow);
                }
            }

            $rules[$action] = $actions;
        }

        return $rules;
    }


    /**
     * Check if a field is unique
     *
     * @param   string  $field  Name of the field
     *
     * @return bool True if unique
     * @since 4.0.0
     */
    private function isUnique($field): bool
    {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);

        $categories        = explode(',', $this->primary_category_id);
        $andWhereCondition = [];
        foreach ($categories as $categoryid) {
            $andWhereCondition[] = $db->quoteName('primary_category_id') . ' like "%' . $categoryid . '%"';
        }


        $query
            ->select($db->quoteName($field))
            ->from($db->quoteName($this->_tbl))
            ->where($db->quoteName($field) . ' = ' . $db->quote($this->$field))
            ->where($db->quoteName('id') . ' <> ' . (int) $this->{$this->_tbl_key});

        if (!empty($andWhereCondition)) {
            $query->andWhere($andWhereCondition);
        }


        $db->setQuery($query);
        $db->execute();

        return ($db->getNumRows() == 0) ? true : false;
    }
}
