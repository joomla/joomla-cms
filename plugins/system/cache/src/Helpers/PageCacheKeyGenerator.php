<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.cache
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Cache\Helpers;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;

/**
 * Joomla! Page Cache Plugin - PageCacheKeyGenerator.
 *
 * @since  4.1
 */
final class PageCacheKeyGenerator
{
	/**
	 * Page Cache Key.
	 *
	 * @var		string
	 * @since	__DEPLOY_VERSION__
	 */
	private $key;

	/**
	 * Constructor.
	 *
	 * @param   Uri  $uri  The current Uri.
	 *
	 * @since    __DEPLOY_VERSION__
	 */
	public function __construct(Uri $uri)
	{
		// Get a cache key for the current page based on the url and possible other factors.
		PluginHelper::importPlugin('pagecache');
		$parts = Factory::getApplication()->triggerEvent('onPageCacheGetKey');
		$parts[] = $uri->toString();

		$this->key = md5(serialize($parts));
	}

	/**
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getKey()
	{
		return $this->key;
	}
}
