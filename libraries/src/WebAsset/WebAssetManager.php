<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Event\WebAsset\WebAssetRegistryAssetChanged;
use Joomla\CMS\WebAsset\Exception\InvalidActionException;
use Joomla\CMS\WebAsset\Exception\UnknownAssetException;
use Joomla\CMS\WebAsset\Exception\UnsatisfiedDependencyException;

/**
 * Web Asset Manager class
 *
 * @method WebAssetManager registerStyle(WebAssetItem|string $asset, string $uri = '', $options = [], $attributes = [], $dependencies = [])
 * @method WebAssetManager useStyle($name)
 * @method WebAssetManager disableStyle($name)
 *
 * @method WebAssetManager registerScript(WebAssetItem|string $asset, string $uri = '', $options = [], $attributes = [], $dependencies = [])
 * @method WebAssetManager useScript($name)
 * @method WebAssetManager disableScript($name)
 *
 * @method WebAssetManager registerPreset(WebAssetItem|string $asset, string $uri = '', $options = [], $attributes = [], $dependencies = [])
 * @method WebAssetManager usePreset($name)
 * @method WebAssetManager disablePreset($name)
 *
 * @since  4.0.0
 */
class WebAssetManager implements WebAssetManagerInterface
{
	/**
	 * Mark inactive asset
	 *
	 * @var    integer
	 *
	 * @since  4.0.0
	 */
	const ASSET_STATE_INACTIVE = 0;

	/**
	 * Mark active asset. Just enabled, but WITHOUT dependency resolved
	 *
	 * @var    integer
	 *
	 * @since  4.0.0
	 */
	const ASSET_STATE_ACTIVE = 1;

	/**
	 * Mark active asset that is enabled as dependency to another asset
	 *
	 * @var    integer
	 *
	 * @since  4.0.0
	 */
	const ASSET_STATE_DEPENDENCY = 2;

	/**
	 * The WebAsset Registry instance
	 *
	 * @var    WebAssetRegistry
	 *
	 * @since  4.0.0
	 */
	protected $registry;

	/**
	 * A list of active assets (including their dependencies).
	 * Array of Name => State
	 *
	 * @var    array
	 *
	 * @since  4.0.0
	 */
	protected $activeAssets = [];

	/**
	 * Internal marker to check the manager state,
	 * to prevent use of the manager after an assets are rendered
	 *
	 * @var    boolean
	 *
	 * @since  4.0.0
	 */
	protected $locked = false;

	/**
	 * Internal marker to keep track when need to recheck dependencies
	 *
	 * @var    boolean
	 *
	 * @since  4.0.0
	 */
	protected $dependenciesIsActual = false;

	/**
	 * Class constructor
	 *
	 * @param   WebAssetRegistry  $registry  The WebAsset Registry instance
	 *
	 * @since   4.0.0
	 */
	public function __construct(WebAssetRegistry $registry)
	{
		$this->registry = $registry;

		// Listen to changes in the registry
		$this->registry->getDispatcher()->addListener(
			'onWebAssetRegistryChangedAssetOverride',
			function(WebAssetRegistryAssetChanged $event)
			{
				// If the changed asset are used
				if ($this->isAssetActive($event->getAssetType(), $event->getAsset()->getName()))
				{
					$this->dependenciesIsActual = false;
				}
			}
		);

		$this->registry->getDispatcher()->addListener(
			'onWebAssetRegistryChangedAssetRemove',
			function(WebAssetRegistryAssetChanged $event)
			{
				// If the changed asset are used
				if ($this->isAssetActive($event->getAssetType(), $event->getAsset()->getName()))
				{
					$this->dependenciesIsActual = false;
				}
			}
		);
	}

	/**
	 * Get associated registry instance
	 *
	 * @return   WebAssetRegistry
	 *
	 * @since  4.0.0
	 */
	public function getRegistry(): WebAssetRegistry
	{
		return $this->registry;
	}

