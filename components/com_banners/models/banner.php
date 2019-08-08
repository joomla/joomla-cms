<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
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
		$item = $this->getItem();

		if (empty($item))
		{
			throw new Exception(JText::_('JERROR_PAGE_NOT_FOUND'), 404);
		}

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
			$trackDate = JFactory::getDate()->toSql();

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
			/** @var JCacheControllerCallback $cache */
			$cache = JFactory::getCache('com_banners', 'callback');

			$id = $this->getState('banner.id');

			// For PHP 5.3 compat we can't use $this in the lambda function below, so grab the database driver now to use it
			$db = $this->getDbo();

			$loader = function ($id) use ($db)
			{
				$query = $db->getQuery(true)
					->select(
						array(
							$db->quoteName('a.clickurl', 'clickurl'),
							$db->quoteName('a.cid', 'cid'),
							$db->quoteName('a.track_clicks', 'track_clicks'),
							$db->quoteName('cl.track_clicks', 'client_track_clicks'),
						)
					)
					->from($db->quoteName('#__banners', 'a'))
					->join('LEFT', '#__banner_clients AS cl ON cl.id = a.cid')
					->where('a.id = ' . (int) $id);

				$db->setQuery($query);

				return $db->loadObject();
			};

			try
			{
				$this->_item = $cache->get($loader, array($id), md5(__METHOD__ . $id));
			}
			catch (JCacheException $e)
			{
				$this->_item = $loader($id);
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
