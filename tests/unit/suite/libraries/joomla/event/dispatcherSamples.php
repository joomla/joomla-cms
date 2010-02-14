<?php
/**
 * @version		$Id: JDispatcherTest.php 14544 2010-02-04 04:56:26Z eddieajau $
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

function myTestHandler($returnArgs = false, $returnValue = '12345') {
	static $args = array();

	if($returnArgs === true) {
		$returnArgs = $args;
		$args = array();
		return $returnArgs;
	} else {
		$args[] = func_get_args();
		return $returnValue;
	}
}


class myTestClassHandler
{
	public static $observables = array();
	
	public function __construct($observable)
	{
		self::$observables[] = $observable;
	}
}
