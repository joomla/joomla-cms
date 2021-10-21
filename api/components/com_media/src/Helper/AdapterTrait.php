<?php
/**
 * @package     Joomla.API
 * @subpackage  com_media
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Api\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Media\Administrator\Adapter\AdapterInterface;
use Joomla\Component\Media\Administrator\Event\MediaProviderEvent;
use Joomla\Component\Media\Administrator\Provider\ProviderInterface;
use Joomla\Component\Media\Administrator\Provider\ProviderManager;

/**
 * Trait for classes that need adapters.
 *
 * @since  __DEPLOY_VERSION__
 */
trait AdapterTrait
{
	/**
	 * Holds the available media file adapters.
	 *
	 * @var   ProviderManager
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $providerManager = null;

	/**
	 * Returns a provider for the given id.
	 *
	 * @return ProviderInterface
	 *
	 * @throws \Exception
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function getProvider(String $id): ProviderInterface
	{
		return $this->getProviderManager()->getProvider($id);
	}

	/**
	 * Return an adapter for the given name.
	 *
	 * @return AdapterInterface
	 *
	 * @throws \Exception
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function getAdapter(String $name): AdapterInterface
	{
		return $this->getProviderManager()->getAdapter($name);
	}

	/**
	 * Return a provider manager.
	 *
	 * @return ProviderManager
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function getProviderManager(): ProviderManager
	{
		if (!$this->providerManager)
		{
			$this->providerManager = new ProviderManager();

			// Fire the event to get the results
			$eventParameters = ['context' => 'AdapterManager', 'providerManager' => $this->providerManager];
			$event           = new MediaProviderEvent('onSetupProviders', $eventParameters);
			PluginHelper::importPlugin('filesystem');
			Factory::getApplication()->triggerEvent('onSetupProviders', $event);
		}

		return $this->providerManager;
	}
}
