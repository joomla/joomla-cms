<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset;

use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Event\WebAsset\WebAssetRegistryAssetChanged;
use Joomla\CMS\WebAsset\Exception\UnknownAssetException;
use Joomla\Event\Dispatcher as EventDispatcher;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;
use Joomla\Filesystem\Path;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Web Asset Registry class
 *
 * @since  4.0.0
 */
class WebAssetRegistry implements WebAssetRegistryInterface, DispatcherAwareInterface
{
    use DispatcherAwareTrait;

    /**
     * Files with Asset info. File path should be relative.
     *
     * @var    array
     * @example of registry file:
     *
     * {
     *      "title" : "Example",
     *      "name"  : "com_example",
     *      "author": "Joomla! CMS",
     *      "assets": [
     *          {
     *              "name": "library1",
     *              "version": "3.5.0",
     *              "type":  "script",
     *              "uri": "com_example/library1.min.js"
     *          },
     *          {
     *              "name": "library2",
     *              "version": "3.5.0",
     *              "type":  "script",
     *              "uri": "com_example/library2.min.js",
     *              "dependencies": [
     *                  "core",
     *                  "library1"
     *              ],
     *              "attribute": {
     *                  "attr-name": "attr value"
     *                  "defer": true
     *              }
     *          },
     *          {
     *              "name": "library1",
     *              "version": "3.5.0",
     *              "type":  "style",
     *              "uri": "com_example/library1.min.css"
     *              "attribute": {
     *                  "media": "all"
     *              }
     *          },
     *          {
     *              "name": "library1",
     *              "type":  "preset",
     *              "dependencies": {
     *                  "library1#style",
     *                  "library1#script"
     *              }
     *          },
     *      ]
     *  }
     *
     * @since  4.0.0
     */
    protected $dataFilesNew = [];

    /**
     * List of parsed files
     *
     * @var array
     *
     * @since  4.0.0
     */
    protected $dataFilesParsed = [];

    /**
     * Registry of available Assets
     *
     * @var array
     *
     * @since  4.0.0
     */
    protected $assets = [];

    /**
     * Registry constructor
     *
     * @since  4.0.0
     */
    public function __construct()
    {
        // Use a dedicated dispatcher
        $this->setDispatcher(new EventDispatcher());
    }

    /**
     * Get an existing Asset from a registry, by asset name.
     *
     * @param   string  $type  Asset type, script or style
     * @param   string  $name  Asset name
     *
     * @return  WebAssetItem
     *
     * @throws  UnknownAssetException  When Asset cannot be found
     *
     * @since   4.0.0
     */
    public function get(string $type, string $name): WebAssetItemInterface
    {
        // Check if any new file was added
        $this->parseRegistryFiles();

        if (empty($this->assets[$type][$name])) {
            throw new UnknownAssetException(\sprintf('There is no "%s" asset of a "%s" type in the registry.', $name, $type));
        }

        return $this->assets[$type][$name];
    }

    /**
     * Add Asset to registry of known assets
     *
     * @param   string                 $type   Asset type, script or style
     * @param   WebAssetItemInterface  $asset  Asset instance
     *
     * @return  self
     *
     * @since   4.0.0
     */
    public function add(string $type, WebAssetItemInterface $asset): WebAssetRegistryInterface
    {
        $type = strtolower($type);

        if (!\array_key_exists($type, $this->assets)) {
            $this->assets[$type] = [];
        }

        // Check if any new file was added
        $this->parseRegistryFiles();

        $eventChange = 'new';
        $eventAsset  = $asset;

        // Use "old" asset for "Changed" event, a "new" asset can be loaded by a name from the registry
        if (!empty($this->assets[$type][$asset->getName()])) {
            $eventChange = 'override';
            $eventAsset  = $this->assets[$type][$asset->getName()];
        }

        $this->assets[$type][$asset->getName()] = $asset;

        $this->dispatchAssetChanged($type, $eventAsset, $eventChange);

        return $this;
    }

    /**
     * Remove Asset from registry.
     *
     * @param   string  $type  Asset type, script or style
     * @param   string  $name  Asset name
     *
     * @return  self
     *
     * @since   4.0.0
     */
    public function remove(string $type, string $name): WebAssetRegistryInterface
    {
        // Check if any new file was added
        $this->parseRegistryFiles();

        if (!empty($this->assets[$type][$name])) {
            $asset = $this->assets[$type][$name];

            unset($this->assets[$type][$name]);

            $this->dispatchAssetChanged($type, $asset, 'remove');
        }

        return $this;
    }

    /**
     * Check whether the asset exists in the registry.
     *
     * @param   string  $type  Asset type, script or style
     * @param   string  $name  Asset name
     *
     * @return  boolean
     *
     * @since   4.0.0
     */
    public function exists(string $type, string $name): bool
    {
        // Check if any new file was added
        $this->parseRegistryFiles();

        return !empty($this->assets[$type][$name]);
    }

    /**
     * Prepare new Asset instance.
     *
     * @param   string   $name          The asset name
     * @param   ?string  $uri           The URI for the asset
     * @param   array    $options       Additional options for the asset
     * @param   array    $attributes    Attributes for the asset
     * @param   array    $dependencies  Asset dependencies
     *
     * @return  WebAssetItem
     *
     * @since   4.0.0
     */
    public function createAsset(
        string $name,
        ?string $uri = null,
        array $options = [],
        array $attributes = [],
        array $dependencies = []
    ): WebAssetItem {
        $nameSpace = \array_key_exists('namespace', $options) ? $options['namespace'] : __NAMESPACE__ . '\\AssetItem';
        $className = \array_key_exists('class', $options) ? $options['class'] : null;

        if ($className && class_exists($nameSpace . '\\' . $className)) {
            $className = $nameSpace . '\\' . $className;

            return new $className($name, $uri, $options, $attributes, $dependencies);
        }

        return new WebAssetItem($name, $uri, $options, $attributes, $dependencies);
    }

