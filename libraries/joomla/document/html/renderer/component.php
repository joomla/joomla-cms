<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Component renderer
 *
 * @package		Joomla.Platform
 * @subpackage	Document
 * @since		11.1
 */
class JDocumentRendererComponent extends JDocumentRenderer
{
	/**
	 * Renders a component script and returns the results as a string
	 *
	 * @param	string $component	The name of the component to render
	 * @param	array $params		Associative array of values
	 *
	 * @return	string				The output of the script
	 */
	public function render($component = null, $params = array(), $content = null)
	{
		return $content;
	}
}
