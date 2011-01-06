<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

// Base this model on the backend version.
require_once JPATH_ADMINISTRATOR.'/components/com_content/models/article.php';

/**
 * Content Component Article Model
 *
 * @package		Joomla.Site
 * @subpackage	com_content
 * @since 1.5
 */
class ContentModelForm extends ContentModelArticle
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		$app = JFactory::getApplication();

		// Load state from the request.
		$pk = JRequest::getInt('a_id');
		$this->setState('article.id', $pk);

		$this->setState('article.catid', JRequest::getInt('catid'));

		// Load the parameters.
		$params	= $app->getParams();
		$this->setState('params', $params);

		$this->setState('layout', JRequest::getCmd('layout'));
	}

	/**
	 * Method to get article data.
	 *
	 * @param	integer	The id of the article.
	 *
	 * @return	mixed	Content item data object on success, false on failure.
	 */
	public function getItem($itemId = null)
	{
		// Initialise variables.
		$itemId = (int) (!empty($itemId)) ? $itemId : $this->getState('article.id');

		// Get a row instance.
		$table = $this->getTable();

		// Attempt to load the row.
		$return = $table->load($itemId);

		// Check for a table object error.
		if ($return === false && $table->getError()) {
			$this->setError($table->getError());
			return false;
		}

		$value = JArrayHelper::toObject($table->getProperties(1), 'JObject');

		// Convert attrib field to Registry.
		$value->params = new JRegistry;
		$value->params->loadJSON($value->attribs);

		// Compute selected asset permissions.
		$user	= JFactory::getUser();
		$userId	= $user->get('id');
		$asset	= 'com_content.article.'.$value->id;

		// Check general edit permission first.
		if ($user->authorise('core.edit', $asset)) {
			$value->params->set('access-edit', true);
		}
		// Now check if edit.own is available.
		else if (!empty($userId) && $user->authorise('core.edit.own', $asset)) {
			// Check for a valid user and that they are the owner.
			if ($userId == $value->created_by) {
				$value->params->set('access-edit', true);
			}
		}

		// Check edit state permission.
		if ($itemId) {
			// Existing item
			$value->params->set('access-change', $user->authorise('core.edit.state', $asset));
		}
		else {
			// New item.
			$catId = (int) $this->getState('article.catid');
			if ($catId) {
				$value->params->set('access-change', $user->authorise('core.edit.state', 'com_content.category.'.$catId));
			}
			else {
				$value->params->set('access-change', $user->authorise('core.edit.state', 'com_content'));
			}
		}

		$value->articletext = $value->introtext;
		if (!empty($value->fulltext)) {
			$value->articletext .= '<hr id="system-readmore" />'.$value->fulltext;
		}

		return $value;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function __save($data)
	{
		// Initialise variables
		$dispatcher = JDispatcher::getInstance();
		$table		= $this->getTable();
		$form		= $this->getForm($data, false);
		$pk			= (!empty($data['id'])) ? $data['id'] : (int) $this->getState('article.id');
		$isNew		= true;

		if (!$form) {
			JError::raiseError(500, $this->getError());
			return false;
		}

		// Validate the posted data.
		$data	= $this->validate($form, $data);
		if ($data === false) {
			return false;
		}

		// Load the row if saving an existing item.
		if ($pk > 0) {
			$table->load($pk);
			$isNew = false;
		}
		if ($isNew){
			// Save the default (empty) rules for the article
			$actions = JAccess::getActions('com_content', 'article');
			$actionArray = array();
			foreach ($actions as $action) {
				$actionArray[$action->name] = array();
			}
			$data['rules'] = $actionArray;
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

		// Set the publish date to now
		if ($table->state == 1 && intval($table->publish_up) == 0) {
			$table->publish_up = JFactory::getDate()->toMySQL();
		}

		// Increment the content version number
		$table->version++;

		// Reorder the articles within the category so the new article is first
		if (empty($table->id)) {
			$table->reorder('catid = '.(int) $table->catid.' AND state >= 0');
		}

		// Include the content plugins for the onSave events.
		JPluginHelper::importPlugin('content');

		$result = $dispatcher->trigger('onContentBeforeSave', array('com_content.article', &$table, $isNew));

		if (in_array(false, $result, true)) {
			JError::raiseError(500, $table->getError());
			return false;
		}

		// Store the data.
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		// Adjust the mapping table.
		// Clear the existing features settings.
		$this->_db->setQuery(
			'DELETE FROM #__content_frontpage' .
			' WHERE content_id = '.$table->id
		);

		if (!$this->_db->query()) {
			throw new Exception($this->_db->getErrorMsg());
		}

		if (isset($data['featured']) && $data['featured'] == 1) {
			$frontpage = $this->getTable('Featured', 'ContentTable');

			try
			{
				$this->_db->setQuery(
					'UPDATE #__content AS a' .
					' SET a.featured = 1'.
					' WHERE a.id = '.$table->id
				);
				if (!$this->_db->query()) {
					throw new Exception($this->_db->getErrorMsg());
				}

				// Featuring.
				$this->_db->setQuery(
					'INSERT INTO #__content_frontpage (`content_id`, `ordering`)' .
					' VALUES ('.$table->id.',1)'
				);

				if (!$this->_db->query()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());
				return false;
			}

			$frontpage->reorder();
		}

		// Clean the cache.
		$cache = JFactory::getCache('com_content');
		$cache->clean();

		$dispatcher->trigger('onContentAfterSave', array('com_content.article', &$table, $isNew));

		$this->setState('article.id', $table->id);

		return true;
	}
}