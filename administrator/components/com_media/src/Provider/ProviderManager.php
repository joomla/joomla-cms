<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Provider;

\defined('_JEXEC') or die;

use Joomla\Component\Media\Administrator\Adapter\AdapterInterface;

/**
 * Media Adapter Manager
 *
 * @since  4.0.0
 */
class ProviderManager
{
	/**
	 * The array of providers
	 *
	 * @var  ProviderInterface[]
	 *
	 * @since  4.0.0
	 */
	private $providers = [];

	/**
	 * Returns an associative array of adapters with provider name as the key
	 *
	 * @return  ProviderInterface[]
	 *
	 * @since  4.0.0
	 */
	public function getProviders()
	{
		return $this->providers;
	}

	/**
	 * Register a provider into the ProviderManager
	 *
	 * @param   ProviderInterface  $provider  The provider to be registered
	 *
	 * @return  void
	 *
	 * @since  4.0.0
	 */
	public function registerProvider(ProviderInterface $provider)
	{
		$this->providers[$provider->getID()] = $provider;
	}

	/**
	 * Returns the provider for a particular ID
	 *
	 * @param   string  $id  The ID for the provider
	 *
	 * @return  ProviderInterface
	 *
	 * @throws \Exception
	 *
	 * @since  4.0.0
	 */
	public function getProvider($id)
	{
		if (!isset($this->providers[$id]))
		{
			throw new \Exception("Media Provider not found");
		}

		return $this->providers[$id];
	}

	/**
	 * Returns an adapter for an account
	 *
	 * @param   string  $name  The name of an adapter
	 *
	 * @return  AdapterInterface
	 *
	 * @throws \Exception
	 *
	 * @since  4.0.0
	 */
	public function getAdapter($name)
	{
		list($provider, $account) = array_pad(explode('-', $name, 2), 2, null);

		if ($account == null)
		{
			throw new \Exception('Account was not set');
		}

		$adapters = $this->getProvider($provider)->getAdapters();

		if (!isset($adapters[$account]))
		{
			throw new \Exception("The account was not found");
		}

		return $adapters[$account];
	}
}
