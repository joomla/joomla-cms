<?php
/**
 * @package    Joomla.Cli
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * A command line cron job to attempt to remove files that should have been deleted at update.
 *
 * @since  3.5
 */
class CliCommandDeletefiles extends JControllerBase
{
	/**
	 * Execute the controller.
	 *
	 * @return  boolean
	 *
	 * @since   3.5
	 */
	public function execute()
	{
		// Import the dependencies
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		// We need the update script
		JLoader::register('JoomlaInstallerScript', JPATH_ADMINISTRATOR . '/components/com_admin/script.php');

		// Instantiate the class
		$class = new JoomlaInstallerScript;

		// Run the delete method
		$class->deleteUnexistingFiles();

		return true;
	}
}
