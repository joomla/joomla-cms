<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Renders a helpsites element
 *
 * @package     Joomla.Platform
 * @subpackage  Parameter
 * @since       11.1
 * @deprecated  Use JFormFieldHelpsite instead
 * @not         When updating not that JformFieldHelpsite does not end in s.
 */
class JElementHelpsites extends JElement
{
	/**
	 * Element name
	 *
	 * @var    string
	 */
	protected $_name = 'Helpsites';

	/**
	 *
	 * @since   11.1
	 *
	 * @deprecated    12.1   Use jFormFieldHelpSites::getOptions instead
	 */
	public function fetchElement($name, $value, &$node, $control_name)
	{
		// Deprecation warning.
		JLog::add('JElementHelpsites::fetchElement is deprecated.', JLog::WARNING, 'deprecated');

		jimport('joomla.language.help');

		// Get Joomla version.
		$version = new JVersion;
		$jver = explode('.', $version->getShortVersion());

		$helpsites = JHelp::createSiteList(JPATH_ADMINISTRATOR . '/help/helpsites.xml', $value);
		array_unshift($helpsites, JHtml::_('select.option', '', JText::_('local')));

		return JHtml::_('select.genericlist', $helpsites, $control_name . '[' . $name . ']',
			array('id' => $control_name . $name, 'list.attr' => 'class="inputbox"', 'list.select' => $value));
	}
}
