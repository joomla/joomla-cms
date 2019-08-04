<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset;

defined('JPATH_PLATFORM') or die;

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
	 * @example of registry file:
	 *
	 * {
	 *		"title" : "Example",
	 *		"name"  : "com_example",
	 *		"author": "Joomla! CMS",
	 *		"assets": [
	 *			{
	 *				"name": "library1",
	 *				"version": "3.5.0",
	 * 				"type":  "script",
	 *				"uri": "com_example/library1.min.js"
	 *			},
	 *			{
	 *				"name": "library2",
	 *				"version": "3.5.0",
	 * 				"type":  "script",
	 *				"uri": "com_example/library2.min.js",
	 *				"dependencies": [
	 *					"core",
	 *					"library1"
	 *				],
	 *				"attribute": {
	 *					"attr-name": "attr value"
	 *					"defer": true
	 *				}
	 *			},
	 * 			{
	 *				"name": "library1",
	 *				"version": "3.5.0",
	 * 				"type":  "style",
	 *				"uri": "com_example/library1.min.css"
	 * 				"attribute": {
	 *					"media": "all"
	 *				}
	 *			},
	 * 			{
	 *				"name": "library1",
	 * 				"type":  "preset",
	 * 				"dependencies": {
	 *					"library1#style",
	 * 					"library1#script"
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

		if (empty($this->assets[$type][$name]))
		{
			throw new UnknownAssetException(sprintf('There is no a "%s" asset of a "%s" type in the registry.', $name, $type));
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

		if (!array_key_exists($type, $this->assets))
		{
			$this->assets[$type] = [];
		}

		$this->assets[$type][$asset->getName()] = $asset;

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
		unset($this->assets[$type][$name]);

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
		return !empty($this->assets[$type][$name]);
	}

	/**
	 * Prepare new Asset instance.
	 *
	 * @param   string  $name          The asset name
	 * @param   string  $uri           The URI for the asset
	 * @param   array   $options       Additional options for the asset
	 * @param   array   $attributes    Attributes for the asset
	 * @param   array   $dependencies  Asset dependencies
	 *
	 * @return  WebAssetItem
	 *
	 * @since   4.0.0
	 */
	public function createAsset(
		string $name,
		string $uri = null,
		array $options = [],
		array $attributes = [],
		array $dependencies = []
	): WebAssetItem
	{
		$nameSpace = array_key_exists('namespace', $options) ? $options['namespace'] : __NAMESPACE__ . '\\AssetItem';
		$className = array_key_exists('class', $options) ? $options['class'] : null;

		if ($className && class_exists($nameSpace . '\\' . $className))
		{
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
			throw new \RuntimeException(sprintf('Asset registry file "%s" are broken', $path));
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

		$namespace = array_key_exists('namespace', $data) ? $data['namespace'] : null;

		// Prepare WebAssetItem instances
		foreach ($data['assets'] as $i => $item)
		{
			if (empty($item['name']))
			{
				throw new \RuntimeException(
					sprintf('Fail parsing of asset registry file "%s". Property "name" are required for asset index "%s"', $path, $i)
				);
			}

			if (empty($item['type']))
			{
				throw new \RuntimeException(
					sprintf('Fail parsing of asset registry file "%s". Property "type" are required for asset "%s"', $path, $item['name'])
				);
			}

			$item['type'] = strtolower($item['type']);

			$name    = $item['name'];
			$uri     = $item['uri'] ?? '';
			$options = $item;
			$options['assetSource'] = $assetSource;

			unset($options['uri'], $options['name']);

			// Inheriting the Namespace
			if ($namespace && !array_key_exists('namespace', $options))
			{
				$options['namespace'] = $namespace;
			}

			$assetItem = $this->createAsset($name, $uri, $options);
			$this->add($item['type'], $assetItem);
		}
	}
}
