<?php

/**
 * @package        JED
 *
 * @copyright  (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
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
 * Jed model.
 *
 * @since  4.0.0
 */
class ReviewcommentModel extends ItemModel
{
    /**
     * Method to check in an item.
     *
     * @param   integer  $id  The id of the row to check out.
     *
     * @return  boolean True on success, false on failure.
     *
     * @since   4.0.0
     * @throws Exception
     * @throws Exception
     */
    public function checkin($id = null)
    {
        // Get the id.
        $id = (!empty($id)) ? $id : (int)$this->getState('reviewcomment.id');
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
     * @param   integer  $id  The id of the row to check out.
     *
     * @return  boolean True on success, false on failure.
     *
     * @since   4.0.0
     * @throws Exception
     * @throws Exception
     */
    public function checkout($id = null)
    {
        // Get the user id.
        $id = (!empty($id)) ? $id : (int)$this->getState('reviewcomment.id');

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
     * Method to delete an item
     *
     * @param   int  $id  Element id
     *
     * @return  bool
     * @throws Exception
     * @throws Exception
     */
    public function delete($id)
    {
        $table = $this->getTable();

        if (empty($result) || $this->isAdminOrSuperUser() || $table->created_by == JedHelper::getUser()->id) {
            return $table->delete($id);
        } else {
            throw new Exception(Text::_("JERROR_ALERTNOAUTHOR"), 401);
        }
    }

    /**
     * Method to get an object.
     *
     * @param   integer  $id  The id of the object to get.
     *
     * @return  mixed    Object on success, false on failure.
     *
     * @throws Exception
     * @throws Exception
     * @throws Exception
     */
    public function getItem($id = null)
    {
        if ($this->item === null) {
            $this->item = false;

            if (empty($id)) {
                $id = $this->getState('reviewcomment.id');
            }

            // Get a level row instance.
            $table = $this->getTable();

            // Attempt to load the row.
            if ($table && $table->load($id)) {
                if (empty($result) || $this->isAdminOrSuperUser() || $table->created_by == JedHelper::getUser()->id) {
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
                    throw new Exception(Text::_("JERROR_ALERTNOAUTHOR"), 401);
                }
            }

            if (empty($this->item)) {
                throw new Exception(Text::_('COM_JED_ITEM_NOT_LOADED'), 404);
            }
        }


        if (isset($this->item->created_by)) {
            $this->item->created_by_name = JedHelper::getUser($this->item->created_by)->name;
        }

        return $this->item;
    }

    /**
     * Get the id of an item by alias
     *
     * @param   string  $alias  Item alias
     *
     * @return  mixed
     * @throws Exception
     * @throws Exception
     */
    public function getItemIdByAlias($alias)
    {
        $table      = $this->getTable();
        $properties = $table->getProperties();
        $result     = null;
        $aliasKey   = null;

        $aliasKey = JedHelper::getAliasFieldNameByView('reviewcomment');


        if (key_exists('alias', $properties)) {
            $table->load(['alias' => $alias]);
            $result = $table->id;
        } elseif (isset($aliasKey) && key_exists($aliasKey, $properties)) {
            $table->load([$aliasKey => $alias]);
            $result = $table->id;
        }
        if (empty($result) || $this->isAdminOrSuperUser() || $table->created_by == JedHelper::getUser()->id) {
            return $result;
        } else {
            throw new Exception(Text::_("JERROR_ALERTNOAUTHOR"), 401);
        }
    }

    /**
     * Get an instance of Table class
     *
     * @param   string  $type    Name of the Table class to get an instance of.
     * @param   string  $prefix  Prefix for the table class name. Optional.
     * @param   array   $config  Array of configuration values for the Table object. Optional.
     *
     * @return  Table|bool Table if success, false on failure.
     * @throws Exception
     * @throws Exception
     */
    public function getTable($type = 'Reviewcomment', $prefix = 'Administrator', $config = [])
    {
        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Checks whether or not a user is manager or super user
     *
     * @return bool
     */
    public function isAdminOrSuperUser()
    {
        try {
            $user = JedHelper::getUser();

            return in_array("8", $user->groups) || in_array("7", $user->groups);
        } catch (Exception $exc) {
            return false;
        }
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return  void
     *
     * @since   4.0.0
     *
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     */
    protected function populateState()
    {
        $app  = Factory::getApplication('com_jed');
        $user = JedHelper::getUser();

        // Check published state
        if ((!$user->authorise('core.edit.state', 'com_jed')) && (!$user->authorise('core.edit', 'com_jed'))) {
            $this->setState('filter.published', 1);
            $this->setState('filter.archived', 2);
        }

        // Load state from the request userState on edit or from the passed variable on default
        if (Factory::getApplication()->input->get('layout') == 'edit') {
            $id = Factory::getApplication()->getUserState('com_jed.edit.reviewcomment.id');
        } else {
            $id = Factory::getApplication()->input->get('id');
            Factory::getApplication()->setUserState('com_jed.edit.reviewcomment.id', $id);
        }

        $this->setState('reviewcomment.id', $id);

        // Load the parameters.
        $params       = $app->getParams();
        $params_array = $params->toArray();

        if (isset($params_array['item_id'])) {
            $this->setState('reviewcomment.id', $params_array['item_id']);
        }

        $this->setState('params', $params);
    }

    /**
     * Publish the element
     *
     * @param   int  $id     Item id
     * @param   int  $state  Publish state
     *
     * @return  boolean
     * @throws Exception
     * @throws Exception
     */
    public function publish($id, $state)
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

    /**
     * This method revises if the $id of the item belongs to the current user
     *
     * @param   integer  $id  The id of the item
     *
     * @return  boolean             true if the user is the owner of the row, false if not.
     *
     */
    public function userIDItem($id)
    {
        try {
            $user = JedHelper::getUser();
            $db   = Factory::getDbo();

            $query = $db->getQuery(true);
            $query->select("id")
                ->from($db->quoteName('#__jed_extension_supply_options'))
                ->where("id = " . $db->escape($id))
                ->where("created_by = " . $user->id);

            $db->setQuery($query);

            $results = $db->loadObject();
            if ($results) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $exc) {
            return false;
        }
    }
}
