<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\HTML\HTMLHelper;

/**
 * Web Asset Item class
 *
 * @since  4.0.0
 */
class WebAssetItem implements WebAssetItemInterface
{
	/**
	 * Asset name
	 *
	 * @var    string  $name
	 * @since  4.0.0
	 */
	protected $name;

	/**
	 * Asset version
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $version;

	/**
	 * The Asset source info, where the asset comes from.
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $assetSource;

	/**
	 * Item weight
	 *
	 * @var    float
	 *
	 * @since  4.0.0
	 */
	protected $weight = 0;

	/**
	 * List of JavaScript files, and its attributes.
	 * The key is file path, the value is array of attributes.
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $js = [];

	/**
	 * List of StyleSheet files, and its attributes
	 * The key is file path, the value is array of attributes.
	 *
	 * @var    array
	 * @since  4.0.0
	 */
	protected $css = [];

	/**
	 * Asset dependencies
	 *
	 * @var    string[]
	 * @since  4.0.0
	 */
	protected $dependencies = [];

	/**
	 * Internal use, to keep track of resolved paths
	 *
	 * @var    array
	 *
	 * @since  4.0.0
	 */
	protected $resolvedPaths = [];

	/**
	 * Class constructor
	 *
	 * @param   string  $name  The asset name
	 * @param   array   $data  The Asset information
	 *
	 * @since   4.0.0
	 */
	public function __construct(string $name, array $data = [])
	{
		$this->name        = $name;
		$this->version     = !empty($data['version']) ? $data['version'] : null;
		$this->assetSource = !empty($data['assetSource']) ? $data['assetSource'] : null;

		$attributes = empty($data['attribute']) ? [] : $data['attribute'];

		// Check for Scripts and StyleSheets, and their attributes
		if (!empty($data['js']))
		{
			foreach ($data['js'] as $js)
			{
				$this->js[$js] = empty($attributes[$js]) ? [] : $attributes[$js];
			}
		}

		if (!empty($data['css']))
		{
			foreach ($data['css'] as $css)
			{
				$this->css[$css] = empty($attributes[$css]) ? [] : $attributes[$css];
			}
		}

		if (!empty($data['dependencies']))
		{
			$this->dependencies = (array) $data['dependencies'];
		}

		if (!empty($data['weight']))
		{
			$this->weight = (float) $data['weight'];
		}
	}

	/**
	 * Return Asset name
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Return Asset version
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function getVersion()
	{
		return $this->version;
	}

	/**
	 * Return dependencies list
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public function getDependencies(): array
	{
		return $this->dependencies;
	}

	/**
	 * Set the desired weight for the Asset in Graph.
	 * Final weight will be calculated by AssetManager according to dependency Graph.
	 *
	 * @param   float  $weight  The asset weight
	 *
	 * @return  self
	 *
	 * @since   4.0.0
	 */
	public function setWeight(float $weight): WebAssetItemInterface
	{
		$this->weight = $weight;

		return $this;
	}

	/**
	 * Return the weight of the Asset.
	 *
	 * @return  float
	 *
	 * @since   4.0.0
	 */
	public function getWeight(): float
	{
		return $this->weight;
	}

	/**
	 * Get CSS files
	 *
	 * @param   boolean  $resolvePath  Whether need to search for a real paths
	 *
	 * @return array
	 *
	 * @since   4.0.0
	 */
	public function getStylesheetFiles($resolvePath = true): array
	{
		if ($resolvePath)
		{
			$files = [];

			foreach ($this->css as $path => $attr)
			{
				$resolved = $this->resolvePath($path, 'stylesheet');
				$fullPath = $resolved['fullPath'];

				if (!$fullPath)
				{
					// File not found, But we keep going ???
					continue;
				}

				$files[$fullPath] = $attr;
				$files[$fullPath]['__isExternal'] = $resolved['external'];
				$files[$fullPath]['__pathOrigin'] = $path;
			}

			return $files;
		}

		return $this->css;
	}

	/**
	 * Get JS files
	 *
	 * @param   boolean  $resolvePath  Whether we need to search for a real paths
	 *
	 * @return array
	 *
	 * @since   4.0.0
	 */
	public function getScriptFiles($resolvePath = true): array
	{
		if ($resolvePath)
		{
			$files = [];

			foreach ($this->js as $path => $attr)
			{
				$resolved = $this->resolvePath($path, 'script');
				$fullPath = $resolved['fullPath'];

				if (!$fullPath)
				{
					// File not found, But we keep going ???
					continue;
				}

				$files[$fullPath] = $attr;
				$files[$fullPath]['__isExternal'] = $resolved['external'];
				$files[$fullPath]['__pathOrigin'] = $path;
			}

			return $files;
		}

		return $this->js;
	}

	/**
	 * Resolve path
	 *
	 * @param   string  $path  The path to resolve
	 * @param   string  $type  The resolver method
	 *
	 * @return array
	 *
	 * @since  4.0.0
	 */
	protected function resolvePath(string $path, string $type): array
	{
		if (!empty($this->resolvedPaths[$path]))
		{
			return $this->resolvedPaths[$path];
		}

		if ($type !== 'script' && $type !== 'stylesheet')
		{
			throw new \UnexpectedValueException('Unexpected [type], expected "script" or "stylesheet"');
		}

		$file     = $path;
		$external = $this->isPathExternal($path);

		if (!$external)
		{
			// Get the file path
			$file = HTMLHelper::_(
				$type,
				$path,
				[
					'pathOnly' => true,
					'relative' => !$this->isPathAbsolute($path)
				]
			);
		}

		$this->resolvedPaths[$path] = [
			'external' => $external,
			'fullPath' => $file ? $file : false,
		];

		return $this->resolvedPaths[$path];
	}

	/**
	 * Check if the Path is External
	 *
	 * @param   string  $path  Path to test
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	protected function isPathExternal(string $path): bool
	{
		return strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0 || strpos($path, '//') === 0;
	}

	/**
	 * Check if the Path is relative to /media folder or absolute
	 *
	 * @param   string  $path  Path to test
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	protected function isPathAbsolute(string $path): bool
	{
		// We have a full path or not
		return is_file(JPATH_ROOT . '/' . $path);
	}
}
