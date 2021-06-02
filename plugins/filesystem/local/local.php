<?php
/**
 * @package         Joomla.Plugin
 * @subpackage      FileSystem.Local
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license         GNU General Public License version 2 or later; see
 *                  LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Media\Administrator\Event\MediaProviderEvent;
use Joomla\Component\Media\Administrator\Provider\ProviderInterface;
use Joomla\Plugin\Filesystem\Local\Adapter\LocalAdapter;

/**
 * FileSystem Local plugin.
 *
 * The plugin to deal with the local filesystem in Media Manager.
 *
 * @since  4.0.0
 */
class PlgFileSystemLocal extends CMSPlugin implements ProviderInterface
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded
	 * automatically.
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Setup Providers for Local Adapter
	 *
	 * @param   MediaProviderEvent  $event  Event for ProviderManager
	 *
	 * @return   void
	 *
	 * @since    4.0.0
	 */
	public function onSetupProviders(MediaProviderEvent $event)
	{
		$event->getProviderManager()->registerProvider($this);
	}

	/**
	 * Returns the ID of the provider
	 *
	 * @return  string
	 *
	 * @since  4.0.0
	 */
	public function getID()
	{
		return $this->_name;
	}

	/**
	 * Returns the display name of the provider
	 *
	 * @return string
	 *
	 * @since  4.0.0
	 */
	public function getDisplayName()
	{
		return Text::_('PLG_FILESYSTEM_LOCAL_DEFAULT_NAME');
	}

	/**
	 * Returns and array of adapters
	 *
	 * @return  \Joomla\Component\Media\Administrator\Adapter\AdapterInterface[]
	 *
	 * @since  4.0.0
	 */
	public function getAdapters()
	{
		$adapters = [];

		foreach ($this->getDirectories() as $directoryEntity)
		{
			if ($directoryEntity->directory)
			{
				$directoryPath = JPATH_ROOT . '/' . $directoryEntity->directory;
				$directoryPath = rtrim($directoryPath) . '/';

				$adapter = new LocalAdapter($directoryPath, $directoryEntity->directory);

				$adapters[$adapter->getAdapterName()] = $adapter;
			}
		}

		return $adapters;
	}

	/**
	 * Return plugin directory paramater settings or sensible default
	 *
	 * @return array
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private function getDirectories()
	{
		// Get plugin directories parameter and makes sure it's an array.
		$directories = (array) $this->params->get('directories');

		// Filter out empty entries.
		$directories = array_filter(
			$directories,
			function ($directoryEntity) {
				return !empty($directoryEntity->directory);
			}
		);

		// If directories have been configured, return them.
		if (count($directories))
		{
			return $directories;
		}

		// Return Media Manager's file path setting.
		$comMediaParams              = ComponentHelper::getParams('com_media');
		$defaultDirectory            = new \stdClass;
		$defaultDirectory->directory = $comMediaParams->get(
			'file_path', 'images'
		);

		return [$defaultDirectory];
	}
}
