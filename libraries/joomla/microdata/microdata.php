<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Microdata
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Joomla Platform class for interacting with Microdata semantics.
 *
 * @package     Joomla.Platform
 * @subpackage  Microdata
 * @since       3.2
 */
class JMicrodata
{
	/**
	 * Array with all available Types and Properties
	 *
	 * @var    array
	 * @since  3.2
	 */
	protected static $types = null;

	/**
	 * The Schema.org Type
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $type = null;

	/**
	 * The Property
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $property = null;

	/**
	 * The Human value or Machine value
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $content = null;

	/**
	 * The Machine value
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $machineContent = null;

	/**
	 * Fallback Type
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $fallbackType = null;

	/**
	 * Fallback Property
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $fallbackProperty = null;

	/**
	 * Used to check if a Fallback must be used
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $fallback = false;

	/**
	 * Used to check if the Microdata semantics output are enabled or disabled
	 *
	 * @var    boolean
	 * @since  3.2
	 */
	protected $enabled = true;

	/**
	 * Initialize the class and setup the default Type
	 *
	 * @param   string   $type  Optional, Fallback to Thing Type
	 * @param   boolean  $flag  Enable or disable microdata output
	 *
	 * @since   3.2
	 */
	public function __construct($type = '', $flag = true)
	{
		if ($this->enabled = (boolean) $flag)
		{
			// Fallback to Thing Type
			if (!$type)
			{
				$type = 'Thing';
			}

			$this->setType($type);
		}
	}

	/**
	 * Load all Types and Properties from the types.json file
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected static function loadTypes()
	{
		// Load the JSON
		if (!static::$types)
		{
			$path = JPATH_PLATFORM . '/joomla/microdata/types.json';
			static::$types = json_decode(file_get_contents($path), true);
		}
	}

	/**
	 * Reset all params
	 *
	 * @return void
	 * 
	 * @since   3.2
	 */
	protected function resetParams()
	{
		$this->content          = null;
		$this->machineContent	= null;
		$this->property         = null;
		$this->fallbackProperty = null;
		$this->fallbackType     = null;
		$this->fallback         = false;
	}

	/**
	 * Enable or Disable Microdata semantics output
	 *
	 * @param   boolean  $flag  Enable or disable microdata output
	 *
	 * @return  JMicrodata  Instance of $this
	 *
	 * @since   3.2
	 */
	public function enable($flag = true)
	{
		$this->enabled = (boolean) $flag;

		return $this;
	}

	/**
	 * Return true if Microdata semantics output are enabled
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 */
	public function isEnabled()
	{
		return $this->enabled;
	}

	/**
	 * Set a new Schema.org Type
	 *
	 * @param   string  $type  The Type to be setup
	 *
	 * @return  JMicrodata  Instance of $this
	 *
	 * @since   3.2
	 */
	public function setType($type)
	{
		if (!$this->enabled)
		{
			return $this;
		}

		// Sanitize the Type
		$this->type = static::sanitizeType($type);

		// If the given Type isn't available, fallback to Thing
		if (!static::isTypeAvailable($this->type))
		{
			$this->type	= 'Thing';
		}

		return $this;
	}

	/**
	 * Return the current Type name
	 *
	 * @return  string
	 *
	 * @since   3.2
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Setup a Property
	 *
	 * @param   string  $property  The Property
	 *
	 * @return  JMicrodata  Instance of $this
	 *
	 * @since   3.2
	 */
	public function property($property)
	{
		if (!$this->enabled)
		{
			return $this;
		}

		// Sanitize the Property
		$property = static::sanitizeProperty($property);

		// Control if the Property exist in the given Type and setup it, if not leave NULL
		if (static::isPropertyInType($this->type, $property))
		{
			$this->property = $property;
		}
		else
		{
			$this->fallback = true;
		}

		return $this;
	}

	/**
	 * Return the property variable
	 *
	 * @return  string
	 *
	 * @since   3.2
	 */
	public function getProperty()
	{
		return $this->property;
	}

