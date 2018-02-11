<?php
/**
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace DummyNamespace;

defined('JPATH_PLATFORM') or die;

/**
 * Dummy class, used for namespaced class loading tests.
 *
 * @package  None
 *
 * @since    __DEPLOY_VERSION__
 */
class DummyClass
{
	/**
	 * Returns class name without namespace.
	 * @return string
	 */
	public static function getName ()
	{
		return 'DummyClass';
	}
}
