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
use Joomla\CMS\Form\Filter\UrlFilter;
use Joomla\CMS\Uri\Uri;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for UrlFilter.
 *
 * This is the only filter in the group which reads its element and needs an environment: the
 * branches which build an internal link resolve Uri::root(), which needs both the server variables
 * and a container holding the configuration. The group, input registry and form are still not read,
 * so they are passed as null.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       __DEPLOY_VERSION__
 */
#[BackupGlobals(true)]
#[TestDox('The url form filter')]
class UrlFilterTest extends UnitTestCase
{
    /**
     * Sets up the server variables and the container Uri::root() resolves through.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpContainer();
        $this->setStaticProperty(InputFilter::class, 'instances', []);

        Uri::reset();

        $_SERVER['HTTP_HOST']   = 'www.example.com:80';
        $_SERVER['SCRIPT_NAME'] = '/joomla/index.php';
        $_SERVER['PHP_SELF']    = '/joomla/index.php';
        $_SERVER['REQUEST_URI'] = '/joomla/index.php?var=value';
    }

    /**
     * Resets the static Uri caches, which are not covered by the Factory snapshot.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function tearDown(): void
    {
        Uri::reset();

        parent::tearDown();
    }

    /**
     * Returns a url type field element.
     *
     * @param   string  $attributes  Extra attributes for the element
     *
     * @return  \SimpleXMLElement
     *
     * @since   __DEPLOY_VERSION__
     */
    private function urlElement(string $attributes = ''): \SimpleXMLElement
    {
        return new \SimpleXMLElement('<field name="unittest" type="url" filter="url" ' . $attributes . '/>');
    }

    /**
     * Test data for the testValueWithAProtocolIsKept method.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function dataWithProtocol(): array
    {
        return [
            'https with path, query and fragment' => ['https://example.com/path?q=1#frag'],
            'http'                                => ['http://example.com'],
            'already punycoded'                   => ['https://xn--mller-kva.de/pfad'],
        ];
    }

    /**
     * Tests that a value which already carries a protocol is handed back unchanged.
     *
     * @param   string  $value  The raw form field value
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[DataProvider('dataWithProtocol')]
    #[TestDox('keeps a value which already carries a protocol')]
    public function testValueWithAProtocolIsKept(string $value): void
    {
        $filter = new UrlFilter();

        $this->assertSame($value, $filter->filter($this->urlElement(), $value, null, null, null));
    }

    /**
     * Test data for the testEmptyValueIsRejected method.
     *
     * @return  array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function dataEmpty(): array
    {
        return [
            'empty string' => [''],
            'null'         => [null],
            'string zero'  => ['0'],
        ];
    }

    /**
     * Tests that an empty value is rejected as boolean false rather than an empty string.
     *
     * Note that the check is empty(), so the string "0" is rejected too.
     *
     * @param   mixed  $value  The raw form field value
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[DataProvider('dataEmpty')]
    #[TestDox('rejects an empty value as boolean false')]
    public function testEmptyValueIsRejected($value): void
    {
        $filter = new UrlFilter();

        $this->assertFalse($filter->filter($this->urlElement(), $value, null, null, null));
    }

    /**
     * Tests that a value without a protocol is treated as an external link.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('prepends http to a value without a protocol')]
    public function testValueWithoutAProtocolBecomesExternal(): void
    {
        $filter = new UrlFilter();

        $this->assertSame(
            'http://www.example.com',
            $filter->filter($this->urlElement(), 'www.example.com', null, null, null)
        );
        $this->assertSame(
            'http://example.com/path',
            $filter->filter($this->urlElement(), 'example.com/path', null, null, null)
        );
    }

    /**
     * Tests that a value which looks like an internal link gets the site root prepended.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('prepends the site root to a link which starts with index.php')]
    public function testInternalLinkGetsTheSiteRoot(): void
    {
        $filter = new UrlFilter();

        $this->assertSame(
            Uri::root() . 'index.php?option=com_content',
            $filter->filter($this->urlElement(), 'index.php?option=com_content', null, null, null)
        );
    }

    /**
     * Tests that the characters which are never valid in a uri are removed.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('removes the characters which are never valid in a uri')]
    public function testInvalidUriCharactersAreRemoved(): void
    {
        $filter = new UrlFilter();

        $this->assertSame(
            'https://example.com/ab',
            $filter->filter($this->urlElement(), 'https://example.com/a"b', null, null, null)
        );
        $this->assertSame(
            'https://example.com',
            $filter->filter($this->urlElement(), '<script>https://example.com', null, null, null)
        );
    }

    /**
     * Tests that surrounding whitespace is trimmed.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('trims surrounding whitespace')]
    public function testSurroundingWhitespaceIsTrimmed(): void
    {
        $filter = new UrlFilter();

        $this->assertSame(
            'https://example.com',
            $filter->filter($this->urlElement(), '  https://example.com  ', null, null, null)
        );
    }

    /**
     * Tests that an internationalised host is converted to punycode.
     *
     * Only the host is converted, the path is left as it was.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('converts an internationalised host to punycode')]
    public function testInternationalisedHostIsPunycoded(): void
    {
        $filter = new UrlFilter();

        $this->assertSame(
            'https://xn--mller-kva.de/pfad',
            $filter->filter($this->urlElement(), 'https://müller.de/pfad', null, null, null)
        );
    }

    /**
     * Tests that a relative field prefixes a bare path with the site path.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('prefixes a bare path with the site path when the field is relative')]
    public function testRelativeFieldPrefixesABarePath(): void
    {
        $filter = new UrlFilter();

        $this->assertSame(
            Uri::root(true) . '/some/page',
            $filter->filter($this->urlElement('relative="true" '), 'some/page', null, null, null)
        );
    }

    /**
     * Tests that a relative field leaves an already absolute path alone.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('leaves an already absolute path alone when the field is relative')]
    public function testRelativeFieldKeepsAnAbsolutePath(): void
    {
        $filter = new UrlFilter();

        $this->assertSame(
            '/already/absolute',
            $filter->filter($this->urlElement('relative="true" '), '/already/absolute', null, null, null)
        );
    }

    /**
     * Tests that a relative field still keeps a value which carries a protocol.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('keeps a value with a protocol when the field is relative')]
    public function testRelativeFieldKeepsAValueWithAProtocol(): void
    {
        $filter = new UrlFilter();

        $this->assertSame(
            'https://example.com',
            $filter->filter($this->urlElement('relative="true" '), 'https://example.com', null, null, null)
        );
    }

    /**
     * Tests that a field of another type gets the external treatment even when it is relative.
     *
     * The relative branch is only reachable for fields of type url, so a text field holding a bare
     * path is turned into an external link.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('treats a value in a non url field as external')]
    public function testNonUrlFieldTreatsTheValueAsExternal(): void
    {
        $filter  = new UrlFilter();
        $element = new \SimpleXMLElement('<field name="unittest" type="text" filter="url" />');

        $this->assertSame(
            'http://www.example.com',
            $filter->filter($element, 'www.example.com', null, null, null)
        );
    }
}
