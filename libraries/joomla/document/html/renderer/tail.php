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
 * JDocument tail renderer
 *
 * @package     Joomla.Platform
 * @subpackage  Document
 * @since       3.3
 */
class JDocumentRendererTail extends JDocumentRendererHead
{
	/**
	 * Renders the document head and returns the results as a string
	 *
	 * @param   string  $name     Name argument from jdoc:include.
	 * @param   array   $params   Associative array of values
	 * @param   string  $content  Not used.
	 *
	 * @return  string  The output of the script
	 * 
	 * @since   3.3
	 *
	 * @note    Unused arguments are retained to preserve backward compatibility.
	 */
	public function render($name, $params = array(), $content = null)
	{
		$buffer = $this->renderAssets($this->_doc->getAssets(), false);

		return $buffer;
	}

}
