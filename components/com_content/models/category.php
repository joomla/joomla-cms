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

jimport('joomla.application.component.modelitem');

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

	protected $_parent = null;

	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	protected $_context = 'com_content.category';

	/**
	 * The category that applies.
	 *
	 * @access	protected
	 * @var		object
	 */
	protected $_category = null;

	/**
	 * The list of other newfeed categories.
	 *
	 * @access	protected
	 * @var		array
	 */
	protected $_categories = null;

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * return	void
	 * @since	1.6
	 */
	protected function populateState()
	{
		// Initiliase variables.
		$app	= JFactory::getApplication('site');
		$pk		= JRequest::getInt('id');

		$this->setState('category.id', $pk);

		// Load the parameters.
		$params = clone $app->getParams();
		$layout = JRequest::getCmd('layout');
		if ($layout) {
			$params->set('category_layout', $layout);
		}
		$this->setState('params', $params);

		$user		= JFactory::getUser();

		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$groups	= implode(',', $user->authorisedLevels());

		if ((!$user->authorise('core.edit.state', 'com_content')) &&  (!$user->authorise('core.edit', 'com_content'))){
			// limit to published for people who can't edit or edit.state.
			$this->setState('filter.published', 1);
			// Filter by start and end dates.
			$nullDate = $db->Quote($db->getNullDate());
			$nowDate = $db->Quote(JFactory::getDate()->toMySQL());

			$query->where('(a.publish_up = ' . $nullDate . ' OR a.publish_up <= ' . $nowDate . ')');
			$query->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');
		}

		// Optional filter text
		$this->setState('list.filter', JRequest::getString('filter-search'));

		// filter.order
		$itemid = JRequest::getInt('id', 0) . ':' . JRequest::getInt('Itemid', 0);
		$this->setState('list.ordering', $app->getUserStateFromRequest('com_content.category.list.' . $itemid . '.filter_order', 'filter_order', '',
			'string'));
		$this->setState('list.direction', $app->getUserStateFromRequest('com_content.category.list.' . $itemid . '.filter_order_Dir',
			'filter_order_Dir', '', 'cmd'));

		$limitstart = JRequest::getVar('limitstart', 0, '', 'int');
		$this->setState('list.start', $limitstart);

		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'));
		$this->setState('list.limit', $limit);

		// set the depth of the category query based on parameter
		$showSubcategories = $params->get('show_subcategory_content', '0');

		if ($showSubcategories) {
			$this->setState('filter.max_category_levels', $params->get('max_levels', '1'));
		}

		if ($showSubcategories == 'all_articles') {
			$this->setState('filter.subcategories', true);
		}

		$this->setState('filter.language',$app->getLanguageFilter());
	}

 	/**
	 * Get the parameters for displaying the category
	 *
	 * @return	JRegistry	The Category Params.
	 */
	public function getParams()
	{
		if (!isset($this->params))
		{
			// Get application and menu parameters
			parent::getParams();

			// Compute category
			$category = $this->getCategory();
			
			// Initiate categories array
			$categories = array();

			// Compute the whole branch
			while ($category){
				$categories[] = $category;
				$category = $category->getParent();
			}

			// Merge the params starting from the root until the current category
			$categories = array_reverse($categories);
			foreach ($categories as $category) {
				$this->params->merge(new JRegistry($category->params));
			}
		}
		return $this->params;
	}

	/**
	 * Get the articles in the category
	 *
	 * @return	mixed	An array of articles or false if an error occurs.
	 * @since	1.5
	 */
	function getItems()
	{

		if ($this->_articles === null && $category = $this->getCategory()) {
			$params = $this->getParams();
			$limit = max($this->getState('list.limit'), $params->get('num_leading_articles') + $params->get('num_intro_articles') + $params->get('num_links'));
			$model = JModel::getInstance('Articles', 'ContentModel', array('ignore_request' => true));
			$model->setState('params', JFactory::getApplication()->getParams());
			$model->setState('filter.category_id', $category->id);
			$model->setState('filter.published', $this->getState('filter.published'));
			$model->setState('filter.access', $params->get('show_noauth')=='0');
			$model->setState('filter.language', $this->getState('filter.language'));
			$model->setState('list.ordering', $this->_buildContentOrderBy());
			$model->setState('list.start', $this->getState('list.start'));
			$model->setState('list.limit', $limit);
			$model->setState('list.direction', $this->getState('list.direction'));
			$model->setState('list.filter', $this->getState('list.filter'));
			// filter.subcategories indicates whether to include articles from subcategories in the list or blog
			$model->setState('filter.subcategories', $this->getState('filter.subcategories'));
			$model->setState('filter.max_category_levels', $this->setState('filter.max_category_levels'));
//			$model->setState('list.links', $params->get('num_links'));

			if ($limit >= 0) {
				$this->_articles = $model->getItems();

				if ($this->_articles === false) {
					$this->setError($model->getError());
				}
			}
			else {
				$this->_articles=array();
			}

			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination($model->getTotal(), (int) $this->getState('list.start'), (int) $this->getState('list.limit')); //$model->getPagination();
		}

		return $this->_articles;
	}

	/**
	 * Build the orderby for the query
	 *
	 * @return	string	$orderby portion of query
	 * @since	1.5
	 */
	protected function _buildContentOrderBy()
	{
		$app	= JFactory::getApplication('site');
		$params	= $this->getParams();
		$itemid	= JRequest::getInt('id', 0) . ':' . JRequest::getInt('Itemid', 0);
		$filter_order = $app->getUserStateFromRequest('com_content.category.list.' . $itemid . '.filter_order', 'filter_order', '', 'string');
		$filter_order_Dir = $app->getUserStateFromRequest('com_content.category.list.' . $itemid . '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');
		$orderby = ' ';

		if ($filter_order && $filter_order_Dir) {
			$orderby .= $filter_order . ' ' . $filter_order_Dir . ', ';
		}

		$articleOrderby		= $params->get('orderby_sec', 'rdate');
		$articleOrderDate	= $params->get('order_date');
		$categoryOrderby	= $params->def('orderby_pri', '');
		$secondary			= ContentHelperQuery::orderbySecondary($articleOrderby, $articleOrderDate) . ', ';
		$primary			= ContentHelperQuery::orderbyPrimary($categoryOrderby);

		$orderby .= $primary . ' ' . $secondary . ' a.created ';

		return $orderby;
	}

	public function getPagination()
	{
		if (!isset($this->_pagination)) {
			$this->_pagination = false;
			$this->getItems();
		}
		return $this->_pagination;
	}

	/**
	 * Method to get category data for the current category
	 *
	 * @param	int		An optional ID
	 *
	 * @return	object
	 * @since	1.5
	 */
	public function getCategory()
	{
		if (!is_object($this->_item)) {
/*			if( isset( $this->state->params ) ) {
				$params = $this->state->params;
				$options = array();
				$options['countItems'] = $params->get('show_cat_num_articles', 1);
			}
			else {
				$options['countItems'] = 0;
			}
*/
			$categories = JCategories::getInstance('Content', array('countItems'=>'1'));
			$this->_item = $categories->get($this->getState('category.id', 'root'));

			// Compute selected asset permissions.
			if (is_object($this->_item)) {
				$user	= JFactory::getUser();
				$userId	= $user->get('id');
				$asset	= 'com_content.category.'.$this->_item->id;

				// Check general create permission.
				if ($user->authorise('core.create', $asset)) {
					$this->_item->getParams()->set('access-create', true);
				}

				// TODO: Why aren't we lazy loading the children and siblings?
				$this->_children = $this->_item->getChildren();
				$this->_parent = false;

				if ($this->_item->getParent()) {
					$this->_parent = $this->_item->getParent();
				}

				$this->_rightsibling = $this->_item->getSibling();
				$this->_leftsibling = $this->_item->getSibling(false);
			}
			else {
				$this->_children = false;
				$this->_parent = false;
			}
		}

		return $this->_item;
	}

	/**
	 * Get the parent categorie.
	 *
	 * @param	int		An optional category id. If not supplied, the model state 'category.id' will be used.
	 *
	 * @return	mixed	An array of categories or false if an error occurs.
	 * @since	1.6
	 */
	public function getParent()
	{
		if (!is_object($this->_item)) {
			$this->getCategory();
		}

		return $this->_parent;
	}

	/**
	 * Get the left sibling (adjacent) categories.
	 *
	 * @return	mixed	An array of categories or false if an error occurs.
	 * @since	1.6
	 */
	function &getLeftSibling()
	{
		if (!is_object($this->_item)) {
			$this->getCategory();
		}

		return $this->_leftsibling;
	}

	/**
	 * Get the right sibling (adjacent) categories.
	 *
	 * @return	mixed	An array of categories or false if an error occurs.
	 * @since	1.6
	 */
	function &getRightSibling()
	{
		if (!is_object($this->_item)) {
			$this->getCategory();
		}

		return $this->_rightsibling;
	}

	/**
	 * Get the child categories.
	 *
	 * @param	int		An optional category id. If not supplied, the model state 'category.id' will be used.
	 *
	 * @return	mixed	An array of categories or false if an error occurs.
	 * @since	1.6
	 */
	function &getChildren()
	{
		if (!is_object($this->_item)) {
			$this->getCategory();
		}

		return $this->_children;
	}
}