	/**
	 * Adds support for magic method calls
	 *
	 * @param   string  $method     A method name
	 * @param   string  $arguments  An arguments for a method
	 *
	 * @return mixed
	 *
	 * @throws  \BadMethodCallException
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __call($method, $arguments)
	{
		if (0 === strpos($method, 'use'))
		{
			$type = strtolower(substr($method, 3));

			if (empty($arguments[0]))
			{
				throw new \BadMethodCallException('An asset name are required');
			}

			return $this->useAsset($type, $arguments[0]);
		}

		if (0 === strpos($method, 'disable'))
		{
			$type = strtolower(substr($method, 7));

			if (empty($arguments[0]))
			{
				throw new \BadMethodCallException('An asset name are required');
			}

			return $this->disableAsset($type, $arguments[0]);
		}

		if (0 === strpos($method, 'register'))
		{
			$type = strtolower(substr($method, 8));

			if (empty($arguments[0]))
			{
				throw new \BadMethodCallException('An asset instance or an asset name are required');
			}

			return $this->registerAsset($type, ...$arguments);
		}

		throw new \BadMethodCallException(sprintf('Undefined method %s in class %s', $method, get_class($this)));
	}

	/**
	 * Enable an asset item to be attached to a Document
	 *
	 * @param   string  $type  The asset type, script or style
	 * @param   string  $name  The asset name
	 *
	 * @return self
	 *
	 * @throws  UnknownAssetException  When Asset cannot be found
	 * @throws  InvalidActionException When the Manager already attached to a Document
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function useAsset(string $type, string $name): WebAssetManagerInterface
	{
		if ($this->locked)
		{
			throw new InvalidActionException('WebAssetManager are locked, you came late');
		}

		// Check whether asset exists
		$asset = $this->registry->get($type, $name);

		if (empty($this->activeAssets[$type]))
		{
			$this->activeAssets[$type] = [];
		}

		// Asset already enabled
		if (!empty($this->activeAssets[$type][$name]))
		{
			// Set state to active, in case it was ASSET_STATE_DEPENDENCY
			$this->activeAssets[$type][$name] = static::ASSET_STATE_ACTIVE;

			return $this;
		}

		$this->activeAssets[$type][$name] = static::ASSET_STATE_ACTIVE;

		// To re-check dependencies
		if ($asset->getDependencies())
		{
			$this->dependenciesIsActual = false;
		}

		return $this;
	}

	/**
	 * Deactivate an asset item, so it will not be attached to a Document
	 *
	 * @param   string  $type  The asset type, script or style
	 * @param   string  $name  The asset name
	 *
	 * @return  self
	 *
	 * @throws  UnknownAssetException  When Asset cannot be found
	 * @throws  InvalidActionException When the Manager already attached to a Document
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function disableAsset(string $type, string $name): WebAssetManagerInterface
	{
		if ($this->locked)
		{
			throw new InvalidActionException('WebAssetManager are locked, you came late');
		}

		// Check whether asset exists
		$this->registry->get($type, $name);

		unset($this->activeAssets[$type][$name]);

		// To re-check dependencies
		$this->dependenciesIsActual = false;

		return $this;
	}

	/**
	 * Get a state for the Asset
	 *
	 * @param   string  $type  The asset type, script or style
	 * @param   string  $name  The asset name
	 *
	 * @return  integer
	 *
	 * @throws  UnknownAssetException  When Asset cannot be found
	 *
	 * @since  4.0.0
	 */
	public function getAssetState(string $type, string $name): int
	{
		// Check whether asset exists first
		$this->registry->get($type, $name);

		// Make sure that all dependencies are active
		if (!$this->dependenciesIsActual)
		{
			$this->enableDependencies();
		}

		if (!empty($this->activeAssets[$type][$name]))
		{
			return $this->activeAssets[$type][$name];
		}

		return static::ASSET_STATE_INACTIVE;
	}

	/**
	 * Check whether the asset are enabled
	 *
	 * @param   string  $type  The asset type, script or style
	 * @param   string  $name  The asset name
	 *
	 * @return  boolean
	 *
	 * @throws  UnknownAssetException  When Asset cannot be found
	 *
	 * @since  4.0.0
	 */
	public function isAssetActive(string $type, string $name): bool
	{
		return $this->getAssetState($type, $name) !== static::ASSET_STATE_INACTIVE;
	}

	/**
	 * Check whether the asset exists in the registry.
	 *
	 * @param   string  $type  Asset type, script or style
	 * @param   string  $name  Asset name
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function assetExists(string $type, string $name): bool
	{
		return $this->registry->exists($type, $name);
	}

	/**
	 * Register a new asset.
	 * Allow to register WebAssetItem instance in the registry, by call registerAsset($type, $assetInstance)
	 * Or create an asset on fly (from name and Uri) and register in the registry, by call registerAsset($type, $assetName, $uri, $options ....)
	 *
	 * @param   string               $type          The asset type, script or style
	 * @param   WebAssetItem|string  $asset         The asset name or instance to register
	 * @param   string               $uri           The URI for the asset
	 * @param   array                $options       Additional options for the asset
	 * @param   array                $attributes    Attributes for the asset
	 * @param   array                $dependencies  Asset dependencies
	 *
	 * @return  self
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function registerAsset(string $type, $asset, string $uri = '', array $options = [], array $attributes = [], array $dependencies = [])
	{
		if ($asset instanceof WebAssetItemInterface)
		{
			$this->registry->add($type, $asset);
		}
		elseif (is_string($asset))
		{
			$options['type'] = $type;
			$assetInstance = $this->registry->createAsset($asset, $uri, $options, $attributes, $dependencies);
			$this->registry->add($type, $assetInstance);
		}
		else
		{
			throw new \BadMethodCallException('The $asset variable should be either WebAssetItemInterface or a string of the asset name');
		}

		return $this;
	}

	/**
	 * Get all active assets
	 *
	 * @param   string  $type  The asset type, script or style
	 * @param   bool    $sort  Whether need to sort the assets to follow the dependency Graph
	 *
	 * @return  WebAssetItem[]
	 *
	 * @throws  UnknownAssetException  When Asset cannot be found
	 * @throws  UnsatisfiedDependencyException When Dependency cannot be found
	 *
	 * @since  4.0.0
	 */
	public function getAssets(string $type, bool $sort = false): array
	{
		// Make sure that all dependencies are active
		if (!$this->dependenciesIsActual)
		{
			$this->enableDependencies();
		}

		if (empty($this->activeAssets[$type]))
		{
			return [];
		}

		if ($sort)
		{
			$assets = $this->calculateOrderOfActiveAssets($type);
		}
		else
		{
			$assets = [];

			foreach (array_keys($this->activeAssets[$type]) as $name)
			{
				$assets[$name] = $this->registry->get($type, $name);
			}
		}

		return $assets;
	}

