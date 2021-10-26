<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Test class for JHtmlLanguage.
 *
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 * @since       __DEPLOY_VERSION__
 */
class JHtmlLanguageTest extends TestCase
{
	/**
	 * Setup for testing.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function setUp()
	{
		parent::setUp();

		$this->saveFactoryState();

		JFactory::$document = $this->getMockDocument();
	}

	/**
	 * Overrides the parent tearDown method.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function tearDown()
	{
		$this->restoreFactoryState();

		parent::tearDown();
	}

	/**
	 * Tests the JHtmlLanguage::inlineBidirectional method.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testInlineBidirectional()
	{
		// Tests for LTR direction
		JFactory::getDocument()->setDirection('ltr');

		$this->assertEquals(
			JHtml::_('language.inlineBidirectional', 'This is a test.'),
			'<span dir="auto">This is a test.</span>',
			'Value should be wrapped into a span element with default direction auto when document direction is ltr'
		);

		$this->assertEquals(
			JHtml::_('language.inlineBidirectional', 'This is a test.', 'auto'),
			'<span dir="auto">This is a test.</span>',
			'Value should be wrapped into a span element with direction auto when document direction is ltr'
		);

		$this->assertEquals(
			JHtml::_('language.inlineBidirectional', 'This is a test.', 'ltr'),
			'This is a test.',
			'Value should not be changed when desired direction is same as document direction'
		);

		$this->assertEquals(
			JHtml::_('language.inlineBidirectional', 'זה מבחן.', 'rtl'),
			'<span dir="rtl">זה מבחן.</span>',
			'Value should be wrapped into a span element with direction rtl when document direction is ltr'
		);

		$this->assertEquals(
			JHtml::_('language.inlineBidirectional', 'This is a test.', 'ltr', 'bdi'),
			'This is a test.',
			'Value should not be changed when desired direction is same as document direction'
		);

		$this->assertEquals(
			JHtml::_('language.inlineBidirectional', 'זה מבחן.', 'rtl', 'bdi'),
			'<bdi dir="rtl">זה מבחן.</bdi>',
			'Value should be wrapped into a bdi element with direction rtl when document direction is ltr'
		);

		$this->assertEquals(
			JHtml::_('language.inlineBidirectional', 'Dies ist ein Test.', 'ltr', 'span', 'de'),
			'Dies ist ein Test.',
			'Value should not be changed when desired direction is same as document direction'
		);

		$this->assertEquals(
			JHtml::_('language.inlineBidirectional', 'זה מבחן.', 'rtl', 'span', 'he'),
			'<span dir="rtl" lang="he">זה מבחן.</span>',
			'Value should be wrapped into a span element with direction rtl and language he when document direction is ltr'
		);

		// Tests for RTL direction
		JFactory::getDocument()->setDirection('rtl');

		$this->assertEquals(
			JHtml::_('language.inlineBidirectional', 'זה מבחן.'),
			'<span dir="auto">זה מבחן.</span>',
			'Value should be wrapped into a span element with default direction auto when document direction is rtl'
		);

		$this->assertEquals(
			JHtml::_('language.inlineBidirectional', 'זה מבחן.', 'auto'),
			'<span dir="auto">זה מבחן.</span>',
			'Value should be wrapped into a span element with direction auto when document direction is rtl'
		);

		$this->assertEquals(
			JHtml::_('language.inlineBidirectional', 'This is a test.', 'ltr'),
			'<span dir="ltr">This is a test.</span>',
			'Value should be wrapped into a span element with direction ltr when document direction is rtl'
		);

		$this->assertEquals(
			JHtml::_('language.inlineBidirectional', 'זה מבחן.', 'rtl'),
			'זה מבחן.',
			'Value should not be changed when desired direction is same as document direction'
		);

		$this->assertEquals(
			JHtml::_('language.inlineBidirectional', 'This is a test.', 'ltr', 'bdi'),
			'<bdi dir="ltr">This is a test.</bdi>',
			'Value should be wrapped into a bdi element with direction ltr when document direction is rtl'
		);

		$this->assertEquals(
			JHtml::_('language.inlineBidirectional', 'זה מבחן.', 'rtl', 'bdi'),
			'זה מבחן.',
			'Value should not be changed when desired direction is same as document direction'
		);

		$this->assertEquals(
			JHtml::_('language.inlineBidirectional', 'Dies ist ein Test.', 'ltr', 'span', 'de'),
			'<span dir="ltr" lang="de">Dies ist ein Test.</span>',
			'Value should be wrapped into a span element with direction ltr and language de when document direction is rtl'
		);

		$this->assertEquals(
			JHtml::_('language.inlineBidirectional', 'זה מבחן.', 'rtl', 'span', 'he'),
			'זה מבחן.',
			'Value should not be changed when desired direction is same as document direction'
		);
	}
}
