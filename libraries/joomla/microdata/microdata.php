<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Microdata
 *
<<<<<<< HEAD
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
=======
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
>>>>>>> upstream/master
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
<<<<<<< HEAD
	 * @var     array
	 * @since   3.2
=======
	 * @var    array
	 * @since  3.2
>>>>>>> upstream/master
	 */
	protected static $types = null;

	/**
	 * The Schema.org Type
	 *
<<<<<<< HEAD
	 * @var		string
	 * @since	3.2
=======
	 * @var    string
	 * @since  3.2
>>>>>>> upstream/master
	 */
	protected $type = null;

	/**
	 * The Property
	 *
<<<<<<< HEAD
	 * @var		string
	 * @since	3.2
=======
	 * @var    string
	 * @since  3.2
>>>>>>> upstream/master
	 */
	protected $property = null;

	/**
	 * The Human value or Machine value
	 *
<<<<<<< HEAD
	 * @var		string
	 * @since	3.2
=======
	 * @var    string
	 * @since  3.2
>>>>>>> upstream/master
	 */
	protected $content = null;

	/**
	 * The Machine value
<<<<<<< HEAD
	 * 
	 * @var     string
	 * @since   3.2
=======
	 *
	 * @var    string
	 * @since  3.2
>>>>>>> upstream/master
	 */
	protected $machineContent = null;

	/**
	 * Fallback Type
	 *
<<<<<<< HEAD
	 * @var		string
	 * @since	3.2
=======
	 * @var    string
	 * @since  3.2
>>>>>>> upstream/master
	 */
	protected $fallbackType = null;

	/**
	 * Fallback Property
	 *
<<<<<<< HEAD
	 * @var		string
	 * @since	3.2
=======
	 * @var    string
	 * @since  3.2
>>>>>>> upstream/master
	 */
	protected $fallbackProperty = null;

	/**
	 * Used to check if a Fallback must be used
	 *
<<<<<<< HEAD
	 * @var		string
	 * @since	3.2
=======
	 * @var    string
	 * @since  3.2
>>>>>>> upstream/master
	 */
	protected $fallback = false;

	/**
	 * Used to check if the Microdata semantics output are enabled or disabled
	 *
<<<<<<< HEAD
	 * @var 	boolean
	 * @since	3.2
=======
	 * @var    boolean
	 * @since  3.2
>>>>>>> upstream/master
	 */
	protected $enabled = true;

	/**
	 * Initialize the class and setup the default Type
	 *
	 * @param   string   $type  Optional, Fallback to Thing Type
	 * @param   boolean  $flag  Enable or disable microdata output
<<<<<<< HEAD
=======
	 *
	 * @since   3.2
>>>>>>> upstream/master
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
<<<<<<< HEAD
	 * 
	 * @return	void
=======
	 *
	 * @return  void
	 *
	 * @since   3.2
>>>>>>> upstream/master
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
	 * Enable or Disable Microdata semantics output
	 *
	 * @param   boolean  $flag  Enable or disable microdata output
	 *
<<<<<<< HEAD
	 * @return	object
=======
	 * @return  JMicrodata  Instance of $this
	 *
	 * @since   3.2
>>>>>>> upstream/master
	 */
	public function enable($flag = true)
	{
		$this->enabled = (boolean) $flag;

		return $this;
	}

	/**
	 * Return true if Microdata semantics output are enabled
<<<<<<< HEAD
	 * 
	 * @return	boolean
=======
	 *
	 * @return  boolean
	 *
	 * @since   3.2
>>>>>>> upstream/master
	 */
	public function isEnabled()
	{
		return ($this->enabled) ? true : false;
	}

	/**
	 * Set a new Schema.org Type
	 *
	 * @param   string  $type  The Type to be setup
	 *
<<<<<<< HEAD
	 * @return	object
=======
	 * @return  JMicrodata  Instance of $this
	 *
	 * @since   3.2
>>>>>>> upstream/master
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
<<<<<<< HEAD
		if ( !static::isTypeAvailable($this->type) )
=======
		if (!static::isTypeAvailable($this->type))
>>>>>>> upstream/master
		{
			$this->type	= 'Thing';
		}

		return $this;
	}

	/**
	 * Return the current Type name
	 *
<<<<<<< HEAD
	 * @return string
=======
	 * @return  string
	 *
	 * @since   3.2
>>>>>>> upstream/master
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
<<<<<<< HEAD
	 * @return	object
=======
	 * @return  JMicrodata  Instance of $this
	 *
	 * @since   3.2
>>>>>>> upstream/master
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
<<<<<<< HEAD
	 * @return	string
=======
	 * @return  string
	 *
	 * @since   3.2
>>>>>>> upstream/master
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
<<<<<<< HEAD
	 * 
	 * @return	object
=======
	 *
	 * @return  JMicrodata  Instance of $this
	 *
	 * @since   3.2
>>>>>>> upstream/master
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
<<<<<<< HEAD
	 * @return	string
=======
	 * @return  string
	 *
	 * @since   3.2
>>>>>>> upstream/master
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
<<<<<<< HEAD
	 * @return	object
=======
	 * @return  JMicrodata  Instance of $this
	 *
	 * @since   3.2
>>>>>>> upstream/master
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
<<<<<<< HEAD
	 * @return	string
=======
	 * @return  string
	 *
	 * @since   3.2
>>>>>>> upstream/master
	 */
	public function getFallbackType()
	{
		return $this->fallbackType;
	}

	/**
	 * Return the fallbackProperty variable
	 *
<<<<<<< HEAD
	 * @return	string
=======
	 * @return  string
	 *
	 * @since   3.2
>>>>>>> upstream/master
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
<<<<<<< HEAD
	 * @return	string
=======
	 * @return  string
	 *
	 * @since   3.2
>>>>>>> upstream/master
	 */
	public function display($displayType = '', $emptyOutput = false)
	{
		// Initialize the HTML to output
		$html = ($this->content !== null) ? $this->content : '';

		// Control if the Microdata output is enabled, otherwise return the content or empty string
		if (!$this->enabled)
		{
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
<<<<<<< HEAD
				/* Process and return the HTML in an automatic way,
				 * with the Property expected Types and display the Microdata in the right way,
				 * check if the Property is normal, nested or must be rendered in a metadata tag */
=======
				/*
				 * Process and return the HTML in an automatic way,
				 * with the Property expected Types and display the Microdata in the right way,
				 * check if the Property is normal, nested or must be rendered in a metadata tag
				 */
>>>>>>> upstream/master
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

<<<<<<< HEAD
						/* Check if a Content is available,
						 * otherwise Fallback to an 'inline' display type */
=======
						// Check if a Content is available, otherwise Fallback to an 'inline' display type
>>>>>>> upstream/master
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
<<<<<<< HEAD
							$html = static::htmlProperty($this->property)
								. " " . static::htmlScope($nestedType);
=======
							$html = static::htmlProperty($this->property) . ' ' . static::htmlScope($nestedType);
>>>>>>> upstream/master

							if ($nestedProperty)
							{
								$html .= ' ' . static::htmlProperty($nestedProperty);
							}
						}
<<<<<<< HEAD
						break;

					case 'meta':
						/* Check if the Content value is available,
						 * otherwise Fallback to an 'inline' display Type */
						if ($this->content !== null)
						{
							$html = ($this->machineContent !== null) ? $this->machineContent : $this->content;
							$html = static::htmlMeta($html, $this->property)
								. $this->content;
=======

						break;

					case 'meta':
						// Check if the Content value is available, otherwise Fallback to an 'inline' display Type
						if ($this->content !== null)
						{
							$html = ($this->machineContent !== null) ? $this->machineContent : $this->content;
							$html = static::htmlMeta($html, $this->property) . $this->content;
>>>>>>> upstream/master
						}
						else
						{
							$html = static::htmlProperty($this->property);
						}
<<<<<<< HEAD
						break;

					default:
						/* Default expected display type = 'normal'
						 * Check if the Content value is available,
						 * otherwise Fallback to an 'inline' display Type */
=======

						break;

					default:
						/*
						 * Default expected display type = 'normal'
						 * Check if the Content value is available,
						 * otherwise Fallback to an 'inline' display Type
						 */
>>>>>>> upstream/master
						if ($this->content !== null)
						{
							$html = static::htmlSpan($this->content, $this->property);
						}
						else
						{
							$html = static::htmlProperty($this->property);
						}
<<<<<<< HEAD
=======

>>>>>>> upstream/master
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
<<<<<<< HEAD
						$html = static::htmlScope($type::scope())
							. ' ' . static::htmlProperty($this->fallbackProperty);
=======
						$html = static::htmlScope($type::scope()) . ' ' . static::htmlProperty($this->fallbackProperty);
>>>>>>> upstream/master
						break;
				}
			}
			else
			{
<<<<<<< HEAD
				/* Process and return the HTML in an automatic way,
				 * with the Property expected Types an display the Microdata in the right way,
				* check if the Property is nested or must be rendered in a metadata tag */
				switch (static::getExpectedDisplayType($this->fallbackType, $this->fallbackProperty))
				{
					case 'meta':
						/* Check if the Content value is available,
						 * otherwise Fallback to an 'inline' display Type */
=======
				/*
				 * Process and return the HTML in an automatic way,
				 * with the Property expected Types an display the Microdata in the right way,
				 * check if the Property is nested or must be rendered in a metadata tag
				 */
				switch (static::getExpectedDisplayType($this->fallbackType, $this->fallbackProperty))
				{
					case 'meta':
						// Check if the Content value is available, otherwise Fallback to an 'inline' display Type
>>>>>>> upstream/master
						if ($this->content !== null)
						{
							$html = ($this->machineContent !== null) ? $this->machineContent : $this->content;
							$html = static::htmlMeta($html, $this->fallbackProperty, $this->fallbackType);
						}
						else
						{
<<<<<<< HEAD
							$html = static::htmlScope($this->fallbackType)
								. ' ' . static::htmlProperty($this->fallbackProperty);
						}
						break;

					default:
						/* Default expected display type = 'normal'
						 * Check if the Content value is available,
						 * otherwise Fallback to an 'inline' display Type */
=======
							$html = static::htmlScope($this->fallbackType) . ' ' . static::htmlProperty($this->fallbackProperty);
						}

						break;

					default:
						/*
						 * Default expected display type = 'normal'
						 * Check if the Content value is available,
						 * otherwise Fallback to an 'inline' display Type
						 */
>>>>>>> upstream/master
						if ($this->content !== null)
						{
							$html = static::htmlSpan($this->content, $this->fallbackProperty);
							$html = static::htmlSpan($html, '', $this->fallbackType);
						}
						else
						{
<<<<<<< HEAD
							$html = static::htmlScope($this->fallbackType)
								. ' ' . static::htmlProperty($this->fallbackProperty);
						}
=======
							$html = static::htmlScope($this->fallbackType) . ' ' . static::htmlProperty($this->fallbackProperty);
						}

>>>>>>> upstream/master
						break;
				}
			}
		}
		elseif (!$this->fallbackProperty && $this->fallbackType !== null)
		{
			$html = static::htmlScope($this->fallbackType);
		}

		// Reset params
		$this->content          = null;
		$this->property         = null;
		$this->fallbackProperty = null;
		$this->fallbackType     = null;
		$this->fallback         = false;

		return $html;
	}

	/**
	 * Return the HTML of the current Scope
	 *
<<<<<<< HEAD
	 * @return	string
=======
	 * @return  string
	 *
	 * @since   3.2
>>>>>>> upstream/master
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
<<<<<<< HEAD
	 * @return	string
=======
	 * @return  string
	 *
	 * @since   3.2
>>>>>>> upstream/master
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
<<<<<<< HEAD
	 * @return	string
=======
	 * @return  string
	 *
	 * @since   3.2
>>>>>>> upstream/master
	 */
	public static function sanitizeProperty($property)
	{
		return lcfirst(trim($property));
	}

	/**
<<<<<<< HEAD
	 * Return an array with all Types and Properties 
	 *
	 * @return	array
=======
	 * Return an array with all Types and Properties
	 *
	 * @return  array
	 *
	 * @since   3.2
>>>>>>> upstream/master
	 */
	public static function getTypes()
	{
		static::loadTypes();

		return static::$types;
	}