	/**
	 * Setup a Text value or Content value for the Microdata
	 *
	 * @param   string  $value         The human value or marchine value to be used
	 * @param   string  $machineValue  The machine value
	 *
	 * @return  JMicrodata  Instance of $this
	 *
	 * @since   3.2
	 */
	public function content($value, $machineValue = null)
	{
		$this->content = $value;
		$this->machineContent = $machineValue;

		return $this;
	}

	/**
	 * Return the content variable
	 *
	 * @return  string
	 *
	 * @since   3.2
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Setup a Fallback Type and Property
	 *
	 * @param   string  $type      The Fallback Type
	 * @param   string  $property  The Fallback Property
	 *
	 * @return  JMicrodata  Instance of $this
	 *
	 * @since   3.2
	 */
	public function fallback($type, $property)
	{
		if (!$this->enabled)
		{
			return $this;
		}

		// Sanitize the Type
		$this->fallbackType = static::sanitizeType($type);

		// If the given Type isn't available, fallback to Thing
		if (!static::isTypeAvailable($this->fallbackType))
		{
			$this->fallbackType = 'Thing';
		}

		// Control if the Property exist in the given Type and setup it, if not leave NULL
		if (static::isPropertyInType($this->fallbackType, $property))
		{
			$this->fallbackProperty = $property;
		}
		else
		{
			$this->fallbackProperty = null;
		}

		return $this;
	}

	/**
	 * Return the fallbackType variable
	 *
	 * @return  string
	 *
	 * @since   3.2
	 */
	public function getFallbackType()
	{
		return $this->fallbackType;
	}

	/**
	 * Return the fallbackProperty variable
	 *
	 * @return  string
	 *
	 * @since   3.2
	 */
	public function getFallbackProperty()
	{
		return $this->fallbackProperty;
	}

