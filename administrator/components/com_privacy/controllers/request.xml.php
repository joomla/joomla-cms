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
 * Request management controller class.
 *
 * @since  3.9.0
 */
class PrivacyControllerRequest extends JControllerLegacy
{
	/**
	 * Method to export the data for a request.
	 *
	 * @return  $this
	 *
	 * @since   3.9.0
	 */
	public function export()
	{
		$this->input->set('view', 'export');

		return $this->display();
	}
}
