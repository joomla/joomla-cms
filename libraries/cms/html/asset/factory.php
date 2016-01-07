<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.path');
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * Asset factory class.
 */
class JHtmlAssetFactory
{
	/**
	 * Files with assets infos. File path should be relative
	 * @var array as filePath => (bool) isNew
	 *
	 * @example of data file:
{
	"title" : "Example",
	"name"  : "com_example",
	"author": "Joomla! CMS",
	"assets": [
		{
			"name": "library1",
			"version": "3.5.0",
			"js": [
				"com_example/library1.min.js"
			]
		},
		{
			"name": "library2",
			"version": "3.5.0",
			"js": [
				"com_example/library2.min.js"
			],
			"css": [
				"com_example/library2.css"
			],
			"dependency": [
				"core",
				"library1"
			]
		},

	]
}
	 *
	 */
	protected $dataFiles = array();

	/**
	 * Available Assets
	 * @var array
	 */
	protected $assets = array();

	/**
	 * Weight of the most heavier active asset
	 * @var float $lastItemWeight
	 */
	protected $lastItemWeight = 1;

	/**
	 * Deafult defer mode for attached JavaScripts
	 * @var bool $jsDeferMode
	 */
	protected $jsDeferMode = false;

	/**
	 * Class constructor
	 */
	public function __construct()
	{
		$this->searchForDataFiles();
	}

	/**
	 * Add asset to collection of known assets
	 * @param JHtmlAssetItem $asset
	 * @return JHtmlAssetFactory
	 */
	public function addAsset(JHtmlAssetItem $asset)
	{
		$name = $asset->getName();
		$this->assets[$name] = $asset;

		return $this;
	}

	/**
	 * Remove Asset by name
	 * @param string $name
	 * @return JHtmlAssetFactory
	 */
	public function removeAsset($name)
	{
		if(!empty($this->assets[$name]))
		{
			unset($this->assets[$name]);
		}

		return $this;
	}

	/**
	 * Get asset by it's name
	 * @param string $name Asset name
	 * @return JHtmlAssetItem|bool Return asset object or false if asset doesnot exists
	 */
	public function getAsset($name)
	{
		// Check if there any new data file was added
		$this->parseDataFiles();

		if(!empty($this->assets[$name]))
		{
			return $this->assets[$name];
		}

		return false;
	}

	/**
	 * Return dependancy for Asset as array of AssetItem objects
	 * @param JHtmlAssetItem $asset
	 * @return JHtmlAssetItem[]
	 * @throws RuntimeException When Dependency cannot be found
	 */
	public function getAssetDependancy(JHtmlAssetItem $asset)
	{
		$assets = array();

		foreach($asset->getDependency() as $depName){
			$dep = $this->getAsset($depName);

			if(!$dep)
			{
				throw new RuntimeException('Cannot find Dependency "' . $depName . '" for Asset "' . $asset->getName() . '"');
			}

			$assets[$depName] = $dep;
		}

		return $assets;
	}

	/**
	 * Activate deactivate the asset by name
	 * @param string $name  Asset name
	 * @param bool   $state New state
	 * @param bool   $force Force weight calculation if the Asset alredy was enabled previously
	 * @return JHtmlAssetFactory
	 * @throws RuntimeException if asset with given name does not exists
	 */
	public function makeActive($name, $state = true, $force = false)
	{
		$asset = $this->getAsset($name);

		if(!$asset)
		{
			throw new RuntimeException('Asset "' . $name . '" do not exists');
		}

		// Asset alredy has the requested state, so stop here, prevent Weight recalulation
		if ($asset->isActive() === $state && !$force)
		{
			return $this;
		}

		// Change state
		$asset->setActive($state);

		// Calculate weight
		if($state)
		{
			$dependency = $asset->getDependency();
			$this->lastItemWeight = $this->lastItemWeight + count($dependency) + 1;
			$asset->setWeight($this->lastItemWeight);
		}

		return $this;
	}

	/**
	 * Search for all active assets.
	 * @return JHtmlAssetItem[] Array with active assets
	 */
	public function getActiveAssets()
	{
		$assets = array_filter($this->assets, function($asset){
			return $asset->isActive();
		});

		// Order them by weight
		$this->sortByWeight($assets);

		return $assets;
	}

	/**
	 * Allow to change default defer behaviour forJavaScript files
	 * @param bool $defer
	 * @return JHtmlAssetFactory
	 */
	public function deferJavaScript($defer = true)
	{
		$this->jsDeferMode = $defer;
		return $this;
	}

	/**
	 * Attach active assets to the Document
	 * @param JDocument $doc
	 * @return void
	 */
	public function attach(JDocument $doc)
	{
		// Resolve Dependency
		$this->resolveDependency();

		// Attach them do the document
		$assets = $this->getActiveAssets();
		foreach($assets as $asset){
			$this->attachCss($asset->getCss(), $doc);
			$this->attachJs($asset->getJs(), $doc);
		}
	}

	/**
	 * Attach StyleSheet files to the document
	 * @param array $css
	 * @param JDocument $doc
	 * @return void
	 */
	protected function attachCss(array $css, JDocument $doc)
	{
		foreach($css as $path){
			$file = JHtml::_('stylesheet', $path, array(), true, true);

			if ($file)
			{
				$doc->addStyleSheet($file);
			}
		}
	}

