<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Task
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// No direct access
defined('JPATH_BASE') or die();

interface JTaskSuspendable {
	// generate an array that can be fed back to the object
	public function suspendTask();
	// the array that suspendTask generated
	public function restoreTask($options);
	// set the task for this object
	public function setTask(&$task);
}