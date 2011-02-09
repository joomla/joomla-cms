<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  HTML
 */

defined('JPATH_PLATFORM') or die;

/**
 * Renders a custom button
 *
 * @package		Joomla.Platform
 * @subpackage	HTML
 * @since		1.5
 */
class JButtonCustom extends JButton
{
	/**
	 * Button type
	 *
	 * @access	protected
	 * @var		string
	 */
	protected $_name = 'Custom';

	public function fetchButton($type='Custom', $html = '', $id = 'custom')
	{
		return $html;
	}

	/**
	 * Get the button CSS Id
	 *
	 * @access	public
	 * @return	string	Button CSS Id
	 * @since	1.5
	 */
	public function fetchId($type='Custom', $html = '', $id = 'custom')
	{
		return $this->_parent->getName().'-'.$id;
	}
}
