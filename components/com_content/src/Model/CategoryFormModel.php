<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Site\Model;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Access\Rules;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Joomla\Component\Categories\Administrator\Model\CategoryModel as AdminCategoryModel;
use Joomla\Component\Categories\Administrator\Table\CategoryTable;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Frontend category form model for com_content.
 *
 * @since  6.1.0
 */
class CategoryFormModel extends AdminCategoryModel
{
    /**
     * Component option name.
     *
     * @var    string
     * @since  6.1.0
     */
    protected $option = 'com_content';

    /**
     * Model typeAlias string. Used for version history.
     *
     * @var    string
     * @since  6.1.0
     */
    public $typeAlias = 'com_content.category';

    /**
     * Override getState to short-circuit heavy populate logic and set the ID explicitly.
     *
     * @param   string  $property  Optional parameter name.
     * @param   mixed   $default   Optional default value.
     *
     * @return  mixed
     *
     * @since   6.1.0
     */
    public function getState($property = null, $default = null)
    {
        if ($this->state === null) {
            $this->state = new Registry();
        }

        if (!$this->__state_set) {
            $app       = Factory::getApplication();
            $input     = $app->getInput();
            $id        = $input->getInt('id');
            $parentId  = $input->getInt('parent_id');
            $extension = $input->getCmd('extension', 'com_content');
            $parts     = explode('.', $extension);

            $this->state->set($this->getName() . '.id', $id);
            $this->state->set('categoryform.id', $id);
            $this->state->set('category.id', $id);
            $this->state->set('category.parent_id', $parentId);
            $this->state->set('category.extension', $extension);
            $this->state->set('category.component', $parts[0]);
            $this->state->set('category.section', $parts[1] ?? null);
            $this->state->set('params', $app->getParams());
            $this->state->set('return_page', base64_decode($input->get('return', '', 'base64')));
            $this->state->set('layout', $input->getCmd('layout', 'edit'));

            if (!$id && Multilanguage::isEnabled()) {
                $this->state->set('category.language', Factory::getLanguage()->getTag());
            }

            $this->__state_set = true;
        }

        return $property === null ? $this->state : $this->state->get($property, $default);
    }

    /**
     * Method to auto-populate the model state.
     *
     * @return  void
     *
     * @since   6.1.0
     */
    protected function populateState()
    {
        // State is set in getState override to avoid recursion during debugging.
    }

    /**
     * Method to get category data.
     *
     * @param   integer  $pk  The id of the category.
     *
     * @return  mixed  Category item data object on success, false on failure.
     *
     * @since   6.1.0
     */
    public function getItem($pk = null)
    {
        $pk = $pk ?: (int) ($this->getState($this->getName() . '.id') ?: $this->getState('category.id'));

        $item = parent::getItem($pk);

        if (!$item) {
            return false;
        }

        $user         = $this->getCurrentUser();
        $userId       = $user->id;
        $asset        = $item->id ? 'com_content.category.' . $item->id : 'com_content';
        $frontendEdit = Access::check($userId, 'core.edit.frontend', $asset);

        $canEdit = false;

        if ($frontendEdit !== false) {
            if (!$item->id) {
                $parentId = (int) ($item->parent_id ?? 0);
                $canEdit  = $user->authorise('core.create', $asset)
                    || $user->authorise('core.create', 'com_content.category.' . $parentId);
            } elseif ($user->authorise('core.edit', $asset)) {
                $canEdit = true;
            } elseif (!empty($userId) && $user->authorise('core.edit.own', $asset)) {
                $canEdit = (int) $userId === (int) $item->created_user_id;
            }
        }

        $item->accessEdit   = $canEdit;
        $item->accessChange = $frontendEdit !== false && $user->authorise('core.edit.state', $asset);

        if ($item->id) {
            $tagsHelper = new TagsHelper();
            $item->tags = $tagsHelper->getTagIds($item->id, $item->extension . '.category');
        }

        return $item;
    }

