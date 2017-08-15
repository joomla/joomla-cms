<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Joomlaupdate\Administrator\View\Update;

defined('_JEXEC') or die;

use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\View\HtmlView;

/**
 * Joomla! Update's Update View
 *
 * @since  2.5.4
 */
class Html extends HtmlView
{
	/**
	 * Renders the view.
	 *
	 * @param   string  $tpl  Template name.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		\JFactory::getApplication()->input->set('hidemainmenu', true);

		// Set the toolbar information.
		ToolbarHelper::title(\JText::_('COM_JOOMLAUPDATE_OVERVIEW'), 'loop install');

		// Render the view.
		parent::display($tpl);
	}
}
