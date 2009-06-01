<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Languages
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	Languages
 */
class TOOLBAR_languages
{
	function _DEFAULT()
	{
		JToolBarHelper::title(JText::_('Language Manager'), 'langmanager.png');
		JToolBarHelper::makeDefault('publish');
		JToolBarHelper::help('screen.languages');
	}
}