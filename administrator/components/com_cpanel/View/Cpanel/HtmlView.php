<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Cpanel\Administrator\View\Cpanel;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

defined('_JEXEC') or die;

/**
 * HTML View class for the Cpanel component
 *
 * @since  1.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Array of cpanel modules
	 *
	 * @var  array
	 */
	protected $modules = null;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		// Set toolbar items for the page
		\JToolbarHelper::title(\JText::_('COM_CPANEL'), 'home-2 cpanel');
		\JToolbarHelper::help('screen.cpanel');

		// Display the cpanel modules
		$this->modules = ModuleHelper::getModules('cpanel');

		parent::display($tpl);
	}
}
