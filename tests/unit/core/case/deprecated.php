<?php
/**
 * @package    Joomla.Test
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

/**
 * The TestDeprecated class.
 *
 * @since  __DEPLOY_VERSION__
 *
 * @deprecated  Should be removed after all test code updated for phpunit >= 6.0.
 */
trait TestCaseDeprecated
{
	public static function assertTag($matcher, $actual, $message = '', $isHtml = true)
	{
		$dom     = \PHPUnit\Util\Xml::load($actual, $isHtml);
		$tags    = TestDomhelper::findNodes($dom, $matcher, $isHtml);
		$matched = count($tags) > 0 && $tags[0] instanceof DOMNode;

		self::assertTrue($matched, $message);
	}

	public static function assertNotTag($matcher, $actual, $message = '', $ishtml = true)
	{
		$dom = \PHPUnit\Util\Xml::load($actual, $ishtml);
		$tags = TestDomhelper::findNodes($dom, $matcher, $ishtml);
		$matched = count($tags) > 0 && $tags[0] instanceof DOMNode;
		self::assertFalse($matched, $message);
	}
}
