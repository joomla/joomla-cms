<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumModelCategory extends JModelList
{

	protected $_item = null;

	protected $_topics = null;

	protected $_siblings = null;

	protected $_children = null;

	protected $_parent = null;

	protected $_context = 'com_cjforum.category';

	protected $_category = null;

	protected $_categories = null;

	public function __construct ($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
					'id', 'a.id',
					'title', 'a.title',
					'alias', 'a.alias',
					'checked_out', 'a.checked_out',
					'checked_out_time', 'a.checked_out_time',
					'catid', 'a.catid',
					'category_title',
					'state', 'a.state',
					'access', 'a.access',
					'access_level',
					'created', 'a.created',
					'created_by', 'a.created_by',
					'modified', 'a.modified',
					'ordering', 'a.ordering',
					'featured', 'a.featured',
					'language', 'a.language',
					'hits', 'a.hits',
					'publish_up', 'a.publish_up',
					'publish_down', 'a.publish_down',
					'author', 'a.author'
			);
		}
		
		parent::__construct($config);
	}

	protected function populateState ($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication('site');
		$pk = $app->input->getInt('id');
		$this->setState('category.id', $pk);
		
		// Load the parameters. Merge Global and Menu Item params into new
		// object
		$params = $app->getParams();
		$menuParams = new JRegistry();
		
		if ($menu = $app->getMenu()->getActive())
		{
			$menuParams->loadString($menu->params);
		}
		
		$mergedParams = clone $menuParams;
		$mergedParams->merge($params);
		
		$this->setState('params', $mergedParams);
		$user = JFactory::getUser();
		
		if ((! $user->authorise('core.edit.state', 'com_cjforum')) && (! $user->authorise('core.edit', 'com_cjforum')))
		{
			// limit to published for people who can't edit or edit.state.
			$this->setState('filter.published', 1);
		}
		else
		{
			$this->setState('filter.published', array(0, 1,	2));
		}
		
		// process show_noauth parameter
		if (! $params->get('show_noauth'))
		{
			$this->setState('filter.access', true);
		}
		else
		{
			$this->setState('filter.access', false);
		}
		
		// Optional filter text
		$this->setState('list.filter', $app->input->getString('filter-search'));
		
		// filter.order
		$itemid = $app->input->get('id', 0, 'int') . ':' . $app->input->get('Itemid', 0, 'int');
		$orderCol = $app->getUserStateFromRequest('com_cjforum.category.list.' . $itemid . '.filter_order', 'filter_order', '', 'string');
		if (! in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'a.created';
		}
		$this->setState('list.ordering', $orderCol);
		
		$listOrder = $app->getUserStateFromRequest('com_cjforum.category.list.' . $itemid . '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');
		if (! in_array(strtoupper($listOrder), array('ASC', 'DESC', '')))
		{
			$listOrder = 'DESC';
		}
		$this->setState('list.direction', $listOrder);
		
		$this->setState('list.start', $app->input->get('limitstart', 0, 'uint'));
		
		// set limit for query. If list, use parameter. If blog, add blog
		// parameters for limit.
		if (($app->input->get('layout') == 'blog') || $params->get('layout_type') == 'blog')
		{
			$limit = $params->get('num_leading_topics') + $params->get('num_intro_topics') + $params->get('num_links');
			$this->setState('list.links', $params->get('num_links'));
		}
		else
		{
			$limit = $app->getUserStateFromRequest('com_cjforum.category.list.' . $itemid . '.limit', 'limit', $app->getCfg('list_limit', 20), 'uint');
		}
		
		$this->setState('list.limit', $limit);
		
		// set the depth of the category query based on parameter
		$showSubcategories = $params->get('show_subcategory_content', '0');
		
		if ($showSubcategories)
		{
			$this->setState('filter.max_category_levels', $params->get('show_subcategory_content', '1'));
			$this->setState('filter.subcategories', true);
		}
		
		$this->setState('filter.language', JLanguageMultilang::isEnabled());
		
		$this->setState('layout', $app->input->getString('layout'));
	}

	function getItems ()
	{
		$limit = $this->getState('list.limit');
		
		if ($this->_topics === null && $category = $this->getCategory())
		{
			$model = JModelLegacy::getInstance('Topics', 'CjForumModel', array('ignore_request' => true));
			$model->setState('params', JFactory::getApplication()->getParams());
			$model->setState('filter.category_id', $category->id);
			$model->setState('filter.published', $this->getState('filter.published'));
			$model->setState('filter.access', $this->getState('filter.access'));
			$model->setState('filter.language', $this->getState('filter.language'));
			$model->setState('list.ordering', $this->_buildContentOrderBy());
			$model->setState('list.start', $this->getState('list.start'));
			$model->setState('list.limit', $limit);
			$model->setState('list.direction', $this->getState('list.direction'));
			$model->setState('list.filter', $this->getState('list.filter'));
			// filter.subcategories indicates whether to include topics from
			// subcategories in the list or blog
			$model->setState('filter.subcategories', $this->getState('filter.subcategories'));
			$model->setState('filter.max_category_levels', $this->setState('filter.max_category_levels'));
			$model->setState('list.links', $this->getState('list.links'));
			
			if ($limit >= 0)
			{
				$this->_topics = $model->getItems();
				
				if ($this->_topics === false)
				{
					$this->setError($model->getError());
				}
			}
			else
			{
				$this->_topics = array();
			}
			
			$this->_pagination = $model->getPagination();
		}
		
		return $this->_topics;
	}

	protected function _buildContentOrderBy ()
	{
		$app = JFactory::getApplication('site');
		$db = $this->getDbo();
		$params = $this->state->params;
		$itemid = $app->input->get('id', 0, 'int') . ':' . $app->input->get('Itemid', 0, 'int');
		$orderCol = $app->getUserStateFromRequest('com_cjforum.category.list.' . $itemid . '.filter_order', 'filter_order', '', 'string');
		$orderDirn = $app->getUserStateFromRequest('com_cjforum.category.list.' . $itemid . '.filter_order_Dir', 'filter_order_Dir', '', 'cmd');
		$orderby = ' ';
		
		if (! in_array($orderCol, $this->filter_fields))
		{
			$orderCol = 'a.created';
		}
		
		if (! in_array(strtoupper($orderDirn), array('ASC', 'DESC', '')))
		{
			$orderDirn = 'DESC';
		}
		
		if ($orderCol && $orderDirn)
		{
			$orderby .= $db->escape($orderCol) . ' ' . $db->escape($orderDirn) . ', ';
		}
		
		$topicOrderby = $params->get('orderby_sec', 'rdate');
		$topicOrderDate = $params->get('order_date');
		$categoryOrderby = $params->def('orderby_pri', '');
		$secondary = CjForumHelperQuery::orderbySecondary($topicOrderby, $topicOrderDate) . ', ';
		$primary = CjForumHelperQuery::orderbyPrimary($categoryOrderby);
		
		$orderby .= $primary . ' ' . $secondary . ' a.ordering ';
		
		return $orderby;
	}

	public function getPagination ()
	{
		if (empty($this->_pagination))
		{
			return null;
		}
		return $this->_pagination;
	}

	public function getCategory ()
	{
		if (! is_object($this->_item))
		{
			if (isset($this->state->params))
			{
				$params = $this->state->params;
				$options = array();
				$options['countItems'] = $params->get('show_cat_num_topics', 1) || ! $params->get('show_empty_categories_cat', 0);
			}
			else
			{
				$options['countItems'] = 0;
			}
			
			$categories = JCategories::getInstance('CjForum', $options);
			$this->_item = $categories->get($this->getState('category.id', 'root'));
			
			// Compute selected asset permissions.
			if (is_object($this->_item))
			{
				$user = JFactory::getUser();
				$asset = 'com_cjforum.category.' . $this->_item->id;
				
				// Check general create permission.
				if ($user->authorise('core.create', $asset))
				{
					$this->_item->getParams()->set('access-create', true);
				}
				
				// TODO: Why aren't we lazy loading the children and siblings?
				$this->_children = $this->_item->getChildren();
				$this->_parent = false;
				
				if ($this->_item->getParent())
				{
					$this->_parent = $this->_item->getParent();
				}
				
				$this->_rightsibling = $this->_item->getSibling();
				$this->_leftsibling = $this->_item->getSibling(false);
			}
			else
			{
				$this->_children = false;
				$this->_parent = false;
			}
		}
		
		return $this->_item;
	}

	public function getParent ()
	{
		if (! is_object($this->_item))
		{
			$this->getCategory();
		}
		
		return $this->_parent;
	}

	function &getLeftSibling ()
	{
		if (! is_object($this->_item))
		{
			$this->getCategory();
		}
		
		return $this->_leftsibling;
	}

	function &getRightSibling ()
	{
		if (! is_object($this->_item))
		{
			$this->getCategory();
		}
		
		return $this->_rightsibling;
	}

	function &getChildren ()
	{
		if (! is_object($this->_item))
		{
			$this->getCategory();
		}
		
		// Order subcategories
		if (count($this->_children))
		{
			$params = $this->getState()->get('params');
			if ($params->get('orderby_pri') == 'alpha' || $params->get('orderby_pri') == 'ralpha')
			{
				jimport('joomla.utilities.arrayhelper');
				JArrayHelper::sortObjects($this->_children, 'title', ($params->get('orderby_pri') == 'alpha') ? 1 : - 1);
			}
		}
		
		return $this->_children;
	}

	public function hit ($pk = 0)
	{
		$input = JFactory::getApplication()->input;
		$hitcount = $input->getInt('hitcount', 1);
		
		if ($hitcount)
		{
			$pk = (! empty($pk)) ? $pk : (int) $this->getState('category.id');
			
			$table = JTable::getInstance('Category', 'JTable');
			$table->load($pk);
			$table->hit($pk);
		}
		
		return true;
	}
}
