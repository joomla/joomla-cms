<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset;

use Joomla\CMS\Document\Document;
use Joomla\CMS\HTML\HTMLHelper;

defined('JPATH_PLATFORM') or die;

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
	 * Mark active asset. Enabled WITH all dependency
	 *
	 * @var    integer
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	const ASSET_STATE_RESOLVED = 2;

	/**
	 * Mark active asset that is enabled as dependency to another asset
	 *
	 * @var    integer
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	const ASSET_STATE_DEPENDANCY = 3;

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
	 * List of JavaScript files, ant it's attributes.
	 * The key is file path, the value is array of attributes.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $js = array();

	/**
	 * List of StyleSheet files, ant it's attributes
	 * The key is file path, the value is array of attributes.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $css = array();

	/**
	 * Asset dependencies
	 *
	 * @var    string[]
	 * @since  __DEPLOY_VERSION__
	 */
	protected $dependencies = array();

	/**
	 * Class constructor
	 *
	 * @param   string  $name   The asset name
	 * @param   array   $data   The Asset information
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($name, array $data = array())
	{
		$this->name        = strtolower($name); // No fancy Camels or Elephants
		$this->version     = !empty($data['version'])     ? $data['version']     : null;
		$this->assetSource = !empty($data['assetSource']) ? $data['assetSource'] : null;

		$attributes = empty($data['attribute']) ? [] : $data['attribute'];

		// Check for Scripts and StyleSheets, and their attributes
		if (!empty($data['js']))
		{
			foreach ($data['js'] as $js) {
				$this->js[$js] = empty($attributes[$js]) ? [] : $attributes[$js];
			}
		}

		if (!empty($data['css']))
		{
			foreach ($data['css'] as $css) {
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
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Return dependency
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getDependencies()
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
	public function setState($state)
	{
		$this->state = (int) $state;

		return $this;
	}

	/**
	 * Get asset State
	 *
	 * @return  integer
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getState()
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
	public function isActive()
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
	public function setWeight($weight)
	{
		$this->weight = (float) $weight;

		return $this;
	}

	/**
	 * Return current weight of the Asset. Final weight recalculated by AssetFactory.
	 *
	 * @return  float
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getWeight()
	{
		return $this->weight;
	}

	/**
	 * Attach active asset to the Document
	 *
	 * @param   Document  $doc  Document for attach StyleSheet/JavaScript
	 *
	 * @return  self
	 *
	 * @throws  \RuntimeException If try attach inactive asset
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function attach(Document $doc)
	{
		if (!$this->isActive())
		{
			throw new \RuntimeException('Incative Asset cannot be attached');
		}

		return $this->attachCSS($doc)->attachJS($doc);
	}

	/**
	 * Attach StyleSheet files to the document
	 *
	 * @param   Document  $doc  Document for attach StyleSheet/JavaScript
	 *
	 * @return  self
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function attachCSS(Document $doc)
	{
		foreach ($this->css as $path => $attr)
		{
			$file    = $path;
			$version = false;

			if (!$this->isPathExternal($path))
			{
				// Get the file path
				$file = HTMLHelper::_('stylesheet', $path, [
						'pathOnly' => true,
						'relative' => !$this->isPathAbsolute($path)
					]
				);
				$version = 'auto';
			}

			if ($file)
			{
				$doc->addStyleSheet($file, ['version' => $version], $attr);
			}
		}

		return $this;
	}

	/**
	 * Attach JavaScript files to the document
	 *
	 * @param   Document  $doc  Document for attach StyleSheet/JavaScript
	 *
	 * @return  self
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function attachJS(Document $doc)
	{
		foreach ($this->js as $path => $attr)
		{
			$file    = $path;
			$version = false;

			if (!$this->isPathExternal($path))
			{
				// Get the file path
				$file = HTMLHelper::_('script', $path, [
						'pathOnly' => true,
						'relative' => !$this->isPathAbsolute($path)
					]
				);
				$version = 'auto';
			}

			if ($file)
			{
				$doc->addScript($file, ['version' => $version], $attr);
			}
		}

		return $this;
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
	protected function isPathExternal($path)
	{
		return strpos($path, 'http') === 0 || strpos($path, '//') === 0;
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
	protected function isPathAbsolute($path)
	{
		// We have a full path or not
		return is_file(JPATH_ROOT . '/' . $path);
	}
}
