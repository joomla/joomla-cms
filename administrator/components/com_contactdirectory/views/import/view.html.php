<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// import library dependencies
jimport('joomla.application.component.view');

/**
 * The HTML Contact Directory import view
 *
 * @package		Joomla
 * @subpackage	ContactDirectory
 * @version		1.6
 */
class ContactdirectoryViewImport extends JView
{
	/**
	 * Method to display the view.
	 *
	 * @access	public
	 * @param	string	$tpl	A template file to load.
	 * @return	mixed	JError object on failure, void on success.
	 * @throws	object	JError
	 * @since	1.0
	 */
	public function display($tpl = null)
	{
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Load the view template.
		$result = $this->loadTemplate($tpl);

		// Check for an error.
		if (JError::isError($result)) {
			return $result;
		}
		echo $result;
	}
}