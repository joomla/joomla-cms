<?php

/**
 * @package           JED
 *
 * @subpackage        TICKETS
 *
 * @copyright     (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license           GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Site\Model;

// No direct access.
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;

// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Jed\Component\Jed\Site\Helper\JedHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Table\Table;
use Joomla\Utilities\ArrayHelper;

/**
 * JedTicket model.
 *
 * @since  4.0.0
 */
class JedticketModel extends ItemModel
{
    /** Data Table
     * @since 4.0.0
     **/
    private string $dbtable = "#__jed_jedtickets";

    /**
     * Method to check in an item.
     *
     * @param   int|null  $id  The id of the row to check out.
     *
     * @return  boolean True on success, false on failure.
     *
     * @since    4.0.0
     * @throws Exception
     */
    public function checkin(int $id = null): bool
    {
        // Get the id.
        $id = (!empty($id)) ? $id : (int)$this->getState('jedticket.id');
        if ($id || JedHelper::userIDItem($id, $this->dbtable) || JedHelper::isAdminOrSuperUser()) {
            if ($id) {
                // Initialise the table
                $table = $this->getTable();

                // Attempt to check the row in.
                if (method_exists($table, 'checkin')) {
                    if (!$table->checkin($id)) {
                        return false;
                    }
                }
            }

            return true;
        } else {
            throw new Exception(Text::_("JERROR_ALERTNOAUTHOR"), 401);
        }
    }

    /**
     * Method to check out an item for editing.
     *
     * @param   integer|null  $id  The id of the row to check out.
     *
     * @return  boolean True on success, false on failure.
     *
     * @since    4.0.0
     * @throws Exception
     */
    public function checkout(int $id = null): bool
    {
        // Get the user id.
        $id = (!empty($id)) ? $id : (int)$this->getState('jedticket.id');

        if ($id || JedHelper::userIDItem($id, $this->dbtable) || JedHelper::isAdminOrSuperUser()) {
            if ($id) {
                // Initialise the table
                $table = $this->getTable();

                // Get the current user object.
                $user = JedHelper::getUser();

                // Attempt to check the row out.
                if (method_exists($table, 'checkout')) {
                    if (!$table->checkout($user->get('id'), $id)) {
                        return false;
                    }
                }
            }

            return true;
        } else {
            throw new Exception(Text::_("JERROR_ALERTNOAUTHOR"), 401);
        }
    }

