<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Plugins\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;

/**
 * Plugin controller class.
 *
 * @since  1.6
 */
class PluginController extends FormController
{
	/**
	 * Method to reset the public GET rate limit
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @throws  Exception
	 */
	public function resetrate()
	{
		// Check for request forgeries.
		$this->checkToken();

		$id = $this->input->get('extension_id');

		/** @var PluginModel $model */
		$model = $this->getModel();

		// Reset the rate limit
		$model->resetRateLimit($id);
		$this->setRedirect(Route::_('index.php?option=com_plugins', false), '');
	}
}
