<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.joomla
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Content\Joomla\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\CoreContent;
use Joomla\CMS\User\UserFactoryAwareTrait;
use Joomla\CMS\Workflow\WorkflowServiceInterface;
use Joomla\Component\Workflow\Administrator\Table\StageTable;
use Joomla\Component\Workflow\Administrator\Table\WorkflowTable;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
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
        if (!in_array($context, ['com_workflow.stage', 'com_workflow.workflow']) || $isNew || !$table->hasField('published')) {
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
     * @param   object   $article  A JTableContent object
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
                    'message'    => sprintf($lang->_('COM_CONTENT_ON_NEW_CONTENT'), $user->get('name'), $article->title),
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
        if (!in_array($context, ['com_categories.category', 'com_workflow.stage', 'com_workflow.workflow'])) {
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
        if ($value > 0 || !in_array($context, ['com_workflow.workflow', 'com_workflow.stage'])) {
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
        if (count($childCategoryIds)) {
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
        } else { // If we didn't have any categories to check, return 0
            return 0;
        }
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
