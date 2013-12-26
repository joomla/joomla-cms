<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Class representing a document asset.
 *
 * @package     Joomla.Platform
 * @subpackage  Document
 * @since       3.3
 */
class JDocumentAsset
{
	/**
	 * Asset attributes.
	 *
	 * @var    array
	 * @since  3.3
	 */
	protected $attributes = array();

	/**
	 * Asset content.  For internal assets.
	 *
	 * @var    string
	 * @since  3.3
	 */
	protected $content = null;

	/**
	 * Array of assets that this asset depends on.
	 *
	 * @var    array
	 * @since  3.3
	 */
	protected $dependencies = array();

	/**
	 * Unique id for this asset.
	 *
	 * @var    string
	 * @since  3.3
	 */
	protected $id = null;

	/**
	 * Asset type.
	 *
	 * @var    string
	 * @since  3.3
	 */
	protected $type = '';

	/**
	 * URL of the asset.  For external assets.
	 *
	 * @var    string
	 * @since  3.3
	 */
	protected $url = '';

	/**
	 * Class constructor.
	 *
	 * @param   string  $type  Type of the asset.
	 * @param   string  $id    Unique id for this asset.
	 *
	 * @since   3.3
	 */
	public function __construct($type, $id = '')
	{
		$this->type = $type;
		$this->id = $id;
	}

	/**
	 * Adds an asset dependency.
	 * 
	 * @param   JDocumentAsset  $asset  Asset that this asset depends on.
	 * 
	 * @return  JDocumentAsset $this for method chaining.
	 *
	 * @since   3.3
	 */
	 public function addDependency($asset)
	 {
	 	$this->dependencies[] = $asset;
		
		return $this;
	 }

	/**
	 * Gets an asset attribute.
	 *
	 * @param   string  $name     Asset attribute name.
	 * @param   mixed   $default  Default value if attribute does not exist.
	 * 
	 * @return  mixed   Asset attribute value.
	 *
	 * @since   3.3
	 */
	public function getAttribute($name, $default = '')
	{
		return isset($this->attributes[$name]) ? $this->attributes[$name] : $default;
	}

	/**
	 * Gets the asset content.
	 *
	 * @return  string  Asset content.
	 *
	 * @since   3.3
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Gets the array of dependencies.
	 * 
	 * @return  array  Array of JDocumentAsset objects that this object depends on.
	 */
	 public function getDependencies()
	 {
	 	return $this->dependencies;
	 }

	/**
	 * Gets the asset id.
	 *
	 * @return  string  Asset id.
	 *
	 * @since   3.3
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Gets the asset type.
	 *
	 * @return  string  Asset type.
	 *
	 * @since   3.3
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Gets the asset URL.
	 * If a CDN is available then the CDN URL may optionally be returned.
	 *
	 * @param   boolean  True if CDN URL should be returned if available.
	 * @return  string   Asset URL.
	 *
	 * @since   3.3
	 */
	public function getUrl($cdn = false)
	{
		if ($cdn && $this->url != '' && $this->getAttribute('cdn') != '')
		{
			return $this->getAttribute('cdn');
		}

		return $this->url;
	}

	/**
	 * Remove a dependency from this asset.
	 * 
	 * @param   JDocumentAsset  $asset  Asset to remove as a dependency.
	 *
	 * @return  Array  Array of (remaining) dependencies.
	 *
	 * @since   3.3
	 */
	 public function removeDependency(JDocumentAsset $asset)
	 {
	 	foreach ($this->dependencies as $index => $dependency)
		{
			if ($dependency->getId() == $asset->getId())
			{
				unset($this->dependencies[$index]);
				break;
			}
		}
		
		return $this->dependencies;
	 }

	/**
	 * Sets an asset attribute.
	 *
	 * @param   string $name       Asset attribute name.
	 * @param   mixed  $attribute  Asset attribute value.
	 *
	 * @return  JDocumentAsset $this for method chaining.
	 *
	 * @since   3.3
	 */
	public function setAttribute($name, $attribute)
	{
		$this->attributes[$name] = $attribute;
		
		return $this;
	}

	/**
	 * Sets the asset content (for internal assets).
	 *
	 * @param   string  $content  Asset content.
	 *
	 * @return  JDocumentAsset $this for method chaining.
	 *
	 * @since   3.3
	 */
	public function setContent($content)
	{
		$this->content = $content;
		
		return $this;
	}

	/**
	 * Sets the asset id.
	 *
	 * @param   string  $id  Asset id.
	 *
	 * @return  JDocumentAsset $this for method chaining.
	 *
	 * @since   3.3
	 */
	public function setId($id = '')
	{
		// If the id has already been set then don't change it.
		if ($this->id != '')
		{
			return $this;
		}

		// If there is no id, try to come up with a sensible one.
		if ($id == '')
		{
			if ($this->url != '')
			{
				$id = basename($this->url);
			}
			else if ($this->content != '')
			{
				$id = crc32($this->content);
			}
			else if ($this->getAttribute('md5') != '')
			{
				$id = crc32($this->getAttribute('md5'));
			}
			else
			{
				$id = rand(1, 100000);
			}
		}
		
		$this->id = $id;
		
		return $this;
	}

	/**
	 * Sets asset options from a comma-separated string of option names.
	 *
	 * @param   mixed  $options  Asset options.  Simple array or comma-separated string.
	 *
	 * @return  JDocumentAsset $this for method chaining.
	 *
	 * @since   3.3
	 */
	public function setOptions($options)
	{
		// Convert comma-separated string to array.
		if (is_string($options))
		{
			$options = explode(',', $options);
		}

		// Set the option as a boolean attribute.
		foreach ($options as $option)
		{
			$value = true;
			$option = trim($option);

			// A "no" prefix indicates that we negate the option.
			if (substr($option, 0, 2) == 'no')
			{
				$option = substr($option, 2);
				$value = false;
			}
			$this->setAttribute($option, $value);
		}

		return $this;
	}

	/**
	 * Sets the asset type.
	 *
	 * @param   string  $type  Asset type.
	 *
	 * @return  JDocumentAsset $this for method chaining.
	 *
	 * @since   3.3
	 */
	public function setType($type)
	{
		$this->type = $type;
		
		return $this;
	}

	/**
	 * Sets the asset URL.
	 *
	 * @param   string  $url  Asset URL.
	 *
	 * @return  JDocumentAsset $this for method chaining.
	 *
	 * @since   3.3
	 */
	public function setUrl($url)
	{
		$this->url = $url;
		
		return $this;
	}

}
