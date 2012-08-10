<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');

/**
 * Banners model for the Joomla Banners component.
 *
 * @package     Joomla.Site
 * @subpackage  com_banners
 * @since       1.6
 */
class BannersModelBanners extends JModelList
{
	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 *
	 * @return	string		A store id.
	 * @since	1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':' . $this->getState('filter.search');
		$id	.= ':' . $this->getState('filter.tag_search');
		$id	.= ':' . $this->getState('filter.client_id');
		$id	.= ':' . serialize($this->getState('filter.category_id'));
		$id	.= ':' . serialize($this->getState('filter.keywords'));

		return parent::getStoreId($id);
	}

	/**
	 * Gets a list of banners
	 *
	 * @return	array	An array of banner objects.
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		$db			= $this->getDbo();
		$query		= $db->getQuery(true);
		$ordering	= $this->getState('filter.ordering');
		$tagSearch	= $this->getState('filter.tag_search');
		$cid		= $this->getState('filter.client_id');
		$categoryId = $this->getState('filter.category_id');
		$keywords	= $this->getState('filter.keywords');
		$randomise	= ($ordering == 'random');
		$nullDate	= $db->quote($db->getNullDate());

		$query->select(
			'a.id as id,'.
			'a.type as type,'.
			'a.name as name,'.
			'a.clickurl as clickurl,'.
			'a.cid as cid,'.
			'a.params as params,'.
			'a.custombannercode as custombannercode,'.
			'a.track_impressions as track_impressions,'.
			'cl.track_impressions as client_track_impressions'
			);
		$query->from('#__banners as a');
		$query->join('LEFT', '#__banner_clients AS cl ON cl.id = a.cid');
		$query->where('a.state=1');
		$query->where('('.$query->currentTimestamp().' >= a.publish_up OR a.publish_up = '.$nullDate.')');
		$query->where('('.$query->currentTimestamp().' <= a.publish_down OR a.publish_down = '.$nullDate.')');
		$query->where('(a.imptotal = 0 OR a.impmade <= a.imptotal)');

		if ($cid) {
			$query->join('LEFT', '#__categories as cat ON a.catid = cat.id');
			$query->where('a.cid = ' . (int) $cid);
			$query->where('cl.state = 1');
		}

		// Filter by a single or group of categories
		$categoryId = $this->getState('filter.category_id');
		$catid		= $this->getState('filter.category_id', array());

		if (is_numeric($categoryId)) {
			$type = $this->getState('filter.category_id.include', true) ? '= ' : '<> ';

			// Add subcategory check
			$includeSubcategories = $this->getState('filter.subcategories', false);
			$categoryEquals = 'a.catid '.$type.(int) $categoryId;

			if ($includeSubcategories) {
				$levels = (int) $this->getState('filter.max_category_levels', '1');
				// Create a subquery for the subcategory list
				$subQuery = $db->getQuery(true);
				$subQuery->select('sub.id');
				$subQuery->from('#__categories as sub');
				$subQuery->join('INNER', '#__categories as this ON sub.lft > this.lft AND sub.rgt < this.rgt');
				$subQuery->where('this.id = '.(int) $categoryId);
				$subQuery->where('sub.level <= this.level + '.$levels);

				// Add the subquery to the main query
				$query->where('('.$categoryEquals.' OR a.catid IN ('.$subQuery->__toString().'))');
			}
			else {
				$query->where($categoryEquals);
			}
		}
		elseif ((is_array($categoryId)) && (count($categoryId) > 0)) {
			JArrayHelper::toInteger($categoryId);
			$categoryId = implode(',', $categoryId);
			if($categoryId != '0') {
				$type = $this->getState('filter.category_id.include', true) ? 'IN' : 'NOT IN';
				$query->where('a.catid '.$type.' ('.$categoryId.')');
			}
		}

		if ($tagSearch) {
			if (count($keywords) == 0) {
				$query->where('0');
			}
			else {
				$temp = array();
				$config = JComponentHelper::getParams('com_banners');
				$prefix = $config->get('metakey_prefix');

				foreach ($keywords as $keyword)
				{
					$keyword = trim($keyword);
					$condition1 = "a.own_prefix=1 AND a.metakey_prefix=SUBSTRING(" . $db->quote($keyword) . ",1,LENGTH( a.metakey_prefix)) OR a.own_prefix=0 AND cl.own_prefix=1 AND cl.metakey_prefix=SUBSTRING(" . $db->quote($keyword) . ",1,LENGTH(cl.metakey_prefix)) OR a.own_prefix=0 AND cl.own_prefix=0 AND " . ($prefix == substr($keyword, 0, strlen($prefix)) ? '1' : '0');

					$condition2 = "a.metakey REGEXP '[[:<:]]" . $db->escape($keyword) . "[[:>:]]'";

					if ($cid) {
						$condition2 .= " OR cl.metakey REGEXP '[[:<:]]" . $db->escape($keyword) . "[[:>:]]'";
					}

					if ($catid) {
						$condition2 .= " OR cat.metakey REGEXP '[[:<:]]" . $db->escape($keyword) . "[[:>:]]'";
					}

					$temp[] = "($condition1) AND ($condition2)";
				}

				$query->where('(' . implode(' OR ', $temp). ')');
			}
		}

		// Filter by language
		if ($this->getState('filter.language')) {
			$query->where('a.language in (' . $db->Quote(JFactory::getLanguage()->getTag()) . ',' . $db->Quote('*') . ')');
		}

		$query->order('a.sticky DESC,'. ($randomise ? 'RAND()' : 'a.ordering'));
		return $query;
	}

	/**
	 * Get a list of banners.
	 *
	 * @return	array
	 * @since	1.6
	 */
	public function getItems()
	{
		if (!isset($this->cache['items'])) {
			$this->cache['items'] = parent::getItems();

			foreach ($this->cache['items'] as &$item)
			{
				$parameters = new JRegistry;
				$parameters->loadString($item->params);
				$item->params = $parameters;
			}
		}
		return $this->cache['items'];
	}

	/**
	 * Makes impressions on a list of banners
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function impress()
	{
		$trackDate = JFactory::getDate()->format('Y-m-d H');
		$items	= $this->getItems();
		$db	= $this->getDbo();
		$query	= $db->getQuery(true);

		foreach ($items as $item)
		{
			// Increment impression made
			$id = $item->id;
			$query->clear();
			$query->update('#__banners');
			$query->set('impmade = (impmade + 1)');
			$query->where('id = ' . (int) $id);
			$db->setQuery((string) $query);

			try
			{
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				JError::raiseError(500, $e->getMessage());
			}

			// track impressions
			$trackImpressions = $item->track_impressions;
			if ($trackImpressions < 0 && $item->cid) {
				$trackImpressions = $item->client_track_impressions;
			}

			if ($trackImpressions < 0) {
				$config = JComponentHelper::getParams('com_banners');
				$trackImpressions = $config->get('track_impressions');
			}

			if ($trackImpressions > 0) {
				// is track already created ?
				$query->clear();
				$query->select($db->quoteName('count'));
				$query->from('#__banner_tracks');
				$query->where('track_type=1');
				$query->where('banner_id=' . (int) $id);
				$query->where('track_date=' . $db->Quote($trackDate));

				$db->setQuery((string) $query);

				try
				{
					$db->execute();
				}
				catch (RuntimeException $e)
				{
					JError::raiseError(500, $e->getMessage());
				}

				$count = $db->loadResult();

				$query->clear();

				if ($count) {
					// update count
					$query->update('#__banner_tracks');
					$query->set($db->quoteName('count').' = ('.$db->quoteName('count').' + 1)');
					$query->where('track_type=1');
					$query->where('banner_id=' . (int) $id);
					$query->where('track_date='.$db->Quote($trackDate));
				}
				else {
					// insert new count
					//sqlsrv change
					$query->insert('#__banner_tracks');
					$query->columns(
						array($db->quoteName('count'), $db->quoteName('track_type'),
							$db->quoteName('banner_id'), $db->quoteName('track_date'))
					);
					$query->values('1, 1, ' . (int) $id . ', ' . $db->Quote($trackDate));
				}

				$db->setQuery((string) $query);

				try
				{
					$db->execute();
				}
				catch (RuntimeException $e)
				{
					JError::raiseError(500, $e->getMessage());
				}
			}
		}
	}
}
