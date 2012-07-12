<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Utilities
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Wrapper class for php SimpleXMLElement.
 *
 * @package     Joomla.Platform
 * @subpackage  Utilities
 * @since       11.1
 * @deprecated  13.3 Use SimpleXMLElement instead.
 */
class JXMLElement extends SimpleXMLElement
{
	/**
	 * Get the name of the element.
	 *
	 * @return  string
	 *
	 * @since   11.1
	 * @deprecated 13.3  Use SimpleXMLElement::getName() instead.
	 */
	public function name()
	{
		JLog::add('JXMLElement::name() is deprecated, use SimpleXMLElement::getName() instead.', JLog::WARNING, 'deprecated');
		return (string) $this->getName();
	}

	/**
	 * Legacy method to get the element data.
	 *
	 * @return  string
	 *
	 * @deprecated  12.1
	 * @since   11.1
	 */
	public function data()
	{
		// Deprecation warning.
		JLog::add('JXMLElement::data() is deprecated.', JLog::WARNING, 'deprecated');

		return (string) $this;
	}

	/**
	 * Legacy method gets an elements attribute by name.
	 *
	 * @param   string  $name  Attribute to get
	 *
	 * @return  string
	 *
	 * @since   11.1
	 *
	 * @deprecated    12.1
	 * @see           SimpleXMLElement::attributes
	 */
	public function getAttribute($name)
	{
		// Deprecation warning.
		JLog::add('JXMLelement::getAttributes() is deprecated.', JLog::WARNING, 'deprecated');

		return (string) $this->attributes()->$name;
	}

	/**
	 * Return a well-formed XML string based on SimpleXML element
	 *
	 * @param   boolean  $compressed  Should we use indentation and newlines ?
	 * @param   integer  $indent      Indention level.
	 * @param   integer  $level       The level within the document which informs the indentation.
	 *
	 * @return  string
	 *
	 * @since   11.1
	 * @deprecated 13.3  Use SimpleXMLElement::asXML() instead.
	 */
	public function asFormattedXML($compressed = false, $indent = "\t", $level = 0)
	{
		JLog::add('JXMLElement::asFormattedXML() is deprecated, use SimpleXMLElement::asXML() instead.', JLog::WARNING, 'deprecated');
		$out = '';

		// Start a new line, indent by the number indicated in $level
		$out .= ($compressed) ? '' : "\n" . str_repeat($indent, $level);

		// Add a <, and add the name of the tag
		$out .= '<' . $this->getName();

		// For each attribute, add attr="value"
		foreach ($this->attributes() as $attr)
		{
			$out .= ' ' . $attr->getName() . '="' . htmlspecialchars((string) $attr, ENT_COMPAT, 'UTF-8') . '"';
		}

		// If there are no children and it contains no data, end it off with a />
		if (!count($this->children()) && !(string) $this)
		{
			$out .= " />";
		}
		else
		{
			// If there are children
			if (count($this->children()))
			{
				// Close off the start tag
				$out .= '>';

				$level++;

				// For each child, call the asFormattedXML function (this will ensure that all children are added recursively)
				foreach ($this->children() as $child)
				{
					$out .= $child->asFormattedXML($compressed, $indent, $level);
				}

				$level--;

				// Add the newline and indentation to go along with the close tag
				$out .= ($compressed) ? '' : "\n" . str_repeat($indent, $level);

			}
			elseif ((string) $this)
			{
				// If there is data, close off the start tag and add the data
				$out .= '>' . htmlspecialchars((string) $this, ENT_COMPAT, 'UTF-8');
			}

			// Add the end tag
			$out .= '</' . $this->getName() . '>';
		}

		return $out;
	}
}
