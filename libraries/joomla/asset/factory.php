<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Asset factory class.
 *
 * @since  5.0
 */
class JAssetFactory
{
	/**
	 * Files with assets infos. File path should be relative
	 *
	 * @var array as filePath => DATAFILE_NEW/DATAFILE_PARSED
	 *
	 * @example of data file:
	 *	{
	 *		"title" : "Example",
	 *		"name"  : "com_example",
	 *		"author": "Joomla! CMS",
	 *		"assets": [
	 *			{
	 *				"name": "library1",
	 *				"version": "3.5.0",
	 *				"versionAttach": true,
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
	 */
	protected $dataFiles = array();

	/**
	 * Available Assets
	 *
	 * @var array
	 */
	protected $assets = array();

	/**
	 * Weight of the most heavier active asset
	 *
	 * @var float $lastItemWeight
	 */
	protected $lastItemWeight = 1;

	/**
	 * Deafult defer mode for attached JavaScripts
	 *
	 * @var bool $jsDeferMode
	 */
	protected $jsDeferMode = null;

	/**
	 * Mark the new data file
	 *
	 * @var int
	 */
	const DATAFILE_NEW = 1;

	/**
	 * Mark already parsed data file
	 *
	 * @var int
	 */
	const DATAFILE_PARSED = 2;

	/**
	 * Global Asset Factory object
	 *
	 * @var    JAssetFactory
	 * @since  5.0
	 */
	public static $instance = null;

	/**
	 * Class constructor
	 */
	public function __construct()
	{
		$this->searchForDataFiles();
	}

	/**
	 * Return the JAssetFactory object
	 *
	 * @return  JAssetFactory object
	 *
	 * @since   5.0
	 */
	public static function getInstance()
	{
		if (!self::$instance)
		{
			self::$instance = new JAssetFactory;
		}

		return self::$instance;
	}

	/**
	 * Add asset to collection of known assets
	 *
	 * @param   JAssetItem  $asset  Asset instance
	 *
	 * @return  JHtmlAssetFactory
	 */
	public function addAsset(JAssetItem $asset)
	{
		$name = $asset->getName();
		$this->assets[$name] = $asset;

		return $this;
	}

	/**
	 * Remove Asset by name
	 *
	 * @param   string  $name  Asset name
	 *
	 * @return  JHtmlAssetFactory
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
	 * Get asset by it's name
	 *
	 * @param   string  $name  Asset name
	 *
	 * @return  JAssetItem|bool Return asset object or false if asset doesnot exists
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
	 * Return dependancy for Asset as array of AssetItem objects
	 *
	 * @param   JAssetItem  $asset  Asset instance
	 *
	 * @return  JAssetItem[]
	 *
	 * @throws  RuntimeException When Dependency cannot be found
	 */
	public function getAssetDependancy(JAssetItem $asset)
	{
		$assets = array();

		foreach ($asset->getDependency() as $depName)
		{
			$dep = $this->getAsset($depName);

			if (!$dep)
			{
				throw new RuntimeException('Cannot find Dependency "' . $depName . '" for Asset "' . $asset->getName() . '"');
			}

			$assets[$depName] = $dep;
		}

		return $assets;
	}

	/**
	 * Change the asset State
	 *
	 * @param   string  $name   Asset name
	 * @param   int     $state  New state
	 * @param   bool    $force  Force weight calculation if the Asset alredy was enabled previously
	 *
	 * @return  JHtmlAssetFactory
	 *
	 * @throws  RuntimeException if asset with given name does not exists
	 */
	public function setAssetState($name, $state = JAssetItem::ASSET_STATE_ACTIVE, $force = false)
	{
		$asset = $this->getAsset($name);

		if (!$asset)
		{
			throw new RuntimeException('Asset "' . $name . '" do not exists');
		}

		// Asset alredy has the requested state, so stop here, prevent Weight recalulation
		if ($asset->isActive() === $state && !$force)
		{
			return $this;
		}

		// Change state
		$asset->setState($state);

		// Calculate weight
		if ($state !== JAssetItem::ASSET_STATE_INACTIVE)
		{
			$dependency = $asset->getDependency();
			$this->lastItemWeight = $this->lastItemWeight + count($dependency) + 1;
			$asset->setWeight($this->lastItemWeight);
		}

		return $this;
	}

	/**
	 * Search for all active assets.
	 *
	 * @return  JAssetItem[]  Array with active assets
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

		// Order them by weight
		$this->sortByWeight($assets);

		return $assets;
	}

	/**
	 * Search for assets with specific state.
	 *
	 * @param   int  $state  Asset state
	 *
	 * @return  JAssetItem[]  Array with active assets
	 */
	public function getAssetsByState($state)
	{
		$assets = array_filter(
			$this->assets,
			function($asset) use ($state)
			{
				return $asset->getState() === $state;
			}
		);

		// Order them by weight
		$this->sortByWeight($assets);

		return $assets;
	}