	/**
	 * Lock the manager to prevent further modifications
	 *
	 * @return self
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function lock(): self
	{
		$this->locked = true;

		return $this;
	}

	/**
	 * Update Dependencies state for all active Assets or only for given
	 *
	 * @param   string        $type   The asset type, script or style
	 * @param   WebAssetItem  $asset  The asset instance to which need to enable dependencies
	 *
	 * @return  self
	 *
	 * @since  4.0.0
	 */
	protected function enableDependencies(string $type = null, WebAssetItem $asset = null): self
	{
		if ($asset)
		{
			// Get all dependencies of given asset recursively
			$allDependencies = $this->getDependenciesForAsset($type, $asset, true);

			foreach ($allDependencies as $depType => $depItems)
			{
				foreach ($depItems as $depItem)
				{
					// Set dependency state only when it is inactive, to keep a manually activated Asset in their original state
					if (empty($this->activeAssets[$depType][$depItem->getName()]))
					{
						$this->activeAssets[$depType][$depItem->getName()] = static::ASSET_STATE_DEPENDENCY;
					}
				}
			}
		}
		else
		{
			// Re-Check for dependencies for all active assets
			// Firstly, filter out only active assets
			foreach ($this->activeAssets as $type => $activeAsset)
			{
				$this->activeAssets[$type] = array_filter(
					$activeAsset,
					function ($state) {
						return $state === WebAssetManager::ASSET_STATE_ACTIVE;
					}
				);
			}

			// Secondary, check for dependencies of each active asset
			// This need to be separated from previous step because we may have "cross type" dependency
			foreach ($this->activeAssets as $type => $activeAsset)
			{
				foreach (array_keys($activeAsset) as $name)
				{
					$asset = $this->registry->get($type, $name);
					$this->enableDependencies($type, $asset);
				}
			}

			$this->dependenciesIsActual = true;
		}

		return $this;
	}

