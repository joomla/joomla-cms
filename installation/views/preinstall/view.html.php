<?php
/**
 * @version		$Id: view.html.php 235 2009-05-26 06:19:45Z andrew.eddie $
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.view');
jimport('joomla.html.html');

/**
 * The HTML Joomla Core Pre-Install View
 *
 * @package		Joomla.Installation
 * @since		1.6
 */
class JInstallationViewPreinstall extends JView
{
	/**
	 * Display the view
	 *
	 * @access	public
	 */
	function display($tpl = null)
	{
		$state		= $this->get('State');
		$settings	= $this->get('PhpSettings');
		$options	= $this->get('PhpOptions');
		$version	= new JVersion;

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->assignRef('state',	 $state);
		$this->assignRef('settings', $settings);
		$this->assignRef('options',	 $options);
		$this->assignRef('version',  $version);

		parent::display($tpl);
	}
}