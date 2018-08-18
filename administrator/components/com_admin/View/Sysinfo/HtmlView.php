<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Admin\Administrator\View\Sysinfo;

defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\Notallowed;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * Sysinfo View class for the Admin component
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Some PHP settings
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $php_settings = array();

	/**
	 * Config values
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $config = array();

	/**
	 * Some system values
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $info = array();

	/**
	 * PHP info
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $php_info = null;

	/**
	 * Information about writable state of directories
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $directory = array();

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		// Access check.
		if (!\JFactory::getUser()->authorise('core.admin'))
		{
			throw new Notallowed(\JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$this->php_settings = $this->get('PhpSettings');
		$this->config       = $this->get('config');
		$this->info         = $this->get('info');
		$this->php_info     = $this->get('PhpInfo');
		$this->directory    = $this->get('directory');

		$this->addToolbar();

		return parent::display($tpl);
	}

	/**
	 * Setup the Toolbar
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		ToolbarHelper::title(\JText::_('COM_ADMIN_SYSTEM_INFORMATION'), 'info-2 systeminfo');
		ToolbarHelper::link(\JRoute::_('index.php?option=com_admin&view=sysinfo&format=text'), 'COM_ADMIN_DOWNLOAD_SYSTEM_INFORMATION_TEXT', 'download');
		ToolbarHelper::link(\JRoute::_('index.php?option=com_admin&view=sysinfo&format=json'), 'COM_ADMIN_DOWNLOAD_SYSTEM_INFORMATION_JSON', 'download');
		ToolbarHelper::help('JHELP_SITE_SYSTEM_INFORMATION');
	}
}
