<?php

/**
 * @package       JED
 *
 * @subpackage    Tickets
 *
 * @copyright     (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Administrator\Table;

// No direct access
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table as Table;
use Joomla\Database\DatabaseDriver;
use Jed\Component\Jed\Administrator\Helper\JedHelper;

/**
 * Jedticket table
 *
 * @since  4.0.0
 */
class JedticketTable extends Table
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
        $this->typeAlias = 'com_jed.jedticket';
        parent::__construct('#__jed_jedtickets', 'id', $db);
        $this->setColumnAlias('published', 'state');
    }

    /**
     * This function convert an array of Access objects into an rules array.
     *
     * @param   array  $jaccessrules  An array of Access objects.
     *
     * @return  array
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
     * Define a namespaced asset name for inclusion in the #__assets table
     *
     * @return string The asset name
     *
     * @see   Table::_getAssetName
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
     * @since   4.0.0
     * @throws Exception
     */
    public function bind($src, $ignore = ''): ?string
    {
        $date = Factory::getDate();


        // Support for multiple field: ticket_origin
        if (isset($src['ticket_origin'])) {
            if (is_array($src['ticket_origin'])) {
                $src['ticket_origin'] = implode(',', $src['ticket_origin']);
            } elseif (strpos($src['ticket_origin'], ',') != false) {
                $src['ticket_origin'] = explode(',', $src['ticket_origin']);
            } elseif (strlen($src['ticket_origin']) == 0) {
                $src['ticket_origin'] = '';
            }
        } else {
            $src['ticket_origin'] = '';
        }

        // Support for multiple or not foreign key field: ticket_category_type
        if (!empty($src['ticket_category_type'])) {
            if (is_array($src['ticket_category_type'])) {
                $src['ticket_category_type'] = implode(',', $src['ticket_category_type']);
            } elseif (strrpos($src['ticket_category_type'], ',') != false) {
                $src['ticket_category_type'] = explode(',', $src['ticket_category_type']);
            }
        } else {
            $src['ticket_category_type'] = 0;
        }

        // Support for multiple or not foreign key field: allocated_group
        if (!empty($src['allocated_group'])) {
            if (is_array($src['allocated_group'])) {
                $src['allocated_group'] = implode(',', $src['allocated_group']);
            } elseif (strrpos($src['allocated_group'], ',') != false) {
                $src['allocated_group'] = explode(',', $src['allocated_group']);
            }
        } else {
            $src['allocated_group'] = 0;
        }

        // Support for multiple or not foreign key field: linked_item_type
        if (!empty($src['linked_item_type'])) {
            if (is_array($src['linked_item_type'])) {
                $src['linked_item_type'] = implode(',', $src['linked_item_type']);
            } elseif (strrpos($src['linked_item_type'], ',') != false) {
                $src['linked_item_type'] = explode(',', $src['linked_item_type']);
            }
        } else {
            $src['linked_item_type'] = 0;
        }

        // Support for multiple field: ticket_status
        if (isset($src['ticket_status'])) {
            if (is_array($src['ticket_status'])) {
                $src['ticket_status'] = implode(',', $src['ticket_status']);
            } elseif (strpos($src['ticket_status'], ',') != false) {
                $src['ticket_status'] = explode(',', $src['ticket_status']);
            } elseif (strlen($src['ticket_status']) == 0) {
                $src['ticket_status'] = '';
            }
        } else {
            $src['ticket_status'] = '';
        }
        $input = Factory::getApplication()->input;
        $task  = $input->getString('task', '');

        if ($src['id'] == 0 && empty($src['created_by'])) {
            $src['created_by'] = JedHelper::getUser()->id;
        }

        if ($src['id'] == 0) {
            $src['created_on'] = $date->toSql();
        }

        if ($src['id'] == 0 && empty($src['modified_by'])) {
            $src['modified_by'] = JedHelper::getUser()->id;
        }

        if ($task == 'apply' || $task == 'save') {
            $src['modified_by'] = JedHelper::getUser()->id;
        }

        if ($task == 'apply' || $task == 'save') {
            $src['modified_on'] = $date->toSql();
        }


        if (!JedHelper::getUser()->authorise('core.admin', 'com_jed.jedticket.' . $src['id'])) {
            $actions         = Access::getActionsFromFile(
                JPATH_ADMINISTRATOR . '/components/com_jed/access.xml',
                "/access/section[@name='jedticket']/"
            );
            $default_actions = Access::getAssetRules('com_jed.jedticket.' . $src['id'])->getData();
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
}