<<<<<<< HEAD
	/**
	 * Return an array with all available Types
	 *
	 * @return	array
=======
	/**
	 * Return an array with all available Types
	 *
	 * @return  array
	 *
	 * @since   3.2
>>>>>>> upstream/master
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
<<<<<<< HEAD
=======
	 *
	 * @since   3.2
>>>>>>> upstream/master
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
<<<<<<< HEAD
=======
	 *
	 * @since   3.2
>>>>>>> upstream/master
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
<<<<<<< HEAD
	 * @return	boolean
=======
	 * @return  boolean
	 *
	 * @since   3.2
>>>>>>> upstream/master
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
<<<<<<< HEAD
	 * @return	boolean
=======
	 * @return  boolean
	 *
	 * @since   3.2
>>>>>>> upstream/master
	 */
	public static function isTypeAvailable($type)
	{
		static::loadTypes();

<<<<<<< HEAD
		return ( array_key_exists($type, static::$types) ) ? true : false;
=======
		return (array_key_exists($type, static::$types)) ? true : false;
>>>>>>> upstream/master
	}

	/**
	 * Return the microdata in a <meta> tag with the machine content inside.
	 *
	 * @param   string   $content   The machine content to display
	 * @param   string   $property  The Property
	 * @param   string   $scope     Optional, the Type scope to display
	 * @param   boolean  $inverse   Optional, default = false, inverse the $scope with the $property
	 *
<<<<<<< HEAD
	 * @return	string
=======
	 * @return  string
>>>>>>> upstream/master
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
	 * Return the microdata in an <span> tag.
	 *
	 * @param   string   $content   The human value
	 * @param   string   $property  Optional, the human value to display
	 * @param   string   $scope     Optional, the Type scope to display
	 * @param   boolean  $inverse   Optional, default = false, inverse the $scope with the $property
	 *
<<<<<<< HEAD
	 * @return	string
=======
	 * @return  string
>>>>>>> upstream/master
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
<<<<<<< HEAD
	 * @return	string
=======
	 * @return  string
>>>>>>> upstream/master
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
<<<<<<< HEAD
=======
	 *
	 * @since   3.2
>>>>>>> upstream/master
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
<<<<<<< HEAD
=======
	 *
	 * @since   3.2
>>>>>>> upstream/master
	 */
	public static function htmlProperty($property)
	{
		return "itemprop='$property'";
	}
}
