<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Document\Document;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Filesystem\Path;
use Joomla\Event\DispatcherAwareInterface;
use Joomla\Event\DispatcherAwareTrait;

/**
 * Web Asset Factory class
 *
 * @since  __DEPLOY_VERSION__
 */
class WebAssetRegistry implements DispatcherAwareInterface
{
	use DispatcherAwareTrait;

	/**
	 * Mark the new registry file
	 *
	 * @var integer
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	const REGISTRY_FILE_NEW = 1;

	/**
	 * Mark already parsed registry file
	 *
	 * @var integer
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	const REGISTRY_FILE_PARSED = 2;

	/**
	 * Mark a broken/non-existing registry file
	 *
	 * @var integer
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	const REGISTRY_FILE_INVALID = -1;

	/**
	 * Files with Asset info. File path should be relative.
	 *
	 * @example of data file:
	 *
	 * {
	 *		"title" : "Example",
	 *		"name"  : "com_example",
	 *		"author": "Joomla! CMS",
	 *		"assets": [
	 *			{
	 *				"name": "library1",
	 *				"version": "3.5.0",
	 *				"js": [
	 *					"com_example/library1.min.js"
	 *				]
	 *			},
	 *			{
	 *				"name": "library2",
	 *				"version": "3.5.0",
	 *				"js": [
	 *					"com_example/library2.min.js"
	 *				],
	 *				"css": [
	 *					"com_example/library2.css"
	 *				],
	 *				"dependency": [
	 *					"core",
	 *					"library1"
	 *				],
	 *				"attribute": {
	 *					"com_example/library2.min.js": {
	 *						"attrname": "attrvalue"
	 *					},
	 *					"com_example/library2.css": {
	 *						"media": "all"
	 *					}
	 *				}
	 *			},
	 *		]
	 *	}
	 *
	 * @var    array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $dataFiles = [];

	/**
	 * Registry of available Assets
	 *
	 * @var array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $assets = [];

	/**
	 * Weight off the heaviest and active asset
	 *
	 * @var float
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $lastItemWeight = 1;

	/**
	 * Whether append asset version to asset path
	 *
	 * @var    bool
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $useVersioning = true;

	/**
	 * Get an existing Asset from a registry, by asset name.
	 * Return asset object or false if asset does not exist.
	 *
	 * @param   string  $name  Asset name
	 *
	 * @return  WebAssetItem|null
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getAsset(string $name)
	{
		// Check if any new file was added
		$this->parseRegistryFiles();

		if (!empty($this->assets[$name]))
		{
			return $this->assets[$name];
		}

		return null;
	}

	/**
	 * Search for all active assets.
	 *
	 * @return  WebAssetItem[]  Array with active assets
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getActiveAssets(): array
	{
		$assets = array_filter(
			$this->assets,
			function($asset)
			{
				/** @var WebAssetItem $asset */
				return $asset->isActive();
			}
		);

		return $assets;
	}

	/**
	 * Search for assets with specific state.
	 *
	 * @param   int  $state  Asset state
	 *
	 * @return  WebAssetItem[]  Array with active assets
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getAssetsByState(int $state = WebAssetItem::ASSET_STATE_ACTIVE): array
	{
		$assets = array_filter(
			$this->assets,
			function($asset) use ($state)
			{
				/** @var WebAssetItem $asset */
				return $asset->getState() === $state;
			}
		);

		return $assets;
	}

	/**
	 * Add Asset to registry of known assets
	 *
	 * @param   WebAssetItem  $asset  Asset instance
	 *
	 * @return  self
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function addAsset(WebAssetItem $asset): self
	{
		// Check whether the asset already exists, so we must copy its state before override
		if (!empty($this->assets[$asset->getName()]))
		{
			$existing = $this->assets[$asset->getName()];
			$asset->setState($existing->getState());
		}

		$this->assets[$asset->getName()] = $asset;

		return $this;
	}

	/**
	 * Remove Asset from registry.
	 *
	 * @param   string  $name  Asset name
	 *
	 * @return  self
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function removeAsset(string $name): self
	{
		if (!empty($this->assets[$name]))
		{
			unset($this->assets[$name]);
		}

		return $this;
	}

	/**
	 * Change the asset State
	 *
	 * @param   string   $name   Asset name
	 * @param   integer  $state  New state
	 *
	 * @return  self
	 *
	 * @throws  \RuntimeException if asset with given name does not exist
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setAssetState(string $name, int $state = WebAssetItem::ASSET_STATE_ACTIVE): self
	{
		$asset = $this->getAsset($name);

		if (!$asset)
		{
			throw new \RuntimeException('Asset "' . $name . '" does not exist');
		}

		$currentState = $asset->getState();

		// Asset already has the requested state
		if ($currentState === $state)
		{
			return $this;
		}

		// Change state
		$asset->setState($state);

		// Update Dependency
		$this->updateDependency();

		// Trigger the event
		$event = AbstractEvent::create(
			'onWebAssetStateChangedExternally',
			[
				'eventClass' => 'Joomla\\CMS\\Event\\WebAsset\\WebAssetStateChangedEvent',
				'subject'  => $this,
				'asset'    => $asset,
				'oldState' => $currentState,
				'newState' => $state,
			]
		);
		$this->getDispatcher()->dispatch($event->getName(), $event);

		return $this;
	}

	/**
	 * Activate the Asset item
	 *
	 * @param   string  $name  The asset name
	 *
	 * @return self
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function enableAsset(string $name): self
	{
		return $this->setAssetState($name, WebAssetItem::ASSET_STATE_ACTIVE);
	}

	/**
	 * Deactivate the Asset item
	 *
	 * @param   string  $name  The asset name
	 *
	 * @return self
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function disableAsset(string $name): self
	{
		return $this->setAssetState($name, WebAssetItem::ASSET_STATE_INACTIVE);
	}

	/**
	 * Attach active assets to the document
	 *
	 * @param   Document  $doc  Document for attach StyleSheet/JavaScript
	 *
	 * @return  self
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function attachActiveAssetsToDocument(Document $doc): self
	{
		// Resolve Dependency
		$this->updateDependency()->calculateWeightOfActiveAssets();

		// Trigger the event
		$event = AbstractEvent::create(
			'onWebAssetBeforeAttach',
			[
				'eventClass' => 'Joomla\\CMS\\Event\\WebAsset\\WebAssetBeforeAttachEvent',
				'subject'  => $this,
				'document' => $doc,
			]
		);
		$this->getDispatcher()->dispatch($event->getName(), $event);

		$assets = $this->sortAssetsByWeight($this->getActiveAssets());

		// Pre-save existing Scripts, and attach them after requested assets.
		$jsBackup = $doc->_scripts;
		$doc->_scripts = [];

		// Attach active assets to the document
		foreach ($assets as $asset)
		{
			$paths = $asset->getAssetFiles();

			// Add StyleSheets of the asset
			foreach ($paths['stylesheet'] as $path => $attr)
			{
				unset($attr['__isExternal'], $attr['__pathOrigin']);
				$version = $this->useVersioning ? ($asset->getVersion() ?: 'auto') : false;
				$doc->addStyleSheet($path, ['version' => $version], $attr);
			}

			// Add Scripts of the asset
			foreach ($paths['script'] as $path => $attr)
			{
				unset($attr['__isExternal'], $attr['__pathOrigin']);
				$version = $this->useVersioning ? ($asset->getVersion() ?: 'auto') : false;
				$doc->addScript($path, ['version' => $version], $attr);
			}
		}

		// Merge with previously added scripts
		$doc->_scripts = array_replace($doc->_scripts, $jsBackup);

		return $this;
	}

	/**
	 * Update Dependencies state for all active Assets
	 *
	 * @return  self
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function updateDependency(): self
	{
		// First, deactivate all Dependency
		foreach ($this->getAssetsByState(WebAssetItem::ASSET_STATE_DEPENDANCY) as $depItem)
		{
			$depItem->setState(WebAssetItem::ASSET_STATE_INACTIVE);
		}

		// Second, get list of active assets and enable their dependencies
		$assets = $this->getAssetsByState(WebAssetItem::ASSET_STATE_ACTIVE);

		foreach ($assets as $asset)
		{
			$this->updateItemDependency($asset);
		}

		return $this;
	}

	/**
	 * Update Dependencies state for given Asset
	 *
	 * @param   WebAssetItem  $asset  Asset instance
	 *
	 * @return  self
	 *
	 * @throws  \RuntimeException When Dependency cannot be resolved
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function updateItemDependency(WebAssetItem $asset): self
	{
		foreach ($this->getDependenciesForAsset($asset, true) as $depItem)
		{
			// Set dependency state only when it is inactive, to keep a manually activated Asset in their original state
			if (!$depItem->isActive())
			{
				$depItem->setState(WebAssetItem::ASSET_STATE_DEPENDANCY);
			}
		}

		return $this;
	}

	/**
	 * Calculate weight of active Assets, by its Dependencies
	 *
	 * @return  self
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function calculateWeightOfActiveAssets(): self
	{
		// See https://en.wikipedia.org/wiki/Topological_sorting#Kahn.27s_algorithm
		$result        = [];
		$graphOutgoing = [];
		$graphIncoming = [];
		$activeAssets  = $this->getActiveAssets();

		// Build Graphs of Outgoing and Incoming connections
		foreach ($activeAssets as $asset)
		{
			$name = $asset->getName();
			$graphOutgoing[$name] = array_combine($asset->getDependencies(), $asset->getDependencies());

			if (!array_key_exists($name, $graphIncoming))
			{
				$graphIncoming[$name] = [];
			}

			foreach ($asset->getDependencies() as $depName)
			{
				$graphIncoming[$depName][$name] = $name;
			}
		}

		// Find items without incoming connections
		$emptyIncoming = array_keys(
			array_filter(
				$graphIncoming,
				function ($el){
					return !$el;
				}
			)
		);

		// Loop through, and sort the graph
		while ($emptyIncoming)
		{
			// Add the node without incoming connection to the result
			$item = array_shift($emptyIncoming);
			$result[] = $item;

			// Check of each neighbor of the node
			foreach (array_reverse($graphOutgoing[$item]) as $neighbor)
			{
				// Remove incoming connection of already visited node
				unset($graphIncoming[$neighbor][$item]);

				// If there no more incoming connections add the node to queue
				if (empty($graphIncoming[$neighbor]))
				{
					$emptyIncoming[] = $neighbor;
				}
			}
		}

		// Update a weight for each active asset
		foreach (array_reverse($result) as $index => $name)
		{
			$activeAssets[$name]->setWeight($index + 1);
		}

		return $this;
	}

	/**
	 * Return dependancy for Asset as array of AssetItem objects
	 *
	 * @param   WebAssetItem  $asset          Asset instance
	 * @param   boolean       $recursively    Whether to search for dependancy recursively
	 * @param   WebAssetItem  $recursionRoot  Initial item to prevent loop
	 *
	 * @return  WebAssetItem[]
	 *
	 * @throws  \RuntimeException When Dependency cannot be found
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getDependenciesForAsset(WebAssetItem $asset, $recursively = false, WebAssetItem $recursionRoot = null): array
	{
		$assets        = [];
		$recursionRoot = $recursionRoot ?? $asset;

		foreach ($asset->getDependencies() as $depName)
		{
			// Skip already loaded in recursion
			if ($recursionRoot->getName() === $depName)
			{
				continue;
			}

			$dep = $this->getAsset($depName);

			if (!$dep)
			{
				throw new \RuntimeException('Cannot find Dependency "' . $depName . '" for Asset "' . $asset->getName() . '"');
			}

			$assets[$depName] = $dep;

			if (!$recursively)
			{
				continue;
			}

			$parentDeps = $this->getDependenciesForAsset($dep, true, $recursionRoot);
			$assets     = array_replace($assets, $parentDeps);
		}

		return $assets;
	}

	/**
	 * Sort assets by its weight
	 *
	 * @param   WebAssetItem[]  $assets  Array of assets to sort
	 *
	 * @return  WebAssetItem[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function sortAssetsByWeight(array $assets): array
	{
		uasort(
			$assets,
			function($a, $b)
			{
				/** @var WebAssetItem $a */
				/** @var WebAssetItem $b */
				if ($a->getWeight() === $b->getWeight())
				{
					return 0;
				}

				return $a->getWeight() > $b->getWeight() ? 1 : -1;
			}
		);

		return $assets;
	}

	/**
	 * Prepare new Asset instance.
	 *
	 * @param   string  $name  Asset name
	 * @param   array   $data  Asset information
	 *
	 * @return  WebAssetItem
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function createAsset(string $name, array $data = []): WebAssetItem
	{
		return new WebAssetItem($name, $data);
	}

	/**
	 * Register new file with Asset(s) info
	 *
	 * @param   string  $path  Relative path
	 *
	 * @return  self
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function addRegistryFile(string $path): self
	{
		$path = Path::clean($path);

		if (isset($this->dataFiles[$path]))
		{
			return $this;
		}

		$this->dataFiles[$path] = is_file(JPATH_ROOT . '/' . $path) ? static::REGISTRY_FILE_NEW : static::REGISTRY_FILE_INVALID;

		return $this;
	}

	/**
	 * Parse registered files
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function parseRegistryFiles()
	{
		// Filter new asset data files and parse each
		$constantIsNew = static::REGISTRY_FILE_NEW;
		$files = array_filter(
			$this->dataFiles,
			function($state) use ($constantIsNew)
			{
				return $state === $constantIsNew;
			}
		);

		if (!$files)
		{
			return;
		}

		foreach (array_keys($files) as $path)
		{
			$this->parseRegistryFile($path);

			// Mark as parsed (not new)
			$this->dataFiles[$path] = static::REGISTRY_FILE_PARSED;
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
	 * @since   __DEPLOY_VERSION__
	 */
	protected function parseRegistryFile($path)
	{
		$data = file_get_contents(JPATH_ROOT . '/' . $path);
		$data = $data ? json_decode($data, true) : null;

		if (!$data)
		{
			throw new \RuntimeException('Asset data file "' . $path . '" is broken');
		}

		// Asset exists but empty, skip it silently
		if (empty($data['assets']))
		{
			return;
		}

		// Keep source info
		$assetSource = [
			'registryFile' => $path,
		];

		// Prepare WebAssetItem instances
		foreach ($data['assets'] as $item)
		{
			if (empty($item['name']))
			{
				throw new \RuntimeException('Asset data file "' . $path . '" contains incorrect asset defination');
			}

			$item['assetSource'] = $assetSource;
			$assetItem = $this->createAsset($item['name'], $item);
			$this->addAsset($assetItem);
		}
	}

	/**
	 * Dump available assets to simple array, with some basic info
	 *
	 * @param   bool  $onlyActive  Return only active Assets
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function debugAssets(bool $onlyActive = false): array
	{
		// Update dependencies
		$this->updateDependency()->calculateWeightOfActiveAssets();

		$assets = $onlyActive ? $this->getActiveAssets() : $this->assets;
		$assets = $this->sortAssetsByWeight($assets);
		$result = [];

		foreach ($assets as $asset)
		{
			$result[$asset->getName()] = [
				'name'   => $asset->getName(),
				'deps'   => implode(', ', $asset->getDependencies()),
				'state'  => $asset->getState(),
				'weight' => $asset->getWeight(),
			];
		}

		return $result;
	}

	/**
	 * Whether append asset version to asset path
	 *
	 * @param   bool  $useVersioning  Boolean flag
	 *
	 * @return  self
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function useVersioning(bool $useVersioning): self
	{
		$this->useVersioning = $useVersioning;

		return $this;
	}
}
