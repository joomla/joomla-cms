<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

require_once JPATH_COMPONENT_ADMINISTRATOR.'/helpers/content.php';

/**
 * Item Model for an Article.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_content
 * @since		1.6
 */
class ContentModelArticle extends JModelAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_CONTENT';

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param	object	$record	A record object.
	 *
	 * @return	boolean	True if allowed to delete the record. Defaults to the permission set in the component.
	 * @since	1.6
	 */
	protected function canDelete($record)
	{
		if (!empty($record->id)) {
			if ($record->state != -2) {
				return ;
			}
			$user = JFactory::getUser();
			return $user->authorise('core.delete', 'com_content.article.'.(int) $record->id);
		}
	}

	/**
	 * Method to test whether a record can have its state edited.
	 *
	 * @param	object	$record	A record object.
	 *
	 * @return	boolean	True if allowed to change the state of the record. Defaults to the permission set in the component.
	 * @since	1.6
	 */
	protected function canEditState($record)
	{
		$user = JFactory::getUser();

		// Check for existing article.
		if (!empty($record->id)) {
			return $user->authorise('core.edit.state', 'com_content.article.'.(int) $record->id);
		}
		// New article, so check against the category.
		else if (!empty($record->catid)) {
			return $user->authorise('core.edit.state', 'com_content.category.'.(int) $record->catid);
		}
		// Default to component settings if neither article nor category known.
		else {
			return parent::canEditState($record);
		}
	}

	/**
	 * Method to perform batch operations on an item or a set of items.
	 *
	 * @param	array	$commands	An array of commands to perform.
	 * @param	array	$pks		An array of articles ids.
	 *
	 * @return	boolean	Returns true on success, false on failure.
	 * @since	1.6
	 */
	function batch($commands, $pks)
	{
		// Sanitize user ids.
		$pks = array_unique($pks);
		JArrayHelper::toInteger($pks);

		// Remove any values of zero.
		if (array_search(0, $pks, true)) {
			unset($pks[array_search(0, $pks, true)]);
		}

		if (empty($pks)) {
			$this->setError(JText::_('COM_CONTENT_NO_ITEM_SELECTED'));
			return false;
		}

		$done = false;

		if (!empty($commands['assetgroup_id'])) {
			if (!$this->batchAccess($commands['assetgroup_id'], $pks)) {
				return false;
			}

			$done = true;
		}

		if (!empty($commands['category_id'])) {
			$cmd = JArrayHelper::getValue($commands, 'move_copy', 'c');

			if ($cmd == 'c' && !$this->batchCopy($commands['category_id'], $pks)) {
				return false;
			}
			else if ($cmd == 'm' && !$this->batchMove($commands['category_id'], $pks)) {
				return false;
			}
			$done = true;
		}

		if (!$done) {
			$this->setError(JText::_('JGLOBAL_ERROR_INSUFFICIENT_BATCH_INFORMATION'));
			return false;
		}

		return true;
	}

	/**
	 * Batch access level changes for a group of rows.
	 *
	 * @param	int		$value	The new value matching an Asset Group ID.
	 * @param	array	$pks	An array of row IDs.
	 *
	 * @return	booelan	True if successful, false otherwise and internal error is set.
	 * @since	1.6
	 */
	protected function batchAccess($value, $pks)
	{
		// Check that user has edit permission for articles
		$user	= JFactory::getUser();
		if (!$user->authorise('core.edit', 'com_content')) {
			$this->setError(JText::_('JGLOBAL_BATCH_ARTICLE_CANNOT_EDIT'));
			return false;
		}
		$table = $this->getTable();

		foreach ($pks as $pk)
		{
			$table->reset();
			$table->load($pk);
			$table->access = (int) $value;

			if (!$table->store()) {
				$this->setError($table->getError());
				return false;
			}
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Batch copy articles to a new category or current.
	 *
	 * @param	int		$value	The new category.
	 * @param	array	$pks	An array of row IDs.
	 *
	 * @return	boolean	True if successful, false otherwise and internal error is set.
	 * @since	1.6
	 */
	protected function batchCopy($value, $pks)
	{
		
		$categoryId	= (int) $value;

		$table	= $this->getTable();
		$db		= $this->getDbo();

		// Check that the category exists
		if ($categoryId) {
			$categoryTable = JTable::getInstance('Category');
			if (!$categoryTable->load($categoryId)) {
				if ($error = $categoryTable->getError()) {
					// Fatal error
					$this->setError($error);
					return false;
				}
				else {
					$this->setError(JText::_('JGLOBAL_BATCH_MOVE_CATEGORY_NOT_FOUND'));
					return false;
				}
			}
		}

		if (empty($categoryId)) {
			$this->setError(JText::_('JGLOBAL_BATCH_MOVE_CATEGORY_NOT_FOUND'));
			return false;
		}
		
		// Check that user has create permission for categories
		$user = JFactory::getUser();
		if (!$user->authorise('core.create', 'com_content')) {
			$this->setError(JText::_('JGLOBAL_BATCH_ARTICLE_CANNOT_CREATE'));
			return false;
		}

		// Parent exists so we let's proceed
		while (!empty($pks))
		{
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
				else {
					// Not fatal error
					$this->setError(JText::sprintf('JGLOBAL_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			// Reset the id because we are making a copy.
			$table->id			= 0;

			// New category ID
			$table->catid	= $categoryId;

			// TODO: Deal with ordering?
			//$table->ordering	= 1;
			
			// Alter the title & alias
			list($title, $alias) = $this->generateNewTitle($categoryId, $table->alias, $table->title);
			$table->title   = $title;
			$table->alias   = $alias;

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
		}

		// Clean the cache
		$this->cleanCache();
				
		return true;
	}

	/**
	 * Batch move articles to a new category
	 *
	 * @param	int		$value	The new category ID.
	 * @param	array	$pks	An array of row IDs.
	 *
	 * @return	booelan	True if successful, false otherwise and internal error is set.
	 * @since	1.6
	 */
	protected function batchMove($value, $pks)
	{
		
		$categoryId	= (int) $value;

		$table	= $this->getTable();
		$db		= $this->getDbo();

		// Check that the category exists
		if ($categoryId) {
			$categoryTable = JTable::getInstance('Category');
			if (!$categoryTable->load($categoryId)) {
				if ($error = $categoryTable->getError()) {
					// Fatal error
					$this->setError($error);
					return false;
				}
				else {
					$this->setError(JText::_('JGLOBAL_BATCH_MOVE_CATEGORY_NOT_FOUND'));
					return false;
				}
			}
		}

		if (empty($categoryId)) {
			$this->setError(JText::_('JGLOBAL_BATCH_MOVE_CATEGORY_NOT_FOUND'));
			return false;
		}
		
		// Check that user has create and edit permission for articles
		$user	= JFactory::getUser();
		if (!$user->authorise('core.create', 'com_content')) {
			$this->setError(JText::_('JGLOBAL_BATCH_ARTICLE_CANNOT_CREATE'));
			return false;
		}

		if (!$user->authorise('core.edit', 'com_content')) {
			$this->setError(JText::_('JGLOBAL_BATCH_ARTICLE_CANNOT_EDIT'));
			return false;
		}

		// Parent exists so we let's proceed
		foreach ($pks as $pk)
		{
			// Check that the row actually exists
			if (!$table->load($pk)) {
				if ($error = $table->getError()) {
					// Fatal error
					$this->setError($error);
					return false;
				}
				else {
					// Not fatal error
					$this->setError(JText::sprintf('JGLOBAL_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			// Set the new Category Id
			$table->catid = $categoryId;

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
		}

		// Clean the cache
		$this->cleanCache();
				
		return true;
	}
	
	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param	JTable	A JTable object.
	 *
	 * @return	void
	 * @since	1.6
	 */
	protected function prepareTable(&$table)
	{
		// Set the publish date to now
		if($table->state == 1 && intval($table->publish_up) == 0) {
			$table->publish_up = JFactory::getDate()->toMySQL();
		}

		// Increment the content version number.
		$table->version++;

		// Reorder the articles within the category so the new article is first
		if (empty($table->id)) {
			$table->reorder('catid = '.(int) $table->catid.' AND state >= 0');
		}
	}

	/**
	 * Returns a Table object, always creating it.
	 *
	 * @param	type	The table type to instantiate
	 * @param	string	A prefix for the table class name. Optional.
	 * @param	array	Configuration array for model. Optional.
	 *
	 * @return	JTable	A database object
	*/
	public function getTable($type = 'Content', $prefix = 'JTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk)) {
			// Convert the params field to an array.
			$registry = new JRegistry;
			$registry->loadJSON($item->attribs);
			$item->attribs = $registry->toArray();

			// Convert the params field to an array.
			$registry = new JRegistry;
			$registry->loadJSON($item->metadata);
			$item->metadata = $registry->toArray();

			$item->articletext = trim($item->fulltext) != '' ? $item->introtext . "<hr id=\"system-readmore\" />" . $item->fulltext : $item->introtext;
		}

		return $item;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 *
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_content.article', 'article', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		// Determine correct permissions to check.
		if ($id = (int) $this->getState('article.id')) {
			// Existing record. Can only edit in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.edit');
			// Existing record. Can only edit own articles in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.edit.own');
		}
		else {
			// New record. Can only create in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.create');
		}

		// Modify the form based on Edit State access controls.
		if (!$this->canEditState((object) $data)) {
			// Disable fields for display.
			$form->setFieldAttribute('featured', 'disabled', 'true');
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('publish_up', 'disabled', 'true');
			$form->setFieldAttribute('publish_down', 'disabled', 'true');
			$form->setFieldAttribute('state', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is an article you can edit.
			$form->setFieldAttribute('featured', 'filter', 'unset');
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('publish_up', 'filter', 'unset');
			$form->setFieldAttribute('publish_down', 'filter', 'unset');
			$form->setFieldAttribute('state', 'filter', 'unset');
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_content.edit.article.data', array());

		if (empty($data)) {
			$data = $this->getItem();

			// Prime some default values.
			if ($this->getState('article.id') == 0) {
				$app = JFactory::getApplication();
				$data->set('catid', JRequest::getInt('catid', $app->getUserState('com_content.articles.filter.category_id')));
			}
		}

		return $data;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 *
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function save($data)
	{
		if (parent::save($data)) {
			if (isset($data['featured'])) {
				$this->featured($this->getState($this->getName().'.id'), $data['featured']);
			}
			return true;
		}

		return false;
	}

	/**
	 * Method to toggle the featured setting of articles.
	 *
	 * @param	array	The ids of the items to toggle.
	 * @param	int		The value to toggle to.
	 *
	 * @return	boolean	True on success.
	 */
	public function featured($pks, $value = 0)
	{
		// Sanitize the ids.
		$pks = (array) $pks;
		JArrayHelper::toInteger($pks);

		if (empty($pks)) {
			$this->setError(JText::_('COM_CONTENT_NO_ITEM_SELECTED'));
			return false;
		}

		$table = $this->getTable('Featured', 'ContentTable');

		try {
			$db = $this->getDbo();

			$db->setQuery(
				'UPDATE #__content AS a' .
				' SET a.featured = '.(int) $value.
				' WHERE a.id IN ('.implode(',', $pks).')'
			);
			if (!$db->query()) {
				throw new Exception($db->getErrorMsg());
			}

			if ((int)$value == 0) {
				// Adjust the mapping table.
				// Clear the existing features settings.
				$db->setQuery(
					'DELETE FROM #__content_frontpage' .
					' WHERE content_id IN ('.implode(',', $pks).')'
				);
				if (!$db->query()) {
					throw new Exception($db->getErrorMsg());
				}
			} else {
				// first, we find out which of our new featured articles are already featured.
				$query = $db->getQuery(true);
				$query->select('f.content_id');
				$query->from('#__content_frontpage AS f');
				$query->where('content_id IN ('.implode(',', $pks).')');
				//echo $query;
				$db->setQuery($query);

				if (!is_array($old_featured = $db->loadResultArray())) {
					throw new Exception($db->getErrorMsg());
				}
				
				// we diff the arrays to get a list of the articles that are newly featured
				$new_featured = array_diff($pks, $old_featured);
				
				// Featuring.
				$tuples = array();
				foreach ($new_featured as $pk) {
					$tuples[] = '('.$pk.', 0)';
				}
				if (count($tuples)) {
					$db->setQuery(
						'INSERT INTO #__content_frontpage (`content_id`, `ordering`)' .
						' VALUES '.implode(',', $tuples)
					);
					if (!$db->query()) {
						$this->setError($db->getErrorMsg());
						return false;
					}
				}
			}

		} catch (Exception $e) {
			$this->setError($e->getMessage());
			return false;
		}

		$table->reorder();

		$this->cleanCache();

		return true;
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param	object	A record object.
	 *
	 * @return	array	An array of conditions to add to add to ordering queries.
	 * @since	1.6
	 */
	protected function getReorderConditions($table)
	{
		$condition = array();
		$condition[] = 'catid = '.(int) $table->catid;
		return $condition;
	}

	/**
	 * Method to change the title & alias.
	 *
	 * @param	int     The value of the article category.
	 * @param   sting   The value of the article Alias.
	 * @param   sting   The value of the article Title.
	 * @return	array   Contains title and alias.
	 * @since	1.6
	 */
	function generateNewTitle($category_id, $alias, $title)
	{
		// Alter the title & alias
		$articleTable = JTable::getInstance('Content');
		while($articleTable->load(array('alias'=>$alias, 'catid'=>$category_id))){
			$m = null;
			if (preg_match('#-(\d+)$#', $alias, $m)) {
				$alias = preg_replace('#-(\d+)$#', '-'.($m[1] + 1).'', $alias);
			} else {
				$alias .= '-2';
			}
			if (preg_match('#\((\d+)\)$#', $title, $m)) {
				$title = preg_replace('#\(\d+\)$#', '('.($m[1] + 1).')', $title);
			} else {
				$title .= ' (2)';
			}
		}

		return array($title ,$alias);
	}

	/**
	 * Custom clean the cache of com_content and content modules
	 *
	 * @since	1.6
	 */
	protected function cleanCache()
	{
		parent::cleanCache('com_content');
		parent::cleanCache('mod_articles_archive');
		parent::cleanCache('mod_articles_categories');
		parent::cleanCache('mod_articles_category');
		parent::cleanCache('mod_articles_latest');
		parent::cleanCache('mod_articles_news');
		parent::cleanCache('mod_articles_popular');
	}	
}
