<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_modules
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Modules component
 *
 * @static
 * @package		Joomla.Administrator
 * @subpackage	com_modules
 * @since 1.6
 */
class ModulesViewPreview extends JViewLegacy
{
	function display($tpl = null)
	{
		$editor = JFactory::getEditor();

		$this->assignRef('editor',		$editor);

		parent::display($tpl);
	}
}
