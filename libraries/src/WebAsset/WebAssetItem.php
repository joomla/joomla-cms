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
}
