<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
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
