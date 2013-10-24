<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Modules component
 *
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 * @since       1.6
 */
class ModulesViewPreview extends JViewLegacy
{
	public function display($tpl = null)
	{
		$editor = JFactory::getConfig()->get('editor');

		$this->editor = JEditor::getInstance($editor);

		parent::display($tpl);
	}
}
