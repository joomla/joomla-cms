<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JControllerStateAjaxSaveorder extends JControllerStateSaveorder
{
	/** @noinspection PhpInconsistentReturnPointsInspection */
	public function execute()
	{
		try
		{
			parent::execute();
		}
		catch (Exception $e)
		{
			$msg = $e->getMessage();
			$this->abort($msg, 'warning');

			return false;
		}

		echo '1';

		// Close the application
		JFactory::getApplication()->close();
	}

	/**
	 * Overridden to allow AJAX without token
	 * @return void
	 */
	protected function validateSession()
	{
		return;
	}
}