	/**
	 * This function handle the logic of a Microdata intelligent display.
	 * Check if the Type, Property are available, if not check for a Fallback,
	 * then reset all params for the next use and
	 * return the Microdata HTML
	 *
	 * @param   string   $displayType  Optional, 'inline', available ['inline'|'span'|'div'|meta]
	 * @param   boolean  $emptyOutput  Return an empty string if the microdata output is disabled and there is a $content value
	 *
	 * @return  string
	 *
	 * @since   3.2
	 */
	public function display($displayType = '', $emptyOutput = false)
	{
		// Initialize the HTML to output
		$html = ($this->content !== null) ? $this->content : '';

		// Control if the Microdata output is enabled, otherwise return the content or empty string
		if (!$this->enabled)
		{
			// Reset params
			$this->resetParams();

			return ($emptyOutput) ? '' : $html;
		}

		// If the property is wrong for the current Type check if Fallback available, otherwise return empty HTML
		if ($this->property && !$this->fallback)
		{
			// Process and return the HTML the way the user expects to
			if ($displayType)
			{
				switch ($displayType)
				{
					case 'span':
						$html = static::htmlSpan($html, $this->property);
						break;

					case 'div':
						$html = static::htmlDiv($html, $this->property);
						break;

					case 'meta':
						$html = ($this->machineContent !== null) ? $this->machineContent : $html;
						$html = static::htmlMeta($html, $this->property);
						break;

					default:
						// Default $displayType = 'inline'
						$html = static::htmlProperty($this->property);
						break;
				}
			}
			else
			{
				/*
				 * Process and return the HTML in an automatic way,
				 * with the Property expected Types and display the Microdata in the right way,
				 * check if the Property is normal, nested or must be rendered in a metadata tag
				 */
				switch (static::getExpectedDisplayType($this->type, $this->property))
				{
					case 'nested':
						// Retrive the expected nested Type of the Property
						$nestedType = static::getExpectedTypes($this->type, $this->property);
						$nestedProperty = '';

						// If there is a Fallback Type then probably it could be the expectedType
						if (in_array($this->fallbackType, $nestedType))
						{
							$nestedType = $this->fallbackType;

							if ($this->fallbackProperty)
							{
								$nestedProperty = $this->fallbackProperty;
							}
						}
						else
						{
							$nestedType = $nestedType[0];
						}

						// Check if a Content is available, otherwise Fallback to an 'inline' display type
						if ($this->content !== null)
						{
							if ($nestedProperty)
							{
								$html = static::htmlSpan(
									$this->content,
									$nestedProperty
								);
							}

							$html = static::htmlSpan(
								$html,
								$this->property,
								$nestedType,
								true
							);
						}
						else
						{
							$html = static::htmlProperty($this->property) . ' ' . static::htmlScope($nestedType);

							if ($nestedProperty)
							{
								$html .= ' ' . static::htmlProperty($nestedProperty);
							}
						}

						break;

					case 'meta':
						// Check if the Content value is available, otherwise Fallback to an 'inline' display Type
						if ($this->content !== null)
						{
							$html = ($this->machineContent !== null) ? $this->machineContent : $this->content;
							$html = static::htmlMeta($html, $this->property) . $this->content;
						}
						else
						{
							$html = static::htmlProperty($this->property);
						}

						break;

					default:
						/*
						 * Default expected display type = 'normal'
						 * Check if the Content value is available,
						 * otherwise Fallback to an 'inline' display Type
						 */
						if ($this->content !== null)
						{
							$html = static::htmlSpan($this->content, $this->property);
						}
						else
						{
							$html = static::htmlProperty($this->property);
						}

						break;
				}
			}
		}
		elseif ($this->fallbackProperty)
		{
			// Process and return the HTML the way the user expects to
			if ($displayType)
			{
				switch ($displayType)
				{
					case 'span':
						$html = static::htmlSpan($html, $this->fallbackProperty, $this->fallbackType);
						break;

					case 'div':
						$html = static::htmlDiv($html, $this->fallbackProperty, $this->fallbackType);
						break;

					case 'meta':
						$html = ($this->machineContent !== null) ? $this->machineContent : $html;
						$html = static::htmlMeta($html, $this->fallbackProperty, $this->fallbackType);
						break;

					default:
						// Default $displayType = 'inline'
						$html = static::htmlScope($type::scope()) . ' ' . static::htmlProperty($this->fallbackProperty);
						break;
				}
			}
			else
			{
				/*
				 * Process and return the HTML in an automatic way,
				 * with the Property expected Types an display the Microdata in the right way,
				 * check if the Property is nested or must be rendered in a metadata tag
				 */
				switch (static::getExpectedDisplayType($this->fallbackType, $this->fallbackProperty))
				{
					case 'meta':
						// Check if the Content value is available, otherwise Fallback to an 'inline' display Type
						if ($this->content !== null)
						{
							$html = ($this->machineContent !== null) ? $this->machineContent : $this->content;
							$html = static::htmlMeta($html, $this->fallbackProperty, $this->fallbackType);
						}
						else
						{
							$html = static::htmlScope($this->fallbackType) . ' ' . static::htmlProperty($this->fallbackProperty);
						}

						break;

					default:
						/*
						 * Default expected display type = 'normal'
						 * Check if the Content value is available,
						 * otherwise Fallback to an 'inline' display Type
						 */
						if ($this->content !== null)
						{
							$html = static::htmlSpan($this->content, $this->fallbackProperty);
							$html = static::htmlSpan($html, '', $this->fallbackType);
						}
						else
						{
							$html = static::htmlScope($this->fallbackType) . ' ' . static::htmlProperty($this->fallbackProperty);
						}

						break;
				}
			}
		}
		elseif (!$this->fallbackProperty && $this->fallbackType !== null)
		{
			$html = static::htmlScope($this->fallbackType);
		}

		// Reset params
		$this->resetParams();

		return $html;
	}

