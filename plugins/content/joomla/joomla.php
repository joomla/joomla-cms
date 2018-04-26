<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.joomla
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\User\User;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Table\CoreContent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Component\Messages\Administrator\Model\MessageModel;
use Joomla\Component\Content\Administrator\Table\ArticleTable;
use Joomla\Component\Workflow\Administrator\Helper\WorkflowHelper;

/**
 * Example Content Plugin
 *
 * @since  1.6
 */
class PlgContentJoomla extends CMSPlugin
{
	/**
	 * Example after save content method
	 * Article is passed by reference, but after the save, so no changes will be saved.
	 * Method is called right after the content is saved
	 *
	 * @param   string   $context  The context of the content passed to the plugin (added in 1.6)
	 * @param   object   $article  A JTableContent object
	 * @param   boolean  $isNew    If the content is just about to be created
	 *
	 * @return  boolean   true if function not enabled, is in frontend or is new. Else true or
	 *                    false depending on success of save function.
	 *
	 * @since   1.6
	 */
	public function onContentAfterSave($context, $article, $isNew)
	{
		// Check we are handling the frontend edit form.
		if ($context !== 'com_content.form')
		{
			return true;
		}

		// Check if this function is enabled.
		if (!$this->params->def('email_new_fe', 1))
		{
			return true;
		}

		// Check this is a new article.
		if (!$isNew)
		{
			return true;
		}

		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__users'))
			->where($db->quoteName('sendEmail') . ' = 1')
			->where($db->quoteName('block') . ' = 0');
		$db->setQuery($query);
		$users = (array) $db->loadColumn();

		if (empty($users))
		{
			return true;
		}

		$user = Factory::getUser();

		// Messaging for new items

		$default_language = ComponentHelper::getParams('com_languages')->get('administrator');
		$debug = Factory::getConfig()->get('debug_lang');
		$result = true;

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
					'message' => sprintf($lang->_('COM_CONTENT_ON_NEW_CONTENT'), $user->get('name'), $article->title)
				);
				$model_message = new MessageModel;
				$result = $model_message->save($message);
			}
		}

		return $result;
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
		if ($context !== 'com_categories.category')
		{
			return true;
		}

		// Check if this function is enabled.
		if (!$this->params->def('check_categories', 1))
		{
			return true;
		}

		$extension = Factory::getApplication()->input->getString('extension');

		// Default to true if not a core extension
		$result = true;

		$tableInfo = array(
			'com_banners' => array('table_name' => '#__banners'),
			'com_contact' => array('table_name' => '#__contact_details'),
			'com_content' => array('table_name' => '#__content'),
			'com_newsfeeds' => array('table_name' => '#__newsfeeds'),
			'com_weblinks' => array('table_name' => '#__weblinks')
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
						. Text::plural('COM_CATEGORIES_N_ITEMS_ASSIGNED', $count);
					Factory::getApplication()->enqueueMessage($msg, 'error');
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
							. Text::plural('COM_CATEGORIES_HAS_SUBCATEGORY_ITEMS', $count);
						Factory::getApplication()->enqueueMessage($msg, 'error');
						$result = false;
					}
				}
			}

			return $result;
		}
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
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		// Count the items in this category
		$query->select('COUNT(id)')
			->from($table)
			->where('catid = ' . $catid);
		$db->setQuery($query);

		try
		{
			$count = $db->loadResult();
		}
		catch (RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}

		return $count;
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
		$db = Factory::getDbo();

		// Create subquery for list of child categories
		$childCategoryTree = $data->getTree();

		// First element in tree is the current category, so we can skip that one
		unset($childCategoryTree[0]);
		$childCategoryIds = array();

		foreach ($childCategoryTree as $node)
		{
			$childCategoryIds[] = $node->id;
		}

		// Make sure we only do the query if we have some categories to look in
		if (count($childCategoryIds))
		{
			// Count the items in this category
			$query = $db->getQuery(true)
				->select('COUNT(id)')
				->from($table)
				->where('catid IN (' . implode(',', $childCategoryIds) . ')');
			$db->setQuery($query);

			try
			{
				$count = $db->loadResult();
			}
			catch (RuntimeException $e)
			{
				Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

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
	 * Change the state in core_content if the state in a table is changed
	 *
	 * @param   string   $context  The context for the content passed to the plugin.
	 * @param   array    $pks      A list of primary key ids of the content that has changed state.
	 * @param   integer  $value    The value of the state that the content has been changed to.
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	public function onContentChangeState($context, $pks, $value)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('core_content_id'))
			->from($db->quoteName('#__ucm_content'))
			->where($db->quoteName('core_type_alias') . ' = ' . $db->quote($context))
			->where($db->quoteName('core_content_item_id') . ' IN (' . $pksImploded = implode(',', $pks) . ')');
		$db->setQuery($query);
		$ccIds = $db->loadColumn();

		$cctable = new CoreContent($db);
		$cctable->publish($ccIds, $value);

		// Check if this function is enabled.
		if (!$this->params->def('email_new_state', 0) || $context != 'com_content.article')
		{
			return true;
		}

		$query = $db->getQuery(true)
			->select($db->quoteName('id'))
			->from($db->quoteName('#__users'))
			->where($db->quoteName('sendEmail') . ' = 1')
			->where($db->quoteName('block') . ' = 0');

		$users = (array) $db->setQuery($query)->loadColumn();

		if (empty($users))
		{
			return true;
		}

		$user = JFactory::getUser();

		// Messaging for changed items
		$default_language = JComponentHelper::getParams('com_languages')->get('administrator');
		$debug = JFactory::getConfig()->get('debug_lang');
		$result = true;

		$article = new ArticleTable($db);

		foreach ($pks as $pk)
		{
			if (!$article->load($pk))
			{
				continue;
			}

			$assoc = WorkflowHelper::getAssociatedEntry($pk);

			// Load new transitions
			$query = $db->getQuery(true)
				->select($db->qn(['t.id']))
				->from($db->qn('#__workflow_transitions', 't'))
				->from($db->qn('#__workflow_states', 's'))
				->where($db->qn('t.from_state_id') . ' = ' . (int) $assoc->state_id)
				->where($db->qn('t.to_state_id') . ' = ' . $db->qn('s.id'))
				->where($db->qn('t.published') . '= 1')
				->where($db->qn('s.published') . '= 1')
				->order($db->qn('t.ordering'));

			$transitions = $db->setQuery($query)->loadObjectList();

			foreach ($users as $user_id)
			{
				if ($user_id != $user->id)
				{
					// Check if the user has available transitions
					$items = array_filter(
						$transitions,
						function ($item) use ($user)
						{
							return $user->authorise('core.execute.transition', 'com_content.transition.' . $item->id);
						}
					);

					if (!count($items))
					{
						continue;
					}

					// Load language for messaging
					$receiver = JUser::getInstance($user_id);
					$lang = JLanguage::getInstance($receiver->getParam('admin_language', $default_language), $debug);
					$lang->load('plg_content_joomla');

					$message = array(
						'user_id_to' => $user_id,
						'subject' => $lang->_('PLG_CONTENT_JOOMLA_ON_STATE_CHANGE_SUBJECT'),
						'message' => sprintf($lang->_('PLG_CONTENT_JOOMLA_ON_STATE_CHANGE_MSG'), $user->name, $article->title)
					);

					$model_message = new MessageModel;
					$result = $model_message->save($message);
				}
			}
		}

		return true;
	}
}