    /**
     * Method to get an object.
     *
     * @param   integer  $pk  The id of the object to get.
     *
     * @return  mixed    Object on success, false on failure.
     *
     * @since 4.0.0
     * @throws Exception
     *
     */
    public function getItem($pk = null)
    {
        $app = Factory::getApplication();

        if ($this->item === null) {
            $this->item = false;

            if (empty($pk)) {
                $pk = $this->getState('jedticket.id');
            }

            // Get a level row instance.
            $table = $this->getTable();

            // Attempt to load the row.
            if ($table->load($pk)) {
                if (empty($result) || JedHelper::isAdminOrSuperUser()) {
                    // Check published state.
                    if ($published = $this->getState('filter.published')) {
                        if (isset($table->state) && $table->state != $published) {
                            throw new Exception(Text::_('COM_JED_ITEM_NOT_LOADED'), 403);
                        }
                    }

                    // Convert the Table to a clean CMSObject.
                    $properties = $table->getProperties(1);
                    $this->item = ArrayHelper::toObject($properties, CMSObject::class);
                } else {
                    $app->enqueueMessage("Sorry you did not create that item", "message");

                    return null;
                }
            }

            if (empty($this->item)) {
                throw new Exception(Text::_('COM_JED_SECURITY_CANT_LOAD'), 404);
            }
        }


        if (!empty($this->item->ticket_origin) || $this->item->ticket_origin == 0) {
            $this->item->ticket_origin = Text::_(
                'COM_JED_JEDTICKETS_FIELD_TICKET_ORIGIN_LABEL_OPTION_' . $this->item->ticket_origin
            );
        }

        if (isset($this->item->ticket_category_type) && $this->item->ticket_category_type != '') {
            if (is_object($this->item->ticket_category_type)) {
                $this->item->ticket_category_type = ArrayHelper::fromObject($this->item->ticket_category_type);
            }

            $values = (is_array($this->item->ticket_category_type)) ? $this->item->ticket_category_type : explode(
                ',',
                $this->item->ticket_category_type
            );

            $textValue = [];

            foreach ($values as $value) {
                $db    = Factory::getContainer()->get('DatabaseDriver');
                $query = $db->getQuery(true);

                $query
                    ->select('`#__jed_ticket_categories_3583656`.`categorytype`')
                    ->from($db->quoteName('#__jed_ticket_categories', '#__jed_ticket_categories_3583656'))
                    ->where($db->quoteName('id') . ' = ' . $db->quote($value));

                $db->setQuery($query);
                $results = $db->loadObject();

                if ($results) {
                    $textValue[] = $results->categorytype;
                }
            }

            $this->item->ticket_category_type = !empty($textValue) ? implode(
                ', ',
                $textValue
            ) : $this->item->ticket_category_type;
        }

        if (isset($this->item->allocated_group) && $this->item->allocated_group != '') {
            if (is_object($this->item->allocated_group)) {
                $this->item->allocated_group = ArrayHelper::fromObject($this->item->allocated_group);
            }

            $values = (is_array($this->item->allocated_group)) ? $this->item->allocated_group : explode(
                ',',
                $this->item->allocated_group
            );

            $textValue = [];

            foreach ($values as $value) {
                $db    = Factory::getContainer()->get('DatabaseDriver');
                $query = $db->getQuery(true);

                $query
                    ->select('`#__jed_ticket_groups_3583668`.`role`')
                    ->from($db->quoteName('#__jed_ticket_groups', '#__jed_ticket_groups_3583668'))
                    ->where($db->quoteName('id') . ' = ' . $db->quote($value));

                $db->setQuery($query);
                $results = $db->loadObject();

                if ($results) {
                    $textValue[] = $results->role;
                }
            }

            $this->item->allocated_group = !empty($textValue) ? implode(
                ', ',
                $textValue
            ) : $this->item->allocated_group;
        }

        if (isset($this->item->allocated_to)) {
            $this->item->allocated_to_name = JedHelper::getUserById($this->item->allocated_to)->name;
        }

        if (isset($this->item->linked_item_type) && $this->item->linked_item_type != '') {
            if (is_object($this->item->linked_item_type)) {
                $this->item->linked_item_type = ArrayHelper::fromObject($this->item->linked_item_type);
            }

            $values = (is_array($this->item->linked_item_type)) ? $this->item->linked_item_type : explode(
                ',',
                $this->item->linked_item_type
            );

            $textValue = [];

            foreach ($values as $value) {
                $db    = Factory::getContainer()->get('DatabaseDriver');
                $query = $db->getQuery(true);

                $query
                    ->select('`#__jed_ticket_linked_item_types_3583670`.`title`')
                    ->from($db->quoteName('#__jed_ticket_linked_item_types', '#__jed_ticket_linked_item_types_3583670'))
                    ->where($db->quoteName('id') . ' = ' . $db->quote($value));

                $db->setQuery($query);
                $results = $db->loadObject();

                if ($results) {
                    $textValue[] = $results->title;
                }
            }

            $this->item->linked_item_type = !empty($textValue) ? implode(
                ', ',
                $textValue
            ) : $this->item->linked_item_type;
        }

        if (!empty($this->item->ticket_status) || $this->item->ticket_status == 0) {
            $this->item->ticket_status = Text::_('JSTATUS_OPTION_' . $this->item->ticket_status);
        }

        if (isset($this->item->created_by)) {
            $this->item->created_by_name = JedHelper::getUserById($this->item->created_by)->name;
        }

        if (isset($this->item->modified_by)) {
            $this->item->modified_by_name = JedHelper::getUserById($this->item->modified_by)->name;
        }

        return $this->item;
    }

