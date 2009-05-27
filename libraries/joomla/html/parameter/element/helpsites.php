<?php
/**
 * @version		$Id: helpsites.php 10707 2008-08-21 09:52:47Z eddieajau $
 * @package		Joomla.Framework
 * @subpackage	Parameter
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Renders a helpsites element
 *
 * @package 	Joomla.Framework
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

		$helpsites 				= JHelp::createSiteList(JPATH_ADMINISTRATOR.DS.'help'.DS.'helpsites-15.xml', $value);
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
