<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.joomla
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Content\Joomla\Extension;

use Joomla\CMS\Cache\CacheControllerFactory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Plugin\System\Schemaorg\BeforeCompileHeadEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\CoreContent;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserFactoryAwareTrait;
use Joomla\CMS\Workflow\WorkflowServiceInterface;
use Joomla\Component\Workflow\Administrator\Table\StageTable;
use Joomla\Component\Workflow\Administrator\Table\WorkflowTable;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Example Content Plugin
 *
 * @since  1.6
 */
final class Joomla extends CMSPlugin
{
    use DatabaseAwareTrait;
    use UserFactoryAwareTrait;

    /**
     * The save event.
     *
     * @param   string   $context  The context
     * @param   object   $table    The item
     * @param   boolean  $isNew    Is new item
     * @param   array    $data     The validated data
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    public function onContentBeforeSave($context, $table, $isNew, $data)
    {
        if ($context === 'com_menus.item') {
            return $this->checkMenuItemBeforeSave($context, $table, $isNew, $data);
        }

        // Check we are handling the frontend edit form.
        if (!\in_array($context, ['com_workflow.stage', 'com_workflow.workflow']) || $isNew || !$table->hasField('published')) {
            return true;
        }

        $item = clone $table;

        $item->load($table->id);

        $publishedField = $item->getColumnAlias('published');

        if ($item->$publishedField > 0 && isset($data[$publishedField]) && $data[$publishedField] < 1) {
            switch ($context) {
                case 'com_workflow.workflow':
                    return $this->workflowNotUsed($item->id);

                case 'com_workflow.stage':
                    return $this->stageNotUsed($item->id);
            }
        }

        return true;
    }

    /**
     * Example after save content method
     * Article is passed by reference, but after the save, so no changes will be saved.
     * Method is called right after the content is saved
     *
     * @param   string   $context  The context of the content passed to the plugin (added in 1.6)
     * @param   object   $article  A \Joomla\CMS\Table\Table object
     * @param   boolean  $isNew    If the content is just about to be created
     *
     * @return  void
     *
     * @since   1.6
     */
    public function onContentAfterSave($context, $article, $isNew): void
    {
        // Check we are handling the frontend edit form.
        if ($context !== 'com_content.form') {
            return;
        }

        // Check if this function is enabled.
        if (!$this->params->def('email_new_fe', 1)) {
            return;
        }

        // Check this is a new article.
        if (!$isNew) {
            return;
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__users'))
            ->where($db->quoteName('sendEmail') . ' = 1')
            ->where($db->quoteName('block') . ' = 0');
        $db->setQuery($query);
        $users = (array) $db->loadColumn();

        if (empty($users)) {
            return;
        }

        $user = $this->getApplication()->getIdentity();

        // Messaging for new items

        $default_language = ComponentHelper::getParams('com_languages')->get('administrator');
        $debug            = $this->getApplication()->get('debug_lang');

        foreach ($users as $user_id) {
            if ($user_id != $user->id) {
                // Load language for messaging
                $receiver = $this->getUserFactory()->loadUserById($user_id);
                $lang     = Language::getInstance($receiver->getParam('admin_language', $default_language), $debug);
                $lang->load('com_content');
                $message = [
                    'user_id_to' => $user_id,
                    'subject'    => $lang->_('COM_CONTENT_NEW_ARTICLE'),
                    'message'    => \sprintf($lang->_('COM_CONTENT_ON_NEW_CONTENT'), $user->name, $article->title),
                ];
                $model_message = $this->getApplication()->bootComponent('com_messages')->getMVCFactory()
                    ->createModel('Message', 'Administrator');
                $model_message->save($message);
            }
        }
    }

    /**
     * Don't allow categories to be deleted if they contain items or subcategories with items
     *
     * @param   string  $context  The context for the content passed to the plugin.
     * @param   object  $data     The data relating to the content that was deleted.
     *
     * @return  boolean
     *
     * @since   1.6
     */
    public function onContentBeforeDelete($context, $data)
    {
        // Skip plugin if we are deleting something other than categories
        if (!\in_array($context, ['com_categories.category', 'com_workflow.stage', 'com_workflow.workflow'])) {
            return true;
        }

        switch ($context) {
            case 'com_categories.category':
                return $this->canDeleteCategories($data);

            case 'com_workflow.workflow':
                return $this->workflowNotUsed($data->id);

            case 'com_workflow.stage':
                return $this->stageNotUsed($data->id);
        }
    }

    /**
     * Don't allow workflows/stages to be deleted if they contain items
     *
     * @param   string  $context  The context for the content passed to the plugin.
     * @param   object  $pks      The IDs of the records which will be changed.
     * @param   object  $value    The new state.
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    public function onContentBeforeChangeState($context, $pks, $value)
    {
        if ($value > 0 || !\in_array($context, ['com_workflow.workflow', 'com_workflow.stage'])) {
            return true;
        }

        $result = true;

        foreach ($pks as $id) {
            switch ($context) {
                case 'com_workflow.workflow':
                    $result = $result && $this->workflowNotUsed($id);
                    break;

                case 'com_workflow.stage':
                    $result = $result && $this->stageNotUsed($id);
                    break;
            }
        }

        return $result;
    }

    /**
     * Add autogenerated schema data for content and contacts
     *
     * @param   BeforeCompileHeadEvent  $event  The event object
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function onSchemaBeforeCompileHead(BeforeCompileHeadEvent $event): void
    {
        if (!$this->getApplication()->isClient('site')) {
            return;
        }

        $context = $event->getContext();
        $schema  = $event->getSchema();

        [$extension, $view, $id] = explode('.', $context);

        if ($extension === 'com_content' && $this->params->get('schema_content', 1)) {
            $this->injectContentSchema($context, $schema);
        } elseif ($extension === 'com_contact' && $this->params->get('schema_contact', 1)) {
            $this->injectContactSchema($context, $schema);
        }
    }

    /**
     * Inject com_content schemas if needed
     *
     * @param   string    $context  The com_content context like com_content.article.5
     * @param   Registry  $schema   The overall schema object to manipulate
     *
     * @return  void
     *
     * @since   5.0.0
     */
    private function injectContentSchema(string $context, Registry $schema)
    {
        $app = $this->getApplication();
        $db  = $this->getDatabase();

        [$extension, $view, $id] = explode('.', $context);

        // Check if there is already a schema for the item, then skip it
        $mySchema = $schema->toArray();

        if (!isset($mySchema['@graph']) || !\is_array($mySchema['@graph'])) {
            return;
        }

        $baseId   = Uri::root() . '#/schema/';
        $schemaId = $baseId . str_replace('.', '/', $context);

        foreach ($mySchema['@graph'] as $entry) {
            // Someone added our context already, no need to add automated data
            if (isset($entry['@id']) && $entry['@id'] == $schemaId) {
                return;
            }
        }

        $additionalSchemas = [];

        $component = $this->getApplication()->bootComponent('com_content')->getMVCFactory();

        $enableCache = $this->params->get('schema_cache', 1);

        $cache = Factory::getContainer()->get(CacheControllerFactory::class)
            ->createCacheController('Callback', ['lifetime' => $app->get('cachetime'), 'caching' => $enableCache, 'defaultgroup' => 'schemaorg']);

        // Add article data
        if ($view == 'article' && $id > 0) {
            $additionalSchemas = $cache->get(function ($id) use ($component, $baseId) {
                $model = $component->createModel('Article', 'Site');

                $article = $model->getItem($id);

                if (empty($article->id)) {
                    return;
                }

                $article->images = new Registry($article->images);

                $articleSchema = $this->createArticleSchema($article);

                $articleSchema['isPartOf'] = ['@id' => $baseId . 'WebPage/base'];

                return [$articleSchema];
            }, [$id]);
        } elseif (\in_array($view, ['category', 'featured', 'archive'])) {
            $additionalSchemas = $cache->get(function ($view, $id) use ($component, $baseId, $app, $db) {
                $menu     = $app->getMenu()->getActive();
                $schemaId = $baseId . 'com_content/' . $view . ($view == 'category' ? '/' . $id : '');

                $additionalSchemas = [];

                $additionalSchema = [
                    '@type'    => 'Blog',
                    '@id'      => $schemaId,
                    'isPartOf' => ['@id' => $baseId . 'WebPage/base'],
                    'name'     => htmlentities($menu->title),
                    'blogPost' => [],
                ];

                if ($menu->getParams()->get('menu-meta_description')) {
                    $additionalSchema['description'] = htmlentities($menu->getParams()->get('menu-meta_description'));
                }

                $model = $component->createModel($view, 'Site');

                $articles = $model->getItems();

                $articleIds = ArrayHelper::getColumn($articles, 'id');

                if (!empty($articleIds)) {
                    $aContext = 'com_content.article';

                    // Load the schema data from the database
                    $query = $db->getQuery(true)
                        ->select('*')
                        ->from($db->quoteName('#__schemaorg'))
                        ->whereIn($db->quoteName('itemId'), $articleIds)
                        ->where($db->quoteName('context') . ' = :context')
                        ->bind(':context', $aContext, ParameterType::STRING);

                    $schemas = $db->setQuery($query)->loadObjectList('itemId');

                    foreach ($articles as $article) {
                        if (isset($schemas[$article->id])) {
                            $localSchema = new Registry($schemas[$article->id]->schema);

                            $localSchema->set('@id', $baseId . str_replace('.', '/', $aContext) . '/' . (int) $article->id);

                            $additionalSchema['blogPost'][] = ['@id' => $localSchema->get('@id')];

                            $additionalSchemas[] = $localSchema->toArray();

                            continue;
                        }

                        // No schema found, fallback to default one
                        $article->images = new Registry($article->images);

                        $articleSchema = $this->createArticleSchema($article);

                        // Set to BlogPosting
                        $articleSchema['@type'] = 'BlogPosting';

                        $additionalSchemas[] = $articleSchema;

                        $additionalSchema['blogPost'][] = ['@id' => $articleSchema['@id']];
                    }
                }

                array_unshift($additionalSchemas, $additionalSchema);

                return $additionalSchemas;
            }, [$view, $id]);
        }

        if (!empty($additionalSchemas)) {
            $mySchema['@graph'] = array_merge($mySchema['@graph'], $additionalSchemas);
        }

        $schema->set('@graph', $mySchema['@graph']);
    }

    /**
     * Returns a finished Article schema type based on a given joomla article
     *
     * @param object $article  An article to extract schema data from
     *
     * @return array
     *
     * @since   5.0.0
     */
    private function createArticleSchema(object $article)
    {
        $baseId   = Uri::root() . '#/schema/';
        $schemaId = $baseId . 'com_content/article/' . (int) $article->id;

        $schema = [];

        $schema['@type']       = 'Article';
        $schema['@id']         = $schemaId;
        $schema['name']        = $article->title;
        $schema['headline']    = $article->title;

        $schema['inLanguage']  = $article->language === '*' ? $this->getApplication()->get('language') : $article->language;

        // Author information
        if ($article->params->get('show_author') && !empty($article->author)) {
            $author = [];

            $author['@type'] = 'Person';
            $author['name']  = $article->created_by_alias ?: $article->author;

            if ($article->params->get('link_author') == true && !empty($article->contact_link)) {
                $author['url'] = $article->contact_link;
            }

            $schema['author'] = $author;
        }

        // Images
        if ($article->images->get('image_intro')) {
            $schema['thumbnailUrl'] = HTMLHelper::_('cleanImageUrl', $article->images->get('image_intro'))->url;
        }

        if ($article->images->get('image_fulltext')) {
            $schema['image'] = HTMLHelper::_('cleanImageUrl', $article->images->get('image_fulltext'))->url;
        }

        // Categories
        $categories = [];

        // Parent category if not root
        if ($article->params->get('show_parent_category') && !empty($article->parent_id) && $article->parent_id > 1) {
            $categories[] = $article->parent_title;
        }

        // Current category
        if ($article->params->get('show_category')) {
            $categories[] = $article->category_title;
        }

        if (!empty($categories)) {
            $schema['articleSection'] = implode(', ', $categories);
        }

        // Dates
        if ($article->params->get('show_publish_date')) {
            $schema['dateCreated'] = Factory::getDate($article->created)->toISO8601();
        }

        if ($article->params->get('show_modify_date')) {
            $schema['dateModified'] = Factory::getDate($article->modified)->toISO8601();
        }

        // Hits
        if ($article->params->get('show_hits')) {
            $counter = [];

            $counter['@type']                = 'InteractionCounter';
            $counter['userInteractionCount'] = $article->hits;

            $schema['interactionStatistic'] = $counter;
        }

        return $schema;
    }

    /**
     * Inject com_contact schemas if needed
     *
     * @param   string    $context  The com_contact context like com_contact.contact.5
     * @param   Registry  $schema   The overall schema object to manipulate
     *
     * @return  void
     *
     * @since   5.0.0
     */
    private function injectContactSchema(string $context, Registry $schema)
    {
        $app = $this->getApplication();
        $db  = $this->getDatabase();

        [$extension, $view, $id] = explode('.', $context);

        // Check if there is already a schema for the item, then skip it
        $mySchema = $schema->toArray();

        if (!isset($mySchema['@graph']) || !\is_array($mySchema['@graph'])) {
            return;
        }

        $baseId   = Uri::root() . '#/schema/';
        $schemaId = $baseId . str_replace('.', '/', $context);

        foreach ($mySchema['@graph'] as $entry) {
            // Someone added our context already, no need to add automated data
            if (isset($entry['@id']) && $entry['@id'] == $schemaId) {
                return;
            }
        }

        $additionalSchema = [];

        $component = $this->getApplication()->bootComponent('com_contact')->getMVCFactory();

        $enableCache = $this->params->get('schema_cache', 1);

        $cache = Factory::getContainer()->get(CacheControllerFactory::class)
            ->createCacheController('Callback', ['lifetime' => $app->get('cachetime'), 'caching' => $enableCache, 'defaultgroup' => 'schemaorg']);

        // Add contact data
        if ($view == 'contact' && $id > 0) {
            $additionalSchema = $cache->get(function ($id) use ($component, $baseId) {
                $model = $component->createModel('Contact', 'Site');

                $contact = $model->getItem($id);

                if (empty($contact->id)) {
                    return;
                }

                $contactSchema = $this->createContactSchema($contact);

                $contactSchema['isPartOf'] = ['@id' => $baseId . 'WebPage/base'];

                return $contactSchema;
            }, [$id]);

            $mySchema['@graph'][] = $additionalSchema;
        } elseif ($view === 'featured') {
            $additionalSchemas = $cache->get(function ($graph) use ($component, $baseId) {
                $model = $component->createModel('Featured', 'Site');

                $contacts = $model->getItems();

                $allSchemas = [];

                foreach ($contacts as $contact) {
                    foreach ($graph as $entry) {
                        $schemaId = $baseId . 'com_contact/contact/' . (int) $contact->id;

                        // Someone added our context already, no need to add automated data
                        if (isset($entry['@id']) && $entry['@id'] == $schemaId) {
                            return;
                        }
                    }

                    $contactSchema = $this->createContactSchema($contact);

                    $contactSchema['isPartOf'] = ['@id' => $baseId . 'WebPage/base'];

                    $allSchemas[] = $contactSchema;
                }

                return $allSchemas;
            }, [$mySchema['@graph']]);

            foreach ($additionalSchemas as $additionalSchema) {
                $mySchema['@graph'][] = $additionalSchema;
            }
        }

        $schema->set('@graph', $mySchema['@graph']);
    }

    /**
     * Returns a finished Person schema type based on a given joomla contact
     *
     * @param object $contact  A contact to extract schema data from
     *
     * @return array
     *
     * @since   5.0.0
     */
    private function createContactSchema(object $contact)
    {
        $baseId   = Uri::root() . '#/schema/';
        $schemaId = $baseId . 'com_contact/contact/' . (int) $contact->id;

        $schema = [];

        $schema['@type']       = 'Person';
        $schema['@id']         = $schemaId;
        $schema['name']        = $contact->name;

        if ($contact->image && $contact->params->get('show_image')) {
            $schema['image'] = HTMLHelper::_('cleanImageUrl', $contact->image)->url;
        }

        if ($contact->con_position && $contact->params->get('show_position')) {
            $schema['jobTitle'] = $contact->con_position;
        }

        $schema['address'] = [];

        if ($contact->params->get('show_street_address') && $contact->address) {
            $schema['address']['streetAddress'] = $contact->address;
        }

        if ($contact->params->get('show_suburb') && $contact->suburb) {
            $schema['address']['addressLocality'] = $contact->suburb;
        }

        if ($contact->params->get('show_state') && $contact->state) {
            $schema['address']['addressRegion'] = $contact->state;
        }

        if ($contact->params->get('show_postcode') && $contact->postcode) {
            $schema['address']['postalCode'] = $contact->postcode;
        }

        if ($contact->params->get('show_country') && $contact->country) {
            $schema['address']['addressCountry'] = $contact->country;
        }

        if ($contact->params->get('show_telephone') && $contact->telephone) {
            $schema['address']['telephone'] = $contact->telephone;
        } elseif ($contact->params->get('show_mobile') && $contact->mobile) {
            $schema['address']['telephone'] = $contact->mobile;
        }

        if ($contact->params->get('show_fax') && $contact->fax) {
            $schema['address']['faxNumber'] = $contact->fax;
        }

        if ($contact->params->get('show_webpage') && $contact->webpage) {
            $schema['address']['url'] = $contact->webpage;
        }

        return $schema;
    }

    /**
     * Checks if a given category can be deleted
     *
     * @param   object  $data  The category object
     *
     * @return  boolean
     */
    private function canDeleteCategories($data)
    {
        // Check if this function is enabled.
        if (!$this->params->def('check_categories', 1)) {
            return true;
        }

        $extension = $this->getApplication()->getInput()->getString('extension');

        // Default to true if not a core extension
        $result = true;

        $tableInfo = [
            'com_banners'   => ['table_name' => '#__banners'],
            'com_contact'   => ['table_name' => '#__contact_details'],
            'com_content'   => ['table_name' => '#__content'],
            'com_newsfeeds' => ['table_name' => '#__newsfeeds'],
            'com_users'     => ['table_name' => '#__user_notes'],
            'com_weblinks'  => ['table_name' => '#__weblinks'],
        ];

        // Now check to see if this is a known core extension
        if (isset($tableInfo[$extension])) {
            // Get table name for known core extensions
            $table = $tableInfo[$extension]['table_name'];

            // See if this category has any content items
            $count = $this->countItemsInCategory($table, $data->get('id'));

            // Return false if db error
            if ($count === false) {
                $result = false;
            } else {
                // Show error if items are found in the category
                if ($count > 0) {
                    $msg = Text::sprintf('COM_CATEGORIES_DELETE_NOT_ALLOWED', $data->get('title'))
                        . ' ' . Text::plural('COM_CATEGORIES_N_ITEMS_ASSIGNED', $count);
                    $this->getApplication()->enqueueMessage($msg, 'error');
                    $result = false;
                }

                // Check for items in any child categories (if it is a leaf, there are no child categories)
                if (!$data->isLeaf()) {
                    $count = $this->countItemsInChildren($table, $data->get('id'), $data);

                    if ($count === false) {
                        $result = false;
                    } elseif ($count > 0) {
                        $msg = Text::sprintf('COM_CATEGORIES_DELETE_NOT_ALLOWED', $data->get('title'))
                            . ' ' . Text::plural('COM_CATEGORIES_HAS_SUBCATEGORY_ITEMS', $count);
                        $this->getApplication()->enqueueMessage($msg, 'error');
                        $result = false;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Checks if a given workflow can be deleted
     *
     * @param   int  $pk  The stage ID
     *
     * @return  boolean
     *
     * @since  4.0.0
     */
    private function workflowNotUsed($pk)
    {
        // Check if this workflow is the default stage
        $table = new WorkflowTable($this->getDatabase());

        $table->load($pk);

        if (empty($table->id)) {
            return true;
        }

        if ($table->default) {
            throw new \Exception($this->getApplication()->getLanguage()->_('COM_WORKFLOW_MSG_DELETE_IS_DEFAULT'));
        }

        $parts = explode('.', $table->extension);

        $component = $this->getApplication()->bootComponent($parts[0]);

        $section = '';

        if (!empty($parts[1])) {
            $section = $parts[1];
        }

        // No core interface => we're ok
        if (!$component instanceof WorkflowServiceInterface) {
            return true;
        }

        /** @var \Joomla\Component\Workflow\Administrator\Model\StagesModel $model */
        $model = $this->getApplication()->bootComponent('com_workflow')->getMVCFactory()
            ->createModel('Stages', 'Administrator', ['ignore_request' => true]);

        $model->setState('filter.workflow_id', $pk);
        $model->setState('filter.extension', $table->extension);

        $stages = $model->getItems();

        $stage_ids = array_column($stages, 'id');

        $result = $this->countItemsInStage($stage_ids, $table->extension);

        // Return false if db error
        if ($result > 0) {
            throw new \Exception($this->getApplication()->getLanguage()->_('COM_WORKFLOW_MSG_DELETE_WORKFLOW_IS_ASSIGNED'));
        }

        return true;
    }

    /**
     * Checks if a given stage can be deleted
     *
     * @param   int  $pk  The stage ID
     *
     * @return  boolean
     *
     * @since  4.0.0
     */
    private function stageNotUsed($pk)
    {
        $table = new StageTable($this->getDatabase());

        $table->load($pk);

        if (empty($table->id)) {
            return true;
        }

        // Check if this stage is the default stage
        if ($table->default) {
            throw new \Exception($this->getApplication()->getLanguage()->_('COM_WORKFLOW_MSG_DELETE_IS_DEFAULT'));
        }

        $workflow = new WorkflowTable($this->getDatabase());

        $workflow->load($table->workflow_id);

        if (empty($workflow->id)) {
            return true;
        }

        $parts = explode('.', $workflow->extension);

        $component = $this->getApplication()->bootComponent($parts[0]);

        // No core interface => we're ok
        if (!$component instanceof WorkflowServiceInterface) {
            return true;
        }

        $stage_ids = [$table->id];

        $result = $this->countItemsInStage($stage_ids, $workflow->extension);

        // Return false if db error
        if ($result > 0) {
            throw new \Exception($this->getApplication()->getLanguage()->_('COM_WORKFLOW_MSG_DELETE_STAGE_IS_ASSIGNED'));
        }

        return true;
    }

    /**
     * Get count of items in a category
     *
     * @param   string   $table  table name of component table (column is catid)
     * @param   integer  $catid  id of the category to check
     *
     * @return  mixed  count of items found or false if db error
     *
     * @since   1.6
     */
    private function countItemsInCategory($table, $catid)
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Count the items in this category
        $query->select('COUNT(' . $db->quoteName('id') . ')')
            ->from($db->quoteName($table))
            ->where($db->quoteName('catid') . ' = :catid')
            ->bind(':catid', $catid, ParameterType::INTEGER);
        $db->setQuery($query);

        try {
            $count = $db->loadResult();
        } catch (\RuntimeException $e) {
            $this->getApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }

        return $count;
    }

    /**
     * Get count of items in assigned to a stage
     *
     * @param   array   $stageIds   The stage ids to test for
     * @param   string  $extension  The extension of the workflow
     *
     * @return  bool
     *
     * @since   4.0.0
     */
    private function countItemsInStage(array $stageIds, string $extension): bool
    {
        $db = $this->getDatabase();

        $parts = explode('.', $extension);

        $stageIds = ArrayHelper::toInteger($stageIds);
        $stageIds = array_filter($stageIds);

        $section = '';

        if (!empty($parts[1])) {
            $section = $parts[1];
        }

        $component = $this->getApplication()->bootComponent($parts[0]);

        $table = $component->getWorkflowTableBySection($section);

        if (empty($stageIds) || !$table) {
            return false;
        }

        $query = $db->getQuery(true);

        $query->select('COUNT(' . $db->quoteName('b.id') . ')')
            ->from($db->quoteName('#__workflow_associations', 'wa'))
            ->from($db->quoteName('#__workflow_stages', 's'))
            ->from($db->quoteName($table, 'b'))
            ->where($db->quoteName('wa.stage_id') . ' = ' . $db->quoteName('s.id'))
            ->where($db->quoteName('wa.item_id') . ' = ' . $db->quoteName('b.id'))
            ->whereIn($db->quoteName('s.id'), $stageIds);

        try {
            return (int) $db->setQuery($query)->loadResult();
        } catch (\Exception $e) {
            $this->getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return false;
    }

    /**
     * Get count of items in a category's child categories
     *
     * @param   string   $table  table name of component table (column is catid)
     * @param   integer  $catid  id of the category to check
     * @param   object   $data   The data relating to the content that was deleted.
     *
     * @return  mixed  count of items found or false if db error
     *
     * @since   1.6
     */
    private function countItemsInChildren($table, $catid, $data)
    {
        $db = $this->getDatabase();

        // Create subquery for list of child categories
        $childCategoryTree = $data->getTree();

        // First element in tree is the current category, so we can skip that one
        unset($childCategoryTree[0]);
        $childCategoryIds = [];

        foreach ($childCategoryTree as $node) {
            $childCategoryIds[] = (int) $node->id;
        }

        // Make sure we only do the query if we have some categories to look in
        if (\count($childCategoryIds)) {
            // Count the items in this category
            $query = $db->getQuery(true)
                ->select('COUNT(' . $db->quoteName('id') . ')')
                ->from($db->quoteName($table))
                ->whereIn($db->quoteName('catid'), $childCategoryIds);
            $db->setQuery($query);

            try {
                $count = $db->loadResult();
            } catch (\RuntimeException $e) {
                $this->getApplication()->enqueueMessage($e->getMessage(), 'error');

                return false;
            }

            return $count;
        }

        // If we didn't have any categories to check, return 0
        return 0;
    }

    /**
     * Change the state in core_content if the stage in a table is changed
     *
     * @param   string   $context  The context for the content passed to the plugin.
     * @param   array    $pks      A list of primary key ids of the content that has changed stage.
     * @param   integer  $value    The value of the condition that the content has been changed to
     *
     * @return  boolean
     *
     * @since   3.1
     */
    public function onContentChangeState($context, $pks, $value)
    {
        $pks = ArrayHelper::toInteger($pks);

        if ($context === 'com_workflow.stage' && $value < 1) {
            foreach ($pks as $pk) {
                if (!$this->stageNotUsed($pk)) {
                    return false;
                }
            }

            return true;
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName('core_content_id'))
            ->from($db->quoteName('#__ucm_content'))
            ->where($db->quoteName('core_type_alias') . ' = :context')
            ->whereIn($db->quoteName('core_content_item_id'), $pks)
            ->bind(':context', $context);
        $db->setQuery($query);
        $ccIds = $db->loadColumn();

        $cctable = new CoreContent($db);
        $cctable->publish($ccIds, $value);

        return true;
    }

    /**
     * The save event.
     *
     * @param   string   $context  The context
     * @param   object   $table    The item
     * @param   boolean  $isNew    Is new item
     * @param   array    $data     The validated data
     *
     * @return  boolean
     *
     * @since   3.9.12
     */
    private function checkMenuItemBeforeSave($context, $table, $isNew, $data)
    {
        // Special case for Create article menu item
        if ($table->link !== 'index.php?option=com_content&view=form&layout=edit') {
            return true;
        }

        // Display error if catid is not set when enable_category is enabled
        $params = json_decode($table->params, true);

        if (isset($params['enable_category']) && $params['enable_category'] === 1 && empty($params['catid'])) {
            $table->setError($this->getApplication()->getLanguage()->_('COM_CONTENT_CREATE_ARTICLE_ERROR'));

            return false;
        }

        return true;
    }
}
