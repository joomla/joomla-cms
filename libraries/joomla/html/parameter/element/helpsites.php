<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  HTML
 */

defined('JPATH_PLATFORM') or die;

/**
 * Renders a helpsites element
 *
 * @package		Joomla.Platform
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

		$helpsites = JHelp::createSiteList(JPATH_ADMINISTRATOR.DS.'help'.DS.'helpsites-'.$jver[0].$jver[1].'.xml', $value);
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
