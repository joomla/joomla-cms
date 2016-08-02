<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  utils
 * @copyright   Copyright (C) 2010 - 2015 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

/**
 * A helper class to read and parse "collection" update XML files over the web
 */
class FOFUtilsUpdateCollection
{
	/**
	 * Reads a "collection" XML update source and returns the complete tree of categories
	 * and extensions applicable for platform version $jVersion
	 *
	 * @param   string  $url       The collection XML update source URL to read from
	 * @param   string  $jVersion  Joomla! version to fetch updates for, or null to use JVERSION
	 *
	 * @return  array  A list of update sources applicable to $jVersion
	 */
	public function getAllUpdates($url, $jVersion = null)
	{
		// Get the target platform
		if (is_null($jVersion))
		{
			$jVersion = JVERSION;
		}

		// Initialise return value
		$updates = array(
			'metadata'		=> array(
				'name'			=> '',
				'description'	=> '',
			),
			'categories'	=> array(),
			'extensions'	=> array(),
		);

		// Download and parse the XML file
		$donwloader = new FOFDownload();
		$xmlSource = $donwloader->getFromURL($url);

		try
		{
			$xml = new SimpleXMLElement($xmlSource, LIBXML_NONET);
		}
		catch(Exception $e)
		{
			return $updates;
		}

		// Sanity check
		if (($xml->getName() != 'extensionset'))
		{
			unset($xml);

			return $updates;
		}

		// Initialise return value with the stream metadata (name, description)
		$rootAttributes = $xml->attributes();
		foreach ($rootAttributes as $k => $v)
		{
			$updates['metadata'][$k] = (string)$v;
		}

		// Initialise the raw list of updates
		$rawUpdates = array(
			'categories'	=> array(),
			'extensions'	=> array(),
		);

		// Segregate the raw list to a hierarchy of extension and category entries
		/** @var SimpleXMLElement $extension */
		foreach ($xml->children() as $extension)
		{
			switch ($extension->getName())
			{
				case 'category':
					// These are the parameters we expect in a category
					$params = array(
						'name'					=> '',
						'description'			=> '',
						'category'				=> '',
						'ref'					=> '',
						'targetplatformversion'	=> $jVersion,
					);

					// These are the attributes of the element
					$attributes = $extension->attributes();

					// Merge them all
					foreach ($attributes as $k => $v)
					{
						$params[$k] = (string)$v;
					}

					// We can't have a category with an empty category name
					if (empty($params['category']))
					{
						continue;
					}

					// We can't have a category with an empty ref
					if (empty($params['ref']))
					{
						continue;
					}

					if (empty($params['description']))
					{
						$params['description'] = $params['category'];
					}

					if (!array_key_exists($params['category'], $rawUpdates['categories']))
					{
						$rawUpdates['categories'][$params['category']] = array();
					}

					$rawUpdates['categories'][$params['category']][] = $params;

					break;

				case 'extension':
					// These are the parameters we expect in a category
					$params = array(
						'element'				=> '',
						'type'					=> '',
						'version'				=> '',
						'name'					=> '',
						'detailsurl'			=> '',
						'targetplatformversion'	=> $jVersion,
					);

					// These are the attributes of the element
					$attributes = $extension->attributes();

					// Merge them all
					foreach ($attributes as $k => $v)
					{
						$params[$k] = (string)$v;
					}

					// We can't have an extension with an empty element
					if (empty($params['element']))
					{
						continue;
					}

					// We can't have an extension with an empty type
					if (empty($params['type']))
					{
						continue;
					}

					// We can't have an extension with an empty version
					if (empty($params['version']))
					{
						continue;
					}

					if (empty($params['name']))
					{
						$params['name'] = $params['element'] . ' ' . $params['version'];
					}

					if (!array_key_exists($params['type'], $rawUpdates['extensions']))
					{
						$rawUpdates['extensions'][$params['type']] = array();
					}

					if (!array_key_exists($params['element'], $rawUpdates['extensions'][$params['type']]))
					{
						$rawUpdates['extensions'][$params['type']][$params['element']] = array();
					}

					$rawUpdates['extensions'][$params['type']][$params['element']][] = $params;
					break;

				default:
					break;
			}
		}

		unset($xml);

		if (!empty($rawUpdates['categories']))
		{
			foreach ($rawUpdates['categories'] as $category => $entries)
			{
				$update = $this->filterListByPlatform($entries, $jVersion);
				$updates['categories'][$category] = $update;
			}
		}

		if (!empty($rawUpdates['extensions']))
		{
			foreach ($rawUpdates['extensions'] as $type => $extensions)
			{
				$updates['extensions'][$type] = array();

				if (!empty($extensions))
				{
					foreach ($extensions as $element => $entries)
					{
						$update = $this->filterListByPlatform($entries, $jVersion);
						$updates['extensions'][$type][$element] = $update;
					}
				}
			}
		}

		return $updates;
	}

