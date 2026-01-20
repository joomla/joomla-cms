<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Form\Rule;

use Joomla\CMS\Form\Rule\UrlRule;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for UrlRule.
 *
 * @since  __DEPLOY_VERSION__
 */
class UrlRuleTest extends UnitTestCase
{
    /**
     * Test data for the testRule method
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public function dataTest(): array
    {
        $xml = new \SimpleXMLElement('<field
			name="unittest"
			type="text"
			validate="url"
			required="true"
		/>');
        $xmlschemes = new \SimpleXMLElement('<field
			name="unittest"
			type="text"
			validate="url"
			required="true"
			schemes="http,https"
		/>');
        $xmlrelative = new \SimpleXMLElement('<field
			name="unittest"
			type="text"
			validate="url"
			required="true"
			relative="true"
		/>');

        return [
            [true, $xml, 'https://example.com'],
            [true, $xml, 'https://example.com/test'],
            [true, $xml, 'https://example.com:8080'],
            [true, $xml, 'ftp://example.com/resource'],
            [true, $xml, 'mailto:test@example.com'],
            [true, $xml, 'tel:+49123456789'],
            [true, $xml, 'file:///etc/passwd'],
            [true, $xml, 'gopher://example.com'],
            [true, $xmlschemes, 'https://example.com'],
            [true, $xmlrelative, 'https://example.com'],
            [true, $xmlrelative, '/relative/path'],
            [false, $xml, ''],
            [false, $xml, 'invalid://example.com'],
            [false, $xml, 'example.com'],
            [false, $xml, '/relative/path'],
            [false, $xml, 'http:///example.com'],
            [false, $xml, 'http:example.com'],
            [false, $xml, "https://exa\x80mple.com"],
            [false, $xml, "https://example.com/pa\x80th"],
            [false, $xml, 'https://example.com:0'],
            [false, $xmlschemes, 'ftp://example.com'],
        ];
    }

    /**
     * Tests the UrlRule::test method.
     *
     * @param   bool               $expected  The expected test result
     * @param   \SimpleXMLElement  $element   The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   string             $value     The form field value to validate.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     * @dataProvider dataTest
     */
    public function testRule(bool $expected, \SimpleXMLElement $element, string $value): void
    {
        $this->assertEquals($expected, (new UrlRule())->test(clone $element, $value));
    }
}
