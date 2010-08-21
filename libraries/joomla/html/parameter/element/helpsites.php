<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Parameter
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Renders a helpsites element
 *
 * @package		Joomla.Framework
 * @subpackage		Parameter
 * @since		1.5
 */

class JElementHelpsites extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	protected $_name = 'Helpsites';

	public function fetchElement($name, $value, &$node, $control_name)
	{
		jimport('joomla.language.help');

		// Get Joomla version.
		$version = new JVersion();
		$jver = explode( '.', $version->getShortVersion() );

		$helpsites = JHelp::createSiteList(JPATH_ADMINISTRATOR.'/help/helpsites-'.$jver[0].$jver[1].'.xml', $value);
		array_unshift($helpsites, JHtml::_('select.option', '', JText::_('local')));

		return JHtml::_('select.genericlist', $helpsites, $control_name .'['. $name .']',
			array(
				'id' => $control_name.$name,
				'list.attr' => 'class="inputbox"',
				'list.select' => $value
			)
		);
	}
}
