<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Access
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Filter;

use Joomla\CMS\Filter\OutputFilter;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\Filter\OutputFilter.
 *
 * @since    4.0.0
 */
class OutputFilterTest extends UnitTestCase
{
    /**
     * Tests enforcing XHTML links.
     *
     * @return  void
     * @since   4.0.0
     */
    public function testLinkXHTMLSafe()
    {
        $this->assertEquals(
            '<a href="http://www.example.com/index.frd?one=1&amp;two=2&amp;three=3">This & That</a>',
            OutputFilter::linkXHTMLSafe('<a href="http://www.example.com/index.frd?one=1&two=2&three=3">This & That</a>'),
            'Should clean ampersands only out of link, not out of link text'
        );
    }

    /**
     * Tests enforcing JS safe output.
     *
     * @return  void
     * @since   4.0.0
     */
    public function testStringJSSafe()
    {
        $this->assertEquals(
            '\u0054\u0065\u0073\u0074\u0022\u003e\u0027\u0020\u00e4\u00f6\u0020\u6d4b\u8bd5\u{28207}',
            OutputFilter::stringJSSafe('Test">\' äö 测试𨈇'),
            'Should convert all input to escaped unicode notation'
        );
    }

    /**
     * Tests filtering strings down to ASCII-7 lowercase URL text
     *
     * @return  void
     * @since   4.0.0
     */
    public function testStringURLSafe()
    {
        $this->assertEquals(
            '1234567890-qwertyuiop-qwertyuiop-asdfghjkl-asdfghjkl-zxcvbnm-zxcvbnm',
            OutputFilter::stringURLSafe('`1234567890-=~!@#$%^&*()_+	qwertyuiop[]\QWERTYUIOP{}|asdfghjkl;\'ASDFGHJKL:"zxcvbnm,./ZXCVBNM<>?'),
            'Should clean keyboard string down to ASCII-7'
        );

        $this->assertEquals(
            'joomlas-version',
            OutputFilter::stringURLSafe('joomla\'s version'),
            'Should remove apostrophe from the string'
        );
    }

    /**
     * Tests replacing single ampersands with the entity, but leaving double ampersands
     * and ampersand-octothorpe combinations intact.
     *
     * @return  void
     * @since   4.0.0
     */
    public function testAmpReplace()
    {
        $this->assertEquals(
            '&&george&amp;mary&#3son',
            OutputFilter::ampReplace('&&george&mary&#3son'),
            'Should replace single ampersands with HTML entity'
        );

        $this->assertEquals(
            'index.php?&&george&amp;mary&#3son&amp;this=that',
            OutputFilter::ampReplace('index.php?&&george&mary&#3son&this=that'),
            'Should replace single ampersands with HTML entity'
        );

        $this->assertEquals(
            'index.php?&&george&amp;mary&#3son&&&this=that',
            OutputFilter::ampReplace('index.php?&&george&mary&#3son&&&this=that'),
            'Should replace single ampersands with HTML entity'
        );

        $this->assertEquals(
            'index.php?&amp;this="this &amp; and that"',
            OutputFilter::ampReplace('index.php?&this="this & and that"'),
            'Should replace single ampersands with HTML entity'
        );

        $this->assertEquals(
            'index.php?&amp;this="this &amp; &amp; &&amp; and that"',
            OutputFilter::ampReplace('index.php?&this="this &amp; & &&amp; and that"'),
            'Should replace single ampersands with HTML entity'
        );
    }
}
