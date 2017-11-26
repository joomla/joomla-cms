<?php
/**
 * Part of 40dev project.
 *
 * @copyright  Copyright (C) 2017 ${ORGANIZATION}.
 * @license    __LICENSE__
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

	public static function assertNotTag($matcher, $actual, $message = '', $ishtml = true) {
		$dom = \PHPUnit\Util\Xml::load($actual, $ishtml);
		$tags = TestDomhelper::findNodes($dom, $matcher, $ishtml);
		$matched = count($tags) > 0 && $tags[0] instanceof DOMNode;
		self::assertFalse($matched, $message);
	}
}
