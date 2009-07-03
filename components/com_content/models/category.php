<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modelitem');
jimport('joomla.database.query');

/**
 * This models supports retrieving a category, the articles associated with the category,
 * sibling, child and parent categories.
 *
 * @package		Joomla.Site
 * @subpackage	com_content
 * @since		1.5
 */
class ContentModelCategory extends JModelItem
{
	/**
	 * Category items data
	 *
	 * @var array
	 */
	protected $_item = null;

	protected $_articles = null;

	protected $_siblings = null;

	protected $_children = null;

	protected $_parents = null;

	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	 protected $_context = 'com_content.article';

	/**
	 * Method to auto-populate the model state.
	 *
	 * @return	void
	 */
	protected function _populateState()
	{
		$app = &JFactory::getApplication('site');

		// Load state from the request.
		$pk = JRequest::getInt('id');
		$this->setState('category.id', $pk);

		// TODO: Add pagination for children , siblings and articles??

		// Load the parameters.
		$params	= $app->getParams();
		$this->setState('params', $params);

		// TODO: Tune these values based on other permissions.
		$this->setState('filter.published',	1);
		$this->setState('filter.access',	true);
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$context	A prefix for the store id.
	 * @return	string		A store id.
	 */
	protected function _getStoreId($id = '')
	{
		// Compile the store id.
		// TODO: Add uniqueness stuff
		return md5($id);
	}

	/**
	 * Method to get article data.
	 *
	 * @param	integer	The id of the category.
	 *
	 * @return	mixed	Menu item data object on success, false on failure.
	 */
	public function &getItem($pk = null)
	{
		// Initialize variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('category.id');

		if ($this->_item === null) {
			$this->_item = array();
		}

		if (!isset($this->_item[$pk]))
		{
			try
			{
				$query = new JQuery;

				$query->select($this->getState('item.select', 'a.*'));
				$query->from('#__categories AS a');

				$query->where('a.extension = '.$this->_db->quote('com_content'));
				$query->where('a.id = '.(int) $pk);

				// Filter by published state.
				$published = $this->getState('filter.published');
				if (is_numeric($published)) {
					$query->where('a.published = '.(int) $published);
				}

				// Filter by access level.
				if ($access = $this->getState('filter.access'))
				{
					$user	= &JFactory::getUser();
					$groups	= implode(',', $user->authorisedLevels());
					$query->where('a.access IN ('.$groups.')');
				}

				$this->_db->setQuery($query);

				$data = $this->_db->loadObject();

				if ($error = $this->_db->getErrorMsg()) {
					throw new Exception($error);
				}

				if (empty($data)) {
					throw new Exception(JText::_('Content_Error_Category_not_found'));
				}

				// Check for published state if filter set.
				if (is_numeric($published) && $data->published != $published) {
					throw new Exception(JText::_('Content_Error_Category_not_found'));
				}

				// Convert parameter fields to objects.
				$registry = new JRegistry;
				$registry->loadJSON($data->params);
				$data->params = clone $this->getState('params');
				$data->params->merge($registry);

				$registry = new JRegistry;
				$registry->loadJSON($data->metadata);
				$data->metadata = $registry;

				// Compute access permissions.
				if ($access)
				{
					// If the access filter has been set, we already know this user can view.
					// TODO
					$data->params->set('access-view', true);
				}
				else
				{
					// If no access filter is set, the layout takes some responsibility for display of limited information.
					$user	= &JFactory::getUser();
					$groups	= $user->authorisedLevels();

					$data->params->set('access-view', in_array($data->access, $groups) && in_array($data->category_access, $groups));
				}
				// TODO: Type 2 permission checks?

				$this->_item[$pk] = $data;
			}
			catch (Exception $e)
			{
				$this->setError($e->getMessage());
				$this->_item[$pk] = false;
			}
		}

		return $this->_item[$pk];
	}

	/**
	 * Get the articles in the category
	 *
	 * @return	mixed	An array of articles or false if an error occurs.
	 */
	function &getArticles()
	{
		if ($this->_articles === null && $category = &$this->getItem())
		{
			$model = &JModel::getInstance('Articles', 'ContentModel', array('ignore_request' => true));
			$model->setState('params',				JFactory::getApplication()->getParams());
			$model->setState('filter.category_id',	$category->id);
			$model->setState('filter.published',	$this->getState('filter.published'));
			$model->setState('filter.access',		$this->getState('filter.access'));
			// TODO: Set ordering
			// TODO: Set limits

			$this->_articles  = $model->getItems();

			if ($this->_articles === false) {
				$this->setError($model->getError());
			}
		}

		return $this->_articles;
	}

	/**
	 * Get the sibling (adjacent) categories.
	 *
	 * @return	mixed	An array of categories or false if an error occurs.
	 */
	function &getSiblings()
	{
		if ($this->_siblings === null && $category = &$this->getItem())
		{
			$model = &JModel::getInstance('Categories', 'ContentModel', array('ignore_request' => true));
			$model->setState('params',				JFactory::getApplication()->getParams());
			$model->setState('filter.parent_id',	$category->parent_id);
			$model->setState('filter.published',	$this->getState('filter.published'));
			$model->setState('filter.access',		$this->getState('filter.access'));
			// TODO: Set limits

			$this->_siblings  = $model->getItems();

			if ($this->_siblings === false) {
				$this->setError($model->getError());
			}
		}

		return $this->_siblings;
	}

	/**
	 * Get the child categories.
	 *
	 * @param	int		An optional category id. If not supplied, the model state 'category.id' will be used.
	 *
	 * @return	mixed	An array of categories or false if an error occurs.
	 */
	function &getChildren($categoryId = 0)
	{
		// Initialize variables.
		$categoryId = (!empty($categoryId)) ? $categoryId : $this->getState('category.id');

		if ($this->_children === null)
		{
			$model = &JModel::getInstance('Categories', 'ContentModel', array('ignore_request' => true));
			$model->setState('params',				JFactory::getApplication()->getParams());
			$model->setState('filter.parent_id',	$categoryId);
			$model->setState('filter.get_children',	true);
			$model->setState('filter.published',	$this->getState('filter.published'));
			$model->setState('filter.access',		$this->getState('filter.access'));
			// TODO: Set limits

			$this->_children  = $model->getItems();

			if ($this->_children === false) {
				$this->setError($model->getError());
			}
		}

		return $this->_children;
	}

	/**
	 * Get the child categories.
	 *
	 * @param	int		An optional category id. If not supplied, the model state 'category.id' will be used.
	 *
	 * @return	mixed	An array of categories or false if an error occurs.
	 */
	function &getParents($categoryId = 0)
	{
		// Initialize variables.
		$categoryId = (!empty($categoryId)) ? $categoryId : $this->getState('category.id');

		if ($this->_parents === null)
		{
			$model = &JModel::getInstance('Categories', 'ContentModel', array('ignore_request' => true));
			$model->setState('params',				JFactory::getApplication()->getParams());
			$model->setState('list.select',			'a.id, a.title, a.level, a.path AS route');
			$model->setState('filter.parent_id',	$categoryId);
			$model->setState('filter.get_parents',	true);
			$model->setState('filter.published',	$this->getState('filter.published'));
			$model->setState('filter.access',		$this->getState('filter.access'));
			// TODO: Set limits

			$this->_parents  = $model->getItems();

			if ($this->_parents === false) {
				$this->setError($model->getError());
			}
		}

		return $this->_parents;
	}
}
