<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');

/**
 * Banner model for the Joomla Banners component.
 *
 * @since  1.5
 */
class BannersModelBanner extends JModelLegacy
{
	/**
	 * Cached item object
	 *
	 * @var    object
	 * @since  1.6
	 */
	protected $_item;

	/**
	 * Clicks the URL, incrementing the counter
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public function click()
	{
		$id = $this->getState('banner.id');

		// Update click count
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->update('#__banners')
			->set('clicks = (clicks + 1)')
			->where('id = ' . (int) $id);

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			JError::raiseError(500, $e->getMessage());
		}

		$item = $this->getItem();

		// Track clicks
		$trackClicks = $item->track_clicks;

		if ($trackClicks < 0 && $item->cid)
		{
			$trackClicks = $item->client_track_clicks;
		}

		if ($trackClicks < 0)
		{
			$config = JComponentHelper::getParams('com_banners');
			$trackClicks = $config->get('track_clicks');
		}

		if ($trackClicks > 0)
		{
			$trackDate = JFactory::getDate()->format('Y-m-d H');

			$query->clear()
				->select($db->quoteName('count'))
				->from('#__banner_tracks')
				->where('track_type=2')
				->where('banner_id=' . (int) $id)
				->where('track_date=' . $db->quote($trackDate));

			$db->setQuery($query);

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

			if ($count)
			{
				// Update count
				$query->update('#__banner_tracks')
					->set($db->quoteName('count') . ' = (' . $db->quoteName('count') . ' + 1)')
					->where('track_type=2')
					->where('banner_id=' . (int) $id)
					->where('track_date=' . $db->quote($trackDate));
			}
			else
			{
				// Insert new count
				$query->insert('#__banner_tracks')
					->columns(
						array(
							$db->quoteName('count'), $db->quoteName('track_type'),
							$db->quoteName('banner_id'), $db->quoteName('track_date')
						)
					)
					->values('1, 2,' . (int) $id . ',' . $db->quote($trackDate));
			}

			$db->setQuery($query);

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

	/**
	 * Get the data for a banner.
	 *
	 * @return  object
	 *
	 * @since   1.6
	 */
	public function &getItem()
	{
		if (!isset($this->_item))
		{
			$cache = JFactory::getCache('com_banners', '');

			$id = $this->getState('banner.id');

			$this->_item = $cache->get($id);

			if ($this->_item === false)
			{
				// Redirect to banner url
				$db = $this->getDbo();
				$query = $db->getQuery(true)
					->select(
						'a.clickurl as clickurl,' .
							'a.cid as cid,' .
							'a.track_clicks as track_clicks'
					)
					->from('#__banners as a')
					->where('a.id = ' . (int) $id)

					->join('LEFT', '#__banner_clients AS cl ON cl.id = a.cid')
					->select('cl.track_clicks as client_track_clicks');

				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (RuntimeException $e)
				{
					JError::raiseError(500, $e->getMessage());
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
	 * @return  string
	 *
	 * @since   1.5
	 */
	public function getUrl()
	{
		$item = $this->getItem();
		$url = $item->clickurl;

		// Check for links
		if (!preg_match('#http[s]?://|index[2]?\.php#', $url))
		{
			$url = "http://$url";
		}

		return $url;
	}
}
