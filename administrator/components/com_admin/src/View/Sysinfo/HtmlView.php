<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Admin\Administrator\View\Sysinfo;

\defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Admin\Administrator\Model\SysinfoModel;

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
	protected $phpSettings = [];

	/**
	 * Config values
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $config = [];

	/**
	 * Some system values
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $info = [];

	/**
	 * PHP info
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $phpInfo = null;

	/**
	 * Information about writable state of directories
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected $directory = [];

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 *
	 * @throws  Exception
	 */
	public function display($tpl = null): void
	{
		// Access check.
		if (!Factory::getUser()->authorise('core.admin'))
		{
			throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		/** @var SysinfoModel $model */
		$model             = $this->getModel();
		$this->phpSettings = $model->getPhpSettings();
		$this->config      = $model->getConfig();
		$this->info        = $model->getInfo();
		$this->phpInfo     = $model->getPHPInfo();
		$this->directory   = $model->getDirectory();

		$this->addToolbar();

		parent::display($tpl);
	}

	/**
	 * Setup the Toolbar
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar(): void
	{
		ToolbarHelper::title(Text::_('COM_ADMIN_SYSTEM_INFORMATION'), 'info-circle systeminfo');
		ToolbarHelper::link(
			Route::_('index.php?option=com_admin&view=sysinfo&format=text&' . Session::getFormToken() . '=1'),
			'COM_ADMIN_DOWNLOAD_SYSTEM_INFORMATION_TEXT',
			'download'
		);
		ToolbarHelper::link(
			Route::_('index.php?option=com_admin&view=sysinfo&format=json&' . Session::getFormToken() . '=1'),
			'COM_ADMIN_DOWNLOAD_SYSTEM_INFORMATION_JSON',
			'download'
		);
		ToolbarHelper::help('JHELP_SITE_SYSTEM_INFORMATION');
	}
}