	/**
	 * Allow to change default defer behaviour forJavaScript files
	 *
	 * @param   bool  $defer  Default "defer" mode for all javascrip files
	 *
	 * @return  JHtmlAssetFactory
	 */
	public function deferJavaScript($defer = null)
	{
		$this->jsDeferMode = $defer;

		return $this;
	}

	/**
	 * Attach active assets to the Document
	 *
	 * @param   JDocument  $doc  Document for attach StyleSheet/JavaScript
	 *
	 * @return  void
	 */
	public function attach(JDocument $doc)
	{
		$app = JFactory::getApplication();

		// Resolve Dependency
		$this->resolveDependency();

		// Trigger the onBeforeHeadAttachHtmlAsset event
		$app->triggerEvent('onBeforeHeadAttachHtmlAsset', array($this));

		// Attach an active assets do the document
		$assets = $this->getActiveAssets();

		// Presave existing Scripts, and attach them after requested assets.
		$jsBackup = $doc->_scripts;
		$doc->_scripts = array();

		foreach ($assets as $asset)
		{
			if ($this->jsDeferMode !== null)
			{
				$asset->deferJavaScript($this->jsDeferMode);
			}

			$asset->attach($doc);
		}

		// Add previously added scripts
		$doc->_scripts = array_merge($doc->_scripts, $jsBackup);
	}

	/**
	 * Resolve Dependency for just added assets
	 *
	 * @return  JHtmlAssetFactory
	 *
	 * @throws  RuntimeException When Dependency cannot be resolved
	 */
	protected function resolveDependency()
	{
		$assets = $this->getAssetsByState(JAssetItem::ASSET_STATE_ACTIVE);

		foreach ($assets as $asset)
		{
			$this->resolveItemDependency($asset);
			$asset->setState(JAssetItem::ASSET_STATE_RESOLVED);
		}

		return $this;
	}

