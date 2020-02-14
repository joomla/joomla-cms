<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2016 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

jimport('joomla.application.component.modellist');

class SppagebuilderModelPages extends JModelList
{

	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id','a.id',
				'title','a.title',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'created_by','a.created_by',
				'published','a.published',
				'catid', 'a.catid', 'category_title',
				'access', 'a.access', 'access_level',
				'created_on','a.created_on',
				'ordering', 'a.ordering',
				'hits', 'a.hits',
				'language','a.language'
			);
		}

		parent::__construct($config);
	}

	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();
		$context = $this->context;

		$search = $this->getUserStateFromRequest($context . '.search', 'filter_search');
		$this->setState('filter.search', $search);

		$access = $this->getUserStateFromRequest($context . '.filter.access', 'filter_access', 0, 'int');
		$this->setState('filter.access', $access);

		$published = $this->getUserStateFromRequest($context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$categoryId = $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id');
		$this->setState('filter.category_id', $categoryId);

		$language = $this->getUserStateFromRequest($context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		// List state information.
		parent::populateState('a.ordering', 'asc');
	}

	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
	}

	protected function getListQuery()
	{

		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$user = JFactory::getUser();

		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.title, a.checked_out, a.checked_out_time, a.text, a.created_by,'.
					'a.published, a.access, a.catid, a.ordering, a.created_on, a.created_by, a.language, a.hits'
			)
		);

		$query->from('#__sppagebuilder as a');

		$query->where($db->quoteName('a.extension') . ' = ' . $db->quote('com_sppagebuilder'));

		$query->select('l.title AS language_title')
			->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.language');

			// Join over the users for the checked out user.
		$query->select('uc.name AS editor')
			->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		$query->select('ua.name AS author_name')
			->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

		$query->select('ug.title AS access_title')
			->join('LEFT','#__viewlevels AS ug ON ug.id = a.access');

		// Join over the categories.
		$query->select('c.title AS category_title')
			->join('LEFT', '#__categories AS c ON c.id = a.catid');

		if ($access = $this->getState('filter.access'))
		{
			$query->where('a.access = ' . (int) $access);
		}

		$published = $this->getState('filter.published');

		if (is_numeric($published))
		{
			$query->where('a.published = ' . (int) $published);
		}
		elseif ($published === '')
		{
			$query->where('(a.published IN (0, 1))');
		}

		// Filter by a single or group of categories.
		$baselevel = 1;
		$categoryId = $this->getState('filter.category_id');

		if (is_numeric($categoryId))
		{
			$cat_tbl = JTable::getInstance('Category', 'JTable');
			$cat_tbl->load($categoryId);
			$rgt = $cat_tbl->rgt;
			$lft = $cat_tbl->lft;
			$baselevel = (int) $cat_tbl->level;
			$query->where('c.lft >= ' . (int) $lft)
				->where('c.rgt <= ' . (int) $rgt);
		}
		elseif (is_array($categoryId))
		{
			JArrayHelper::toInteger($categoryId);
			$categoryId = implode(',', $categoryId);
			$query->where('a.catid IN (' . $categoryId . ')');
		}

		// Filter by language
		if ($language = $this->getState('filter.language'))
		{
			$query->where('a.language = ' . $db->quote($language));
		}

		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			elseif (stripos($search, 'author:') === 0)
			{
				$search = $db->quote('%' . $db->escape(substr($search, 7), true) . '%');
				$query->where('(uc.name LIKE ' . $search . ' OR uc.username LIKE ' . $search . ')');
			}
			else
			{
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where('(a.title LIKE ' . $search . ')');
			}
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.id');
		$orderDirn = $this->state->get('list.direction', 'desc');

		if ($orderCol == 'a.ordering' || $orderCol == 'category_title')
		{
			$orderCol = 'c.title ' . $orderDirn . ', a.ordering';
		}

		// SQL server change
		if ($orderCol == 'language')
		{
			$orderCol = 'l.title';
		}

		if ($orderCol == 'access_level')
		{
			$orderCol = 'ag.title';
		}

		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}

	public function getItems() {
		$app = JApplication::getInstance('site');
		$router = $app->getRouter();

		$items = parent::getItems();
		if(is_array($items) && count($items)) {
			foreach ($items as $key => &$item) {
				$Itemid = SppagebuilderHelper::getMenuId($item->id);
				$item->link = 'index.php?option=com_sppagebuilder&task=page.edit&id=' . $item->id;
				$preview = 'index.php?option=com_sppagebuilder&view=page&id=' . $item->id . $Itemid;
				$sefURI = str_replace('/administrator', '', $router->build($preview));
				$item->preview = $sefURI;

				$lang = '';
				
				if (isset($item->language) && $item->language != '*') {
					$lang_array = explode('-',$item->language);
					$lang = '&lang='.$lang_array[0];
				}

				$front_link = 'index.php?option=com_sppagebuilder&view=form&tmpl=componenet&layout=edit&id=' . $item->id . $Itemid.$lang;
				$sefURI = str_replace('/administrator', '', $router->build($front_link));
				$item->frontend_edit = $sefURI;
			}
		}

		return $items;
	}

}
