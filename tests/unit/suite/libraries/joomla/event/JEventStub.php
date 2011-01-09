<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Stub class to test JEvent
 */
class JEventStub extends JEvent
{
	/**
	 * @var Record of calls made to myEvent
	 */
	public $calls = array();

	/**
	 * Records calls in $calls
	 *
	 * Used to verify the firing of events
	 *
	 * @return true
	 */
	public function myEvent()
	{
		$this->calls[] = array('method' => 'myEvent', 'args' => func_get_args());
		return true;
	}
}