	/**
	 * Resolve Dependency for given asset
	 *
	 * @param   JAssetItem  $asset  Asset instance
	 *
	 * @return  JHtmlAssetFactory
	 *
	 * @throws  RuntimeException When Dependency cannot be resolved
	 */
	protected function resolveItemDependency(JAssetItem $asset)
	{
		foreach ($this->getAssetDependancy($asset) as $depItem)
		{
			$oldState = $depItem->isActive();

			// Make active
			if (!$oldState)
			{
				$depItem->setState(JAssetItem::ASSET_STATE_DEPENDANCY);
			}

			// Calculate weight, make it a bit lighter
			$depWeight   = $depItem->getWeight();
			$assetWeight = $asset->getWeight();

			$depWeight = $depWeight === 0 ? $this->lastItemWeight : $depWeight;
			$weight    = $depWeight > $assetWeight ? $assetWeight : $depWeight;
			$weight    = $weight - 0.1;

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
	 * Sort assets by it`s weight
	 *
	 * @param   JAssetItem[]  &$assets  Linked array of assets
	 * @param   bool          $ask      Order direction: true for ASC and false for DESC
	 *
	 * @return  JHtmlAssetFactory
	 */
	protected function sortByWeight(array &$assets, $ask = true)
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

		return $this;
	}

	/**
	 * Register new file with asset info
	 *
	 * @param   string  $path  Relative path
	 *
	 * @return  JHtmlAssetFactory
	 *
	 * @throws  UnexpectedValueException If file does not exists
	 */
	public function registerDataFile($path)
	{
		$path = JPath::clean($path);

		if (!is_file(JPATH_ROOT . '/' . $path))
		{
			throw new UnexpectedValueException('Asset data file do not available');
		}

		if (!isset($this->dataFiles[$path]))
		{
			$this->dataFiles[$path] = static::DATAFILE_NEW;
		}

		return $this;
	}

	/**
	 * Search for joomla.asset.json filse in the Media folder
	 *
	 * @return  void
	 */
	protected function searchForDataFiles()
	{
		$files = array_merge(
			glob(JPATH_ROOT . '/media/*/joomla.asset.json'), // Search extension assets, in /media
			glob(JPATH_BASE . '/templates/*/joomla.asset.json') // Search the template assets
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
	 */
	protected function parseDataFiles()
	{
		// Filter new asset data files and parse each
		$constantIsNew = static::DATAFILE_NEW;
		$files = array_filter(
			$this->dataFiles,
			function($state) use ($constantIsNew) {
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
	 * @throws  RuntimeException If file is empty or invalid
	 */
	protected function parseDataFile($path)
	{
		$data = json_decode(@file_get_contents(JPATH_ROOT . '/' . $path), true);

		if (!$data)
		{
			throw new RuntimeException('Asset data file "' . $path . '" is incorrect or broken');
		}

		// Asset exists but empty, skip it silently
		if (empty($data['assets']))
		{
			return;
		}

		// Prepare AssetItem instance
		$owner = $data;
		$owner['dataFile'] = $path;
		unset($owner['assets']);

		foreach ($data['assets'] as $item)
		{
			if (empty($item['name']))
			{
				throw new RuntimeException('Asset data file "' . $path . '" contains incorrect asset defination');
			}

			$assetItem = $this->prepareAssetInstance($item['name'], $item, $owner);
			$this->addAsset($assetItem);
		}
	}

	/**
	 * Prepare Asset instance
	 *
	 * @param   string  $name   Asset name
	 * @param   array   $info   Asset information
	 * @param   array   $owner  Asset data file-owner info
	 *
	 * @return  JAssetItem
	 */
	public function prepareAssetInstance($name, array $info = array(), array $owner = array())
	{
		$version = !empty($info['version']) ? $info['version'] : null;

		/*
		Check for specific class
		@TODO whether it realy can be usefull ???

		$class = 'JAsset' . implode('', array_map('ucfirst', explode('.', $name)));
		$class = class_exists($class) ? $class : 'JAssetItem';
		$asset = new $class($name, $version, $owner);
		*/

		$asset = new JAssetItem($name, $version, $owner);

		if (!empty($info['js']))
		{
			$asset->setJs((array) $info['js']);
		}

		if (!empty($info['css']))
		{
			$asset->setCss((array) $info['css']);
		}

		if (!empty($info['dependency']))
		{
			$asset->setDependency((array) $info['dependency']);
		}

		if (array_key_exists('versionAttach', $info))
		{
			$asset->versionAttach($info['versionAttach']);
		}

		if (!empty($info['attribute']) && is_array($info['attribute']))
		{
			foreach ($info['attribute'] as $file => $attributes)
			{
				$asset->setAttributes($file, $attributes);
			}
		}

		return $asset;
	}

	/**
	 * Helper method to build the joomla.asset.json data from files in the media folder
	 *
	 * @param   string  $pathBase  Relative path to Folder for scan for files
	 * @param   string  $prefix    Media prefix. Example com_example for com_example/file.js
	 * @param   string  $title     The collection title
	 * @param   string  $author    The collection author
	 *
	 * @return  string JSON data
	 */
	public function buildDataFileContent($pathBase, $prefix = null, $title = '', $author = 'Joomla! CMS')
	{
		jimport('joomla.filesystem.path');
		jimport('joomla.filesystem.folder');

		$title    = $title ? $title : 'Autogenerated assets collection';
		$assets   = array();
		$prefix   = $prefix === null ? preg_replace('#^media\/#', '', $pathBase) : $prefix;
		$pathBase = JPath::clean(JPATH_ROOT . '/' . trim($pathBase, '/'));

		// Search for JavaScript files
		$js = JFolder::files($pathBase, '\.js$', true, true);

		if (!empty($js))
		{
			foreach ($js as $file)
			{
				// Remove base path
				$relative = preg_replace('#^' . $pathBase . '/#', '', $file);

				// Remove "js/"
				$parts = explode('/', $relative);
				$sIndex = count($parts) - 2;

				if (!empty($parts[$sIndex]) && $parts[$sIndex] === 'js')
				{
					unset($parts[$sIndex]);
				}

				$relative = implode('/', $parts);
				$name     = preg_replace(array('#-uncompressed\.js$#', '#\.min\.js$#', '#\.js$#'), '', $relative);

				if (empty($assets[$name]))
				{
					$assets[$name] = array(
						'name' => $name,
						'version' => '1.0',
						'js'  => array(),
						'css' => array(),
						'dependency' => array(),
					);
				}

				$assets[$name]['js'][$name] = $prefix . '/' . $relative;
			}
		}

		// Search for StyleSheet files
		$css = JFolder::files($pathBase, '\.css$', true, true);

		if (!empty($css))
		{
			foreach ($css as $file)
			{
				// Remove base path
				$relative = preg_replace('#^' . $pathBase . '/#', '', $file);

				// Remove "css/"
				$parts = explode('/', $relative);
				$sIndex = count($parts) - 2;

				if (!empty($parts[$sIndex]) && $parts[$sIndex] === 'css')
				{
					unset($parts[$sIndex]);
				}

				$relative = implode('/', $parts);
				$name     = preg_replace(array('#\.min\.css$#', '#\.css$#'), '', $relative);

				if (empty($assets[$name]))
				{
					$assets[$name] = array(
						'name' => $name,
						'version' => '1.0',
						'js'  => array(),
						'css' => array(),
						'dependency' => array(),
					);
				}

				$assets[$name]['css'][$name] = $prefix . '/' . $relative;
			}
		}

		// Remove asset "keys", and empty valuse
		foreach ($assets as $name => $asset)
		{
			if (empty($asset['js']))
			{
				unset($assets[$name]['js']);
			}
			else
			{
				$assets[$name]['js'] = array_values($asset['js']);
			}

			if (empty($asset['css']))
			{
				unset($assets[$name]['css']);
			}
			else
			{
				$assets[$name]['css'] = array_values($asset['css']);
			}
		}

		// Prepare base
		$data = array(
			'title'  => $title,
			'author' => $author,
			'assets' => array_values($assets),
		);

		return json_encode($data, JSON_PRETTY_PRINT);
	}
}
