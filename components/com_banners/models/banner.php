<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_banners
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');
jimport('joomla.application.component.helper');

JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_banners/tables');

/**
 * Banner model for the Joomla Banners component.
 *
 * @package		Joomla.Site
 * @subpackage	com_banners
 */
class BannersModelBanner extends JModel
{
	protected $_item;

	/**
	 * Clicks the URL, incrementing the counter
	 */
	function click()
	{
		$id = $this->getState('banner.id');
		// update click count
		$query = new JQuery;
		$query->update('#__banners');
		$query->set('clicks = (clicks + 1)');
		$query->where('id = ' . (int)$id);

		$this->_db->setQuery((string)$query);
		if (!$this->_db->query()) {
			JError::raiseError(500, $this->_db->getErrorMsg());
		}

		// track clicks
		$item = &$this->getItem();
		$trackClicks = $item->track_clicks;
		if ($trackClicks < 0 && $item->cid)
		{
			$trackClicks = $item->client_track_clicks;
		}
		if ($trackClicks < 0)
		{
			$config = &JComponentHelper::getParams('com_banners');
			$trackClicks = $config->get('track_clicks');
		}

		if ($trackClicks > 0)
		{
			$trackDate = JFactory::getDate()->toFormat('%Y-%m-%d');

			$query = new JQuery;
			$query->select('`count`');
			$query->from('#__banner_tracks');
			$query->where('track_type=2');
			$query->where('banner_id='.(int)$id);
			$query->where('track_date='.$this->_db->Quote($trackDate));

			$this->_db->setQuery((string)$query);
			if (!$this->_db->query())
			{
				JError::raiseError(500, $this->_db->getErrorMsg());
			}
			$count = $this->_db->loadResult();

			$query = new JQuery;
			if ($count)
			{
				// update count
				$query->update('#__banner_tracks');
				$query->set('`count` = (`count` + 1)');
				$query->where('track_type=2');
				$query->where('banner_id='.(int)$id);
				$query->where('track_date='.$this->_db->Quote($trackDate));
			}
			else
			{
				// insert new count
				$query->insert('#__banner_tracks');
				$query->set('`count` = 1');
				$query->set('track_type=2');
				$query->set('banner_id='.(int)$id);
				$query->set('track_date='.$this->_db->Quote($trackDate));
			}

			$this->_db->setQuery((string)$query);
			if (!$this->_db->query()) {
				JError::raiseError(500, $this->_db->getErrorMsg());
			}
		}
	}

	/**
	 * Get the data for a banner
	 */
	function &getItem()
	{
		if (!isset($this->_item))
		{
			$id = $this->getState('banner.id');
			// redirect to banner url
			$query = new JQuery;
			$query->select(
				'a.clickurl as clickurl,'.
				'a.cid as cid,'.
				'a.track_clicks as track_clicks'
				);
			$query->from('#__banners as a');
			$query->where('a.id = ' . (int) $id);

			$query->join('LEFT', '#__banner_clients AS cl ON cl.id = a.cid');
			$query->select('cl.track_clicks as client_track_clicks');

			$this->_db->setQuery((string)$query);
			if (!$this->_db->query())
			{
				JError::raiseError(500, $this->_db->getErrorMsg());
			}

			$this->_item = $this->_db->loadObject();
		}
		return $this->_item;
	}

	/**
	 * Get the URL for a banner
	 */
	function getUrl()
	{
		$item = &$this->getItem();
		$url = $item->clickurl;
		// check for links
		if (!preg_match('#http[s]?://|index[2]?\.php#', $url))
		{
			$url = "http://$url";
		}
		return $url;
	}
}

