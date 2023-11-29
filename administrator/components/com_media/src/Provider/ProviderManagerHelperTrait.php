<?php

/**
 * @package     Joomla.API
 * @subpackage  com_media
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Provider;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Media\Administrator\Adapter\AdapterInterface;
use Joomla\Component\Media\Administrator\Event\MediaProviderEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Trait for classes that need adapters.
 *
 * @since  4.1.0
 */
trait ProviderManagerHelperTrait
{
    /**
     * Holds the available media file adapters.
     *
     * @var    ProviderManager
     *
     * @since  4.1.0
     */
    private $providerManager = null;

    /**
     * The default adapter name.
     *
     * @var    string
     *
     * @since  4.1.0
     */
    private $defaultAdapterName = null;

    /**
     * Return a provider manager.
     *
     * @return  ProviderManager
     *
     * @since   4.1.0
     */
    public function getProviderManager(): ProviderManager
    {
        if (!$this->providerManager) {
            // Fire the event to get the results
            $eventParameters = ['context' => 'AdapterManager', 'providerManager' => new ProviderManager()];
            $event           = new MediaProviderEvent('onSetupProviders', $eventParameters);
            PluginHelper::importPlugin('filesystem');
            Factory::getApplication()->triggerEvent('onSetupProviders', $event);
            $this->providerManager = $event->getProviderManager();
        }

        return $this->providerManager;
    }

    /**
     * Returns a provider for the given id.
     *
     * @param   string  $id
     *
     * @return  ProviderInterface
     *
     * @throws \Exception
     * @since   4.1.0
     */
    public function getProvider(string $id): ProviderInterface
    {
        return $this->getProviderManager()->getProvider($id);
    }

    /**
     * Return an adapter for the given name.
     *
     * @param   string  $name
     *
     * @return  AdapterInterface
     *
     * @throws \Exception
     * @since   4.1.0
     */
    public function getAdapter(string $name): AdapterInterface
    {
        return $this->getProviderManager()->getAdapter($name);
    }

    /**
     * Returns an array with the adapter name as key and the path of the file.
     *
     * @param   string  $path
     *
     * @return  array
     *
     * @throws \Exception
     * @since   4.1.0
     */
    protected function resolveAdapterAndPath(string $path): array
    {
        $result = [];
        $parts  = explode(':', $path, 2);

        // If we have 2 parts, we have both an adapter name and a file path
        if (\count($parts) === 2) {
            $result['adapter'] = $parts[0];
            $result['path']    = $parts[1];

            return $result;
        }

        if (!$this->getDefaultAdapterName()) {
            throw new \InvalidArgumentException(Text::_('COM_MEDIA_ERROR_NO_ADAPTER_FOUND'));
        }

        // If we have less than 2 parts, we return a default adapter name
        $result['adapter'] = $this->getDefaultAdapterName();

        // If we have 1 part, we return it as the path. Otherwise we return a default path
        $result['path'] = \count($parts) ? $parts[0] : '/';

        return $result;
    }

    /**
     * Returns the default adapter name.
     *
     * @return  string|null
     *
     * @throws  \Exception
     *
     * @since   4.1.0
     */
    protected function getDefaultAdapterName(): ?string
    {
        if ($this->defaultAdapterName) {
            return $this->defaultAdapterName;
        }

        $defaultAdapter = $this->getAdapter('local-' . ComponentHelper::getParams('com_media')->get('file_path', 'images'));

        if (
            !$defaultAdapter
            && $this->getProviderManager()->getProvider('local')
            && $this->getProviderManager()->getProvider('local')->getAdapters()
        ) {
            $defaultAdapter = $this->getProviderManager()->getProvider('local')->getAdapters()[0];
        }

        if (!$defaultAdapter) {
            return null;
        }

        $this->defaultAdapterName = 'local-' . $defaultAdapter->getAdapterName();

        return $this->defaultAdapterName;
    }
}