    /**
     * Get the id of an item by alias
     *
     * @param   string  $alias  Item alias
     *
     * @return  mixed
     *
     * @since 4.0.0
     * @throws Exception
     */
    public function getItemIdByAlias(string $alias)
    {
        $table      = $this->getTable();
        $properties = $table->getProperties();
        $result     = null;
        $aliasKey   = null;

        $aliasKey = JedHelper::getAliasFieldNameByView('jedticket');


        if (key_exists('alias', $properties)) {
            $table->load(['alias' => $alias]);
            $result = $table->id;
        } elseif (isset($aliasKey) && key_exists($aliasKey, $properties)) {
            $table->load([$aliasKey => $alias]);
            $result = $table->id;
        }
        if (empty($result) || JedHelper::isAdminOrSuperUser() || $table->created_by == JedHelper::getUser()->id) {
            return $result;
        } else {
            throw new Exception(Text::_("JERROR_ALERTNOAUTHOR"), 401);
        }
    }

    /**
     * Get an instance of Table class
     *
     * @param   string  $name
     * @param   string  $prefix  Prefix for the table class name. Optional.
     * @param   array   $options
     *
     * @return  Table|bool Table if success, false on failure.
     *
     * @since 4.0.0
     * @throws Exception
     */
    public function getTable($name = 'Jedticket', $prefix = 'Administrator', $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return void
     *
     * @since    4.0.0
     *
     * @throws Exception
     */
    protected function populateState()
    {
        $app  = Factory::getApplication();
        $user = JedHelper::getUser();

        // Check published state
        if ((!$user->authorise('core.edit.state', 'com_jed')) && (!$user->authorise('core.edit', 'com_jed'))) {
            $this->setState('filter.published', 1);
            $this->setState('filter.archived', 2);
        }

        // Load state from the request userState on edit or from the passed variable on default
        if (Factory::getApplication()->input->get('layout') == 'edit') {
            $id = Factory::getApplication()->getUserState('com_jed.edit.jedticket.id');
        } else {
            $id = Factory::getApplication()->input->get('id');
            Factory::getApplication()->setUserState('com_jed.edit.jedticket.id', $id);
        }

        $this->setState('jedticket.id', $id);

        // Load the parameters.
        $params       = $app->getParams();
        $params_array = $params->toArray();

        if (isset($params_array['item_id'])) {
            $this->setState('jedticket.id', $params_array['item_id']);
        }

        $this->setState('params', $params);
    }

    /**
     * Method to delete an item
     *
     * @param   int  $id  Element id
     *
     * @return  bool
     * @since 4.0.0
     * @throws Exception
     */
    /*public function delete(int $id) : bool
    {
        $table = $this->getTable();

        if (empty($result) || JedHelper::isAdminOrSuperUser() || $table->created_by == JedHelper::getUser()->id)
        {
            return $table->delete($id);
        }
        else
        {
            throw new Exception(Text::_("JERROR_ALERTNOAUTHOR"), 401);
        }
    }*/

    /**
     * Publish the element
     *
     * @param   int  $id     Item id
     * @param   int  $state  Publish state
     *
     * @return  boolean
     * @since 4.0.0
     * @throws Exception
     */
    public function publish(int $id, int $state): bool
    {
        $table = $this->getTable();
        if ($id || JedHelper::userIDItem($id, $this->dbtable) || JedHelper::isAdminOrSuperUser()) {
            $table->load($id);
            $table->state = $state;

            return $table->store();
        } else {
            throw new Exception(Text::_("JERROR_ALERTNOAUTHOR"), 401);
        }
    }
}
