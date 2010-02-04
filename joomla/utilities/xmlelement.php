<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage  Utilities
 * @copyright   Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Wrapper class for php SimpleXMLElement.
 *
 * @package		Joomla.Framework
 * @subpackage  Utilities
 * @since		1.6
 */
class JXMLElement extends SimpleXMLElement
{

	/**
	 * Get the name of the element.
	 * Warning: don't use getName() as it's broken up to php 5.2.3
	 *
	 * @return string
	 */
	public function name()
	{
		if(version_compare(phpversion(), '5.2.3', '>'))
		{
			return (string)$this->getName();
		}

		// workaround php bug number 41867, fixed in 5.2.4
		return (string)$this->aaa->getName();
	}

	/**
	 * LEGACY - Gets the element 'data'.
	 *
	 * @return string
	 */
	public function data()
	{
		return (string)$this;
	}

	/**
	 * LEGACY - Gets an elements attribute by name.
	 *
	 * @param string $name
	 * @return string
	 */
	public function getAttribute($name)
	{
		return (string)$this->attributes()->$name;
	}

	/**
	 * Return a well-formed XML string based on SimpleXML element
	 *
	 * @param boolean $compressed Should we use indentation and newlines ?
	 * @param integer $level Indentaion level.
	 * @return string
	 */
	public function asFormattedXML($compressed = false, $indent = "\t", $level = 0)
	{
		$out = '';

		//-- Start a new line, indent by the number indicated in $level
		$out .=($compressed) ? '' : "\n".str_repeat($indent, $level);

		//-- Add a <, and add the name of the tag
		$out .= '<'.$this->getName();

		//-- For each attribute, add attr="value"
		foreach($this->attributes() as $attr) {
			$out .= ' '.$attr->getName().'="'.htmlspecialchars((string)$attr, ENT_COMPAT, 'UTF-8').'"';
		}

		//-- If there are no children and it contains no data, end it off with a />
		if( ! count($this->children())
		&& !(string)$this)
		{
			$out .= " />";
		}
		else
		{
			//-- If there are children
			if(count($this->children()))
			{
				//-- Close off the start tag
				$out .= '>';

				$level ++;

				//-- For each child, call the asFormattedXML function (this will ensure that all children are added recursively)
				foreach($this->children() as $child)
				{
					$out .= $child->asFormattedXML($compressed, $indent, $level);
				}

				$level --;

				//-- Add the newline and indentation to go along with the close tag
				$out .=($compressed) ? '' : "\n".str_repeat($indent, $level);
			}
			elseif((string)$this)
			{
				//-- If there is data, close off the start tag and add the data
				$out .= '>'.htmlspecialchars((string)$this, ENT_COMPAT, 'UTF-8');
			}

			//-- Add the end tag
			$out .= '</'.$this->getName().'>';
		}

		return $out;
	}

}
