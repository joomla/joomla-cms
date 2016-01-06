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
	 * Asset data file owner info.
	 * Just for debug, where it come from.
	 * @var array $owner
	 */
	protected $owner;

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
	 * Item weight
	 * @var float
	 */
	protected $weight = 0;

	/**
	 * Asset state
	 * @var bool $active
	 */
	protected $active = false;

	/**
	 * Class constructor
	 *
	 * @param  string  $name
	 * @param  string  $version
	 * @param  array   $owner
	 */
	public function __construct($name, $version = null, array $owner = array())
	{
		$this->name    = $name;
		$this->version = $version;
		$this->owner   = $owner;
	}

	/**
	 * Return asset name
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Return asset version
	 * @return string
	 */
	public function getVersion()
	{
		return $this->version;
	}

	/**
	 * Set JavaScript files
	 * @param array $js
	 * @return JHtmlAssetItem
	 */
	public function setJs(array $js)
	{
		$this->js = $js;

		return $this;
	}

	/**
	 * Return JavaScript files
	 * @return array
	 */
	public function getJs()
	{
		return $this->js;
	}

	/**
	 * Set StyleSheet files
	 * @param array $css
	 * @return JHtmlAssetItem
	 */
	public function setCss(array $css)
	{
		$this->css = $css;

		return $this;
	}

	/**
	 * Return StyleSheet files
	 * @return array
	 */
	public function getCss()
	{
		return $this->css;
	}

	/**
	 * Set dependency
	 * @param array $dependency
	 * @return JHtmlAssetItem
	 */
	public function setDependency(array $dependency)
	{
		$this->dependency = $dependency;

		return $this;
	}

	/**
	 * Return dependency
	 * @return array
	 */
	public function getDependency()
	{
		return $this->dependency;
	}

	/**
	 * Set asset Weight
	 * @param float $weight
	 * @return JHtmlAssetItem
	 */
	public function setWeight($weight)
	{
		$this->weight = (float) $weight;

		return $this;
	}

	/**
	 * Return asset Weight
	 * @return float
	 */
	public function getWeight()
	{
		return $this->weight;
	}

	/**
	 * Set asset State
	 * @param bool $state
	 * @return JHtmlAssetItem
	 */
	public function setActive($state)
	{
		$this->active = (bool) $state;

		return $this;
	}

	/**
	 * Return asset state
	 * @return bool
	 */
	public function isActive()
	{
		return $this->active;
	}
}
