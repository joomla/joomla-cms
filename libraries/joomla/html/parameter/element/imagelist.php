<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  HTML
 */

defined('JPATH_PLATFORM') or die;

/**
 * Renders a imagelist element
 *
 * @package		Joomla.Platform
 * @subpackage		Parameter
 * @since		1.5
 */

class JElementImageList extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	protected $_name = 'ImageList';

	public function fetchElement($name, $value, &$node, $control_name)
	{
		$filter = '\.png$|\.gif$|\.jpg$|\.bmp$|\.ico$';
		$node->addAttribute('filter', $filter);

		$parameter = $this->_parent->loadElement('filelist');

		return $parameter->fetchElement($name, $value, $node, $control_name);
	}
}