	/**
	 * Return the HTML of the current Scope
	 *
	 * @return  string
	 *
	 * @since   3.2
	 */
	public function displayScope()
	{
		// Control if the Microdata output is enabled, otherwise return the content or empty string
		if (!$this->enabled)
		{
			return '';
		}

		return static::htmlScope($this->type);
	}

	/**
	 * Return the sanitized Type
	 *
	 * @param   string  $type  The Type to sanitize
	 *
	 * @return  string
	 *
	 * @since   3.2
	 */
	public static function sanitizeType($type)
	{
		return ucfirst(trim($type));
	}

	/**
	 * Return the sanitized Property
	 *
	 * @param   string  $property  The Property to sanitize
	 *
	 * @return  string
	 *
	 * @since   3.2
	 */
	public static function sanitizeProperty($property)
	{
		return lcfirst(trim($property));
	}

	/**
	 * Return an array with all Types and Properties
	 *
	 * @return  array
	 *
	 * @since   3.2
	 */
	public static function getTypes()
	{
		static::loadTypes();

		return static::$types;
	}

	/**
	 * Return an array with all available Types
	 *
	 * @return  array
	 *
	 * @since   3.2
	 */
	public static function getAvailableTypes()
	{
		static::loadTypes();

		return array_keys(static::$types);
	}

	/**
	 * Return the expected types of the Property
	 *
	 * @param   string  $type      The Type to process
	 * @param   string  $property  The Property to process
	 *
	 * @return  array
	 *
	 * @since   3.2
	 */
	public static function getExpectedTypes($type, $property)
	{
		static::loadTypes();

		$tmp = static::$types[$type]['properties'];

		// Check if the Property is in the Type
		if (isset($tmp[$property]))
		{
			return $tmp[$property]['expectedTypes'];
		}

		// Check if the Property is inherit
		$extendedType = static::$types[$type]['extends'];

		// Recursive
		if (!empty($extendedType))
		{
			return static::getExpectedTypes($extendedType, $property);
		}

		return array();
	}

	/**
	 * Return the expected display type of the [normal|nested|meta]
	 * In wich way to display the Property:
	 * normal -> itemprop="name"
	 * nested -> itemprop="director" itemscope itemtype="http://schema.org/Person"
	 * meta   -> <meta itemprop="datePublished" content="1991-05-01">
	 *
	 * @param   string  $type      The Type where to find the Property
	 * @param   string  $property  The Property to process
	 *
	 * @return  string
	 *
	 * @since   3.2
	 */
	protected static function getExpectedDisplayType($type, $property)
	{
		$expectedTypes = static::getExpectedTypes($type, $property);

		// Retrieve the first expected type
		$type = $expectedTypes[0];

		// Check if it's a meta display
		if ($type === 'Date' || $type === 'DateTime' || $property === 'interactionCount')
		{
			return 'meta';
		}

		// Check if it's a normal display
		if ($type === 'Text' || $type === 'URL' || $type === 'Boolean' || $type === 'Number')
		{
			return 'normal';
		}

		// Otherwise it's a nested display
		return 'nested';
	}

	/**
	 * Recursive function, control if the given Type has the given Property
	 *
	 * @param   string  $type      The Type where to check
	 * @param   string  $property  The Property to check
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 */
	public static function isPropertyInType($type, $property)
	{
		if (!static::isTypeAvailable($type))
		{
			return false;
		}

		// Control if the Property exists, and return true
		if (array_key_exists($property, static::$types[$type]['properties']))
		{
			return true;
		}

		// Recursive: Check if the Property is inherit
		$extendedType = static::$types[$type]['extends'];

		if (!empty($extendedType))
		{
			return static::isPropertyInType($extendedType, $property);
		}

		return false;
	}

	/**
	 * Control if the given Type class is available
	 *
	 * @param   string  $type  The Type to check
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 */
	public static function isTypeAvailable($type)
	{
		static::loadTypes();

		return (array_key_exists($type, static::$types)) ? true : false;
	}

