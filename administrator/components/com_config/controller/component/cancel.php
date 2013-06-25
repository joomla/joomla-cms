<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Cancel Controller for global configuration components
 *
 * @package     Joomla.Administrator
 * @subpackage  com_config
 * @since       3.1
*/
class ConfigControllerComponentCancel extends JControllerBase
{
	/**
	 * Method to cancel global configuration component.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function execute()
	{
		// Clean the session data.
		$app = JFactory::getApplication();
		$app->setUserState('com_config.config.global.data', null);

		$return = $this->input->post->get('return', null, 'base64');
		$redirect = 'index.php';

		if (!empty($return))
		{
			$redirect = base64_decode($return);
		}

		$app->redirect(JRoute::_($redirect, false));

		return true;
	}
}
