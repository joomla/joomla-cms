<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Base
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Sample Class for JObject tests.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Object
 */
class SampleObject extends JObject
{
	private          $privateProperty         = 'private, declared';
	protected        $protectedProperty       = 'protected, declared';
	public           $publicProperty          = 'public, declared';
	private static   $privateStaticProperty   = 'private, static';
	protected static $protectedStaticProperty = 'protected, static';
	public static    $publicStaticProperty    = 'public, static';
	private          $_privateUnderscore   = 'private, declared, underscored';
	protected        $_protectedUnderscore = 'protected, declared, underscored';
	public           $_publicUnderscore    = 'public, declared, underscored';
}
