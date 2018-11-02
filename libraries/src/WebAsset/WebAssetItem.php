<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\HTML\HTMLHelper;

/**
 * Web Asset Item class
 *
 * @since  __DEPLOY_VERSION__
 */
class WebAssetItem
{
	/**
	 * Mark inactive asset
	 *
	 * @var    integer
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	const ASSET_STATE_INACTIVE = 0;

	/**
	 * Mark active asset. Just enabled, but WITHOUT dependency resolved
	 *
	 * @var    integer
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	const ASSET_STATE_ACTIVE = 1;

	/**
	 * Mark active asset that is enabled as dependency to another asset
	 *
	 * @var    integer
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	const ASSET_STATE_DEPENDANCY = 2;

	/**
	 * Asset state
	 *
	 * @var    integer
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $state = self::ASSET_STATE_INACTIVE;

	/**
	 * Item weight
	 *
	 * @var    float
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $weight = 0;

	/**
	 * Asset name
	 *
	 * @var    string  $name
	 * @since  __DEPLOY_VERSION__
	 */
	protected $name;

	/**
	 * Asset version
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $version;

	/**
	 * The Asset source info, where the asset comes from.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $assetSource;

	/**
	 * List of JavaScript files, and its attributes.
	 * The key is file path, the value is array of attributes.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $js = [];

	/**
	 * List of StyleSheet files, and its attributes
	 * The key is file path, the value is array of attributes.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $css = [];

	/**
	 * Asset dependencies
	 *
	 * @var    string[]
	 * @since  __DEPLOY_VERSION__
	 */
	protected $dependencies = [];

	/**
	 * Internal use, to keep track of resolved paths
	 *
	 * @var    array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $resolvedPaths = [];

	/**
	 * Class constructor
	 *
	 * @param   string  $name  The asset name
	 * @param   array   $data  The Asset information
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(string $name, array $data = [])
	{
		$this->name        = $name;
		$this->version     = !empty($data['version'])     ? $data['version']     : null;
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
	}

	/**
	 * Return Asset name
	 *
	 * @return  string
	 *
	 * @since   __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
	 */
	public function getVersion()
	{
		return $this->version;
	}

	/**
	 * Return dependency
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getDependencies(): array
	{
		return $this->dependencies;
	}

	/**
	 * Set asset State
	 *
	 * @param   int  $state  The asset state
	 *
	 * @return  self
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setState(int $state): self
	{
		$this->state = $state;

		return $this;
	}

	/**
	 * Get asset State
	 *
	 * @return  integer
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getState(): int
	{
		return $this->state;
	}

	/**
	 * Check asset state
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isActive(): bool
	{
		return $this->state !== self::ASSET_STATE_INACTIVE;
	}

	/**
	 * Set the Asset weight. Final weight recalculated by AssetFactory.
	 *
	 * @param   float  $weight  The asset weight
	 *
	 * @return  self
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setWeight(float $weight): self
	{
		$this->weight = $weight;

		return $this;
	}

	/**
	 * Return current weight of the Asset. Final weight recalculated by AssetFactory.
	 *
	 * @return  float
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getWeight(): float
	{
		return $this->weight;
	}

	/**
	 * Get CSS files
	 *
	 * @param   boolean  $resolvePath  Whether need to search for real path
	 *
	 * @return array
	 *
	 * @since   __DEPLOY_VERSION__
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
	 * @param   boolean  $resolvePath  Whether we need to search for real path
	 *
	 * @return array
	 *
	 * @since   __DEPLOY_VERSION__
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
	 * Return list of the asset files, and it's attributes
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getAssetFiles(): array
	{
		return [
			'script'     => $this->getScriptFiles(true),
			'stylesheet' => $this->getStylesheetFiles(true),
		];
	}

	/**
	 * Resolve path
	 *
	 * @param   string  $path  The path to resolve
	 * @param   string  $type  The resolver method
	 *
	 * @return array
	 *
	 * @since  __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
	 */
	protected function isPathAbsolute(string $path): bool
	{
		// We have a full path or not
		return is_file(JPATH_ROOT . '/' . $path);
	}
}
