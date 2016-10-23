<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Content.joomla
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Example Content Plugin
 *
 * @since  1.6
 */
class PlgContentJoomla extends JPlugin
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
	 * @return  boolean   true if function not enabled, is in front-end or is new. Else true or
	 *                    false depending on success of save function.
	 *
	 * @since   1.6
	 */
	public function onContentAfterSave($context, $article, $isNew)
	{
	        //First check for article/tag mapping.
		$this->setArticleTagOrdering($context, $article, $isNew);

		// Check we are handling the frontend edit form.
		if ($context != 'com_content.form')
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

		$db = JFactory::getDbo();
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

		$user = JFactory::getUser();

		// Messaging for new items
		JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_messages/models', 'MessagesModel');
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_messages/tables');

		$default_language = JComponentHelper::getParams('com_languages')->get('administrator');
		$debug = JFactory::getConfig()->get('debug_lang');

		foreach ($users as $user_id)
		{
			if ($user_id != $user->id)
			{
				// Load language for messaging
				$receiver = JUser::getInstance($user_id);
				$lang = JLanguage::getInstance($receiver->getParam('admin_language', $default_language), $debug);
				$lang->load('com_content');
				$message = array(
					'user_id_to' => $user_id,
					'subject' => $lang->_('COM_CONTENT_NEW_ARTICLE'),
					'message' => sprintf($lang->_('COM_CONTENT_ON_NEW_CONTENT'), $user->get('name'), $article->title)
				);
				$model_message = JModelLegacy::getInstance('Message', 'MessagesModel');
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
		if ($context != 'com_categories.category')
		{
			return true;
		}

		// Check if this function is enabled.
		if (!$this->params->def('check_categories', 1))
		{
			return true;
		}

		$extension = JFactory::getApplication()->input->getString('extension');

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
					$msg = JText::sprintf('COM_CATEGORIES_DELETE_NOT_ALLOWED', $data->get('title'))
						. JText::plural('COM_CATEGORIES_N_ITEMS_ASSIGNED', $count);
					JError::raiseWarning(403, $msg);
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
						$msg = JText::sprintf('COM_CATEGORIES_DELETE_NOT_ALLOWED', $data->get('title'))
							. JText::plural('COM_CATEGORIES_HAS_SUBCATEGORY_ITEMS', $count);
						JError::raiseWarning(403, $msg);
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
		$db = JFactory::getDbo();
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
			JError::raiseWarning(500, $e->getMessage());

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
		$db = JFactory::getDbo();

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
				JError::raiseWarning(500, $e->getMessage());

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
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('core_content_id'))
			->from($db->quoteName('#__ucm_content'))
			->where($db->quoteName('core_type_alias') . ' = ' . $db->quote($context))
			->where($db->quoteName('core_content_item_id') . ' IN (' . $pksImploded = implode(',', $pks) . ')');
		$db->setQuery($query);
		$ccIds = $db->loadColumn();

		$cctable = new JTableCorecontent($db);
		$cctable->publish($ccIds, $value);

		return true;
	}


	/**
	 * Create (or update) a row whenever an article is tagged.
	 * The article/tag mapping allows to order the articles against a given tag. 
	 *
	 * @param   string   $context  The context of the content passed to the plugin (added in 1.6)
	 * @param   object   $article  A JTableContent object
	 * @param   boolean  $isNew    If the content is just about to be created
	 *
	 * @return  void
	 *
	 */
	private function setArticleTagOrdering($context, $article, $isNew)
	{
	  //Filter the sent event.
	  if($context == 'com_content.article' || $context == 'com_content.form') { 
	    //Get the jform data.
	    $jform = JFactory::getApplication()->input->post->get('jform', array(), 'array');

	    // Create a new query object.
	    $db = JFactory::getDbo();
	    $query = $db->getQuery(true);

	    //Check we have tags before treating data.
	    if(isset($jform['tags'])) {
	      //Retrieve all the rows matching the item id.
	      $query->select('article_id, tag_id, IFNULL(ordering, "NULL") AS ordering')
		    ->from('#__content_tag_map')
		    ->where('article_id='.(int)$article->id);
	      $db->setQuery($query);
	      $tags = $db->loadObjectList();

	      $values = array();
	      foreach($jform['tags'] as $tagId) {
		$newTag = true; 
		//In order to preserve the ordering of the old tags we check if 
		//they match those newly selected.
		foreach($tags as $tag) {
		  if($tag->tag_id == $tagId) {
		    $values[] = $tag->article_id.','.$tag->tag_id.','.$tag->ordering;
		    $newTag = false; 
		    break;
		  }
		}

		if($newTag) {
		  $values[] = $article->id.','.$tagId.',NULL';
		}
	      }

	      //Delete all the rows matching the item id.
	      $query->clear();
	      $query->delete('#__content_tag_map')
		    ->where('article_id='.(int)$article->id);
	      $db->setQuery($query);
	      $db->query();

	      $columns = array('article_id', 'tag_id', 'ordering');
	      //Insert a new row for each tag linked to the item.
	      $query->clear();
	      $query->insert('#__content_tag_map')
		    ->columns($columns)
		    ->values($values);
	      $db->setQuery($query);
	      $db->query();
	    }
	    else { //No tags selected or tags removed.
	      //Delete all the rows matching the item id.
	      $query->delete('#__content_tag_map')
		    ->where('article_id='.(int)$article->id);
	      $db->setQuery($query);
	      $db->query();
	    }
	  }
	}


	/**
	 * This is an event that is called right after the content is deleted.
	 *
	 * @param   string  $context  The context for the content passed to the plugin.
	 * @param   object  $data     The data relating to the content that was deleted.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function onContentAfterDelete($context, $data)
	{
	  //Filter the sent event.
	  if($context == 'com_content.article') {
	    // Create a new query object.
	    $db = JFactory::getDbo();
	    $query = $db->getQuery(true);

	    //Delete all the mapping rows linked to the article id. 
	    $query->delete('#__content_tag_map')
		  ->where('article_id='.(int)$data->id);
	    $db->setQuery($query);
	    $db->query();

	    return true;
	  }
	  else { //Hand over to Joomla.
	    return true;
	  }
	}
}
