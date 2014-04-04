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
 * JDocument JavaScript renderer.
 *
 * @package     Joomla.Platform
 * @subpackage  Document
 * @since       3.3
 */
class JDocumentRendererJavascript extends JDocumentRenderer
{
	/**
	 * Saves a JavaScript definition for later rendering.
	 *
	 * @param   string  $head     (unused)
	 * @param   array   $params   Associative array of values
	 * @param   string  $content  The script
	 *
	 * @return  string  The output of the script
	 *
	 * @since   3.3
	 *
	 * @note    Unused arguments are retained to preserve backward compatibility.
	 */
	public function render($head, $params = array(), $content = null)
	{
		$this->saveAsset('javascript', $params);
	}

	/**
	 * Determine the asset id.
	 * 
	 * @param   array   $params  Associative array of values.
	 * 
	 * @return  string  Asset id.
	 * 
	 * @since   3.3
	 */
	protected function getId(array $params)
	{
		// Explicitly set?
		if (isset($params['id']) && $params['id'] != '')
		{
			return $params['id'];
		}

		// Base it on the URL?
		if (isset($params['url']) && basename($params['url']) != '')
		{
			return basename($params['url']);
		}

		// Base it on the content?
		if (isset($params['content']) && $params['content'] != '')
		{
			return crc32($params['content']);
		}

		// MD5 parameter?
		if (isset($params['md5']) && $params['md5'] != '')
		{
			return crc32($params['md5']);
		}
		
		return rand(1, 100000);
	}

	/**
	 * Saves an asset for later rendering.
	 * 
	 * This is a generic method which can be used in classes extended from this class.
	 * 
	 * @param   string  $type    Asset type.
	 * @param   array   $params  Associative array of values.
	 * 
	 * @return  JDocumentAsset   The saved asset object.
	 * 
	 * @since   3.3
	 */
	protected function saveAsset($type, $params = array())
	{
		// Determine the asset id.
		$assetId = $this->getId($params);
		
		// Find existing asset object if it exists.
		$asset = $this->_doc->findAssetById($assetId);
		
		// If not found, create new asset object.
		if (is_null($asset))
		{
			$asset = new JDocumentAsset($type, $assetId);
			$this->_doc->addAsset($asset);
		}

		// Add or update URL, content or MD5 parameters.
		if (isset($params['url']))
		{
			$asset->setUrl($params['url']);
		}
		else if (isset($params['content']))
		{
			$asset->setContent($params['content']);
		}
		else
		{
			isset($params['md5']) ? $asset->setAttribute('md5', $params['md5']) : null;
		}

		// Set boolean options from comma-separated list.
		isset($params['options']) ? $asset->setOptions($params['options']) : '';

		// Set the CDN if there is one.
		isset($params['cdn']) ? $asset->setAttribute('cdn', $params['cdn']) : null;

		// Set the MS IE conditional if there is one.
		isset($params['ie']) ? $asset->setAttribute('ie', $params['ie']) : null;

		// Add dependencies.
		if (isset($params['dependson']))
		{
			// Get array from comma-separated string.
			$uses = explode(',', $params['dependson']);

			// Add each dependency in turn.			
			foreach ($uses as $use)
			{
				// Strip whitespace.
				$use = trim($use);

				// Find the dependent asset if it exists.
				$dependency = $this->_doc->findAssetById($use);
	
				// If we didn't find the dependency then create it.
				if (is_null($dependency))
				{
					$dependency = new JDocumentAsset($type, $use);
					$this->_doc->addAsset($dependency);
				}
	
				// Add the asset to the dependency graph.
				$asset->addDependency($dependency);
			}
		}

		// In debug mode, output a comment with a dump of the asset.
		if ($asset->getAttribute('debug'))
		{
			echo '<!--';
			print_r($asset);
			echo '-->';
		}

		return $asset;
	}
}
