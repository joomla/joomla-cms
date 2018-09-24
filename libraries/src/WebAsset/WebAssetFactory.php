<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset;

defined('JPATH_PLATFORM') or die;

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
		$asset = new WebAssetItem($name, $data);

//		if (!empty($info['js']))
//		{
//			$asset->setJs((array) $info['js']);
//		}
//
//		if (!empty($info['css']))
//		{
//			$asset->setCss((array) $info['css']);
//		}
//
//		if (!empty($info['dependency']))
//		{
//			$asset->setDependency((array) $info['dependency']);
//		}
//
//		if (array_key_exists('versionAttach', $info))
//		{
//			$asset->versionAttach($info['versionAttach']);
//		}
//
//		if (!empty($info['attribute']) && is_array($info['attribute']))
//		{
//			foreach ($info['attribute'] as $file => $attributes)
//			{
//				$asset->setAttributes($file, $attributes);
//			}
//		}

		return $asset;
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
