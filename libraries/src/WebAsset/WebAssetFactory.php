<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;

/**
 * Web Asset Factory class
 *
 * @since  __DEPLOY_VERSION__
 */
class WebAssetFactory
{
	/**
	 * Mark the new data file
	 *
	 * @var integer
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	const DATAFILE_NEW = 1;

	/**
	 * Mark already parsed data file
	 *
	 * @var integer
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	const DATAFILE_PARSED = 2;

	/**
	 * Files with Asset info. File path should be relative.
	 *
	 * @var    array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $dataFiles = array();

	/**
	 * Registry of available Assets
	 *
	 * @var array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $assets = array();

	/**
	 * Weight of the most heavier and active asset
	 *
	 * @var float
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $lastItemWeight = 1;

	/**
	 * Class constructor
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct()
	{
		$this->searchForDataFiles();
	}

	/**
	 * Get an existing Asset from a registry, by asset name.
	 * Return asset object or false if asset does not exists.
	 *
	 * @param   string  $name  Asset name
	 *
	 * @return  WebAssetItem|bool
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getAsset($name)
	{
		// Check if there any new data file was added
		$this->parseDataFiles();

		if (!empty($this->assets[$name]))
		{
			return $this->assets[$name];
		}

		return false;
	}

	/**
	 * Search for all active assets.
	 *
	 * @return  WebAssetItem[]  Array with active assets
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getActiveAssets()
	{
		$assets = array_filter(
			$this->assets,
			function($asset)
			{
				return $asset->isActive();
			}
		);

		// Order them by weight and return
		return $this->sortByWeight($assets);
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
	public function getAssetsByState($state = WebAssetItem::ASSET_STATE_ACTIVE)
	{
		$assets = array_filter(
			$this->assets,
			function($asset) use ($state)
			{
				return $asset->getState() === $state;
			}
		);

		// Order them by weight and return
		return $this->sortByWeight($assets);
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
	public function addAsset(WebAssetItem $asset)
	{
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
	public function removeAsset($name)
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
	 * @param   string    $name   Asset name
	 * @param   integer   $state  New state
	 *
	 * @return  self
	 *
	 * @throws  \RuntimeException if asset with given name does not exists
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setAssetState($name, $state = WebAssetItem::ASSET_STATE_ACTIVE)
	{
		$asset = $this->getAsset($name);

		if (!$asset)
		{
			throw new \RuntimeException('Asset "' . $name . '" do not exists');
		}

		// Asset already has the requested state
		if ($asset->getState() === $state)
		{
			return $this;
		}

		// Change state
		$asset->setState($state);

		// Update last weight, to keep an order of enabled items
		if ($asset->isActive())
		{
			$this->lastItemWeight = $this->lastItemWeight + 1;
			$asset->setWeight($this->lastItemWeight);
		}

		return $this;
	}

	/**
	 * Activate the Asset item
	 *
	 * @param $name
	 *
	 * @return self
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function enableAsset($name)
	{
		return $this->setAssetState($name, WebAssetItem::ASSET_STATE_ACTIVE);
	}

	/**
	 * Deactivate the Asset item
	 *
	 * @param $name
	 *
	 * @return self
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function disableAsset($name)
	{
		return $this->setAssetState($name, WebAssetItem::ASSET_STATE_INACTIVE);
	}

	/**
	 * Attach an active assets to the Document
	 *
	 * @param   HtmlDocument  $doc  Document for attach StyleSheet/JavaScript
	 *
	 * @return  self
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function attach(HtmlDocument $doc)
	{
		//$app = Factory::getApplication();

		// Resolve Dependency
		$this->resolveDependency();

		// Trigger the event
		//$app->triggerEvent('onBeforeAttachWebAsset', array($this));

		// Attach an active assets do the document
		$assets = $this->getActiveAssets();

		var_dump($assets);

		return $this;
	}

	/**
	 * Resolve Dependency for just added assets
	 *
	 * @return  self
	 *
	 * @throws  \RuntimeException When Dependency cannot be resolved
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function resolveDependency()
	{
		$assets = $this->getAssetsByState(WebAssetItem::ASSET_STATE_ACTIVE);

		foreach ($assets as $asset)
		{
			$this->resolveItemDependency($asset);
			$asset->setState(WebAssetItem::ASSET_STATE_RESOLVED);
		}

		return $this;
	}

	/**
	 * Resolve Dependency for given asset
	 *
	 * @param   WebAssetItem  $asset  Asset instance
	 *
	 * @return  self
	 *
	 * @throws  \RuntimeException When Dependency cannot be resolved
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function resolveItemDependency(WebAssetItem $asset)
	{
		foreach ($this->getDependenciesForAsset($asset) as $depItem)
		{
			$oldState = $depItem->isActive();

			// Make active
			if (!$oldState)
			{
				$depItem->setState(WebAssetItem::ASSET_STATE_DEPENDANCY);
			}

			// Calculate weight, make it a bit lighter
			$depWeight   = $depItem->getWeight();
			$assetWeight = $asset->getWeight();

			$depWeight = $depWeight === 0 ? $this->lastItemWeight : $depWeight;
			$weight    = $depWeight > $assetWeight ? $assetWeight : $depWeight;
			$weight    = $weight - 0.01;

			$depItem->setWeight($weight);

			// Prevent duplicated work if Dependency already was activated
			if (!$oldState)
			{
				$this->resolveItemDependency($depItem);
			}
		}

		return $this;
	}

	/**
	 * Return dependancy for Asset as array of AssetItem objects
	 *
	 * @param   WebAssetItem  $asset  Asset instance
	 *
	 * @return  WebAssetItem[]
	 *
	 * @throws  \RuntimeException When Dependency cannot be found
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getDependenciesForAsset(WebAssetItem $asset)
	{
		$assets = array();

		foreach ($asset->getDependencies() as $depName)
		{
			$dep = $this->getAsset($depName);

			if (!$dep)
			{
				throw new \RuntimeException('Cannot find Dependency "' . $depName . '" for Asset "' . $asset->getName() . '"');
			}

			$assets[$depName] = $dep;
		}

		return $assets;
	}

	/**
	 * Sort assets by it`s weight
	 *
	 * @param   WebAssetItem[]  $assets  Linked array of assets
	 * @param   bool            $ask     Order direction: true for ASC and false for DESC
	 *
	 * @return  WebAssetItem[]
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function sortByWeight(array $assets, $ask = true)
	{
		uasort(
			$assets,
			function($a, $b) use ($ask)
			{
				if ($a->getWeight() === $b->getWeight())
				{
					return 0;
				}

				if ($ask)
				{
					return $a->getWeight() > $b->getWeight() ? 1 : -1;
				}
				else
				{
					return $a->getWeight() > $b->getWeight() ? -1 : 1;
				}
			}
		);

		return $assets;
	}

	/**
	 * Prepare new Asset instance.
	 *
	 * @param   string  $name         Asset name
	 * @param   array   $data         Asset information
	 *
	 * @return  WebAssetItem
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function createAsset($name, array $data = array())
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
	public function registerDataFile($path)
	{
		$path = Path::clean($path);

		if (is_file(JPATH_ROOT . '/' . $path) && !isset($this->dataFiles[$path]))
		{
			$this->dataFiles[$path] = static::DATAFILE_NEW;
		}

		return $this;
	}

	/**
	 * Search for joomla.asset.json files in the Media folder, and templates.
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function searchForDataFiles()
	{
		$files = array_merge(
			glob(JPATH_ROOT . '/media/*/joomla.asset.json', GLOB_NOSORT), // Search extension assets, in /media
			glob(JPATH_BASE . '/templates/*/joomla.asset.json', GLOB_NOSORT) // Search the template assets
		);

		if (empty($files))
		{
			return;
		}

		foreach ($files as $file)
		{
			$path = preg_replace('#^' . JPATH_ROOT . '/#', '', $file);
			$this->registerDataFile($path);
		}
	}

	/**
	 * Parse registered data files
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function parseDataFiles()
	{
		// Filter new asset data files and parse each
		$constantIsNew = static::DATAFILE_NEW;
		$files = array_filter(
			$this->dataFiles,
			function($state) use ($constantIsNew)
			{
				return $state === $constantIsNew;
			}
		);

		foreach (array_keys($files) as $path)
		{
			$this->parseDataFile($path);

			// Mark as parsed (not new)
			$this->dataFiles[$path] = static::DATAFILE_PARSED;
		}
	}

	/**
	 * Parse data file
	 *
	 * @param   string  $path  Relative path to the data file
	 *
	 * @return  void
	 *
	 * @throws  \RuntimeException If file is empty or invalid
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function parseDataFile($path)
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
		$assetSource = $data;
		$assetSource['dataFile'] = $path;
		unset($assetSource['assets']);

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
}
