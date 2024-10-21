<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Model;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\MVC\Model\WorkflowBehaviorTrait;
use Joomla\CMS\MVC\Model\WorkflowModelInterface;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Table\TableInterface;
use Joomla\CMS\Tag\TaggableTableInterface;
use Joomla\CMS\UCM\UCMType;
use Joomla\CMS\Versioning\VersionableModelTrait;
use Joomla\CMS\Workflow\Workflow;
use Joomla\Component\Categories\Administrator\Helper\CategoriesHelper;
use Joomla\Component\Content\Administrator\Event\Model\FeatureEvent;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use Joomla\Database\ParameterType;
use Joomla\Filter\OutputFilter;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Item Model for an Article.
 *
 * @since  1.6
 */

class ArticleModel extends AdminModel implements WorkflowModelInterface
{
    use WorkflowBehaviorTrait;
    use VersionableModelTrait;

    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     * @since  1.6
     */
    protected $text_prefix = 'COM_CONTENT';

    /**
     * The type alias for this content type (for example, 'com_content.article').
     *
     * @var    string
     * @since  3.2
     */
    public $typeAlias = 'com_content.article';

    /**
     * The context used for the associations table
     *
     * @var    string
     * @since  3.4.4
     */
    protected $associationsContext = 'com_content.item';

    /**
     * The event to trigger before changing featured status one or more items.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $event_before_change_featured = null;

    /**
     * The event to trigger after changing featured status one or more items.
     *
     * @var    string
     * @since  4.0.0
     */
    protected $event_after_change_featured = null;

    /**
     * Constructor.
     *
     * @param   array                  $config       An array of configuration options (name, state, dbo, table_path, ignore_request).
     * @param   ?MVCFactoryInterface   $factory      The factory.
     * @param   ?FormFactoryInterface  $formFactory  The form factory.
     *
     * @since   1.6
     * @throws  \Exception
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null, ?FormFactoryInterface $formFactory = null)
    {
        $config['events_map'] = $config['events_map'] ?? [];

        $config['events_map'] = array_merge(
            ['featured' => 'content'],
            $config['events_map']
        );

        parent::__construct($config, $factory, $formFactory);

        // Set the featured status change events
        $this->event_before_change_featured = $config['event_before_change_featured'] ?? $this->event_before_change_featured;
        $this->event_before_change_featured = $this->event_before_change_featured ?? 'onContentBeforeChangeFeatured';
        $this->event_after_change_featured  = $config['event_after_change_featured'] ?? $this->event_after_change_featured;
        $this->event_after_change_featured  = $this->event_after_change_featured ?? 'onContentAfterChangeFeatured';

        $this->setUpWorkflow('com_content.article');
    }

    /**
     * Function that can be overridden to do any data cleanup after batch copying data
     *
     * @param   TableInterface  $table  The table object containing the newly created item
     * @param   integer         $newId  The id of the new item
     * @param   integer         $oldId  The original item id
     *
     * @return  void
     *
     * @since  3.8.12
     */
    protected function cleanupPostBatchCopy(TableInterface $table, $newId, $oldId)
    {
        // Check if the article was featured and update the #__content_frontpage table
        if ($table->featured == 1) {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select(
                    [
                        $db->quoteName('featured_up'),
                        $db->quoteName('featured_down'),
                    ]
                )
                ->from($db->quoteName('#__content_frontpage'))
                ->where($db->quoteName('content_id') . ' = :oldId')
                ->bind(':oldId', $oldId, ParameterType::INTEGER);

            $featured = $db->setQuery($query)->loadObject();

            if ($featured) {
                $query = $db->getQuery(true)
                    ->insert($db->quoteName('#__content_frontpage'))
                    ->values(':newId, 0, :featuredUp, :featuredDown')
                    ->bind(':newId', $newId, ParameterType::INTEGER)
                    ->bind(':featuredUp', $featured->featured_up, $featured->featured_up ? ParameterType::STRING : ParameterType::NULL)
                    ->bind(':featuredDown', $featured->featured_down, $featured->featured_down ? ParameterType::STRING : ParameterType::NULL);

                $db->setQuery($query);
                $db->execute();
            }
        }

        $this->workflowCleanupBatchMove($oldId, $newId);

        $oldItem = $this->getTable();
        $oldItem->load($oldId);
        $fields = FieldsHelper::getFields('com_content.article', $oldItem, true);

        $fieldsData = [];

        if (!empty($fields)) {
            $fieldsData['com_fields'] = [];

            foreach ($fields as $field) {
                $fieldsData['com_fields'][$field->name] = $field->rawvalue;
            }
        }

        Factory::getApplication()->triggerEvent('onContentAfterSave', ['com_content.article', &$this->table, false, $fieldsData]);
    }

