<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_modules
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Modules\Site\Dispatcher;

\defined('_JEXEC') or die;

use Joomla\CMS\Access\Exception\NotAllowed;
use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\MVC\Controller\BaseController;

/**
 * ComponentDispatcher class for com_modules
 *
 * @since  4.0.0
 */
class Dispatcher extends ComponentDispatcher
{
	/**
	 * Load the language
	 *
	 * @since   4.0.0
	 *
	 * @return  void
	 */
	protected function loadLanguage()
	{
		$this->app->getLanguage()->load('com_modules', JPATH_ADMINISTRATOR);
	}

	/**
	 * Dispatch a controller task. Redirecting the user if appropriate.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function checkAccess()
	{
		parent::checkAccess();

		if ($this->input->get('view') === 'modules'
			&& $this->input->get('layout') === 'modal'
			&& !$this->app->getIdentity()->authorise('core.create', 'com_modules'))
		{
			throw new NotAllowed;
		}
	}

	/**
	 * Get a controller from the component
	 *
	 * @param   string  $name    Controller name
	 * @param   string  $client  Optional client (like Administrator, Site etc.)
	 * @param   array   $config  Optional controller config
	 *
	 * @return  \Joomla\CMS\MVC\Controller\BaseController
	 *
	 * @since   4.0.0
	 */
	public function getController(string $name, string $client = '', array $config = array()): BaseController
	{
		if ($this->input->get('task') === 'orderPosition')
		{
			$config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
			$client = 'Administrator';
		}

		return parent::getController($name, $client, $config);
	}
}
