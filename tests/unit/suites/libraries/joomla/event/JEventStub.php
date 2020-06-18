<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Event
 *
 * @copyright   © 2012 Open Source Matters, Inc. <https://www.joomla.org/contribute-to-joomla.html>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Stub class to test JEvent
 *
 * @package     Joomla.UnitTest
 * @subpackage  Event
 * @since       1.7.0
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
