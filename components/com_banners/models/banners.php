<?php
/**
 * @version		$Id: banner.php 13359 2009-10-28 04:23:55Z louis $
 * @package		Joomla.Site
 * @subpackage	com_banners
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');
jimport('joomla.application.component.helper');

JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_banners/tables');

/**
 * Banners model for the Joomla Banners component.
 *
 * @package		Joomla.Site
 * @subpackage	com_banners
 */
class BannersModelBanners extends JModelList
{
	protected $_items;

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
	 */
	protected function _getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.tag_search');
		$id	.= ':'.$this->getState('filter.client_id');
		$id	.= ':'.$this->getState('filter.category_id');
		$id	.= ':'.$this->getState('filter.keywords');

		return parent::_getStoreId($id);
	}

	/**
	 * Gets a list of banners
	 * @return array An array of banner objects
	 */
	function _getListQuery()
	{
		static $query;
		if (!isset($query)) {
			$db		= $this->getDbo();
			$query	= $db->getQuery(true);
			$ordering	= $this->getState('filter.ordering');
			$tagSearch	= $this->getState('filter.tag_search');
			$cid		= $this->getState('filter.client_id');
			$catid		= $this->getState('filter.category_id');
			$keywords	= $this->getState('filter.keywords');
			$randomise	= ($ordering == 'random');

			$query->select(
				'a.id as id,'.
				'a.type as type,'.
				'a.name as name,'.
				'a.clickurl as clickurl,'.
				'a.cid as cid,'.
				'a.params as params,'.
				'a.track_impressions as track_impressions'
			);
			$query->from('#__banners as a');
			$query->where('a.state=1');
			$query->where("(NOW() >= a.publish_up OR a.publish_up='0000-00-00 00:00:00')");
			$query->where("(NOW() <= a.publish_down OR a.publish_down='0000-00-00 00:00:00')");
			$query->where('(a.imptotal = 0 OR a.impmade = a.imptotal)');

			if ($cid) {
				$query->where('a.cid = ' . (int) $cid);
				$query->join('LEFT', '#__banner_clients AS cl ON cl.id = a.cid');
				$query->select('cl.track_impressions as client_track_impressions');
				$query->where('cl.state = 1');
			}

			if ($catid) {
				$query->where('a.catid = ' . (int) $catid);
				$query->join('LEFT', '#__categories AS cat ON cat.id = a.catid');
				$query->where('cat.published = 1');
			}

			if ($tagSearch) {
				if (count($keywords) == 0) {
					$query->where('0');
				} else {
					$temp = array();
					$config = &JComponentHelper::getParams('com_banners');
					$prefix = $config->get('metakey_prefix');

					foreach ($keywords as $keyword) {
						$keyword=trim($keyword);
						$condition1 = "a.own_prefix=1 AND  a.metakey_prefix=SUBSTRING('".$keyword."',1,LENGTH( a.metakey_prefix)) OR a.own_prefix=0 AND cl.own_prefix=1 AND cl.metakey_prefix=SUBSTRING('".$keyword."',1,LENGTH(cl.metakey_prefix)) OR a.own_prefix=0 AND cl.own_prefix=0 AND ".($prefix==substr($keyword,0,strlen($prefix))?'1':'0');

						$condition2="a.metakey REGEXP '[[:<:]]".$this->_db->getEscaped($keyword) . "[[:>:]]'";
						if ($cid) {
							$condition2.=" OR cl.metakey REGEXP '[[:<:]]".$this->_db->getEscaped($keyword) . "[[:>:]]'";
						}
						if ($catid) {
							$condition2.=" OR cat.metakey REGEXP '[[:<:]]".$this->_db->getEscaped($keyword) . "[[:>:]]'";
						}
						$temp[]="($condition1) AND ($condition2)";
					}
					$query->where('(' . implode(' OR ', $temp). ')');
				}
			}
			$query->order('a.sticky DESC,'. ($randomise ? 'RAND()' : 'a.ordering'));
		}
		return $query;
	}

	function &getItems()
	{
		if (!isset($this->_items)) {
			$this->_items = &parent::getItems();

			foreach ($this->_items as &$item) {
				$parameters = new JRegistry;
				$parameters->loadJSON($item->params);
				$item->params = $parameters->toObject();
			}
		}
		return $this->_items;
	}
	/**
	 * Makes impressions on a list of banners
	 */
	function impress()
	{
		$trackDate = JFactory::getDate()->toFormat('%Y-%m-%d');
		$items	= &$this->getItems();
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		foreach ($items as $item) {
			// Increment impression made
			$id = $item->id;
			$query->clear();
			$query->update('#__banners');
			$query->set('impmade = (impmade + 1)');
			$query->where('id = '.(int)$id);
			$this->_db->setQuery((string)$query);
			if (!$this->_db->query()) {
				JError::raiseError(500, $this->_db->getErrorMsg());
			}

			// track impressions
			$trackImpressions = $item->track_impressions;
			if ($trackImpressions < 0 && $item->cid) {
				$trackImpressions = $item->client_track_impressions;
			}

			if ($trackImpressions < 0) {
				$config = &JComponentHelper::getParams('com_banners');
				$trackImpressions = $config->get('track_impressions');
			}

			if ($trackImpressions > 0) {
				// is track already created ?
				$query->clear();
				$query->select('`count`');
				$query->from('#__banner_tracks');
				$query->where('track_type=1');
				$query->where('banner_id='.(int) $id);
				$query->where('track_date='.$this->_db->Quote($trackDate));

				$this->_db->setQuery((string)$query);
				if (!$this->_db->query()) {
					JError::raiseError(500, $this->_db->getErrorMsg());
				}
				$count = $this->_db->loadResult();

				$query->clear();
				if ($count) {
					// update count
					$query->update('#__banner_tracks');
					$query->set('`count` = (`count` + 1)');
					$query->where('track_type=1');
					$query->where('banner_id='.(int)$id);
					$query->where('track_date='.$this->_db->Quote($trackDate));
				} else {
					// insert new count
					$query->insert('#__banner_tracks');
					$query->set('`count` = 1');
					$query->set('track_type=1');
					$query->set('banner_id='.(int)$id);
					$query->set('track_date='.$this->_db->Quote($trackDate));
				}

				$this->_db->setQuery((string)$query);
				if (!$this->_db->query()) {
					JError::raiseError(500, $this->_db->getErrorMsg());
				}
			}
		}
	}
}