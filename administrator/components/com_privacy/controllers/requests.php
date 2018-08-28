<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Requests management controller class.
 *
 * @since  __DEPLOY_VERSION__
 */
class PrivacyControllerRequests extends JControllerAdmin
{
	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JModelLegacy|boolean  Model object on success; otherwise false on failure.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getModel($name = 'Request', $prefix = 'PrivacyModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
}
