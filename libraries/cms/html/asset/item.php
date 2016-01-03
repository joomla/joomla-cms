<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Single Asset item class.
 */
class JHtmlAssetItem
{
	/**
	 * Asset name
	 * @var  string  $name
	 */
	protected $name;

	/**
	 * Asset version
	 * @var  string
	 */
	protected $version;

	/**
	 * Asset JavaScript files
	 * @var  string[]
	 */
	protected $js = array();

	/**
	 * Asset StyleSheet files
	 * @var  string[]
	 */
	protected $css = array();

	/**
	 * Asset dependency
	 * @var  string[]
	 */
	protected $dependency = array();

	/**
	 * Class constructor
	 *
	 * @param  string  $name
	 * @param  array   $js
	 * @param  array   $css
	 * @param  array   $dependency
	 * @param  string  $version
	 */
	public function __construct($name, array $js = array(), array $css = array(), array $dependency = array(), $version = null)
	{
		$this->name       = $name;
		$this->version    = $version;
		$this->js         = $js;
		$this->css        = $css;
		$this->dependency = $dependency;
	}

	/**
	 * Return asset name
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}
}