	/**
	 * Calculate weight of active Assets, by its Dependencies
	 *
	 * @param   string  $type  The asset type, script or style
	 *
	 * @return  WebAssetItem[]
	 *
	 * @since  4.0.0
	 */
	protected function calculateOrderOfActiveAssets($type): array
	{
		// See https://en.wikipedia.org/wiki/Topological_sorting#Kahn.27s_algorithm
		$graphOrder    = [];
		$activeAssets  = $this->getAssets($type, false);

		// Get Graph of Outgoing and Incoming connections
		$connectionsGraph = $this->getConnectionsGraph($activeAssets);
		$graphOutgoing    = $connectionsGraph['outgoing'];
		$graphIncoming    = $connectionsGraph['incoming'];

		// Make a copy to be used during weight processing
		$graphIncomingCopy = $graphIncoming;

		// Find items without incoming connections
		$emptyIncoming = array_keys(
			array_filter(
				$graphIncoming,
				function ($el) {
					return !$el;
				}
			)
		);

		// Loop through, and sort the graph
		while ($emptyIncoming)
		{
			// Add the node without incoming connection to the result
			$item = array_shift($emptyIncoming);
			$graphOrder[] = $item;

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

		// Sync Graph order with FIFO order
		$fifoWeights      = [];
		$graphWeights     = [];
		$requestedWeights = [];

		foreach (array_keys($this->activeAssets[$type]) as $index => $name)
		{
			$fifoWeights[$name] = $index * 10 + 10;
		}

		foreach (array_reverse($graphOrder) as $index => $name)
		{
			$graphWeights[$name]     = $index * 10 + 10;
			$requestedWeights[$name] = $activeAssets[$name]->getOption('weight') ?: $fifoWeights[$name];
		}

		// Try to set a requested weight, or make it close as possible to requested, but keep the Graph order
		while ($requestedWeights)
		{
			$item   = key($requestedWeights);
			$weight = array_shift($requestedWeights);

			// Skip empty items
			if ($weight === null)
			{
				continue;
			}

			// Check the predecessors (Outgoing vertexes), the weight cannot be lighter than the predecessor have
			$topBorder = $weight - 1;

			if (!empty($graphOutgoing[$item]))
			{
				$prevWeights = [];

				foreach ($graphOutgoing[$item] as $pItem)
				{
					$prevWeights[] = $graphWeights[$pItem];
				}

				$topBorder = max($prevWeights);
			}

			// Calculate a new weight
			$newWeight = $weight > $topBorder ? $weight : $topBorder + 1;

			// If a new weight heavier than existing, then we need to update all incoming connections (children)
			if ($newWeight > $graphWeights[$item] && !empty($graphIncomingCopy[$item]))
			{
				// Sort Graph of incoming by actual position
				foreach ($graphIncomingCopy[$item] as $incomingItem)
				{
					// Set a weight heavier than current, then this node to be processed in next iteration
					if (empty($requestedWeights[$incomingItem]))
					{
						$requestedWeights[$incomingItem] = $graphWeights[$incomingItem] + $newWeight;
					}
				}
			}

			// Set a new weight
			$graphWeights[$item] = $newWeight;
		}

		asort($graphWeights);

		// Get Assets in calculated order
		$resultAssets  = [];

		foreach (array_keys($graphWeights) as $name)
		{
			$resultAssets[$name] = $activeAssets[$name];
		}

		return $resultAssets;
	}

	/**
	 * Build Graph of Outgoing and Incoming connections for given assets.
	 *
	 * @param   WebAssetItem[]  $assets  Asset instances
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	protected function getConnectionsGraph(array $assets): array
	{
		$graphOutgoing = [];
		$graphIncoming = [];

		foreach ($assets as $asset)
		{
			$name = $asset->getName();

			// Outgoing nodes
			$graphOutgoing[$name] = [];

			foreach ($asset->getDependencies() as $depName)
			{
				// Skip cross-dependency "depname#type" case, the dependencies calculated per type, separately
				if (strrpos($depName, '#'))
				{
					continue;
				}

				$graphOutgoing[$name][$depName] = $depName;
			}

			// Incoming nodes
			if (!array_key_exists($name, $graphIncoming))
			{
				$graphIncoming[$name] = [];
			}

			foreach ($asset->getDependencies() as $depName)
			{
				// Skip cross-dependency "depname#type" case, the dependencies calculated per type, separately
				if (strrpos($depName, '#'))
				{
					continue;
				}

				$graphIncoming[$depName][$name] = $name;
			}
		}

		return [
			'outgoing' => $graphOutgoing,
			'incoming' => $graphIncoming,
		];
	}

	/**
	 * Return dependencies for Asset as array of WebAssetItem objects
	 *
	 * @param   string        $type           The asset type, script or style
	 * @param   WebAssetItem  $asset          Asset instance
	 * @param   boolean       $recursively    Whether to search for dependency recursively
	 * @param   string        $recursionType  The type of initial item to prevent loop
	 * @param   WebAssetItem  $recursionRoot  Initial item to prevent loop
	 *
	 * @return  array
	 *
	 * @throws  UnsatisfiedDependencyException When Dependency cannot be found
	 *
	 * @since   4.0.0
	 */
	protected function getDependenciesForAsset(
		string $type,
		WebAssetItem $asset,
		$recursively = false,
		string $recursionType = null,
		WebAssetItem $recursionRoot = null
	): array
	{
		$assets        = [];
		$recursionRoot = $recursionRoot ?? $asset;
		$recursionType = $recursionType ?? $type;

		foreach ($asset->getDependencies() as $depName)
		{
			$depType = $type;

			// Check for cross-dependency "depname#type" case
			if ($pos = strrpos($depName, '#'))
			{
				$depType = substr($depName, $pos + 1);
				$depName = substr($depName, 0, $pos);
			}

			// Skip already loaded in recursion
			if ($recursionRoot->getName() === $depName && $recursionType === $depType)
			{
				continue;
			}

			if (!$this->registry->exists($depType, $depName))
			{
				throw new UnsatisfiedDependencyException(
					sprintf('Unsatisfied dependency "%s" for an asset "%s" of type "%s"', $depName, $asset->getName(), $depType)
				);
			}

			$dep = $this->registry->get($depType, $depName);

			$assets[$depType][$depName] = $dep;

			if (!$recursively)
			{
				continue;
			}

			$parentDeps = $this->getDependenciesForAsset($depType, $dep, true, $recursionType, $recursionRoot);
			$assets     = array_replace_recursive($assets, $parentDeps);
		}

		return $assets;
	}
}
