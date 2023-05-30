<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  FileSystem.local
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Filesystem\Local\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Media\Administrator\Event\MediaProviderEvent;
use Joomla\Component\Media\Administrator\Provider\ProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\Filesystem\Local\Adapter\LocalAdapter;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * FileSystem Local plugin.
 *
 * The plugin to deal with the local filesystem in Media Manager.
 *
 * @since  4.0.0
 */
final class Local extends CMSPlugin implements ProviderInterface
{
    /**
     * Affects constructor behavior. If true, language files will be loaded automatically.
     *
     * @var    boolean
     * @since  4.0.0
     */
    protected $autoloadLanguage = true;
    /**
     * The root directory path
     *
     * @var    string
     * @since  4.3.0
     */
    private $rootDirectory;

    /**
     * Constructor.
     *
     * @param   DispatcherInterface  $dispatcher     The dispatcher
     * @param   array                $config         An optional associative array of configuration settings
     * @param   string               $rootDirectory  The root directory to look for images
     *
     * @since   4.3.0
     */
    public function __construct(DispatcherInterface $dispatcher, array $config, string $rootDirectory)
    {
        parent::__construct($dispatcher, $config);

        $this->rootDirectory = $rootDirectory;
    }

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
        return $this->getApplication()->getLanguage()->_('PLG_FILESYSTEM_LOCAL_DEFAULT_NAME');
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
        $adapters    = [];
        $directories = $this->params->get('directories', '[{"directory": "images", "thumbs": 0}]');

        // Do a check if default settings are not saved by user, if not initialize them manually
        if (is_string($directories)) {
            $directories = json_decode($directories);
        }

        foreach ($directories as $directoryEntity) {
            if (!$directoryEntity->directory) {
                continue;
            }

            $directoryPath = $this->rootDirectory . '/' . $directoryEntity->directory;
            $directoryPath = rtrim($directoryPath) . '/';

            if (!isset($directoryEntity->thumbs)) {
                $directoryEntity->thumbs = 0;
            }

            $adapter = new LocalAdapter(
                $directoryPath,
                $directoryEntity->directory,
                $directoryEntity->thumbs,
                [200, 200]
            );

            $adapters[$adapter->getAdapterName()] = $adapter;
        }

        return $adapters;
    }
}
