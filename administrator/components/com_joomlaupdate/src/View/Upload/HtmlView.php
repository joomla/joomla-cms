<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Joomlaupdate\Administrator\View\Upload;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Joomla! Update's Update View
 *
 * @since  3.6.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Renders the view.
	 *
	 * @param   string  $tpl  Template name.
	 *
	 * @return  void
	 *
	 * @since   3.6.0
	 */
	public function display($tpl = null)
	{
		// Set the toolbar information.
		ToolbarHelper::title(Text::_('COM_JOOMLAUPDATE_OVERVIEW'), 'loop install');
		ToolbarHelper::divider();
		ToolbarHelper::help('JHELP_COMPONENTS_JOOMLA_UPDATE');

		// Load com_installer's language
		$language = Factory::getLanguage();
		$language->load('com_installer', JPATH_ADMINISTRATOR, 'en-GB', false, true);
		$language->load('com_installer', JPATH_ADMINISTRATOR, null, true);

		// Render the view.
		parent::display($tpl);
	}
}
