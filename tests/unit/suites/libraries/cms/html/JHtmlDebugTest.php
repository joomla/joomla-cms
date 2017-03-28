<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JHtmlDebug.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Html
 * @since       __DEPLOY_VERSION__
 */
class JHtmlDebugTest extends TestCase
{
	/**
	 * Test xdebugLink method.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testXdebugLink()
	{
		JHtmlDebug::$xdebugLinkFormat = 'XDebugLink: %f %l';

		$link = JHtmlDebug::xdebuglink(JPath::clean(JPATH_ROOT . '/path/to/file'), 123);
		$expected = sprintf('<a href="XDebugLink: %s 123" >%s</a>', JPath::clean(JPATH_ROOT . '/path/to/file'), JPath::clean('JROOT/path/to/file:123'));

		self::assertEquals($expected, $link);

		JHtmlDebug::$xdebugLinkFormat = '';

		$link = JHtmlDebug::xdebuglink(JPath::clean(JPATH_ROOT . '/path/to/file'), 123);

		self::assertEquals(JPath::clean('JROOT/path/to/file:123'), $link);
	}

	/**
	 * Test backtrace() method.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testBacktrace()
	{
		JFactory::$application = $this->getMockCmsApp();
		$trace = debug_backtrace();

		self::assertEquals(
			JLayoutHelper::render('joomla.error.backtrace', array('backtrace' => $trace)),
			JHtml::_('debug.backtrace', $trace)
		);

		self::assertEquals('', JHtml::_('debug.backtrace', array()));
	}
}
