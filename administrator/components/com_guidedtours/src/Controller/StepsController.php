<?php

/**
 * @package       Joomla.Administrator
 * @subpackage    com_guidedtours
 * @copyright (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\AdminController;

/**
 * Component Controller
 *
 * @since __DEPLOY_VERSION__
 */

class StepsController extends AdminController
{
	/**
	 * The default view.
	 *
	 * @var   string
	 * @since __DEPLOY_VERSION__
	 */
	protected $default_view = 'steps';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean $cachable  If true, the view output will be cached
	 * @param   array   $urlparams An array of safe URL parameters and their variable types, for valid values see {@link \JFilterInput::clean()}.
	 *
	 * @return static  This object to support chaining.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function display($cachable = false, $urlparams = array())
	{
		$view   = $this->input->get('view', $this->defaultView);
		$layout = $this->input->get('layout', 'default');
		$id     = $this->input->getInt('id');

		if ($view == 'step' && $layout == 'edit' && !$this->checkEditId('com_guidedtours.edit.step', $id))
		{
			$this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
			$this->setRedirect(Route::_('index.php?option=com_guidedtour&view=steps', false));

			return false;
		}

		return parent::display();
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string $name   The model name. Optional.
	 * @param   string $prefix The class prefix. Optional.
	 * @param   array  $config The array of possible config values. Optional.
	 *
	 * @return \Joomla\CMS\MVC\Model\BaseDatabaseModel
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function getModel($name = 'Step', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
}
