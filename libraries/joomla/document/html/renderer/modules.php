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
 * JDocument Modules renderer
 *
 * @package		Joomla.Platform
 * @subpackage	Document
 * @since		11.1
 */
class JDocumentRendererModules extends JDocumentRenderer
{
	/**
	 * Renders multiple modules script and returns the results as a string
	 *
	 * @param	string	$name		The position of the modules to render
	 * @param	array	$params		Associative array of values
	 * @return	string	The output of the script
	 */
	public function render($position, $params = array(), $content = null)
	{
		$renderer	= $this->_doc->loadRenderer('module');
		$buffer		= '';

		foreach (JModuleHelper::getModules($position) as $mod) {
			$buffer .= $renderer->render($mod, $params, $content);
		}
		return $buffer;
	}
}