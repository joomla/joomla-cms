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
 * Web Asset Manager class
 *
 * @since  __DEPLOY_VERSION__
 */
class WebAssetManager implements DispatcherAwareInterface
{
	use DispatcherAwareTrait;

	/**
	 * The WebAsset Registry instance
	 *
	 * @var    WebAssetRegistry
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $registry;

	/**
	 * A list of active assets (including their dependencies).
	 * Array of Name => State
	 *
	 * @var    array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $activeAssets = [];

	/**
	 * Whether append asset version to asset path
	 *
	 * @var    bool
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $useVersioning = true;

	/**
	 * Class constructor
	 *
	 * @param   WebAssetRegistry  $registry   The WebAsset Registry instance
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(WebAssetRegistry $registry)
	{
		$this->registry = $registry;

		$this->setDispatcher($this->registry->getDispatcher());
	}

	/**
	 * Get associated registry instance
	 *
	 * @return   WebAssetRegistry
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getRegistry(): WebAssetRegistry
	{
		return $this->registry;
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
		$asset = $this->registry->getAsset($name);

		if (!$asset)
		{
			throw new \RuntimeException('Asset "' . $name . '" does not exist');
		}

		// Asset already enabled
		if (!empty($this->activeAssets[$name]))
		{
			// Set state to active, in case it was ASSET_STATE_DEPENDENCY
			$this->activeAssets[$name] = WebAssetItem::ASSET_STATE_ACTIVE;

			return $this;
		}

		$this->activeAssets[$name] = WebAssetItem::ASSET_STATE_ACTIVE;

		$this->enableDependencies($asset);

		return $this;
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
		unset($this->activeAssets[$name]);

		// @TODO: disable dependencies

		return $this;
	}

	/**
	 * Get a state for the Asset
	 *
	 * @param   string  $name  The asset name
	 *
	 * @return  int
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getAssetState(string $name): int
	{
		if (!empty($this->activeAssets[$name]))
		{
			return $this->activeAssets[$name];
		}

		return WebAssetItem::ASSET_STATE_INACTIVE;
	}

	/**
	 * Update Dependencies state for all active Assets or only for given
	 *
	 * @param   WebAssetItem  $asset  The asset name
	 *
	 * @return  self
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function enableDependencies(WebAssetItem $asset = null): self
	{
		if ($asset)
		{
			foreach ($this->getDependenciesForAsset($asset, true) as $depItem)
			{
				// Set dependency state only when it is inactive, to keep a manually activated Asset in their original state
				if (empty($this->activeAssets[$depItem->getName()]))
				{
					$this->activeAssets[$depItem->getName()] = WebAssetItem::ASSET_STATE_DEPENDENCY;
				}
			}
		}
		else
		{
			// @TODO: update Dependencies for all active assets
		}

		return $this;
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
		// Trigger the event
//		$event = AbstractEvent::create(
//			'onWebAssetBeforeAttach',
//			[
//				'eventClass' => 'Joomla\\CMS\\Event\\WebAsset\\WebAssetBeforeAttachEvent',
//				'subject'  => $this,
//				'document' => $doc,
//			]
//		);
//		$this->getDispatcher()->dispatch($event->getName(), $event);

		// Resolve an Order of Assets and their Dependencies
		$assets = $this->calculateOrderOfActiveAssets();

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
	 * Calculate weight of active Assets, by its Dependencies
	 *
	 * @return  WebAssetItem[]
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function calculateOrderOfActiveAssets(): array
	{
		// See https://en.wikipedia.org/wiki/Topological_sorting#Kahn.27s_algorithm
		$result        = [];
		$graphOutgoing = [];
		$graphIncoming = [];
		$activeAssets  = [];
var_dump($this->activeAssets);
		foreach (array_keys($this->activeAssets) as $name)
		{
			$activeAssets[$name] = $this->registry->getAsset($name);
		}

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

var_dump(array_reverse($result));

		// Get Assets in calculated order
		$resultAssets = [];
		foreach (array_reverse($result) as $index => $name)
		{
			//$activeAssets[$name]->setWeight($index + 1);
			$resultAssets[$name] = $activeAssets[$name];
		}

		return $resultAssets;
	}

	/**
	 * Return dependencies for Asset as array of WebAssetItem objects
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

			$dep = $this->registry->getAsset($depName);

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

	/**
	 * Dump available assets to simple array, with some basic info
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function debugAssets(): array
	{
		// Update dependencies
		$assets = $this->calculateOrderOfActiveAssets();
		$result = [];

		foreach ($assets as $asset)
		{
			$result[$asset->getName()] = [
				'name'   => $asset->getName(),
				'deps'   => implode(', ', $asset->getDependencies()),
				'state'  => $this->getAssetState($asset->getName()),
			];
		}

		return $result;
	}
}
