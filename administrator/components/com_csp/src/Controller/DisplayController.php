<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_csp
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Csp\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Csp\Administrator\Helper\ReporterHelper;

/**
 * Csp display controller.
 *
 * @since  4.0.0
 */
class DisplayController extends BaseController
{
	/**
	 * The default view.
	 *
	 * @var     string
	 * @since   4.0.0
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
	 * @since   4.0.0
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
				Text::_('COM_CSP_SYSTEM_PLUGIN'),
				'class="alert-link" data-toggle="modal" id="title-' . $httpHeadersId . '"'
			);

			$this->app->enqueueMessage(Text::sprintf('COM_CSP_PLUGIN_MODAL_DISABLED', $link), 'error');
		}

		return parent::display();
	}
}
