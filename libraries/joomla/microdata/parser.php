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
 * PHP class for parsing the HTML markup and
 * convert the data-* HTML5 attributes in Microdata semantics
 *
 * @package     Joomla.Platform
 * @subpackage  Microdata
 * @since       3.3
 */
class JMicrodataParser
{
	/**
	 * The type of semantic, will be an instance of JMicrodata
	 *
	 * @var   null
	 * @since 3.3
	 */
	protected $handler = null;

	/**
	 * The suffix to search for when parsing the data-* HTML5 attribute
	 *
	 * @var   array
	 * @since 3.3
	 */
	protected $suffix = array('sd');

	/**
	 * Initialize the class and setup the default $semantic, Microdata
	 *
	 * @param   null  $suffix  The suffix to search for when parsing the data-* HTML5 attribute
	 *
	 * @since   3.3
	 */
	public function __construct($suffix = null)
	{
		if ($suffix)
		{
			$this->suffix($suffix);
		}

		$this->handler = new JMicrodata;
	}

	/**
	 * Return the $handler, which is an instance of JMicrodata
	 *
	 * @return JMicrodata
	 *
	 * @since  3.3
	 */
	public function getHandler()
	{
		return $this->handler;
	}

	/**
	 * Setup the $suffix to search for when parsing the data-* HTML5 attribute
	 *
	 * @param   mixed  $suffix  The suffix
	 *
	 * @return  JMicrodataParser
	 *
	 * @since   3.3
	 */
	public function suffix($suffix)
	{
		if (is_array($suffix))
		{
			while ($string = array_pop($suffix))
			{
				$this->addSuffix($string);
			}

			return $this;
		}

		$this->addSuffix($suffix);

		return $this;
	}

	/**
	 * Add a new $suffix to search for when parsing the data-* HTML5 attribute
	 *
	 * @param   string  $string  The suffix
	 *
	 * @return  void
	 *
	 * @since   3.3
	 */
	protected function addSuffix($string)
	{
		$string = trim(strtolower((string) $string));

		// Avoid adding a duplicate suffix, also the suffix must be at least one character long
		if (array_search($string, $this->suffix) || empty($string))
		{
			return;
		}

		// Add the new suffix
		array_push($this->suffix, $string);
	}

	/**
	 * Remove a $suffix entry
	 *
	 * @param   string  $string  The suffix
	 *
	 * @return  JMicrodataParser
	 *
	 * @since   3.3
	 */
	public function removeSuffix($string)
	{
		$string = strtolower((string) $string);

		// Search and remove the suffix
		unset(
			$this->suffix[array_search($string, $this->suffix)]
		);

		return $this;
	}

	/**
	 * Return the current $suffix
	 *
	 * @return string
	 *
	 * @since  3.3
	 */
	public function getSuffix()
	{
		return $this->suffix;
	}

	/**
	 * Parse the unit param that will be used to setup the JMicrodata class,
	 * e.g. giving the following: $string = 'Type.property.EType';
	 * will return an array:
	 * array(
	 *     'type'        => 'Type,
	 *     'property'    => 'property'
	 *     'expectedType => 'EType'
	 * );
	 *
	 * @param   string  $string  The string to parse
	 *
	 * @return  array
	 */
	protected static function parseParam($string)
	{
		// The default array
		$params = array(
			'type' => null,
			'property' => null,
			'expectedType' => null
		);

		// Sanitize the $string and parse
		$string = explode('.', trim((string) $string));

		// If no matches found return the default array
		if (empty($string[0]))
		{
			return $params;
		}

		// If the first letter is uppercase, then the param string could be 'Type.property.EType', otherwise it should be the 'property.EType'
		if (ctype_upper($string[0]{0}))
		{
			$params['type'] = $string[0];

			// If the first letter is lowercase, then it should be the property, otherwise return
			if (count($string) > 1 && !empty($string[1]) && ctype_lower($string[1]{0}))
			{
				$params['property'] = $string[1];

				// If the first letter is uppercase, then it should be expected Type, otherwise return
				if (count($string) > 2 && !empty($string[2]) && ctype_upper($string[2]{0}))
				{
					$params['expectedType'] = $string[2];
				}
			}
		}
		else
		{
			$params['property'] = $string[0];

			// If the first letter is uppercase, then it should be the expectedType
			if (count($string) > 1 && !empty($string[1]) && ctype_upper($string[1]{0}))
			{
				$params['expectedType'] = $string[1];
			}
		}

		return $params;
	}

