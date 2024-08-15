<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Provider;

use Joomla\CMS\Language\Text;
use Joomla\Component\Media\Administrator\Adapter\AdapterInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

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
     * Unregister a provider from the ProviderManager.
     * When no provider, or null is passed in, then all providers are cleared.
     *
     * @param   ?ProviderInterface  $provider  The provider to be unregistered
     *
     * @return  void
     *
     * @since   4.0.6
     */
    public function unregisterProvider(?ProviderInterface $provider = null): void
    {
        if ($provider === null) {
            $this->providers = [];
            return;
        }

        if (!\array_key_exists($provider->getID(), $this->providers)) {
            return;
        }

        unset($this->providers[$provider->getID()]);
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
        if (!isset($this->providers[$id])) {
            throw new \Exception(Text::_('COM_MEDIA_ERROR_MEDIA_PROVIDER_NOT_FOUND'));
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

        if ($account == null) {
            throw new \Exception(Text::_('COM_MEDIA_ERROR_ACCOUNT_NOT_SET'));
        }

        $adapters = $this->getProvider($provider)->getAdapters();

        if (!isset($adapters[$account])) {
            throw new \Exception(Text::_('COM_MEDIA_ERROR_ACCOUNT_NOT_FOUND'));
        }

        return $adapters[$account];
    }
}
