<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_banners
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Site\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Cache\Exception\CacheExceptionInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\ParameterType;

/**
 * Banner model for the Joomla Banners component.
 *
 * @since  1.5
 */
class BannerModel extends BaseDatabaseModel
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
	 * @throws  \Exception
	 */
	public function click()
	{
		$item = $this->getItem();

		if (empty($item))
		{
			throw new \Exception(Text::_('JERROR_PAGE_NOT_FOUND'), 404);
		}

		$id = (int) $this->getState('banner.id');

		// Update click count
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->update($db->quoteName('#__banners'))
			->set($db->quoteName('clicks') . ' = ' . $db->quoteName('clicks') . ' + 1')
			->where($db->quoteName('id') . ' = :id')
			->bind(':id', $id, ParameterType::INTEGER);

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (\RuntimeException $e)
		{
			throw new \Exception($e->getMessage(), 500);
		}

		// Track clicks
		$trackClicks = $item->track_clicks;

		if ($trackClicks < 0 && $item->cid)
		{
			$trackClicks = $item->client_track_clicks;
		}

		if ($trackClicks < 0)
		{
			$config = ComponentHelper::getParams('com_banners');
			$trackClicks = $config->get('track_clicks');
		}

		if ($trackClicks > 0)
		{
			$trackDate = Factory::getDate()->toSql();

			$query = $db->getQuery(true);

			$query->select($db->quoteName('count'))
				->from($db->quoteName('#__banner_tracks'))
				->where(
					[
						$db->quoteName('track_type') . ' = 2',
						$db->quoteName('banner_id') . ' = :id',
						$db->quoteName('track_date') . ' = :trackDate',
					]
				)
				->bind(':id', $id, ParameterType::INTEGER)
				->bind(':trackDate', $trackDate);

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (\RuntimeException $e)
			{
				throw new \Exception($e->getMessage(), 500);
			}

			$count = $db->loadResult();

			$query = $db->getQuery(true);

			if ($count)
			{
				// Update count
				$query->update($db->quoteName('#__banner_tracks'))
					->set($db->quoteName('count') . ' = ' . $db->quoteName('count') . ' + 1')
					->where(
						[
							$db->quoteName('track_type') . ' = 2',
							$db->quoteName('banner_id') . ' = :id',
							$db->quoteName('track_date') . ' = :trackDate',
						]
					)
					->bind(':id', $id, ParameterType::INTEGER)
					->bind(':trackDate', $trackDate);
			}
			else
			{
				// Insert new count
				$query->insert($db->quoteName('#__banner_tracks'))
					->columns(
						[
							$db->quoteName('count'),
							$db->quoteName('track_type'),
							$db->quoteName('banner_id'),
							$db->quoteName('track_date'),
						]
					)
					->values('1, 2 , :id, :trackDate')
					->bind(':id', $id, ParameterType::INTEGER)
					->bind(':trackDate', $trackDate);
			}

			$db->setQuery($query);

			try
			{
				$db->execute();
			}
			catch (\RuntimeException $e)
			{
				throw new \Exception($e->getMessage(), 500);
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
			/** @var \Joomla\CMS\Cache\Controller\CallbackController $cache */
			$cache = Factory::getCache('com_banners', 'callback');

			$id = (int) $this->getState('banner.id');

			// For PHP 5.3 compat we can't use $this in the lambda function below, so grab the database driver now to use it
			$db = $this->getDbo();

			$loader = function ($id) use ($db)
			{
				$query = $db->getQuery(true);

				$query->select(
					[
						$db->quoteName('a.clickurl'),
						$db->quoteName('a.cid'),
						$db->quoteName('a.track_clicks'),
						$db->quoteName('cl.track_clicks', 'client_track_clicks'),
					]
				)
					->from($db->quoteName('#__banners', 'a'))
					->join('LEFT', $db->quoteName('#__banner_clients', 'cl'), $db->quoteName('cl.id') . ' = ' . $db->quoteName('a.cid'))
					->where($db->quoteName('a.id') . ' = :id')
					->bind(':id', $id, ParameterType::INTEGER);

				$db->setQuery($query);

				return $db->loadObject();
			};

			try
			{
				$this->_item = $cache->get($loader, array($id), md5(__METHOD__ . $id));
			}
			catch (CacheExceptionInterface $e)
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