	/**
	 * Parse the params that will be used to setup the JMicrodata class,
	 * e.g giving the following: $string ='Type Type.property.EType ... FType.fProperty gProperty.EType sProperty';
	 * will return an array:
	 * array(
	 *     'setType'   => 'Type',
	 *     'fallbacks' => array(
	 *         'specialized' => array(
	 *             'Type'  => array('property'  => 'EType'),
	 *             'FType' => array('fproperty' => null)
	 *             ...
	 *         ),
	 *         'global' => array(
	 *              ...
	 *             'gProperty' => 'EType',
	 *             'sProperty' => null
	 *         )
	 *     )
	 * );
	 *
	 * @param   string  $string  The string to parse
	 *
	 * @return  array
	 */
	protected static function parseParams($string)
	{
		// The default array
		$params = array(
			'setType'   => null,
			'fallbacks' => array(
				'specialized' => array(),
				'global' => array()
			)
		);

		// Sanitize the $string, remove single and multiple whitespaces
		$string = trim(preg_replace('/\s+/', ' ', (string) $string));

		// Break the strings in small param chunks
		$string = explode(' ', $string);

		// Parse the small param chunks
		foreach ($string as $match)
		{
			$tmp          = self::parseParam($match);
			$type         = $tmp['type'];
			$property     = $tmp['property'];
			$expectedType = $tmp['expectedType'];

			// If a 'type' is available and there is no 'property', then it should be a 'setType'
			if ($type && !$property && !$params['setType'])
			{
				$params['setType'] = $type;
			}

			// If a 'property' is available and there is no 'type', then it should be a 'global' fallback
			if (!$type && $property)
			{
				$params['fallbacks']['global'][$property] = $expectedType;
			}

			// If both 'type' and 'property' is available, then it should be a 'specialized' fallback
			if ($type && $property && !array_key_exists($type, $params['fallbacks']['specialized']))
			{
				$params['fallbacks']['specialized'][$type] = array($property => $expectedType);
			}
		}

		return $params;
	}

	/**
	 * Generate the Microdata semantics
	 *
	 * @param   array  $params  The params used to setup the JMicrodata library
	 *
	 * @return  string
	 */
	protected function display($params)
	{
		$html       = '';
		$setType    = $params['setType'];

		// Specialized fallbacks
		$sFallbacks = $params['fallbacks']['specialized'];

		// Global fallbacks
		$gFallbacks = $params['fallbacks']['global'];

		// Set the current Type if available
		if ($setType)
		{
			$this->handler->setType($setType);
		}

		// If no properties available and there is a 'setType', return and display the scope
		if ($setType && !$sFallbacks && !$gFallbacks)
		{
			return $this->handler->displayScope();
		}

		// Get the current Type
		$currentType = $this->handler->getType();

		// Check if there is an available 'specialized' fallback property for the current Type
		if ($sFallbacks && array_key_exists($currentType, $sFallbacks))
		{
			$property     = key($sFallbacks[$currentType]);
			$expectedType = $sFallbacks[$currentType][$property];

			$html .= $this->handler->property($property)->display('inline');

			// Check if an expected Type is available and it is valid
			if ($expectedType
				&& in_array($expectedType, JMicrodata::getExpectedTypes($currentType, $property)))
			{
				// Update the current Type
				$this->handler->setType($expectedType);

				// Display the scope
				$html .= ' ' . $this->handler->displayScope();
			}

			return $html;
		}

		// Check if there is an available 'global' fallback property for the current Type
		if ($gFallbacks)
		{
			foreach ($gFallbacks as $property => $expectedType)
			{
				// Check if the property is available in the current Type
				if (JMicrodata::isPropertyInType($currentType, $property))
				{
					$html .= $this->handler->property($property)->display('inline');

					// Check if an expected Type is available
					if ($expectedType
						&& in_array($expectedType, JMicrodata::getExpectedTypes($currentType, $property)))
					{
						// Update the current Type
						$this->handler->setType($expectedType);

						// Display the scope
						$html .= ' ' . $this->handler->displayScope();
					}

					return $html;
				}
			}
		}

		return $html;
	}

	/**
	 * Find the first data-suffix attribute match available in the node
	 * e.g. <tag data-one="suffix" data-two="suffix" /> will return 'one'
	 *
	 * @param   DOMElement  $node  The node to parse
	 *
	 * @return  mixed
	 *
	 * @since   3.3
	 */
	protected function getNodeSuffix(DOMElement $node)
	{
		foreach ($this->suffix as $suffix)
		{
			if ($node->hasAttribute("data-$suffix"))
			{
				return $suffix;
			}
		}

		return null;
	}

	/**
	 * Parse the HTML and replace the data-* HTML5 attributes with Microdata semantics
	 *
	 * @param   string  $html  The HTML to parse
	 *
	 * @return  string
	 *
	 * @since   3.3
	 */
	public function parse($html)
	{
		// Create a new DOMDocument
		$doc = new DOMDocument;
		$doc->loadHTML($html);

		// Create a new DOMXPath, to make XPath queries
		$xpath = new DOMXPath($doc);

		// Create the query pattern
		$query = array();

		foreach ($this->suffix as $suffix)
		{
			array_push($query, "//*[@data-" . $suffix . "]");
		}

		// Search for the data-* HTML5 attributes
		$nodeList = $xpath->query(implode('|', $query));

		// Replace each match
		foreach ($nodeList as $node)
		{
			// Retrieve the params used to setup the JMicrodata library
			$suffix    = $this->getNodeSuffix($node);
			$attribute = $node->getAttribute("data-" . $suffix);
			$params    = $this->parseParams($attribute);

			// Generate the Microdata semantic
			$semantic  = $this->display($params);

			// Replace the data-* HTML5 attributes with Microdata semantics
			$pattern   = '/data-' . $suffix . "=." . $attribute . "./";
			$html      = preg_replace($pattern, $semantic, $html, 1);
		}

		return $html;
	}
}