    /**
     * Batch move categories to a new category.
     *
     * @param   integer  $value     The new category ID.
     * @param   array    $pks       An array of row IDs.
     * @param   array    $contexts  An array of item contexts.
     *
     * @return  boolean  True on success.
     *
     * @since   3.8.6
     */
    protected function batchMove($value, $pks, $contexts)
    {
        if (empty($this->batchSet)) {
            // Set some needed variables.
            $this->user           = $this->getCurrentUser();
            $this->table          = $this->getTable();
            $this->tableClassName = \get_class($this->table);
            $this->contentType    = new UCMType();
            $this->type           = $this->contentType->getTypeByTable($this->tableClassName);
        }

        $categoryId = (int) $value;

        if (!$this->checkCategoryId($categoryId)) {
            return false;
        }

        PluginHelper::importPlugin('system');

        // Parent exists so we proceed
        foreach ($pks as $pk) {
            if (!$this->user->authorise('core.edit', $contexts[$pk])) {
                $this->setError(Text::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

                return false;
            }

            // Check that the row actually exists
            if (!$this->table->load($pk)) {
                if ($error = $this->table->getError()) {
                    // Fatal error
                    $this->setError($error);

                    return false;
                }

                // Not fatal error
                $this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                continue;
            }

            $fields = FieldsHelper::getFields('com_content.article', $this->table, true);

            $fieldsData = [];

            if (!empty($fields)) {
                $fieldsData['com_fields'] = [];

                foreach ($fields as $field) {
                    $fieldsData['com_fields'][$field->name] = $field->rawvalue;
                }
            }

            // Set the new category ID
            $this->table->catid = $categoryId;

            // We don't want to modify tags - so remove the associated tags helper
            if ($this->table instanceof TaggableTableInterface) {
                $this->table->clearTagsHelper();
            }

            // Check the row.
            if (!$this->table->check()) {
                $this->setError($this->table->getError());

                return false;
            }

            // Store the row.
            if (!$this->table->store()) {
                $this->setError($this->table->getError());

                return false;
            }

            // Run event for moved article
            Factory::getApplication()->triggerEvent('onContentAfterSave', ['com_content.article', &$this->table, false, $fieldsData]);
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

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
        if (empty($record->id) || ($record->state != -2)) {
            return false;
        }

        return $this->getCurrentUser()->authorise('core.delete', 'com_content.article.' . (int) $record->id);
    }

    /**
     * Method to test whether a record can have its state edited.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
     *
     * @since   1.6
     */
    protected function canEditState($record)
    {
        $user = $this->getCurrentUser();

        // Check for existing article.
        if (!empty($record->id)) {
            return $user->authorise('core.edit.state', 'com_content.article.' . (int) $record->id);
        }

        // New article, so check against the category.
        if (!empty($record->catid)) {
            return $user->authorise('core.edit.state', 'com_content.category.' . (int) $record->catid);
        }

        // Default to component settings if neither article nor category known.
        return parent::canEditState($record);
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
        // Set the publish date to now
        if ($table->state == Workflow::CONDITION_PUBLISHED && (int) $table->publish_up == 0) {
            $table->publish_up = Factory::getDate()->toSql();
        }

        if ($table->state == Workflow::CONDITION_PUBLISHED && \intval($table->publish_down) == 0) {
            $table->publish_down = null;
        }

        // Increment the content version number.
        $table->version++;

        // Reorder the articles within the category so the new article is first
        if (empty($table->id)) {
            $table->reorder('catid = ' . (int) $table->catid . ' AND state >= 0');
        }
    }

    /**
     * Method to change the published state of one or more records.
     *
     * @param   array    &$pks   A list of the primary keys to change.
     * @param   integer  $value  The value of the published state.
     *
     * @return  boolean  True on success.
     *
     * @since   4.0.0
     */
    public function publish(&$pks, $value = 1)
    {
        $this->workflowBeforeStageChange();

        return parent::publish($pks, $value);
    }

    /**
     * Method to get a single record.
     *
     * @param   integer  $pk  The id of the primary key.
     *
     * @return  mixed  Object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        if ($item = parent::getItem($pk)) {
            // Convert the params field to an array.
            $registry      = new Registry($item->attribs);
            $item->attribs = $registry->toArray();

            // Convert the metadata field to an array.
            $registry       = new Registry($item->metadata);
            $item->metadata = $registry->toArray();

            // Convert the images field to an array.
            $registry     = new Registry($item->images);
            $item->images = $registry->toArray();

            // Convert the urls field to an array.
            $registry   = new Registry($item->urls);
            $item->urls = $registry->toArray();

            $item->articletext = ($item->fulltext !== null && trim($item->fulltext) != '') ? $item->introtext . '<hr id="system-readmore">' . $item->fulltext : $item->introtext;

            if (!empty($item->id)) {
                $item->tags = new TagsHelper();
                $item->tags->getTagIds($item->id, 'com_content.article');

                $item->featured_up   = null;
                $item->featured_down = null;

                if ($item->featured) {
                    // Get featured dates.
                    $db    = $this->getDatabase();
                    $query = $db->getQuery(true)
                        ->select(
                            [
                                $db->quoteName('featured_up'),
                                $db->quoteName('featured_down'),
                            ]
                        )
                        ->from($db->quoteName('#__content_frontpage'))
                        ->where($db->quoteName('content_id') . ' = :id')
                        ->bind(':id', $item->id, ParameterType::INTEGER);

                    $featured = $db->setQuery($query)->loadObject();

                    if ($featured) {
                        $item->featured_up   = $featured->featured_up;
                        $item->featured_down = $featured->featured_down;
                    }
                }
            }
        }

        // Load associated content items
        $assoc = Associations::isEnabled();

        if ($assoc) {
            $item->associations = [];

            if ($item->id != null) {
                $associations = Associations::getAssociations('com_content', '#__content', 'com_content.item', $item->id);

                foreach ($associations as $tag => $association) {
                    $item->associations[$tag] = $association->id;
                }
            }
        }

        return $item;
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  Form|boolean  A Form object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = [], $loadData = true)
    {
        $app  = Factory::getApplication();

        // Get the form.
        $form = $this->loadForm('com_content.article', 'article', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        // Object uses for checking edit state permission of article
        $record = new \stdClass();

        // Get ID of the article from input, for frontend, we use a_id while backend uses id
        $articleIdFromInput = $app->isClient('site')
            ? $app->getInput()->getInt('a_id', 0)
            : $app->getInput()->getInt('id', 0);

        // On edit article, we get ID of article from article.id state, but on save, we use data from input
        $id = (int) $this->getState('article.id', $articleIdFromInput);

        $record->id = $id;

        // For new articles we load the potential state + associations
        if ($id == 0 && $formField = $form->getField('catid')) {
            $assignedCatids = $data['catid'] ?? $form->getValue('catid');

            $assignedCatids = \is_array($assignedCatids)
                ? (int) reset($assignedCatids)
                : (int) $assignedCatids;

            // Try to get the category from the category field
            if (empty($assignedCatids)) {
                $assignedCatids = $formField->getAttribute('default', null);

                if (!$assignedCatids) {
                    // Choose the first category available
                    $catOptions = $formField->options;

                    if ($catOptions && !empty($catOptions[0]->value)) {
                        $assignedCatids = (int) $catOptions[0]->value;
                    }
                }
            }

            // Activate the reload of the form when category is changed
            $form->setFieldAttribute('catid', 'refresh-enabled', true);
            $form->setFieldAttribute('catid', 'refresh-cat-id', $assignedCatids);
            $form->setFieldAttribute('catid', 'refresh-section', 'article');

            // Store ID of the category uses for edit state permission check
            $record->catid = $assignedCatids;
        } else {
            // Get the category which the article is being added to
            if (!empty($data['catid'])) {
                $catId = (int) $data['catid'];
            } else {
                $catIds  = $form->getValue('catid');

                $catId = \is_array($catIds)
                    ? (int) reset($catIds)
                    : (int) $catIds;

                if (!$catId) {
                    $catId = (int) $form->getFieldAttribute('catid', 'default', 0);
                }
            }

            $record->catid = $catId;
        }

        // Modify the form based on Edit State access controls.
        if (!$this->canEditState($record)) {
            // Disable fields for display.
            $form->setFieldAttribute('featured', 'disabled', 'true');
            $form->setFieldAttribute('featured_up', 'disabled', 'true');
            $form->setFieldAttribute('featured_down', 'disabled', 'true');
            $form->setFieldAttribute('ordering', 'disabled', 'true');
            $form->setFieldAttribute('publish_up', 'disabled', 'true');
            $form->setFieldAttribute('publish_down', 'disabled', 'true');
            $form->setFieldAttribute('state', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is an article you can edit.
            $form->setFieldAttribute('featured', 'filter', 'unset');
            $form->setFieldAttribute('featured_up', 'filter', 'unset');
            $form->setFieldAttribute('featured_down', 'filter', 'unset');
            $form->setFieldAttribute('ordering', 'filter', 'unset');
            $form->setFieldAttribute('publish_up', 'filter', 'unset');
            $form->setFieldAttribute('publish_down', 'filter', 'unset');
            $form->setFieldAttribute('state', 'filter', 'unset');
        }

        // Don't allow to change the created_by user if not allowed to access com_users.
        if (!$this->getCurrentUser()->authorise('core.manage', 'com_users')) {
            $form->setFieldAttribute('created_by', 'filter', 'unset');
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
        $app  = Factory::getApplication();
        $data = $app->getUserState('com_content.edit.article.data', []);

        if (empty($data)) {
            $data = $this->getItem();

            // Pre-select some filters (Status, Category, Language, Access) in edit form if those have been selected in Article Manager: Articles
            if ($this->getState('article.id') == 0) {
                $filters     = (array) $app->getUserState('com_content.articles.filter');
                $data->state = $app->getInput()->getInt(
                    'state',
                    ((isset($filters['published']) && $filters['published'] !== '') ? $filters['published'] : null)
                );
                $data->catid = $app->getInput()->getInt('catid', (!empty($filters['category_id']) ? $filters['category_id'] : null));

                if ($app->isClient('administrator')) {
                    $data->language = $app->getInput()->getString('language', (!empty($filters['language']) ? $filters['language'] : null));
                }

                $data->access = $app->getInput()->getInt('access', (!empty($filters['access']) ? $filters['access'] : $app->get('access')));
            }
        }

        // If there are params fieldsets in the form it will fail with a registry object
        if (isset($data->params) && $data->params instanceof Registry) {
            $data->params = $data->params->toArray();
        }

        $this->preprocessData('com_content.article', $data);

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
     * @since   3.7.0
     */
    public function validate($form, $data, $group = null)
    {
        if (!$this->getCurrentUser()->authorise('core.admin', 'com_content')) {
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
        $app    = Factory::getApplication();
        $input  = $app->getInput();
        $filter = InputFilter::getInstance();

        if (isset($data['metadata']) && isset($data['metadata']['author'])) {
            $data['metadata']['author'] = $filter->clean($data['metadata']['author'], 'TRIM');
        }

        if (isset($data['created_by_alias'])) {
            $data['created_by_alias'] = $filter->clean($data['created_by_alias'], 'TRIM');
        }

        if (isset($data['images']) && \is_array($data['images'])) {
            $registry = new Registry($data['images']);

            $data['images'] = (string) $registry;
        }

        $this->workflowBeforeSave();

        // Create new category, if needed.
        $createCategory = true;

        if (\is_null($data['catid'])) {
            // When there is no catid passed don't try to create one
            $createCategory = false;
        }

        // If category ID is provided, check if it's valid.
        if (is_numeric($data['catid']) && $data['catid']) {
            $createCategory = !CategoriesHelper::validateCategoryId($data['catid'], 'com_content');
        }

        // Save New Category
        if ($createCategory && $this->canCreateCategory()) {
            $category = [
                // Remove #new# prefix, if exists.
                'title'     => strpos($data['catid'], '#new#') === 0 ? substr($data['catid'], 5) : $data['catid'],
                'parent_id' => 1,
                'extension' => 'com_content',
                'language'  => $data['language'],
                'published' => 1,
            ];

            /** @var \Joomla\Component\Categories\Administrator\Model\CategoryModel $categoryModel */
            $categoryModel = Factory::getApplication()->bootComponent('com_categories')
                ->getMVCFactory()->createModel('Category', 'Administrator', ['ignore_request' => true]);

            // Create new category.
            if (!$categoryModel->save($category)) {
                $this->setError($categoryModel->getError());

                return false;
            }

            // Get the Category ID.
            $data['catid'] = $categoryModel->getState('category.id');
        }

        if (isset($data['urls']) && \is_array($data['urls'])) {
            $check = $input->post->get('jform', [], 'array');

            foreach ($data['urls'] as $i => $url) {
                if ($url != false && ($i == 'urla' || $i == 'urlb' || $i == 'urlc')) {
                    if (preg_match('~^#[a-zA-Z]{1}[a-zA-Z0-9-_:.]*$~', $check['urls'][$i]) == 1) {
                        $data['urls'][$i] = $check['urls'][$i];
                    } else {
                        $data['urls'][$i] = PunycodeHelper::urlToPunycode($url);
                    }
                }
            }

            unset($check);

            $registry = new Registry($data['urls']);

            $data['urls'] = (string) $registry;
        }

        // Alter the title for save as copy
        if ($input->get('task') == 'save2copy') {
            $origTable = $this->getTable();

            if ($app->isClient('site')) {
                $origTable->load($input->getInt('a_id'));

                if ($origTable->title === $data['title']) {
                    /**
                     * If title of article is not changed, set alias to original article alias so that Joomla! will generate
                     * new Title and Alias for the copied article
                     */
                    $data['alias'] = $origTable->alias;
                } else {
                    $data['alias'] = '';
                }
            } else {
                $origTable->load($input->getInt('id'));
            }

            if ($data['title'] == $origTable->title) {
                list($title, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['title']);
                $data['title']       = $title;
                $data['alias']       = $alias;
            } elseif ($data['alias'] == $origTable->alias) {
                $data['alias'] = '';
            }
        }

        // Automatic handling of alias for empty fields
        if (\in_array($input->get('task'), ['apply', 'save', 'save2new']) && (!isset($data['id']) || (int) $data['id'] == 0)) {
            if ($data['alias'] == null) {
                if ($app->get('unicodeslugs') == 1) {
                    $data['alias'] = OutputFilter::stringUrlUnicodeSlug($data['title']);
                } else {
                    $data['alias'] = OutputFilter::stringURLSafe($data['title']);
                }

                $table = $this->getTable();

                if ($table->load(['alias' => $data['alias'], 'catid' => $data['catid']])) {
                    $msg = Text::_('COM_CONTENT_SAVE_WARNING');
                }

                list($title, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['title']);
                $data['alias']       = $alias;

                if (isset($msg)) {
                    $app->enqueueMessage($msg, 'warning');
                }
            }
        }

        if (parent::save($data)) {
            // Check if featured is set and if not managed by workflow
            if (isset($data['featured']) && !$this->bootComponent('com_content')->isFunctionalityUsed('core.featured', 'com_content.article')) {
                if (
                    !$this->featured(
                        $this->getState($this->getName() . '.id'),
                        $data['featured'],
                        $data['featured_up'] ?? null,
                        $data['featured_down'] ?? null
                    )
                ) {
                    return false;
                }
            }

            $this->workflowAfterSave($data);

            return true;
        }

        return false;
    }

    /**
     * Method to toggle the featured setting of articles.
     *
     * @param   array        $pks           The ids of the items to toggle.
     * @param   integer      $value         The value to toggle to.
     * @param   string|Date  $featuredUp    The date which item featured up.
     * @param   string|Date  $featuredDown  The date which item featured down.
     *
     * @return  boolean  True on success.
     */
    public function featured($pks, $value = 0, $featuredUp = null, $featuredDown = null)
    {
        // Sanitize the ids.
        $pks     = (array) $pks;
        $pks     = ArrayHelper::toInteger($pks);
        $value   = (int) $value;
        $context = $this->option . '.' . $this->name;

        $this->workflowBeforeStageChange();

        // Include the plugins for the change of state event.
        PluginHelper::importPlugin($this->events_map['featured']);

        // Convert empty strings to null for the query.
        if ($featuredUp === '') {
            $featuredUp = null;
        }

        if ($featuredDown === '') {
            $featuredDown = null;
        }

        if (empty($pks)) {
            $this->setError(Text::_('COM_CONTENT_NO_ITEM_SELECTED'));

            return false;
        }

        $table = $this->getTable('Featured', 'Administrator');

        // Trigger the before change state event.
        $eventResult = Factory::getApplication()->getDispatcher()->dispatch(
            $this->event_before_change_featured,
            AbstractEvent::create(
                $this->event_before_change_featured,
                [
                    'eventClass' => FeatureEvent::class,
                    'subject'    => $this,
                    'extension'  => $context,
                    'pks'        => $pks,
                    'value'      => $value,
                ]
            )
        );

        if ($eventResult->getArgument('abort', false)) {
            $this->setError(Text::_($eventResult->getArgument('abortReason')));

            return false;
        }

        try {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__content'))
                ->set($db->quoteName('featured') . ' = :featured')
                ->whereIn($db->quoteName('id'), $pks)
                ->bind(':featured', $value, ParameterType::INTEGER);
            $db->setQuery($query);
            $db->execute();

            if ($value === 0) {
                // Adjust the mapping table.
                // Clear the existing features settings.
                $query = $db->getQuery(true)
                    ->delete($db->quoteName('#__content_frontpage'))
                    ->whereIn($db->quoteName('content_id'), $pks);
                $db->setQuery($query);
                $db->execute();
            } else {
                // First, we find out which of our new featured articles are already featured.
                $query = $db->getQuery(true)
                    ->select($db->quoteName('content_id'))
                    ->from($db->quoteName('#__content_frontpage'))
                    ->whereIn($db->quoteName('content_id'), $pks);
                $db->setQuery($query);

                $oldFeatured = $db->loadColumn();

                // Update old featured articles
                if (\count($oldFeatured)) {
                    $query = $db->getQuery(true)
                        ->update($db->quoteName('#__content_frontpage'))
                        ->set(
                            [
                                $db->quoteName('featured_up') . ' = :featuredUp',
                                $db->quoteName('featured_down') . ' = :featuredDown',
                            ]
                        )
                        ->whereIn($db->quoteName('content_id'), $oldFeatured)
                        ->bind(':featuredUp', $featuredUp, $featuredUp ? ParameterType::STRING : ParameterType::NULL)
                        ->bind(':featuredDown', $featuredDown, $featuredDown ? ParameterType::STRING : ParameterType::NULL);
                    $db->setQuery($query);
                    $db->execute();
                }

                // We diff the arrays to get a list of the articles that are newly featured
                $newFeatured = array_diff($pks, $oldFeatured);

                // Featuring.
                if ($newFeatured) {
                    $query = $db->getQuery(true)
                        ->insert($db->quoteName('#__content_frontpage'))
                        ->columns(
                            [
                                $db->quoteName('content_id'),
                                $db->quoteName('ordering'),
                                $db->quoteName('featured_up'),
                                $db->quoteName('featured_down'),
                            ]
                        );

                    $dataTypes = [
                        ParameterType::INTEGER,
                        ParameterType::INTEGER,
                        $featuredUp ? ParameterType::STRING : ParameterType::NULL,
                        $featuredDown ? ParameterType::STRING : ParameterType::NULL,
                    ];

                    foreach ($newFeatured as $pk) {
                        $query->values(implode(',', $query->bindArray([$pk, 0, $featuredUp, $featuredDown], $dataTypes)));
                    }

                    $db->setQuery($query);
                    $db->execute();
                }
            }
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        $table->reorder();

        // Trigger the change state event.
        Factory::getApplication()->getDispatcher()->dispatch(
            $this->event_after_change_featured,
            AbstractEvent::create(
                $this->event_after_change_featured,
                [
                    'eventClass' => FeatureEvent::class,
                    'subject'    => $this,
                    'extension'  => $context,
                    'pks'        => $pks,
                    'value'      => $value,
                ]
            )
        );

        $this->cleanCache();

        return true;
    }

    /**
     * A protected method to get a set of ordering conditions.
     *
     * @param   object  $table  A record object.
     *
     * @return  array  An array of conditions to add to ordering queries.
     *
     * @since   1.6
     */
    protected function getReorderConditions($table)
    {
        return [
            $this->getDatabase()->quoteName('catid') . ' = ' . (int) $table->catid,
        ];
    }

    /**
     * Allows preprocessing of the Form object.
     *
     * @param   Form    $form   The form object
     * @param   array   $data   The data to be merged into the form object
     * @param   string  $group  The plugin group to be executed
     *
     * @return  void
     *
     * @since   3.0
     */
    protected function preprocessForm(Form $form, $data, $group = 'content')
    {
        if ($this->canCreateCategory()) {
            $form->setFieldAttribute('catid', 'allowAdd', 'true');

            // Add a prefix for categories created on the fly.
            $form->setFieldAttribute('catid', 'customPrefix', '#new#');
        }

        // Association content items
        if (Associations::isEnabled()) {
            $languages = LanguageHelper::getContentLanguages(false, false, null, 'ordering', 'asc');

            if (\count($languages) > 1) {
                $addform = new \SimpleXMLElement('<form />');
                $fields  = $addform->addChild('fields');
                $fields->addAttribute('name', 'associations');
                $fieldset = $fields->addChild('fieldset');
                $fieldset->addAttribute('name', 'item_associations');

                foreach ($languages as $language) {
                    $field = $fieldset->addChild('field');
                    $field->addAttribute('name', $language->lang_code);
                    $field->addAttribute('type', 'modal_article');
                    $field->addAttribute('language', $language->lang_code);
                    $field->addAttribute('label', $language->title);
                    $field->addAttribute('translate_label', 'false');
                    $field->addAttribute('select', 'true');
                    $field->addAttribute('new', 'true');
                    $field->addAttribute('edit', 'true');
                    $field->addAttribute('clear', 'true');
                    $field->addAttribute('propagate', 'true');
                }

                $form->load($addform, false);
            }
        }

        $this->workflowPreprocessForm($form, $data);

        parent::preprocessForm($form, $data, $group);
    }

    /**
     * Custom clean the cache of com_content and content modules
     *
     * @param   string   $group     The cache group
     * @param   integer  $clientId  No longer used, will be removed without replacement
     *                              @deprecated   4.3 will be removed in 6.0
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function cleanCache($group = null, $clientId = 0)
    {
        parent::cleanCache('com_content');
        parent::cleanCache('mod_articles_archive');
        parent::cleanCache('mod_articles_categories');
        parent::cleanCache('mod_articles_category');
        parent::cleanCache('mod_articles_latest');
        parent::cleanCache('mod_articles_news');
        parent::cleanCache('mod_articles_popular');
    }

    /**
     * Void hit function for pagebreak when editing content from frontend
     *
     * @return  void
     *
     * @since   3.6.0
     */
    public function hit()
    {
    }

    /**
     * Is the user allowed to create an on the fly category?
     *
     * @return  boolean
     *
     * @since   3.6.1
     */
    private function canCreateCategory()
    {
        return $this->getCurrentUser()->authorise('core.create', 'com_content');
    }

    /**
     * Delete #__content_frontpage items if the deleted articles was featured
     *
     * @param   array  $pks  The primary key related to the contents that was deleted.
     *
     * @return  boolean
     *
     * @since   3.7.0
     */
    public function delete(&$pks)
    {
        $return = parent::delete($pks);

        if ($return) {
            // Now check to see if this articles was featured if so delete it from the #__content_frontpage table
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__content_frontpage'))
                ->whereIn($db->quoteName('content_id'), $pks);
            $db->setQuery($query);
            $db->execute();

            $this->workflow->deleteAssociation($pks);
        }

        return $return;
    }
}
