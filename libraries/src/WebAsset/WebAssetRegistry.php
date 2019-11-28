<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\WebAsset\Exception\UnknownAssetException;

/**
 * Web Asset Registry class
 *
 * @since  4.0.0
 */
class WebAssetRegistry implements WebAssetRegistryInterface
{
	/**
	 * Files with Asset info. File path should be relative.
	 *
	 * @var    array
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
	 * Get an existing Asset from a registry, by asset name.
	 *
	 * @param   string  $name  Asset name
	 *
	 * @return  WebAssetItem
	 *
	 * @throws  UnknownAssetException  When Asset cannot be found
	 *
	 * @since   4.0.0
	 */
	public function get(string $name): WebAssetItemInterface
	{
		// Check if any new file was added
		$this->parseRegistryFiles();

		if (empty($this->assets[$name]))
		{
			throw new UnknownAssetException($name);
		}

		return $this->assets[$name];
	}

	/**
	 * Add Asset to registry of known assets
	 *
	 * @param   WebAssetItemInterface  $asset  Asset instance
	 *
	 * @return  self
	 *
	 * @since   4.0.0
	 */
	public function add(WebAssetItemInterface $asset): WebAssetRegistryInterface
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
	 * @since   4.0.0
	 */
	public function remove(string $name): WebAssetRegistryInterface
	{
		unset($this->assets[$name]);

		return $this;
	}

	/**
	 * Check whether the asset exists in the registry.
	 *
	 * @param   string  $name  Asset name
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	public function exists(string $name): bool
	{
		return !empty($this->assets[$name]);
	}

	/**
	 * Prepare new Asset instance.
	 *
	 * @param   string  $name  Asset name
	 * @param   array   $data  Asset information
	 *
	 * @return  WebAssetItem
	 *
	 * @since   4.0.0
	 */
	public function createAsset(string $name, array $data = []): WebAssetItem
	{
		$nameSpace = \array_key_exists('namespace', $data) ? $data['namespace'] : __NAMESPACE__ . '\\AssetItem';
		$className = \array_key_exists('class', $data) ? $data['class'] : null;

		if ($className && class_exists($nameSpace . '\\' . $className))
		{
			$className = $nameSpace . '\\' . $className;

			return new $className($name, $data);
		}

		return new WebAssetItem($name, $data);
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

		if (isset($this->dataFilesNew[$path]) || isset($this->dataFilesParsed[$path]))
		{
			return $this;
		}

		if (is_file(JPATH_ROOT . '/' . $path))
		{
			$this->dataFilesNew[$path] = $path;
		}

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
		if (!$this->dataFilesNew)
		{
			return;
		}

		foreach ($this->dataFilesNew as $path)
		{
			$this->parseRegistryFile($path);

			// Mark as parsed (not new)
			unset($this->dataFilesNew[$path]);
			$this->dataFilesParsed[$path] = $path;
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

		$namespace = \array_key_exists('namespace', $data) ? $data['namespace'] : null;

		// Prepare WebAssetItem instances
		foreach ($data['assets'] as $item)
		{
			if (empty($item['name']))
			{
				throw new \RuntimeException('Asset data file "' . $path . '" contains incorrect asset definition');
			}

			// Inheriting the Namespace
			if ($namespace && !\array_key_exists('namespace', $item))
			{
				$item['namespace'] = $namespace;
			}

			$item['assetSource'] = $assetSource;
			$assetItem = $this->createAsset($item['name'], $item);
			$this->add($assetItem);
		}
	}
}
