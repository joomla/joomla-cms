<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_csp
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Csp\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Csp\Administrator\Helper\ReporterHelper;

/**
 * Csp display controller.
 *
 * @since  __DEPLOY_VERSION__
 */
class DisplayController extends BaseController
{
	/**
	 * The default view.
	 *
	 * @var     string
	 * @since   __DEPLOY_VERSION__
	 */
	protected $default_view = 'reports';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached.
	 * @param   mixed    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
	 *
	 * @return  static   This object to support chaining.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function display($cachable = false, $urlparams = false)
	{
		// Show messages about the plugin when it is disabled
		if (!PluginHelper::isEnabled('system', 'httpheaders'))
		{
			$httpHeadersId = ReporterHelper::getHttpHeadersPluginId();
			$link = HTMLHelper::_(
				'link',
				'#plugin' . $httpHeadersId . 'Modal',
				\JText::_('COM_CSP_SYSTEM_PLUGIN'),
				'class="alert-link" data-toggle="modal" id="title-' . $httpHeadersId . '"'
			);

			$this->app->enqueueMessage(\JText::sprintf('COM_CSP_PLUGIN_MODAL_DISABLED', $link), 'error');
		}

		parent::display();
	}
}
