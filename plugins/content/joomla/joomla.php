<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.joomla
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Table\CoreContent;
use Joomla\CMS\User\User;
use Joomla\CMS\Workflow\WorkflowServiceInterface;
use Joomla\Component\Workflow\Administrator\Table\StageTable;
use Joomla\Component\Workflow\Administrator\Table\WorkflowTable;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

/**
 * Example Content Plugin
 *
 * @since  1.6
 */
class PlgContentJoomla extends CMSPlugin
{
	/**
	 * Application object
	 *
	 * @var    CMSApplicationInterface
	 * @since  4.0.0
	 */
	protected $app;

	/**
	 * Database Driver Instance
	 *
	 * @var    DatabaseDriver
	 * @since  4.0.0
	 */
	protected $db;

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
		if ($context === 'com_menus.item')
		{
			return $this->checkMenuItemBeforeSave($context, $table, $isNew, $data);
		}

		// Check we are handling the frontend edit form.
		if (!in_array($context, ['com_workflow.stage', 'com_workflow.workflow']) || $isNew)
		{
			return true;
		}

		$item = clone $table;

		$item->load($table->id);

		if ($item->published != -2 && $data['published'] == -2)
		{
			switch ($context)
			{
				case 'com_workflow.workflow':
					return $this->_canDeleteWorkflow($item->id);

				case 'com_workflow.stage':
					return $this->_canDeleteStage($item->id);
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
		if ($context !== 'com_content.form')
		{
			return;
		}

		// Check if this function is enabled.
		if (!$this->params->def('email_new_fe', 1))
		{
			return;
		}

		// Check this is a new article.
		if (!$isNew)
		{
			return;
		}

		$db = $this->db;
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__users'))
			->where($db->quoteName('sendEmail') . ' = 1')
			->where($db->quoteName('block') . ' = 0');
		$db->setQuery($query);
		$users = (array) $db->loadColumn();

		if (empty($users))
		{
			return;
		}

		$user = $this->app->getIdentity();

		// Messaging for new items

		$default_language = ComponentHelper::getParams('com_languages')->get('administrator');
		$debug = $this->app->get('debug_lang');

		foreach ($users as $user_id)
		{
			if ($user_id != $user->id)
			{
				// Load language for messaging
				$receiver = User::getInstance($user_id);
				$lang = Language::getInstance($receiver->getParam('admin_language', $default_language), $debug);
				$lang->load('com_content');
				$message = array(
					'user_id_to' => $user_id,
					'subject' => $lang->_('COM_CONTENT_NEW_ARTICLE'),
					'message' => sprintf($lang->_('COM_CONTENT_ON_NEW_CONTENT'), $user->get('name'), $article->title),
				);
				$model_message = $this->app->bootComponent('com_messages')->getMVCFactory()
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
		if (!in_array($context, ['com_categories.category', 'com_workflow.stage', 'com_workflow.workflow']))
		{
			return true;
		}

		switch ($context)
		{
			case 'com_categories.category':
				return $this->_canDeleteCategories($data);

			case 'com_workflow.workflow':
				return $this->_canDeleteWorkflow($data->id);

			case 'com_workflow.stage':
				return $this->_canDeleteStage($data->id);
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
		if ($value != -2 || !in_array($context, ['com_workflow.workflow', 'com_workflow.stage']))
		{
			return true;
		}

		$result = true;

		foreach ($pks as $id)
		{
			switch ($context)
			{
				case 'com_workflow.workflow':
					return $result && $this->_canDeleteWorkflow($id);

				case 'com_workflow.stage':
					$result = $result && $this->_canDeleteStage($id);
			}
		}

		return true;
	}

	/**
	 * Checks if a given category can be deleted
	 *
	 * @param   object  $data  The category object
	 *
	 * @return  boolean
	 */
	private function _canDeleteCategories($data)
	{
		// Check if this function is enabled.
		if (!$this->params->def('check_categories', 1))
		{
			return true;
		}

		$extension = $this->app->input->getString('extension');

		// Default to true if not a core extension
		$result = true;

		$tableInfo = array(
			'com_banners' => array('table_name' => '#__banners'),
			'com_contact' => array('table_name' => '#__contact_details'),
			'com_content' => array('table_name' => '#__content'),
			'com_newsfeeds' => array('table_name' => '#__newsfeeds'),
			'com_users' => array('table_name' => '#__user_notes'),
			'com_weblinks' => array('table_name' => '#__weblinks'),
		);

		// Now check to see if this is a known core extension
		if (isset($tableInfo[$extension]))
		{
			// Get table name for known core extensions
			$table = $tableInfo[$extension]['table_name'];

			// See if this category has any content items
			$count = $this->_countItemsInCategory($table, $data->get('id'));

			// Return false if db error
			if ($count === false)
			{
				$result = false;
			}
			else
			{
				// Show error if items are found in the category
				if ($count > 0)
				{
					$msg = Text::sprintf('COM_CATEGORIES_DELETE_NOT_ALLOWED', $data->get('title'))
						. ' ' . Text::plural('COM_CATEGORIES_N_ITEMS_ASSIGNED', $count);
					$this->app->enqueueMessage($msg, 'error');
					$result = false;
				}

				// Check for items in any child categories (if it is a leaf, there are no child categories)
				if (!$data->isLeaf())
				{
					$count = $this->_countItemsInChildren($table, $data->get('id'), $data);

					if ($count === false)
					{
						$result = false;
					}
					elseif ($count > 0)
					{
						$msg = Text::sprintf('COM_CATEGORIES_DELETE_NOT_ALLOWED', $data->get('title'))
							. ' ' . Text::plural('COM_CATEGORIES_HAS_SUBCATEGORY_ITEMS', $count);
						$this->app->enqueueMessage($msg, 'error');
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
	private function _canDeleteWorkflow($pk)
	{
		// Check if this workflow is the default stage
		$table = new WorkflowTable($this->db);

		$table->load($pk);

		if (empty($table->id))
		{
			return true;
		}

		if ($table->default)
		{
			throw new Exception(Text::_('COM_WORKFLOW_MSG_DELETE_IS_DEFAULT'));
		}

		$parts = explode('.', $table->extension);

		$component = $this->app->bootComponent($parts[0]);

		$section = '';

		if (!empty($parts[1]))
		{
			$section = $parts[1];
		}

		// No core interface => we're ok
		if (!$component instanceof WorkflowServiceInterface)
		{
			return true;
		}

		/** @var \Joomla\Component\Workflow\Administrator\Model\StagesModel $model */
		$model = $this->app->bootComponent('com_workflow')->getMVCFactory()
			->createModel('Stages', 'Administrator', ['ignore_request' => true]);

		$model->setState('filter.workflow_id', $pk);
		$model->setState('filter.extension', $table->extension);

		$stages = $model->getItems();

		$stage_ids = array_column($stages, 'id');

		$result = $this->_countItemsInStage($stage_ids, $table->extension);

		// Return false if db error
		if ($result > 0)
		{
			throw new Exception(Text::_('COM_WORKFLOW_MSG_DELETE_WORKFLOW_IS_ASSIGNED'));
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
	private function _canDeleteStage($pk)
	{
		$table = new StageTable($this->db);

		$table->load($pk);

		if (empty($table->id))
		{
			return true;
		}

		// Check if this stage is the default stage
		if ($table->default)
		{
			throw new Exception(Text::_('COM_WORKFLOW_MSG_DELETE_IS_DEFAULT'));
		}

		$workflow = new WorkflowTable($this->db);

		$workflow->load($table->workflow_id);

		if (empty($workflow->id))
		{
			return true;
		}

		$parts = explode('.', $workflow->extension);

		$component = $this->app->bootComponent($parts[0]);

		// No core interface => we're ok
		if (!$component instanceof WorkflowServiceInterface)
		{
			return true;
		}

		$stage_ids = [$table->id];

		$result = $this->_countItemsInStage($stage_ids, $workflow->extension);

		// Return false if db error
		if ($result > 0)
		{
			throw new Exception(Text::_('COM_WORKFLOW_MSG_DELETE_STAGE_IS_ASSIGNED'));
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
	private function _countItemsInCategory($table, $catid)
	{
		$db = $this->db;
		$query = $db->getQuery(true);

		// Count the items in this category
		$query->select('COUNT(' . $db->quoteName('id') . ')')
			->from($db->quoteName($table))
			->where($db->quoteName('catid') . ' = :catid')
			->bind(':catid', $catid, ParameterType::INTEGER);
		$db->setQuery($query);

		try
		{
			$count = $db->loadResult();
		}
		catch (RuntimeException $e)
		{
			$this->app->enqueueMessage($e->getMessage(), 'error');

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
	private function _countItemsInStage(array $stageIds, string $extension) : bool
	{
		$db = $this->db;

		$parts = explode('.', $extension);

		$stageIds = ArrayHelper::toInteger($stageIds);
		$stageIds = array_filter($stageIds);

		$section = '';

		if (!empty($parts[1]))
		{
			$section = $parts[1];
		}

		$component = $this->app->bootComponent($parts[0]);

		$table = $component->getWorkflowTableBySection($section);

		if (empty($stageIds) || !$table)
		{
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

		try
		{
			return (int) $db->setQuery($query)->loadResult();
		}
		catch (Exception $e)
		{
			$this->app->enqueueMessage($e->getMessage(), 'error');
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
	private function _countItemsInChildren($table, $catid, $data)
	{
		$db = $this->db;

		// Create subquery for list of child categories
		$childCategoryTree = $data->getTree();

		// First element in tree is the current category, so we can skip that one
		unset($childCategoryTree[0]);
		$childCategoryIds = array();

		foreach ($childCategoryTree as $node)
		{
			$childCategoryIds[] = (int) $node->id;
		}

		// Make sure we only do the query if we have some categories to look in
		if (count($childCategoryIds))
		{
			// Count the items in this category
			$query = $db->getQuery(true)
				->select('COUNT(' . $db->quoteName('id') . ')')
				->from($db->quoteName($table))
				->whereIn($db->quoteName('catid'), $childCategoryIds);
			$db->setQuery($query);

			try
			{
				$count = $db->loadResult();
			}
			catch (RuntimeException $e)
			{
				$this->app->enqueueMessage($e->getMessage(), 'error');

				return false;
			}

			return $count;
		}
		else
			// If we didn't have any categories to check, return 0
		{
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

		if ($context === 'com_workflow.stage' && $value == -2)
		{
			foreach ($pks as $pk)
			{
				if (!$this->_canDeleteStage($pk))
				{
					return false;
				}
			}

			return true;
		}

		$db = $this->db;
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
		// Check we are handling the frontend edit form.
		if ($context === 'com_menus.item')
		{
			return true;
		}

		// Special case for Create article menu item
		if ($table->link !== 'index.php?option=com_content&view=form&layout=edit')
		{
			return true;
		}

		// Display error if catid is not set when enable_category is enabled
		$params = json_decode($table->params, true);

		if ($params['enable_category'] == 1 && empty($params['catid']))
		{
			$table->setError(Text::_('COM_CONTENT_CREATE_ARTICLE_ERROR'));

			return false;
		}

		return true;
	}
}