	/**
	 * Attach JavaScript files to the document
	 * @param array $js
	 * @param JDocument $doc
	 * @return void
	 */
	protected function attachJs(array $js, JDocument $doc)
	{
		foreach($js as $path){
			$file = JHtml::_('script', $path, false, true, true);

			if ($file)
			{
				$doc->addScript($file, 'text/javascript', $this->jsDeferMode);
			}
		}
	}


	/**
	 * Resolve Dependency for active assets
	 * @return JHtmlAssetFactory
	 * @throws RuntimeException When Dependency cannot be resolved
	 */
	protected function resolveDependency()
	{
		$assets = $this->getActiveAssets();

		foreach($assets as $asset){
			$this->resolveItemDependency($asset);
		}

		return $this;
	}

	/**
	 * Resolve Dependency for given asset
	 * @param JHtmlAssetItem $asset
	 * @return JHtmlAssetFactory
	 * @throws RuntimeException When Dependency cannot be resolved
	 */
	protected function resolveItemDependency(JHtmlAssetItem $asset)
	{
		foreach($this->getAssetDependancy($asset) as $depItem){
			$oldState = $depItem->isActive();

			// Make active
			$depItem->setActive(true);

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
	 * @param JHtmlAssetItem[] $assets Linked array
	 * @param bool $ask Order direction: true for ASC and false for DESC
	 * @return JHtmlAssetFactory
	 */
	protected function sortByWeight(array &$assets, $ask = true)
	{
		uasort($assets, function($a, $b) use ($ask) {
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
		});

		return $this;
	}

	/**
	 * Register new file with asset info
	 * @param  string  $path  Relative path
	 * @return JHtmlAssetFactory
	 * @throws UnexpectedValueException If file does not exists
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
			$this->dataFiles[$path] = true;
		}

		return $this;
	}

	/**
	 * Search for joomla.asset.json filse in the Media folder
	 * @return void
	 */
	protected function searchForDataFiles()
	{
		$files = glob(JPATH_ROOT . '/media/*/joomla.asset.json');

		if (empty($files))
		{
			return;
		}

		foreach ($files as $file) {
			$path = preg_replace('#^' . JPATH_ROOT . '/#', '', $file);
			$this->registerDataFile($path);
		}
	}

	/**
	 * Parse registered data files
	 * @return void
	 */
	protected function parseDataFiles()
	{
		// Filter new asset data files and parse each
		foreach (array_keys(array_filter($this->dataFiles)) as $path) {
			$this->parseDataFile($path);

			// Mark as parsed (not new)
			$this->dataFiles[$path] = false;
		}
	}

	/**
	 * Parse data file
	 * @return void
	 * @throws RuntimeException If file is empty or invalid
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

		foreach ($data['assets'] as $item){
			if(empty($item['name']))
			{
				throw new RuntimeException('Asset data file "' . $path . '" contains incorrect asset defination');
			}

			$version   = !empty($item['version']) ? $item['version'] : null;
			$assetItem = new JHtmlAssetItem($item['name'], $version, $owner);

			if (!empty($item['js']))
			{
				$assetItem->setJs((array) $item['js']);
			}

			if (!empty($item['css']))
			{
				$assetItem->setCss((array) $item['css']);
			}

			if (!empty($item['dependency']))
			{
				$assetItem->setDependency((array) $item['dependency']);
			}

			$this->addAsset($assetItem);
		}
	}

	/**
	 * Helper method to build the joomla.asset.json data from files in the media folder
	 *
	 * @param string $pathBase Relative path to Folder for scan for files
	 * @param string $prefix   Media folder prefix
	 * @param string $title
	 * @param string $author
	 *
	 * @return string JSON data
	 */
	public function buildDataFileContent($pathBase, $prefix = null, $title = '', $author = 'Joomla! CMS')
	{
		$title  = $title ? $title : 'Autogenerated assets collection';
		$assets = array();
		$prefix   = $prefix === null ? preg_replace('#^media\/#', '', $pathBase) : $prefix;
		$pathBase = JPath::clean(JPATH_ROOT . '/' . trim($pathBase, '/'));

		// Search for JavaScript files
		$js = JFolder::files($pathBase, '\.js$', true, true);
		if (!empty($js))
		{
			foreach($js as $file){
				// Remove base path
				$relative = preg_replace('#^' . $pathBase . '/#', '', $file);

				// Remove "js/"
				$parts = explode('/', $relative);
				$sIndex = count($parts) - 2;

				if(!empty($parts[$sIndex]) && $parts[$sIndex] === 'js')
				{
					unset($parts[$sIndex]);
				}

				$relative = implode('/', $parts);
				$name     = preg_replace(array('#-uncompressed\.js$#', '#\.min\.js$#', '#\.js$#'), '', $relative);

				if(empty($assets[$name]))
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
			foreach($css as $file){
				// Remove base path
				$relative = preg_replace('#^' . $pathBase . '/#', '', $file);

				// Remove "css/"
				$parts = explode('/', $relative);
				$sIndex = count($parts) - 2;

				if(!empty($parts[$sIndex]) && $parts[$sIndex] === 'css')
				{
					unset($parts[$sIndex]);
				}

				$relative = implode('/', $parts);
				$name     = preg_replace(array('#\.min\.css$#', '#\.css$#'), '', $relative);

				if(empty($assets[$name]))
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
		foreach($assets as $name => $asset) {
			if (empty($asset['js']))
			{
				unset($assets[$name]['js']);
			}
			else
			{
				$assets[$name]['js'] = array_values($asset['js']);
			}

			if(empty($asset['css']))
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