	/**
	 * Filters a list of updates, returning only those available for the
	 * specified platform version $jVersion
	 *
	 * @param   array   $updates   An array containing update definitions (categories or extensions)
	 * @param   string  $jVersion  Joomla! version to fetch updates for, or null to use JVERSION
	 *
	 * @return  array|null  The update definition that is compatible, or null if none is compatible
	 */
	private function filterListByPlatform($updates, $jVersion = null)
	{
		// Get the target platform
		if (is_null($jVersion))
		{
			$jVersion = JVERSION;
		}

		$versionParts = explode('.', $jVersion, 4);
		$platformVersionMajor = $versionParts[0];
		$platformVersionMinor = (count($versionParts) > 1) ? $platformVersionMajor . '.' . $versionParts[1] : $platformVersionMajor;
		$platformVersionNormal = (count($versionParts) > 2) ? $platformVersionMinor . '.' . $versionParts[2] : $platformVersionMinor;
		$platformVersionFull = (count($versionParts) > 3) ? $platformVersionNormal . '.' . $versionParts[3] : $platformVersionNormal;

		$pickedExtension = null;
		$pickedSpecificity = -1;

		foreach ($updates as $update)
		{
			// Test the target platform
			$targetPlatform = (string)$update['targetplatformversion'];

			if ($targetPlatform === $platformVersionFull)
			{
				$pickedExtension = $update;
				$pickedSpecificity = 4;
			}
			elseif (($targetPlatform === $platformVersionNormal) && ($pickedSpecificity <= 3))
			{
				$pickedExtension = $update;
				$pickedSpecificity = 3;
			}
			elseif (($targetPlatform === $platformVersionMinor) && ($pickedSpecificity <= 2))
			{
				$pickedExtension = $update;
				$pickedSpecificity = 2;
			}
			elseif (($targetPlatform === $platformVersionMajor) && ($pickedSpecificity <= 1))
			{
				$pickedExtension = $update;
				$pickedSpecificity = 1;
			}
		}

		return $pickedExtension;
	}

	/**
	 * Returns only the category definitions of a collection
	 *
	 * @param   string  $url       The URL of the collection update source
	 * @param   string  $jVersion  Joomla! version to fetch updates for, or null to use JVERSION
	 *
	 * @return  array  An array of category update definitions
	 */
	public function getCategories($url, $jVersion = null)
	{
		$allUpdates = $this->getAllUpdates($url, $jVersion);

		return $allUpdates['categories'];
	}

	/**
	 * Returns the update source for a specific category
	 *
	 * @param   string  $url       The URL of the collection update source
	 * @param   string  $category  The category name you want to get the update source URL of
	 * @param   string  $jVersion  Joomla! version to fetch updates for, or null to use JVERSION
	 *
	 * @return  string|null  The update stream URL, or null if it's not found
	 */
	public function getCategoryUpdateSource($url, $category, $jVersion = null)
	{
		$allUpdates = $this->getAllUpdates($url, $jVersion);

		if (array_key_exists($category, $allUpdates['categories']))
		{
			return $allUpdates['categories'][$category]['ref'];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Get a list of updates for extensions only, optionally of a specific type
	 *
	 * @param   string  $url       The URL of the collection update source
	 * @param   string  $type      The extension type you want to get the update source URL of, empty to get all
	 *                             extension types
	 * @param   string  $jVersion  Joomla! version to fetch updates for, or null to use JVERSION
	 *
	 * @return  array|null  An array of extension update definitions or null if none is found
	 */
	public function getExtensions($url, $type = null, $jVersion = null)
	{
		$allUpdates = $this->getAllUpdates($url, $jVersion);

		if (empty($type))
		{
			return $allUpdates['extensions'];
		}
		elseif (array_key_exists($type, $allUpdates['extensions']))
		{
			return $allUpdates['extensions'][$type];
		}
		else
		{
			return null;
		}
	}

	/**
	 * Get the update source URL for a specific extension, based on the type and element, e.g.
	 * type=file and element=joomla is Joomla! itself.
	 *
	 * @param   string  $url       The URL of the collection update source
	 * @param   string  $type      The extension type you want to get the update source URL of
	 * @param   string  $element   The extension element you want to get the update source URL of
	 * @param   string  $jVersion  Joomla! version to fetch updates for, or null to use JVERSION
	 *
	 * @return  string|null  The update source URL or null if the extension is not found
	 */
	public function getExtensionUpdateSource($url, $type, $element, $jVersion = null)
	{
		$allUpdates = $this->getExtensions($url, $type, $jVersion);

		if (empty($allUpdates))
		{
			return null;
		}
		elseif (array_key_exists($element, $allUpdates))
		{
			return $allUpdates[$element]['detailsurl'];
		}
		else
		{
			return null;
		}
	}
}