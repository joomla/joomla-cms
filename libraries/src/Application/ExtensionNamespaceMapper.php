<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Application;

\defined('JPATH_PLATFORM') or die;

use JLoader;

/**
 * Trait for application classes which ensures the namespace mapper exists and includes it.
 *
 * @since  4.0.0
 */
trait ExtensionNamespaceMapper
{
	/**
	 * Allows the application to load a custom or default identity.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function createExtensionNamespaceMap()
	{
		JLoader::register('JNamespacePsr4Map', JPATH_LIBRARIES . '/namespacemap.php');
		$extensionPsr4Loader = new \JNamespacePsr4Map;
		$extensionPsr4Loader->load();
	}
}
