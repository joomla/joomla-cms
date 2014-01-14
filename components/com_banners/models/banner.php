<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_banners
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.helper');

JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');

/**
 * Banner model for the Joomla Banners component.
 *
 * @package		Joomla.Site
 * @subpackage	com_banners
 */
class BannersModelBanner extends JModelLegacy
{
	protected $_item;

	/**
	 * Clicks the URL, incrementing the counter
	 *
	 * @return	void
	 */
	function click()
	{
		$id = $this->getState('banner.id');

		// update click count
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$query->update('#__banners');
		$query->set('clicks = (clicks + 1)');
		$query->where('id = ' . (int) $id);

		$db->setQuery((string) $query);

		if (!$db->query()) {
			JError::raiseError(500, $db->getErrorMsg());
		}

		// track clicks

		$item =  $this->getItem();

		$trackClicks = $item->track_clicks;

		if ($trackClicks < 0 && $item->cid) {
			$trackClicks = $item->client_track_clicks;
		}

		if ($trackClicks < 0) {
			$config = JComponentHelper::getParams('com_banners');
			$trackClicks = $config->get('track_clicks');
		}

		if ($trackClicks > 0) {
			$trackDate = JFactory::getDate()->format('Y-m-d H');

			$query->clear();
			$query->select($db->quoteName('count'));
			$query->from('#__banner_tracks');
			$query->where('track_type=2');
			$query->where('banner_id='.(int)$id);
			$query->where('track_date='.$db->Quote($trackDate));

			$db->setQuery((string) $query);

			if (!$db->query()) {
				JError::raiseError(500, $db->getErrorMsg());
			}

			$count = $db->loadResult();

			$query->clear();

			if ($count) {
				// update count
				$query->update('#__banner_tracks');
				$query->set($db->quoteName('count').' = ('.$db->quoteName('count') . ' + 1)');
				$query->where('track_type=2');
				$query->where('banner_id='.(int)$id);
				$query->where('track_date='.$db->Quote($trackDate));
			}
			else {
				// insert new count
				//sqlsrv change
				$query->insert('#__banner_tracks');
				$query->columns(array($db->quoteName('count'), $db->quoteName('track_type'),
								$db->quoteName('banner_id') , $db->quoteName('track_date')));
				$query->values( '1, 2,' . (int)$id . ',' . $db->Quote($trackDate));
			}

			$db->setQuery((string) $query);

			if (!$db->query()) {
				JError::raiseError(500, $db->getErrorMsg());
			}
		}
	}

	/**
	 * Get the data for a banner.
	 *
	 * @return	object
	 */
	function &getItem()
	{
		if (!isset($this->_item))
		{
			$cache = JFactory::getCache('com_banners', '');

			$id = $this->getState('banner.id');

			$this->_item =  $cache->get($id);

			if ($this->_item === false) {
				// redirect to banner url
				$db		= $this->getDbo();
				$query	= $db->getQuery(true);
				$query->select(
					'a.clickurl as clickurl,'.
					'a.cid as cid,'.
					'a.track_clicks as track_clicks'
					);
				$query->from('#__banners as a');
				$query->where('a.id = ' . (int) $id);

				$query->join('LEFT', '#__banner_clients AS cl ON cl.id = a.cid');
				$query->select('cl.track_clicks as client_track_clicks');

				$db->setQuery((string) $query);

				if (!$db->query()) {
					JError::raiseError(500, $db->getErrorMsg());
				}

				$this->_item = $db->loadObject();
				$cache->store($this->_item, $id);
			}
		}

		return $this->_item;
	}

	/**
	 * Get the URL for a banner
	 *
	 * @return	string
	 */
	function getUrl()
	{
		$item = $this->getItem();
		$url = $item->clickurl;

		// check for links
		if (!preg_match('#http[s]?://|index[2]?\.php#', $url)) {
			$url = "http://$url";
		}

		return $url;
	}
}
