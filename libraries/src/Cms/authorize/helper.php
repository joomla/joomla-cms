<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Authorize
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

class JAuthorizeHelper
{
	/**
	 * Method to return a list of actions from a file for which permissions can be set.
	 *
	 * @param   string  $file   The path to the XML file.
	 * @param   string  $xpath  An optional xpath to search for the fields.
	 *
	 * @return  boolean|array   False if case of error or the list of actions available.
	 *
	 * @since   12.1
	 */
	public static function getActionsFromFile($file, $xpath = "/access/section[@name='component']/")
	{
		if (!is_file($file) || !is_readable($file))
		{
			// If unable to find the file return false.
			return false;
		}
		else
		{
			// Else return the actions from the xml.
			$xml = simplexml_load_file($file);

			return self::getActionsFromData($xml, $xpath);
		}
	}

	/**
	 * Method to return a list of actions from a string or from an xml for which permissions can be set.
	 *
	 * @param   string|SimpleXMLElement  $data   The XML string or an XML element.
	 * @param   string                   $xpath  An optional xpath to search for the fields.
	 *
	 * @return  boolean|array   False if case of error or the list of actions available.
	 *
	 * @since   12.1
	 */
	public static function getActionsFromData($data, $xpath = "/access/section[@name='component']/")
	{
		// If the data to load isn't already an XML element or string return false.
		if ((!($data instanceof SimpleXMLElement)) && (!is_string($data)))
		{
			return false;
		}

		// Attempt to load the XML if a string.
		if (is_string($data))
		{
			try
			{
				$data = new SimpleXMLElement($data);
			}
			catch (Exception $e)
			{
				return false;
			}

			// Make sure the XML loaded correctly.
			if (!$data)
			{
				return false;
			}
		}

		// Initialise the actions array
		$actions = array();

		// Get the elements from the xpath
		$elements = $data->xpath($xpath . 'action[@name][@title][@description]');

		// If there some elements, analyse them
		if (!empty($elements))
		{
			foreach ($elements as $action)
			{
				// Add the action to the actions array
				$actions[] = (object) array(
					'name' => (string) $action['name'],
					'title' => (string) $action['title'],
					'description' => (string) $action['description']
				);
			}
		}

		// Finally return the actions array
		return $actions;
	}

	/**
	 * Method to get the extension name from the asset name.
	 *
	 * @param   string  $asset  Asset Name
	 *
	 * @return  string  Extension Name.
	 *
	 * @since    1.6
	 */
	public static function getExtensionNameFromAsset($asset)
	{
		static $loaded = array();

		if (!isset($loaded[$asset]))
		{
			if (is_numeric($asset))
			{
				$table = JTable::getInstance('Asset');
				$table->load($asset);
				$assetName = $table->name;
			}
			else
			{
				$assetName = $asset;
			}

			$firstDot = strpos($assetName, '.');

			if ($assetName !== 'root.1' && $firstDot !== false)
			{
				$assetName = substr($assetName, 0, $firstDot);
			}

			$loaded[$asset] = $assetName;
		}

		return $loaded[$asset];
	}

	/**
	 * Get cleaned asset id
	 *
	 * @param   int|string  $assetId  Asset Id
	 *
	 * @return  int|string  Asset Id
	 *
	 * @since    4.0
	 */
	public static function cleanAssetId($assetId)
	{
		$assetId = self::cleanRegex($assetId);

		return empty($assetId) ? (is_numeric($assetId) ? 1 : 'root.1') : $assetId;
	}

	/**
	 * Get cleaned action name
	 *
	 * @param   string  $action  Action
	 *
	 * @return  string  Action
	 *
	 * @since    4.0
	 */
	public static function cleanAction($action)
	{
		return  self::cleanRegex($action);
	}

	/**
	 * Clean using regex
	 *
	 * @param   int|string  $idToClean Variable to clean
	 *
	 * @return  int|string  Cleaned variable
	 *
	 * @since    4.0
	 */
	private static function cleanRegex($idToClean)
	{
		return strtolower(preg_replace('#[\s\-]+#', '.', trim($idToClean)));
	}
}