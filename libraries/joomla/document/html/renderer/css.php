<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

// Needs to autoload really.
require_once 'javascript.php';

/**
 * JDocument CSS renderer.
 *
 * @package     Joomla.Platform
 * @subpackage  Document
 * @since       3.3
 */
class JDocumentRendererCss extends JDocumentRendererJavascript
{
	/**
	 * Saves a CSS definition for later rendering.
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
		// Create/save the asset.
		$asset = $this->saveAsset('css', $params);
		
		// Add media attribute if required.
		$asset->setAttribute('media', isset($params['media']) ? $params['media'] : '');
	}
		
}