    /**
     * Get the return URL.
     *
     * @return  string  The return URL.
     *
     * @since   6.1.0
     */
    public function getReturnPage()
    {
        return base64_encode($this->getState('return_page', ''));
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     *
     * @since   6.1.0
     */
    protected function loadFormData()
    {
        $app  = Factory::getApplication();
        $data = $app->getUserState('com_content.edit.category.data', []);
        $stateId = (int) $this->getState('category.id');

        if (empty($data)) {
            $data = $this->getItem();

            if (empty($data->id)) {
                $data->published = $app->getInput()->getInt('published', $data->published ?? null);
                $data->language  = $app->getInput()->getCmd('language', $data->language ?? null);
                $data->access    = $app->getInput()->getInt('access', $app->get('access'));
            }
        } else {
            // Drop stale session data if it belongs to another category.
            if (isset($data['id']) && (int) $data['id'] !== $stateId) {
                $app->setUserState('com_content.edit.category.data', null);
                $data = $this->getItem();
            }

            $data = (object) $data;
        }

        // Use the core categories context so plugins process category data as expected.
        $this->preprocessData('com_categories.category', $data);

        // Ensure tags are stored as a simple array.
        if (isset($data->tags) && $data->tags instanceof TagsHelper) {
            $data->tags = $data->tags->getTagIds($data->id ?? 0, $this->option . '.category');
        }

        return $data;
    }

    /**
     * Method to get the row form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form  A Form object
     *
     * @since   6.1.0
     * @throws  \Exception on failure
     */
    public function getForm($data = [], $loadData = true)
    {
        $app       = Factory::getApplication();
        $extension = $this->getState('category.extension');
        $jinput    = $app->getInput();

        // Ensure extension is available to downstream preprocessForm lookups.
        $jinput->set('extension', $extension ?? 'com_content');

        // Add admin form/field paths to reuse the core category form.
        Form::addFormPath(JPATH_ADMINISTRATOR . '/components/com_categories/forms');
        Form::addFieldPath(JPATH_ADMINISTRATOR . '/components/com_categories/models/fields');

        // A workaround to get the extension into the model for save requests.
        if (empty($extension) && isset($data['extension'])) {
            $extension = $data['extension'];
            $parts     = explode('.', $extension);

            $this->setState('category.extension', $extension);
            $this->setState('category.component', $parts[0]);
            $this->setState('category.section', $parts[1] ?? null);
        }

        // Get the form.
        $form = $this->loadForm('com_categories.category' . $extension, 'category', ['control' => 'jform', 'load_data' => $loadData]);

        // Modify the form based on Edit State access controls.
        if (empty($data['extension'])) {
            $data['extension'] = $extension;
        }

        $categoryId = $jinput->get('id');
        $parts      = explode('.', $extension);
        $assetKey   = $categoryId ? $extension . '.category.' . $categoryId : $parts[0];

        if (!$this->getCurrentUser()->authorise('core.edit.state', $assetKey)) {
            // Disable fields for display.
            $form->setFieldAttribute('ordering', 'disabled', 'true');
            $form->setFieldAttribute('published', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is a record you can edit.
            $form->setFieldAttribute('ordering', 'filter', 'unset');
            $form->setFieldAttribute('published', 'filter', 'unset');
        }

        // Don't allow to change the created_user_id user if not allowed to access com_users.
        if (!$this->getCurrentUser()->authorise('core.manage', 'com_users')) {
            $form->setFieldAttribute('created_user_id', 'filter', 'unset');
        }

        return $form;
    }

    /**
     * Override save to bypass history on the frontend.
     *
     * @param   array  $data  The data to save.
     *
     * @return  boolean
     */
    public function save($data)
    {
        $table   = $this->getTable();
        $input   = Factory::getApplication()->getInput();
        $pk      = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
        $isNew   = true;
        $context = $this->option . '.' . $this->name;

        if (!empty($data['tags']) && $data['tags'][0] != '') {
            $table->newTags = $data['tags'];
        }

        PluginHelper::importPlugin($this->events_map['save']);

        if ($pk > 0) {
            $table->load($pk);
            $isNew = false;
        }

        if ($table->parent_id != ($data['parent_id'] ?? 0) || ($data['id'] ?? 0) == 0) {
            $table->setLocation($data['parent_id'] ?? 0, 'last-child');
        }

        if ($input->get('task') == 'save2copy') {
            $origTable = $this->getTable();
            $origTable->load($input->getInt('id'));

            if ($data['title'] == $origTable->title) {
                [$title, $alias] = $this->generateNewTitle($data['parent_id'], $data['alias'], $data['title']);
                $data['title']   = $title;
                $data['alias']   = $alias;
            } elseif ($data['alias'] == $origTable->alias) {
                $data['alias'] = '';
            }

            $data['published'] = 0;
        }

        if (!$table->bind($data)) {
            $this->setError($table->getError());

            return false;
        }

        if (isset($data['rules'])) {
            $rules = new Rules($data['rules']);
            $table->setRules($rules);
        }

        if (!$table->check()) {
            $this->setError($table->getError());

            return false;
        }

        $result = Factory::getApplication()->triggerEvent($this->event_before_save, [$context, &$table, $isNew, $data]);

        if (\in_array(false, $result, true)) {
            $this->setError($table->getError());

            return false;
        }

        if (!$table->store()) {
            $this->setError($table->getError());

            return false;
        }

        $assoc = $this->getAssoc();

        if ($assoc) {
            $associations = $data['associations'] ?? [];
            $associations = ArrayHelper::toInteger($associations);

            foreach ($associations as $tag => $id) {
                if (!$id) {
                    unset($associations[$tag]);
                }
            }

            $allLanguage = $table->language == '*';

            if ($allLanguage && !empty($associations)) {
                Factory::getApplication()->enqueueMessage(Text::_('COM_CATEGORIES_ERROR_ALL_LANGUAGE_ASSOCIATED'), 'notice');
            }

            $db    = $this->getDatabase();
            $id    = (int) $table->id;
            $query = $db->createQuery()
                ->select($db->quoteName('key'))
                ->from($db->quoteName('#__associations'))
                ->where($db->quoteName('context') . ' = :associationscontext')
                ->where($db->quoteName('id') . ' = :id')
                ->bind(':associationscontext', $this->associationsContext)
                ->bind(':id', $id, ParameterType::INTEGER);
            $db->setQuery($query);
            $oldKey = $db->loadResult();

            if ($associations || $oldKey !== null) {
                $where = [];

                $query = $db->createQuery()
                    ->delete($db->quoteName('#__associations'))
                    ->where($db->quoteName('context') . ' = :associationscontext')
                    ->bind(':associationscontext', $this->associationsContext);

                if ($associations) {
                    $where[] = $db->quoteName('id') . ' IN (' . implode(',', $query->bindArray(array_values($associations))) . ')';
                }

                if ($oldKey !== null) {
                    $where[] = $db->quoteName('key') . ' = :oldKey';
                    $query->bind(':oldKey', $oldKey);
                }

                $query->extendWhere('AND', $where, 'OR');
            }

            $db->setQuery($query);

            try {
                $db->execute();
            } catch (\RuntimeException $e) {
                $this->setError($e->getMessage());

                return false;
            }

            if (!$allLanguage) {
                $associations[$table->language] = (int) $table->id;
            }

            if (\count($associations) > 1) {
                $key = md5(json_encode($associations));
                $query->clear()
                    ->insert($db->quoteName('#__associations'))
                    ->columns(
                        [
                            $db->quoteName('id'),
                            $db->quoteName('context'),
                            $db->quoteName('key'),
                        ]
                    );

                foreach ($associations as $id) {
                    $id = (int) $id;

                    $query->values(
                        implode(
                            ',',
                            $query->bindArray(
                                [$id, $this->associationsContext, $key],
                                [ParameterType::INTEGER, ParameterType::STRING, ParameterType::STRING]
                            )
                        )
                    );
                }

                $db->setQuery($query);

                try {
                    $db->execute();
                } catch (\RuntimeException $e) {
                    $this->setError($e->getMessage());

                    return false;
                }
            }
        }

        Factory::getApplication()->triggerEvent($this->event_after_save, [$context, &$table, $isNew, $data]);

        if (!$table->rebuildPath($table->id)) {
            $this->setError($table->getError());

            return false;
        }

        if (!$table->rebuild($table->id, $table->lft, $table->level, $table->path)) {
            $this->setError($table->getError());

            return false;
        }

        $this->setState($this->getName() . '.id', $table->id);

        if (Factory::getApplication()->getInput()->get('task') == 'editAssociations') {
            return $this->redirectToAssociations($data);
        }

        $this->cleanCache();

        return true;
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $type    The table name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  \Joomla\CMS\Table\Table  A Table object.
     *
     * @since   6.1.0
     */
    public function getTable($type = 'Category', $prefix = 'Administrator', $config = [])
    {
        $table = new CategoryTable($this->getDatabase());
        $table->setCurrentUser($this->getCurrentUser());
        $table->extension = 'com_content';

        return $table;
    }
}