    /**
     * Register new file with Asset(s) info
     *
     * @param   string  $path  Relative path
     *
     * @return  self
     *
     * @since  4.0.0
     */
    public function addRegistryFile(string $path): self
    {
        $path = Path::clean($path);

        if (isset($this->dataFilesNew[$path]) || isset($this->dataFilesParsed[$path])) {
            return $this;
        }

        if (is_file(JPATH_ROOT . '/' . $path)) {
            $this->dataFilesNew[$path] = $path;
        }

        return $this;
    }

    /**
     * Get a list of the registry files
     *
     * @return  array
     *
     * @since  4.0.0
     */
    public function getRegistryFiles(): array
    {
        return array_values($this->dataFilesParsed + $this->dataFilesNew);
    }

    /**
     * Helper method to register new file with Template Asset(s) info
     *
     * @param   string   $template  The template name
     * @param   integer  $client    The application client id
     *
     * @return  self
     *
     * @since  4.0.0
     */
    public function addTemplateRegistryFile(string $template, int $client): self
    {
        switch ($client) {
            case 0:
                $this->addRegistryFile('templates/' . $template . '/joomla.asset.json');
                break;
            case 1:
                // Use basename() for cases when the administrator folder were redefined
                $this->addRegistryFile(basename(JPATH_ADMINISTRATOR) . '/templates/' . $template . '/joomla.asset.json');
                break;
            default:
                break;
        }

        return $this;
    }

    /**
     * Helper method to register new file with Extension Asset(s) info
     *
     * @param   string  $name  A full extension name, actually a name in the /media folder, eg: com_example, plg_system_example etc.
     *
     * @return  self
     *
     * @since  4.0.0
     */
    public function addExtensionRegistryFile(string $name): self
    {
        $this->addRegistryFile('media/' . $name . '/joomla.asset.json');

        return $this;
    }

    /**
     * Parse registered files
     *
     * @return  void
     *
     * @since  4.0.0
     */
    protected function parseRegistryFiles()
    {
        if (!$this->dataFilesNew) {
            return;
        }

        $paths = $this->dataFilesNew;

        $this->dataFilesNew = [];

        foreach ($paths as $path) {
            // Parse only if the file was not parsed already
            if (empty($this->dataFilesParsed[$path])) {
                $this->parseRegistryFile($path);

                // Mark the file as parsed
                $this->dataFilesParsed[$path] = $path;
            }
        }
    }

    /**
     * Parse registry file
     *
     * @param   string  $path  Relative path to the data file
     *
     * @return  void
     *
     * @throws  \RuntimeException If file is empty or invalid
     *
     * @since   4.0.0
     */
    protected function parseRegistryFile($path)
    {
        $data = file_get_contents(JPATH_ROOT . '/' . $path);
        $data = $data ? json_decode($data, true) : null;

        if ($data === null) {
            throw new \RuntimeException(\sprintf('Asset registry file "%s" contains invalid JSON', $path));
        }

        // Check if asset field exists and contains data. If it doesn't - we can just bail here.
        if (empty($data['assets'])) {
            return;
        }

        // Keep source info
        $assetSource = [
            'registryFile' => $path,
        ];

        $namespace = \array_key_exists('namespace', $data) ? $data['namespace'] : null;

        // Prepare WebAssetItem instances
        foreach ($data['assets'] as $i => $item) {
            if (empty($item['name'])) {
                throw new \RuntimeException(
                    \sprintf('Failed parsing asset registry file "%s". Property "name" is required for asset index "%s"', $path, $i)
                );
            }

            if (empty($item['type'])) {
                throw new \RuntimeException(
                    \sprintf('Failed parsing asset registry file "%s". Property "type" is required for asset "%s"', $path, $item['name'])
                );
            }

            $item['type'] = strtolower($item['type']);

            $name                   = $item['name'];
            $uri                    = $item['uri'] ?? '';
            $options                = $item;
            $options['assetSource'] = $assetSource;

            unset($options['uri'], $options['name']);

            // Inheriting the Namespace
            if ($namespace && !\array_key_exists('namespace', $options)) {
                $options['namespace'] = $namespace;
            }

            $assetItem = $this->createAsset($name, $uri, $options);
            $this->add($item['type'], $assetItem);
        }
    }

    /**
     * Dispatch an event to notify listeners about asset changes: new, remove, override
     * Events:
     *  - onWebAssetRegistryChangedAssetNew       When new asset added to the registry
     *  - onWebAssetRegistryChangedAssetOverride  When the asset overridden
     *  - onWebAssetRegistryChangedAssetRemove    When new asset was removed from the registry
     *
     * @param   string                 $type    Asset type, script or style
     * @param   WebAssetItemInterface  $asset   Asset instance
     * @param   string                 $change  A type of change: new, remove, override
     *
     * @return  void
     *
     * @since  4.0.0
     */
    protected function dispatchAssetChanged(string $type, WebAssetItemInterface $asset, string $change)
    {
        // Trigger the event
        $event = AbstractEvent::create(
            'onWebAssetRegistryChangedAsset' . ucfirst($change),
            [
                'eventClass' => WebAssetRegistryAssetChanged::class,
                'subject'    => $this,
                'assetType'  => $type,
                'asset'      => $asset,
                'change'     => $change,
            ]
        );

        $this->getDispatcher()->dispatch($event->getName(), $event);
    }
}
