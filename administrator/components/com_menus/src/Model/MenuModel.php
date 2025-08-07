<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Menu Item Model for Menus.
 *
 * @since  1.6
 */
class MenuModel extends AdminModel
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     * @since  1.6
     */
    protected $text_prefix = 'COM_MENUS_MENU';

    /**
     * Model context string.
     *
     * @var  string
     */
    protected $_context = 'com_menus.menu';

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
     *
     * @since   1.6
     */
    protected function canDelete($record)
    {
        return $this->getCurrentUser()->authorise('core.delete', 'com_menus.menu.' . (int) $record->id);
    }

    /**
     * Method to test whether the state of a record can be edited.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
     *
     * @since   1.6
     */
    protected function canEditState($record)
    {
        return $this->getCurrentUser()->authorise('core.edit.state', 'com_menus.menu.' . (int) $record->id);
    }

    /**
     * Returns a Table object, always creating it
     *
     * @param   string  $type    The table type to instantiate
     * @param   string  $prefix  A prefix for the table class name. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  Table   A database object
     *
     * @since   1.6
     */
    public function getTable($type = 'MenuType', $prefix = '\\Joomla\\CMS\\Table\\', $config = [])
    {
        return Table::getInstance($type, $prefix, $config);
    }

    /**
     * Auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState()
    {
        $app = Factory::getApplication();

        // Load the User state.
        $id = $app->getInput()->getInt('id');
        $this->setState('menu.id', $id);

        // Load the parameters.
        $params = ComponentHelper::getParams('com_menus');
        $this->setState('params', $params);

        // Load the clientId.
        $clientId = $app->getUserStateFromRequest('com_menus.menus.client_id', 'client_id', 0, 'int');
        $this->setState('client_id', $clientId);
    }

    /**
     * Method to get a menu item.
     *
     * @param   integer  $itemId  The id of the menu item to get.
     *
     * @return  \stdClass|false  Menu item data object on success, false on failure.
     *
     * @since   1.6
     */
    public function getItem($itemId = null)
    {
        $itemId = (!empty($itemId)) ? $itemId : (int) $this->getState('menu.id');

        // Get a menu item row instance.
        $table = $this->getTable();

        // Attempt to load the row.
        $return = $table->load($itemId);

        // Check for a table object error.
        if ($return === false && $table->getError()) {
            $this->setError($table->getError());

            return false;
        }

        $properties = $table->getProperties(1);
        $value      = ArrayHelper::toObject($properties);

        return $value;
    }

    /**
     * Method to get the menu item form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form|boolean    A Form object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm('com_menus.menu', 'menu', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        if (!$this->getState('client_id', 0)) {
            $form->removeField('preset');
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     *
     * @since   1.6
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_menus.edit.menu.data', []);

        if (empty($data)) {
            $data = $this->getItem();

            if (empty($data->id)) {
                $data->client_id = $this->state->get('client_id', 0);
            }
        } else {
            unset($data['preset']);
        }

        $this->preprocessData('com_menus.menu', $data);

        return $data;
    }

    /**
     * Method to validate the form data.
     *
     * @param   Form    $form   The form to validate against.
     * @param   array   $data   The data to validate.
     * @param   string  $group  The name of the field group to validate.
     *
     * @return  array|boolean  Array of filtered data if valid, false otherwise.
     *
     * @see     \Joomla\CMS\Form\FormRule
     * @see     \Joomla\CMS\Filter\InputFilter
     * @since   3.9.23
     */
    public function validate($form, $data, $group = null)
    {
        if (!$this->getCurrentUser()->authorise('core.admin', 'com_menus')) {
            if (isset($data['rules'])) {
                unset($data['rules']);
            }
        }

        return parent::validate($form, $data, $group);
    }

    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  True on success.
     *
     * @since   1.6
     */
    public function save($data)
    {
        $id         = (!empty($data['id'])) ? $data['id'] : (int) $this->getState('menu.id');
        $isNew      = true;

        // Get a row instance.
        $table = $this->getTable();

        // Include the plugins for the save events.
        PluginHelper::importPlugin('content');

        // Load the row if saving an existing item.
        if ($id > 0) {
            $isNew = false;
            $table->load($id);
        }

        // Bind the data.
        if (!$table->bind($data)) {
            $this->setError($table->getError());

            return false;
        }

        // Check the data.
        if (!$table->check()) {
            $this->setError($table->getError());

            return false;
        }

        // Trigger the before event.
        $result = Factory::getApplication()->triggerEvent('onContentBeforeSave', [$this->_context, &$table, $isNew, $data]);

        // Store the data.
        if (\in_array(false, $result, true) || !$table->store()) {
            $this->setError($table->getError());

            return false;
        }

        // Trigger the after save event.
        Factory::getApplication()->triggerEvent('onContentAfterSave', [$this->_context, &$table, $isNew]);

        $this->setState('menu.id', $table->id);

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Method to delete groups.
     *
     * @param   array  $itemIds  An array of item ids.
     *
     * @return  boolean  Returns true on success, false on failure.
     *
     * @since   1.6
     */
    public function delete(&$pks)
    {
        // Sanitize the ids.
        $itemIds = ArrayHelper::toInteger((array) $pks);

        // Get a group row instance.
        $table = $this->getTable();

        // Include the plugins for the delete events.
        PluginHelper::importPlugin('content');

        // Iterate the items to delete each one.
        foreach ($itemIds as $itemId) {
            if ($table->load($itemId)) {
                // Trigger the before delete event.
                $result = Factory::getApplication()->triggerEvent('onContentBeforeDelete', [$this->_context, $table]);

                if (\in_array(false, $result, true) || !$table->delete($itemId)) {
                    $this->setError($table->getError());

                    return false;
                }

                // Trigger the after delete event.
                Factory::getApplication()->triggerEvent('onContentAfterDelete', [$this->_context, $table]);

                // @todo: Delete the menu associations - Menu items and Modules
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Gets a list of all mod_mainmenu modules and collates them by menutype
     *
     * @return  array
     *
     * @since   1.6
     */
    public function &getModules()
    {
        $db = $this->getDatabase();

        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('a.id'),
                    $db->quoteName('a.title'),
                    $db->quoteName('a.params'),
                    $db->quoteName('a.position'),
                    $db->quoteName('ag.title', 'access_title'),
                ]
            )
            ->from($db->quoteName('#__modules', 'a'))
            ->join('LEFT', $db->quoteName('#__viewlevels', 'ag'), $db->quoteName('ag.id') . ' = ' . $db->quoteName('a.access'))
            ->where($db->quoteName('a.module') . ' = ' . $db->quote('mod_menu'));
        $db->setQuery($query);

        $modules = $db->loadObjectList();

        $result = [];

        foreach ($modules as &$module) {
            $params = new Registry($module->params);

            $menuType = $params->get('menutype');

            if (!isset($result[$menuType])) {
                $result[$menuType] = [];
            }

            $result[$menuType][] = &$module;
        }

        return $result;
    }

    /**
     * Returns the extension elements for the given items
     *
     * @param  array  $itemIds  The item ids
     *
     * @return array
     *
     * @since  4.2.0
     */
    public function getExtensionElementsForMenuItems(array $itemIds): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $query
            ->select($db->quoteName('e.element'))
            ->from($db->quoteName('#__extensions', 'e'))
            ->join('INNER', $db->quoteName('#__menu', 'm'), $db->quoteName('m.component_id') . ' = ' . $db->quoteName('e.extension_id'))
            ->whereIn($db->quoteName('m.id'), ArrayHelper::toInteger($itemIds));

        return $db->setQuery($query)->loadColumn();
    }

    /**
     * Custom clean the cache
     *
     * @param  string  $group  Cache group name.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function cleanCache($group = null)
    {
        parent::cleanCache('com_menus');
        parent::cleanCache('com_modules');
        parent::cleanCache('mod_menu');
    }
}
