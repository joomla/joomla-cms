<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Registry
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * XML format handler for JRegistry.
 *
 * @package 	Joomla.Framework
 * @subpackage	Registry
 * @since		1.5
 */
class JRegistryFormatXML extends JRegistryFormat
{
	/**
	 * Converts an object into an XML formatted string.
	 * 	-	If more than two levels of nested groups are necessary, since INI is not
	 * 		useful, XML or another format should be used.
	 *
	 * @param	object	Data source object.
	 * @param	array	Options used by the formatter.
	 * @return	string	XML formatted string.
	 */
	public function objectToString($object, $params)
	{
		$depth = 1;
		$retval = "<?xml version=\"1.0\" ?>\n<config>\n";
		foreach (get_object_vars($object) as $key=>$item) {
			if (is_object($item)) {
				$retval .= "\t<group name=\"".$key."\">\n";
				$retval .= $this->_buildXMLstringLevel($item, $depth+1);
				$retval .= "\t</group>\n";
			} else {
				$retval .= "\t<entry name=\"".$key."\">".$item."</entry>\n";
			}
		}
		$retval .= '</config>';
		return $retval;
	}

	/**
	 * Method to build a level of the XML string -- called recursively
	 *
	 * @param	object	Object that represents a node of the xml document
	 * @param	int		The depth in the XML tree of the $object node
	 * @return	string	XML string
	 */
	protected function _buildXMLstringLevel($object, $depth)
	{
		// Initialise variables.
		$retval = '';
		$tab	= '';
		for ($i = 1;$i <= $depth; $i++) {
			$tab .= "\t";
		}

		foreach (get_object_vars($object) as $key=>$item) {
			if (is_object($item)) {
				$retval .= $tab."<group name=\"".$key."\">\n";
				$retval .= $this->_buildXMLstringLevel($item, $depth+1);
				$retval .= $tab."</group>\n";
			} else {
				$retval .= $tab."<entry name=\"".$key."\">".$item."</entry>\n";
			}
		}
		return $retval;
	}

	/**
	 * Parse a XML formatted string and convert it into an object.
	 *
	 * @param	string	XML Formatted String
	 * @return	object	Data object.
	 */
	public function stringToObject($data, $namespace='')
	{
		return true;
	}
}
