<?php
/**
 * @version		$Id: view.html.php 2008-08-03 chantal.bisson $
 * @package	Contact Directory
 * @copyright	(C) 2008 Chantal Bisson. All rights reserved.
 * @license		GNU General Public License
 * @author		Chantal Bisson
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// import library dependencies
jimport('joomla.application.component.view');

/**
 * The HTML Contact Directory import view
 *
 * @author	Chantal Bisson
 * @package Contact Directory
 * @version	1.0
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
	function display($tpl = null)
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