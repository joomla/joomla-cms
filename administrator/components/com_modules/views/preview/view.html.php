<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// @deprecated  4.0 not used for a long time

defined('_JEXEC') or die;

/**
 * HTML View class for the Modules component
 *
 * @since  1.6
 */
class ModulesViewPreview extends JViewLegacy
{
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$editor = JFactory::getConfig()->get('editor');

		$this->editor = JEditor::getInstance($editor);

		parent::display($tpl);
	}
}
