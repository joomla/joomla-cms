<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_tags
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Tags\Administrator\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Versioning\VersionableModelTrait;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Tags Component Tag Model
 *
 * @since  3.1
 */
class TagModel extends AdminModel
{
    use VersionableModelTrait;

    /**
     * @var    string  The prefix to use with controller messages.
     * @since  3.1
     */
    protected $text_prefix = 'COM_TAGS';

    /**
     * @var    string  The type alias for this content type.
     * @since  3.2
     */
    public $typeAlias = 'com_tags.tag';

    /**
     * Batch copy/move command. If set to false,
     * the batch copy/move command is not supported
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    protected $batch_copymove = 'tag_id';

    /**
     * Allowed batch commands
     *
     * @var    array
     * @since  3.7.0
     */
    protected $batch_commands = [
        'assetgroup_id' => 'batchAccess',
        'language_id'   => 'batchLanguage',
    ];

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
     *
     * @since   3.1
     */
    protected function canDelete($record)
    {
        if (empty($record->id) || $record->published != -2) {
            return false;
        }

        return parent::canDelete($record);
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $type    The table name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  \Joomla\Cms\Table\Table|\Joomla\Cms\Table\Nested  A Table object
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getTable($type = 'Tag', $prefix = 'Administrator', $config = [])
    {
        return parent::getTable($type, $prefix, $config);
    }

    /**
     * Auto-populate the model state.
     *
     * @note Calling getState in this method will result in recursion.
     *
     * @return  void
     *
     * @since   3.1
     */
    protected function populateState()
    {
        $app = Factory::getApplication();

        $parentId = $app->getInput()->getInt('parent_id');
        $this->setState('tag.parent_id', $parentId);

        // Load the User state.
        $pk = $app->getInput()->getInt('id');
        $this->setState($this->getName() . '.id', $pk);

        // Load the parameters.
        $params = ComponentHelper::getParams('com_tags');
        $this->setState('params', $params);
    }

    /**
     * Method to get a tag.
     *
     * @param   integer  $pk  An optional id of the object to get, otherwise the id from the model state is used.
     *
     * @return  mixed  Tag data object on success, false on failure.
     *
     * @since   3.1
     */
    public function getItem($pk = null)
    {
        if ($result = parent::getItem($pk)) {
            // Prime required properties.
            if (empty($result->id)) {
                $result->parent_id = $this->getState('tag.parent_id');
            }

            // Convert the metadata field to an array.
            $registry         = new Registry($result->metadata);
            $result->metadata = $registry->toArray();

            // Convert the images field to an array.
            $registry       = new Registry($result->images);
            $result->images = $registry->toArray();

            // Convert the urls field to an array.
            $registry     = new Registry($result->urls);
            $result->urls = $registry->toArray();

            // Convert the modified date to local user time for display in the form.
            $tz = new \DateTimeZone(Factory::getApplication()->get('offset'));

            if ((int) $result->modified_time) {
                $date = new Date($result->modified_time);
                $date->setTimezone($tz);
                $result->modified_time = $date->toSql(true);
            } else {
                $result->modified_time = null;
            }
        }

        return $result;
    }

    /**
     * Method to get the row form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  bool|\Joomla\CMS\Form\Form  A Form object on success, false on failure
     *
     * @since   3.1
     */
    public function getForm($data = [], $loadData = true)
    {
        $jinput = Factory::getApplication()->getInput();

        // Get the form.
        $form = $this->loadForm('com_tags.tag', 'tag', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        $user = $this->getCurrentUser();

        if (!$user->authorise('core.edit.state', 'com_tags' . $jinput->get('id'))) {
            // Disable fields for display.
            $form->setFieldAttribute('ordering', 'disabled', 'true');
            $form->setFieldAttribute('published', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is a record you can edit.
            $form->setFieldAttribute('ordering', 'filter', 'unset');
            $form->setFieldAttribute('published', 'filter', 'unset');
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     *
     * @since   3.1
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = Factory::getApplication()->getUserState('com_tags.edit.tag.data', []);

        if (empty($data)) {
            $data = $this->getItem();
        }

        $this->preprocessData('com_tags.tag', $data);

        return $data;
    }

    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  True on success.
     *
     * @since   3.1
     */
    public function save($data)
    {
        /** @var \Joomla\Component\Tags\Administrator\Table\TagTable $table */
        $table      = $this->getTable();
        $input      = Factory::getApplication()->getInput();
        $pk         = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
        $isNew      = true;
        $context    = $this->option . '.' . $this->name;

        // Include the plugins for the save events.
        PluginHelper::importPlugin($this->events_map['save']);

        try {
            // Load the row if saving an existing tag.
            if ($pk > 0) {
                $table->load($pk);
                $isNew = false;
            }

            // Set the new parent id if parent id not matched OR while New/Save as Copy .
            if ($table->parent_id != $data['parent_id'] || $data['id'] == 0) {
                $table->setLocation($data['parent_id'], 'last-child');
            }

            // Alter the title for save as copy
            if ($input->get('task') == 'save2copy') {
                $origTable = $this->getTable();
                $origTable->load($input->getInt('id'));

                if ($data['title'] == $origTable->title) {
                    list($title, $alias) = $this->generateNewTitle($data['parent_id'], $data['alias'], $data['title']);
                    $data['title']       = $title;
                    $data['alias']       = $alias;
                } elseif ($data['alias'] == $origTable->alias) {
                    $data['alias'] = '';
                }

                $data['published'] = 0;
            }

            // Bind the data.
            if (!$table->bind($data)) {
                $this->setError($table->getError());

                return false;
            }

            // Prepare the row for saving
            $this->prepareTable($table);

            // Check the data.
            if (!$table->check()) {
                $this->setError($table->getError());

                return false;
            }

            // Trigger the before save event.
            $result = Factory::getApplication()->triggerEvent($this->event_before_save, [$context, $table, $isNew, $data]);

            if (\in_array(false, $result, true)) {
                $this->setError($table->getError());

                return false;
            }

            // Store the data.
            if (!$table->store()) {
                $this->setError($table->getError());

                return false;
            }

            // Trigger the after save event.
            Factory::getApplication()->triggerEvent($this->event_after_save, [$context, $table, $isNew]);

            // Rebuild the path for the tag:
            if (!$table->rebuildPath($table->id)) {
                $this->setError($table->getError());

                return false;
            }

            // Rebuild the paths of the tag's children:
            if (!$table->rebuild($table->id, $table->lft, $table->level, $table->path)) {
                $this->setError($table->getError());

                return false;
            }
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        $this->setState($this->getName() . '.id', $table->id);
        $this->setState($this->getName() . '.new', $isNew);

        // Clear the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Prepare and sanitise the table data prior to saving.
     *
     * @param   \Joomla\CMS\Table\Table  $table  A Table object.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function prepareTable($table)
    {
        // Increment the content version number.
        $table->version++;
    }

    /**
     * Method rebuild the entire nested set tree.
     *
     * @return  boolean  False on failure or error, true otherwise.
     *
     * @since   3.1
     */
    public function rebuild()
    {
        // Get an instance of the table object.
        /** @var \Joomla\Component\Tags\Administrator\Table\TagTable $table */

        $table = $this->getTable();

        if (!$table->rebuild()) {
            $this->setError($table->getError());

            return false;
        }

        // Clear the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Method to save the reordered nested set tree.
     * First we save the new order values in the lft values of the changed ids.
     * Then we invoke the table rebuild to implement the new ordering.
     *
     * @param   array    $idArray   An array of primary key ids.
     * @param   integer  $lftArray  The lft value
     *
     * @return  boolean  False on failure or error, True otherwise
     *
     * @since   3.1
     */
    public function saveorder($idArray = null, $lftArray = null)
    {
        // Get an instance of the table object.
        /** @var \Joomla\Component\Tags\Administrator\Table\TagTable $table */

        $table = $this->getTable();

        if (!$table->saveorder($idArray, $lftArray)) {
            $this->setError($table->getError());

            return false;
        }

        // Clear the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Method to change the title & alias.
     *
     * @param   integer  $parentId  The id of the parent.
     * @param   string   $alias     The alias.
     * @param   string   $title     The title.
     *
     * @return  array  Contains the modified title and alias.
     *
     * @since   3.1
     */
    protected function generateNewTitle($parentId, $alias, $title)
    {
        // Alter the title & alias
        /** @var \Joomla\Component\Tags\Administrator\Table\TagTable $table */

        $table = $this->getTable();

        while ($table->load(['alias' => $alias])) {
            $title = ($table->title != $title) ? $title : StringHelper::increment($title);
            $alias = StringHelper::increment($alias, 'dash');
        }

        return [$title, $alias];
    }

    /**
     * Batch copy tags to a new tag or parent.
     *
     * @param   integer  $value     The new tag or sub-item.
     * @param   array    $pks       An array of row IDs.
     * @param   array    $contexts  An array of item contexts.
     *
     * @return  mixed  An array of new IDs on success, boolean false on failure.
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function batchCopy($value, $pks, $contexts)
    {
        $parentId  = (int) $value;
        $table     = $this->getTable();
        $db        = $this->getDatabase();
        $query     = $db->getQuery(true);
        $newIds    = [];

        // Check that the parent exists
        if ($parentId) {
            if (!$table->load($parentId)) {
                if ($error = $table->getError()) {
                    // Fatal error
                    $this->setError($error);

                    return false;
                }
                // Non-fatal error
                $this->setError(Text::_('JGLOBAL_BATCH_MOVE_PARENT_NOT_FOUND'));
                $parentId = 0;
            }
        }

        // If the parent is 0, set it to the ID of the root item in the tree
        if (empty($parentId)) {
            if (!$parentId = $table->getRootId()) {
                $this->setError($table->getError());

                return false;
            }
        }

        // Check that user has create permission for tags
        if (!$this->getCurrentUser()->authorise('core.create', 'com_tags')) {
            $this->setError(Text::_('COM_TAGS_BATCH_COPY_MOVE_CANNOT_CREATE'));

            return false;
        }

        // We need to log the parent ID
        $parents = [];

        // Calculate the emergency stop count as a precaution against a runaway loop bug
        $query->select('COUNT(' . $db->quoteName('id') . ')')
            ->from($db->quoteName('#__tags'));
        $db->setQuery($query);

        try {
            $count = $db->loadResult();
        } catch (\RuntimeException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        // Parent exists so let's proceed
        while (!empty($pks) && $count > 0) {
            // Pop the first id off the stack
            $pk = array_shift($pks);

            $table->reset();

            // Check that the row actually exists
            if (!$table->load($pk)) {
                if ($error = $table->getError()) {
                    // Fatal error
                    $this->setError($error);

                    return false;
                }
                // Non-fatal error
                $this->setError(Text::sprintf('JGLOBAL_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                continue;
            }

            // Copy is a bit tricky, because we also need to copy the children
            $query = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__tags'))
                ->where(
                    [
                        $db->quoteName('lft') . ' > :lft',
                        $db->quoteName('rgt') . ' < :rgt',
                    ]
                )
                ->bind(':lft', $table->lft, ParameterType::INTEGER)
                ->bind(':rgt', $table->rgt, ParameterType::INTEGER);
            $db->setQuery($query);
            $childIds = $db->loadColumn();

            // Add child IDs to the array only if they aren't already there.
            foreach ($childIds as $childId) {
                if (!\in_array($childId, $pks)) {
                    $pks[] = $childId;
                }
            }

            // Make a copy of the old ID and Parent ID
            $oldId       = $table->id;
            $oldParentId = $table->parent_id;

            // Reset the id because we are making a copy.
            $table->id = 0;

            // If we are copying children, the Old ID will turn up in the parents list
            // otherwise it's a new top level item
            $table->parent_id = $parents[$oldParentId] ?? $parentId;

            // Set the new location in the tree for the node.
            $table->setLocation($table->parent_id, 'last-child');

            // @todo: Deal with ordering?
            // $table->ordering = 1;
            $table->level = null;
            $table->lft   = null;
            $table->rgt   = null;

            // Alter the title & alias
            [$title, $alias] = $this->generateNewTitle($table->parent_id, $table->alias, $table->title);
            $table->title    = $title;
            $table->alias    = $alias;

            // Unpublish because we are making a copy
            $this->table->published = 0;

            // Check the row.
            if (!$table->check()) {
                $this->setError($table->getError());

                return false;
            }

            // Store the row.
            if (!$table->store()) {
                $this->setError($table->getError());

                return false;
            }

            // Get the new item ID
            $newId = $table->get('id');

            // Add the new ID to the array
            $newIds[$pk] = $newId;

            // Now we log the old 'parent' to the new 'parent'
            $parents[$oldId] = $table->id;
            $count--;
        }

        // Rebuild the hierarchy.
        if (!$table->rebuild()) {
            $this->setError($table->getError());

            return false;
        }

        // Rebuild the tree path.
        if (!$table->rebuildPath($table->id)) {
            $this->setError($table->getError());

            return false;
        }

        // Clean the cache
        $this->cleanCache();

        return $newIds;
    }

    /**
     * Batch move tags to a new tag or parent.
     *
     * @param   integer  $value     The new tag or sub-item.
     * @param   array    $pks       An array of row IDs.
     * @param   array    $contexts  An array of item contexts.
     *
     * @return  boolean  True on success.
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function batchMove($value, $pks, $contexts)
    {
        $parentId  = (int) $value;
        $table     = $this->getTable();

        // Check that the parent exists.
        if ($parentId) {
            if (!$table->load($parentId)) {
                if ($error = $table->getError()) {
                    // Fatal error
                    $this->setError($error);

                    return false;
                }
                // Non-fatal error
                $this->setError(Text::_('JGLOBAL_BATCH_MOVE_PARENT_NOT_FOUND'));
                $parentId = 0;
            }
        }

        // Check that user has create and edit permission for tags
        $user = $this->getCurrentUser();

        if (!$user->authorise('core.create', 'com_tags')) {
            $this->setError(Text::_('COM_TAGS_BATCH_COPY_MOVE_CANNOT_CREATE'));

            return false;
        }

        if (!$user->authorise('core.edit', 'com_tags')) {
            $this->setError(Text::_('COM_TAGS_BATCH_COPY_MOVE_CANNOT_EDIT'));

            return false;
        }

        // Parent exists so let's proceed
        foreach ($pks as $pk) {
            // Check that the row actually exists
            if (!$table->load($pk)) {
                if ($error = $table->getError()) {
                    // Fatal error
                    $this->setError($error);

                    return false;
                }
                // Non-fatal error
                $this->setError(Text::sprintf('JGLOBAL_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                continue;
            }

            // Set the new location in the tree for the node.
            $table->setLocation($parentId, 'last-child');

            // Set the new Parent Id
            $table->parent_id = $parentId;

            // Check the row.
            if (!$table->check()) {
                $this->setError($table->getError());

                return false;
            }

            // Store the row.
            if (!$table->store()) {
                $this->setError($table->getError());

                return false;
            }

            // Rebuild the tree path.
            if (!$table->rebuildPath()) {
                $this->setError($table->getError());

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }
}
