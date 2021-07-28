<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Media\Administrator\Event\MediaProviderEvent;
use Joomla\Component\Media\Administrator\Provider\ProviderManager;

/**
 * Media View Model
 *
 * @since  4.0.0
 */
class MediaModel extends BaseDatabaseModel
{
	/**
	 * Obtain list of supported providers
	 *
	 * @return array
	 *
	 * @since 4.0.0
	 */
	public function getProviders()
	{
		// Setup provider manager and event parameters
		$providerManager = new ProviderManager;
		$eventParameters = ['context' => 'AdapterManager', 'providerManager' => $providerManager];
		$event           = new MediaProviderEvent('onSetupProviders', $eventParameters);
		$results         = [];

		// Import plugin group and fire the event
		PluginHelper::importPlugin('filesystem');
		Factory::getApplication()->triggerEvent('onSetupProviders', $event);

		foreach ($providerManager->getProviders() as $provider)
		{
			$result               = new \stdClass;
			$result->name         = $provider->getID();
			$result->displayName  = $provider->getDisplayName();
			$result->adapterNames = [];

			foreach ($provider->getAdapters() as $adapter)
			{
				$result->adapterNames[] = $adapter->getAdapterName();
			}

			$results[] = $result;
		}

		return $results;
	}
}
