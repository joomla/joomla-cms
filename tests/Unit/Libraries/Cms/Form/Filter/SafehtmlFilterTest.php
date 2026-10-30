<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Form\Filter;

use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Form\Filter\SafehtmlFilter;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for SafehtmlFilter.
 *
 * The filter delegates to InputFilter in its block-defined-tags mode. Only the delegation and the
 * handful of behaviours the form layer depends on are asserted here, the exhaustive cleaning matrix
 * belongs to the tests of the filter package itself. The element, group, input registry and form
 * are not read, so they are all passed as null.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       __DEPLOY_VERSION__
 */
#[TestDox('The safehtml form filter')]
class SafehtmlFilterTest extends UnitTestCase
{
    /**
     * Resets the InputFilter instance cache, which is keyed by the constructor arguments and is
     * otherwise shared with every other test in the run.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setStaticProperty(InputFilter::class, 'instances', []);
    }

    /**
     * Tests that a script element is removed.
     *
     * Note the element is removed but its text content is kept, which is what InputFilter does in
     * this mode.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('removes a script element')]
    public function testScriptElementIsRemoved(): void
    {
        $filter  = new SafehtmlFilter();
        $element = new \SimpleXMLElement('<field name="unittest" type="editor" filter="safehtml" />');

        $result = $filter->filter($element, '<p>ok</p><script>alert(1)</script>', null, null, null);

        $this->assertStringNotContainsString('<script', $result);
        $this->assertStringContainsString('<p>ok</p>', $result);
    }

    /**
     * Tests that an event handler attribute is removed.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('removes an event handler attribute')]
    public function testEventHandlerAttributeIsRemoved(): void
    {
        $filter  = new SafehtmlFilter();
        $element = new \SimpleXMLElement('<field name="unittest" type="editor" filter="safehtml" />');

        $result = $filter->filter($element, '<p onclick="steal()">ok</p>', null, null, null);

        $this->assertStringNotContainsString('onclick', $result);
        $this->assertStringContainsString('ok', $result);
    }

    /**
     * Tests that ordinary formatting markup survives.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('keeps ordinary formatting markup')]
    public function testFormattingMarkupIsKept(): void
    {
        $filter  = new SafehtmlFilter();
        $element = new \SimpleXMLElement('<field name="unittest" type="editor" filter="safehtml" />');

        $this->assertSame(
            '<p><strong>bold</strong></p>',
            $filter->filter($element, '<p><strong>bold</strong></p>', null, null, null)
        );
    }

    /**
     * Tests that text without markup and an empty string are handed back unchanged.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('leaves plain text and an empty string alone')]
    public function testPlainTextIsUnchanged(): void
    {
        $filter  = new SafehtmlFilter();
        $element = new \SimpleXMLElement('<field name="unittest" type="editor" filter="safehtml" />');

        $this->assertSame('plain text', $filter->filter($element, 'plain text', null, null, null));
        $this->assertSame('', $filter->filter($element, '', null, null, null));
    }
}
