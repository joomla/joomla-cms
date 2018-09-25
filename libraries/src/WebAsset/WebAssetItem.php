<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset;

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
	 * Mark active asset. Just loaded, but WITHOUT dependency resolved
	 *
	 * @var    integer
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	const ASSET_STATE_ACTIVE = 1;

	/**
	 * Mark active asset. Loaded WITH all dependency
	 *
	 * @var    integer
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	const ASSET_STATE_RESOLVED = 2;

	/**
	 * Mark active asset that is loaded as Dependacy to another asset
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
	 * List of JavaScript files
	 *
	 * @var    string[]
	 * @since  __DEPLOY_VERSION__
	 */
	protected $js = array();

	/**
	 * List of StyleSheet files
	 *
	 * @var    string[]
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

		if (!empty($data['js']))
		{
			$this->js = (array) $data['js'];
		}

		if (!empty($data['css']))
		{
			$this->css = (array) $data['css'];
		}

		if (!empty($data['dependencies']))
		{
			$this->dependencies = (array) $data['dependencies'];
		}

//		if (array_key_exists('versionAttach', $info))
//		{
//			$this->versionAttach($info['versionAttach']);
//		}
//
//		if (!empty($info['attribute']) && is_array($info['attribute']))
//		{
//			foreach ($info['attribute'] as $file => $attributes)
//			{
//				$this->setAttributes($file, $attributes);
//			}
//		}
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
}
