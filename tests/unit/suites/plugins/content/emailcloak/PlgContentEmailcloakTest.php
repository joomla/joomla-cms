<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Plugins
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

require JPATH_BASE . '/plugins/content/emailcloak/emailcloak.php';

/**
 * Test class for Email cloaking plugin.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Plugins
 * @since       3.6.2
 */
class PlgContentEmailcloakTest extends TestCaseDatabase
{
	/**
	 * An instance of the class to test.
	 *
	 * @var    PlgContentEmailcloak
	 * @since  3.6.2
	 */
	protected $class;

	/**
	 * Setup for testing.
	 *
	 * @return  void
	 *
	 * @since   3.6.2
	 */
	public function setup()
	{
		JFactory::$application = $this->getMockCmsApp();
		JFactory::$session     = $this->getMockSession();

		// Create a mock dispatcher instance
		$dispatcher = $this->getMockDispatcher();

		$plugin = array(
			'name'   => 'emailcloak',
			'type'   => 'Content',
			'params' => new \JRegistry,
		);

		$this->class = new PlgContentEmailcloak($dispatcher, $plugin);
	}

	/**
	 * Provides the data to test the constructor method.
	 * more examples to add can be found here:
	 *  - https://github.com/joomla/joomla-cms/pull/4182#issuecomment-53395318
	 *  - https://github.com/joomla/joomla-cms/pull/3735#issue-35215540
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	public function dataTestOnContentPrepare()
	{
		return array(
			// 0
			array(
				// This first row is the input, this is what would be in the article
				'this should not be parsed as it has no (at) sign in it - see what I did there? ;)',

				/**
				 * This second row is what you would expect the JS, after rendering in a browser
				 * At the moment there is a slight unit test bug in that what you see here will start with
				 * the opening <a> tag, and not the full surrounding html from the article - I might fix that in future
				 * but for now we are testing the actual replacement inside the <a> tags and not the surrounding html
				 * due to the crazyness of the unit tests converting from JS to HTML to compare
				 */
				'this should not be parsed as it has no (at) sign in it - see what I did there? ;)',