	/**
	 * Return the microdata in a <meta> tag with content for machines.
	 *
	 * @param   string   $content   The machine content to display
	 * @param   string   $property  The Property
	 * @param   string   $scope     Optional, the Type scope to display
	 * @param   boolean  $inverse   Optional, default = false, inverse the $scope with the $property
	 *
	 * @return  string
	 *
	 * @since	3.2
	 */
	public static function htmlMeta($content, $property, $scope = '', $inverse = false)
	{
		// Control if the Property has allready the itemprop
		if (stripos($property, 'itemprop') !== 0)
		{
			$property = static::htmlProperty($property);
		}

		// Control if the Scope have allready the itemtype
		if (!empty($scope) && stripos($scope, 'itemscope') !== 0)
		{
			$scope = static::htmlScope($scope);
		}

		if ($inverse)
		{
			$tmp = join(' ', array($property, $scope));
		}
		else
		{
			$tmp = join(' ', array($scope, $property));
		}

		$tmp = trim($tmp);

		return "<meta $tmp content='$content'/>";
	}

	/**
	 * Return the microdata in a <span> tag.
	 *
	 * @param   string   $content   The human value
	 * @param   string   $property  Optional, the human value to display
	 * @param   string   $scope     Optional, the Type scope to display
	 * @param   boolean  $inverse   Optional, default = false, inverse the $scope with the $property
	 *
	 * @return  string
	 *
	 * @since	3.2
	 */
	public static function htmlSpan($content, $property = '', $scope = '', $inverse = false)
	{
		// Control if the Property has allready the itemprop
		if (!empty($property) && stripos($property, 'itemprop') !== 0)
		{
			$property = static::htmlProperty($property);
		}

		// Control if the Scope have allready the itemtype
		if (!empty($scope) && stripos($scope, 'itemscope') !== 0)
		{
			$scope = static::htmlScope($scope);
		}

		if ($inverse)
		{
			$tmp = join(' ', array($property, $scope));
		}
		else
		{
			$tmp = join(' ', array($scope, $property));
		}

		$tmp = trim($tmp);
		$tmp = ($tmp) ? ' ' . $tmp : '';

		return "<span$tmp>$content</span>";
	}

	/**
	 * Return the microdata in an <div> tag.
	 *
	 * @param   string   $content   The human value
	 * @param   string   $property  Optional, the human value to display
	 * @param   string   $scope     Optional, the Type scope to display
	 * @param   boolean  $inverse   Optional, default = false, inverse the $scope with the $property
	 *
	 * @return  string
	 *
	 * @since	3.2
	 */
	public static function htmlDiv($content, $property = '', $scope = '', $inverse = false)
	{
		// Control if the Property has allready the itemprop
		if (!empty($property) && stripos($property, 'itemprop') !== 0)
		{
			$property = static::htmlProperty($property);
		}

		// Control if the Scope have allready the itemtype
		if (!empty($scope) && stripos($scope, 'itemscope') !== 0)
		{
			$scope = static::htmlScope($scope);
		}

		if ($inverse)
		{
			$tmp = join(' ', array($property, $scope));
		}
		else
		{
			$tmp = join(' ', array($scope, $property));
		}

		$tmp = trim($tmp);
		$tmp = ($tmp) ? ' ' . $tmp : '';

		return "<div$tmp>$content</div>";
	}

	/**
	 * Return the HTML Scope
	 *
	 * @param   string  $scope  The Scope to process
	 *
	 * @return  string
	 *
	 * @since   3.2
	 */
	public static function htmlScope($scope)
	{
		if (stripos($scope, 'http') !== 0)
		{
			$scope = 'https://schema.org/' . ucfirst($scope);
		}

		return "itemscope itemtype='$scope'";
	}

	/**
	 * Return the HTML Property
	 *
	 * @param   string  $property  The Property to process
	 *
	 * @return  string
	 *
	 * @since   3.2
	 */
	public static function htmlProperty($property)
	{
		return "itemprop='$property'";
	}
}
