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
 * Guidedtours list controller class.
 *
 * @since _DEPLOY_VERSION_
 */

class ToursController extends AdminController
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string $name   The model name. Optional.
	 * @param   string $prefix The class prefix. Optional.
	 * @param   array  $config The array of possible config values. Optional.
	 *
	 * @return \Joomla\CMS\MVC\Model\BaseDatabaseModel
	 *
	 * @since _DEPLOY_VERSION_
	 */
	public function getModel($name = 'Tour', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
}
