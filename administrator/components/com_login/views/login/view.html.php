<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_login
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Login component
 *
 * @since  1.6
 */
class LoginViewLogin extends JViewLegacy
{
	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse.
	 *
	 * @return  void
	 *
	 * @since  3.7.0
	 */
	public function display($tpl = null)
	{
		/**
		 * To prevent clickjacking, only allow the login form to be used inside a frame in the same origin.
		 * So send a X-Frame-Options HTTP Header with the SAMEORIGIN value.
		 *
		 * @link https://www.owasp.org/index.php/Clickjacking_Defense_Cheat_Sheet
		 * @link https://tools.ietf.org/html/rfc7034
		 */
		JFactory::getApplication()->setHeader('X-Frame-Options', 'SAMEORIGIN');

		return parent::display($tpl);
	}
}
