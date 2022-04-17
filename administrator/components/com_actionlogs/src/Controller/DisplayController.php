<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_actionlogs
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Actionlogs\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Component\Actionlogs\Administrator\Helper\ActionlogsHelper;

/**
 * Plugins master display controller.
 *
 * @since  4.0.0
 */
class DisplayController extends BaseController
{
	/**
	 * The default view.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $default_view = 'actionlogs';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached.
	 * @param   mixed    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
	 *
	 * @return  static|boolean	 This object to support chaining or false on failure.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$view   = $this->input->get('view', 'actionlogs');
		$layout = $this->input->get('layout', 'default');
		$id     = $this->input->getInt('id');

		if ($view === 'actionlogs')
		{
			$pluginEnabled = PluginHelper::isEnabled('actionlog', 'joomla');

			// Show message if the plugin is not enabled
			if (!$pluginEnabled)
			{
				$actionlogPluginId = ActionlogsHelper::getActionlogPluginId();
				$link = HTMLHelper::_(
					'link',
					'#plugin' . $actionlogPluginId . 'Modal',
					Text::_('COM_ACTIONLOGS_JOOMLA_PLUGIN'),
					'class="alert-link" data-bs-toggle="modal" id="title-' . $actionlogPluginId . '"'
				);
				$this->app->enqueueMessage(Text::sprintf('COM_ACTIONLOGS_PLUGIN_MODAL_DISABLED', $link), 'error');
			}
		}

		return parent::display();
	}

}
