<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The HTML Menus Menu Item TYpes View.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 * @since       1.6
 */
class MenusViewMenutypes extends JViewLegacy
{
	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$input = JFactory::getApplication()->input;
		$this->recordId = $input->getInt('recordId');
		$this->types    = $this->get('TypeOptions');

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @since   3.0
	 */
	protected function addToolbar()
	{
		// Add page title
		JToolbarHelper::title(JText::_('COM_MENUS'), 'menumgr.png');

		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');

		// Cancel
		$title = JText::_('JTOOLBAR_CANCEL');
		$dhtml = "<button onClick=\"location.href='index.php?option=com_menus&view=items'\" class=\"btn\">
					<i class=\"icon-remove\" title=\"$title\"></i>
					$title</button>";
		$bar->appendButton('Custom', $dhtml, 'new');
	}
}