				// This third row is the full output of the cloak with inline javascript mode enabled
				''
			),

			// 1
			array(
				'<a href="mailto:toto@toto.com?subject=Mysubject" class="myclass" >email</a>',
				'<joomla-hidden-mail  is-link="1" is-email="0" first="dG90bw==" last="dG90by5jb20/c3ViamVjdD1NeXN1YmplY3Q=" text="ZW1haWw=" base="" >JLIB_HTML_CLOAKING</joomla-hidden-mail>',
				'<a href="mailto:toto@toto.com?subject=Mysubject" class="myclass" >email</a>',
			),

			// 2
			array(
				'<a href="http://mce_host/ourdirectory/email@example.org">anytext</a>',
				'<joomla-hidden-mail  is-link="1" is-email="0" first="ZW1haWw=" last="ZXhhbXBsZS5vcmc=" text="YW55dGV4dA==" base="" >JLIB_HTML_CLOAKING</joomla-hidden-mail>',
				'<a href="http://mce_host/ourdirectory/email@example.org">anytext</a>',
			),

			// 3
			array(
				'<p><a href="mailto:joe@nowhere.com"><span style="font-style: 8pt;">Joe_fontsize8</span></a></p>',
				'<p><joomla-hidden-mail  is-link="1" is-email="0" first="am9l" last="bm93aGVyZS5jb20=" text="PHNwYW4gc3R5bGU9ImZvbnQtc3R5bGU6IDhwdDsiPkpvZV9mb250c2l6ZTg8L3NwYW4+" base="" >JLIB_HTML_CLOAKING</joomla-hidden-mail></p>',
				'<p><a href="mailto:joe@nowhere.com"><span style="font-style: 8pt;">Joe_fontsize8</span></a></p>',
			),

			// 4
			array(
				'<p><a href="mailto:joe@nowhere13.com?subject= A text"><span style="font-size: 14pt;">Joe_subject_ fontsize13</span></a></p>',
				'<p><joomla-hidden-mail  is-link="1" is-email="0" first="am9l" last="bm93aGVyZTEzLmNvbT9zdWJqZWN0PSBBIHRleHQ=" text="PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTRwdDsiPkpvZV9zdWJqZWN0XyBmb250c2l6ZTEzPC9zcGFuPg==" base="" >JLIB_HTML_CLOAKING</joomla-hidden-mail></p>',
				'<p><a href="mailto:joe@nowhere13.com?subject= A text"><span style="font-size: 14pt;">Joe_subject_ fontsize13</span></a></p>',
			),

			// 5
			array(
				'<p><a href="mailto:joe@nowhere.com"><strong>something</strong></a></p>',
				'<p><joomla-hidden-mail  is-link="1" is-email="0" first="am9l" last="bm93aGVyZS5jb20=" text="PHN0cm9uZz5zb21ldGhpbmc8L3N0cm9uZz4=" base="" >JLIB_HTML_CLOAKING</joomla-hidden-mail></p>',
				'<p><a href="mailto:joe@nowhere.com"><strong>something</strong></a></p>',
			),

			// 6
			// TODO: I would expect that the email in the strong tag should ALSO be converted?
			array(
				'<p><a href="mailto:joe@nowhere.com"><strong>mymail@mysite.com</strong></a></p>',
				'<p><joomla-hidden-mail  is-link="1" is-email="1" first="am9l" last="bm93aGVyZS5jb20=" text="PHN0cm9uZz5teW1haWxAbXlzaXRlLmNvbTwvc3Ryb25nPg==" base="" >JLIB_HTML_CLOAKING</joomla-hidden-mail></p>',
				'<p><a href="mailto:joe@nowhere.com"><strong>mymail@mysite.com</strong></a></p>',
			),

			// 7
			array(
				'<p><a href="mailto:joe@nowhere.com"><strong><span style="font-size: 14px;">mymail@mysite.com</span></strong></a></p>',
				'<p><joomla-hidden-mail  is-link="1" is-email="1" first="am9l" last="bm93aGVyZS5jb20=" text="PHN0cm9uZz48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxNHB4OyI+bXltYWlsQG15c2l0ZS5jb208L3NwYW4+PC9zdHJvbmc+" base="" >JLIB_HTML_CLOAKING</joomla-hidden-mail></p>',
				'<p><a href="mailto:joe@nowhere.com"><strong><span style="font-size: 14px;">mymail@mysite.com</span></strong></a></p>',
			),

			// 8
			array(
				'<p><a href="mailto:joe@nowhere.com"><strong><span style="font-size: 14px;">Joe Nobody</span></strong></a></p>',
				'<p><joomla-hidden-mail  is-link="1" is-email="0" first="am9l" last="bm93aGVyZS5jb20=" text="PHN0cm9uZz48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxNHB4OyI+Sm9lIE5vYm9keTwvc3Bhbj48L3N0cm9uZz4=" base="" >JLIB_HTML_CLOAKING</joomla-hidden-mail></p>',
				'<p><a href="mailto:joe@nowhere.com"><strong><span style="font-size: 14px;">Joe Nobody</span></strong></a></p>',
			),

			// 9
			// TODO: I would expect that the email in the strong tag should ALSO be converted?
			array(
				'<p><a href="mailto:joe@nowhere.com?subject= A text"><strong><span style="font-size: 16px;">joe@nowhere.com</span></strong></a></p>',
				'<p><joomla-hidden-mail  is-link="1" is-email="1" first="am9l" last="bm93aGVyZS5jb20/c3ViamVjdD0gQSB0ZXh0" text="PHN0cm9uZz48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxNnB4OyI+am9lQG5vd2hlcmUuY29tPC9zcGFuPjwvc3Ryb25nPg==" base="" >JLIB_HTML_CLOAKING</joomla-hidden-mail></p>',
				'<p><a href="mailto:joe@nowhere.com?subject= A text"><strong><span style="font-size: 16px;">joe@nowhere.com</span></strong></a></p>',
			),

			// 10
			array(
				'<p><a href="mailto:joe@nowhere.com?subject=Text"><img src="path/to/something.jpg">joe@nowhere.com</a></p>',
				'<p><joomla-hidden-mail  is-link="1" is-email="1" first="am9l" last="bm93aGVyZS5jb20/c3ViamVjdD1UZXh0" text="PGltZyBzcmM9InBhdGgvdG8vc29tZXRoaW5nLmpwZyI+am9lQG5vd2hlcmUuY29t" base="" >JLIB_HTML_CLOAKING</joomla-hidden-mail></p>',
				'<p><a href="mailto:joe@nowhere.com?subject=Text"><img src="path/to/something.jpg">joe@nowhere.com</a></p>',
			),

			// 11
			array(
				'<a href="http://mce_host/ourdirectory/email@example.org">email@example.org</a>',
				'<joomla-hidden-mail  is-link="1" is-email="1" first="ZW1haWw=" last="ZXhhbXBsZS5vcmc=" text="ZW1haWxAZXhhbXBsZS5vcmc=" base="" >JLIB_HTML_CLOAKING</joomla-hidden-mail>',
				'<a href="http://mce_host/ourdirectory/email@example.org">email@example.org</a>',
			),

			// 12 - similar to test 9 but with the addition of classes
			// TODO: I would expect that the email in the strong tag should ALSO be converted?
			array(
				'<p><a href="mailto:joe@nowhere.com?subject= A text" class="class1 class2"><strong><span style="font-size: 16px;">joe@nowhere.com</span></strong></a></p>',
				'<p><joomla-hidden-mail  is-link="1" is-email="1" first="am9l" last="bm93aGVyZS5jb20/c3ViamVjdD0gQSB0ZXh0" text="PHN0cm9uZz48c3BhbiBzdHlsZT0iZm9udC1zaXplOiAxNnB4OyI+am9lQG5vd2hlcmUuY29tPC9zcGFuPjwvc3Ryb25nPg==" base="" >JLIB_HTML_CLOAKING</joomla-hidden-mail></p>',
				'<p><a href="mailto:joe@nowhere.com?subject= A text" class="class1 class2"><strong><span style="font-size: 16px;">joe@nowhere.com</span></strong></a></p>',
			),

			// 13 - Similar to test 4 but with the addition of classes
			array(
				'<p><a href="mailto:joe@nowhere13.com?subject= A text" class="class 1 class 2"><span style="font-size: 14pt;">Joe_subject_ fontsize13</span></a></p>',
				'<p><joomla-hidden-mail  is-link="1" is-email="0" first="am9l" last="bm93aGVyZTEzLmNvbT9zdWJqZWN0PSBBIHRleHQ=" text="PHNwYW4gc3R5bGU9ImZvbnQtc2l6ZTogMTRwdDsiPkpvZV9zdWJqZWN0XyBmb250c2l6ZTEzPC9zcGFuPg==" base="" >JLIB_HTML_CLOAKING</joomla-hidden-mail></p>',
				'<p><a href="mailto:joe@nowhere13.com?subject= A text" class="class 1 class 2"><span style="font-size: 14pt;">Joe_subject_ fontsize13</span></a></p>',
			),

			// 14
			array(
				'<a href="mailto:toto@toto.com" class="myclass" >toto@toto.com</a>',
				'<joomla-hidden-mail  is-link="1" is-email="1" first="dG90bw==" last="dG90by5jb20=" text="dG90b0B0b3RvLmNvbQ==" base="" >JLIB_HTML_CLOAKING</joomla-hidden-mail>',
				'<a href="mailto:toto@toto.com" class="myclass" >toto@toto.com</a>',
			),

			// 15
			array(
				'<a href="mailto:toto@toto.com" class="myclass" >Click Here</a>',
				'<joomla-hidden-mail  is-link="1" is-email="0" first="dG90bw==" last="dG90by5jb20=" text="Q2xpY2sgSGVyZQ==" base="" >JLIB_HTML_CLOAKING</joomla-hidden-mail>',
				'<a href="mailto:toto@toto.com" class="myclass" >Click Here</a>',
			),

			// 16 UTF8
			array(
				'<a href="mailto:joomlatest@xn----7sblgc4ag8bhcd.xn--p1ai"> joomlatest@джумла-тест.рф</a>',
				'<joomla-hidden-mail  is-link="1" is-email="0" first="am9vbWxhdGVzdA==" last="eG4tLS0tN3NibGdjNGFnOGJoY2QueG4tLXAxYWk=" text="IGpvb21sYXRlc3RA0LTQttGD0LzQu9CwLdGC0LXRgdGCLtGA0YQ=" base="" >JLIB_HTML_CLOAKING</joomla-hidden-mail>',
				'<a href="mailto:joomlatest@xn----7sblgc4ag8bhcd.xn--p1ai">Click Here</a>',
			),

			// 17 UTF8 and image
			array(
				'<a href="mailto:joomlatest@xn----7sblgc4ag8bhcd.xn--p1ai?subject= джумла-тес" rel="alternate"><img src="images/powered_by.png" alt="" /></a>',
				'<joomla-hidden-mail  is-link="1" is-email="0" first="am9vbWxhdGVzdA==" last="eG4tLS0tN3NibGdjNGFnOGJoY2QueG4tLXAxYWk/c3ViamVjdD0g0LTQttGD0LzQu9CwLdGC0LXRgQ==" text="PGltZyBzcmM9ImltYWdlcy9wb3dlcmVkX2J5LnBuZyIgYWx0PSIiIC8+" base="" >JLIB_HTML_CLOAKING</joomla-hidden-mail>',
				'<a href="mailto:joomlatest@xn----7sblgc4ag8bhcd.xn--p1ai?subject= джумла-тес" rel="alternate"><img src="images/powered_by.png" alt="" /></a>',
			)
		);
	}

	/**
	 * Tests PlgContentEmailcloakTest::_cloak()
	 *
	 * @param   string  $input         The text to test.
	 * @param   string  $expectedHTML  The expectation of the filtering.
	 * @param   string  $expectedJs    The expected javascript
	 *
	 * @return  void
	 *
	 * @dataProvider  dataTestOnContentPrepare
	 * @since         3.6.2
	 */
	public function testOnContentPrepareWithRowNoFinder($input, $expectedHTML, $expectedJs)
	{
		$row = new \stdClass;
		$row->text = $input;
		$params = new JRegistry;

		// Assert we have the correct event
		$this->assertInstanceOf('PlgContentEmailcloak', $this->class);

		// Assert that we are getting a clean process
		$res = $this->class->onContentPrepare('com_content.article', $row, $params);
		$this->assertEquals(1, $res);

		// We never cloaked an email but lets ensure we did not screw up the article text anyway!
		$this->assertEquals($expectedHTML, $row->text);
	}

	/**
	 * Tests that if we are the com_finder indexer that we return with no cloaking
	 * Tests also that we can set $row as string instead of a normal object
	 */
	public function testIndexer()
	{
		$row = 'test string';
		$params = new JRegistry;

		// Assert we have the correct event
		$this->assertInstanceOf('PlgContentEmailcloak', $this->class);

		// Assert that we are getting a clean process
		$res = $this->class->onContentPrepare('com_finder.indexer', $row, $params);
		$this->assertEquals(1, $res);
		$this->assertEquals('test string', $row);
	}
}
