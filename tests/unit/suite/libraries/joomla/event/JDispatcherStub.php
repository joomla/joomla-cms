<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// we need to create a class that will allow use to set _methods and _observers

class JDispatcherStub extends JDispatcher
{
	/**
	 * We don't need to worry about the constructor so long as we set the state up properly
	 */
	public function __construct()
	{

	}

	/**
	 * Set the methods that are available to call
	 * @param The value to set _methods to
	 */
	public function setMethods($methods)
	{
		$this->_methods = $methods;
	}

	/**
	 * Set the observers listening to us
	 * @param The value to set _observers to
	 */
	public function setObservers($observers)
	{
		$this->_observers = $observers;
	}
}
