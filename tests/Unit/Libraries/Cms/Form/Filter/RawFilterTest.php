<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Form
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Form\Filter;

use Joomla\CMS\Form\Filter\RawFilter;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for RawFilter.
 *
 * The filter hands the value straight back. The element, group, input registry and form are not
 * read, so they are all passed as null.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Form
 * @since       __DEPLOY_VERSION__
 */
#[TestDox('The raw form filter')]
class RawFilterTest extends UnitTestCase
{
    /**
     * Tests that scalar values are handed back untouched.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('returns scalar values untouched')]
    public function testScalarValuesArePassedThrough(): void
    {
        $filter  = new RawFilter();
        $element = new \SimpleXMLElement('<field name="unittest" type="text" filter="raw" />');

        $this->assertSame('<b>markup</b>', $filter->filter($element, '<b>markup</b>', null, null, null));
        $this->assertSame('', $filter->filter($element, '', null, null, null));
        $this->assertSame(0, $filter->filter($element, 0, null, null, null));
        $this->assertNull($filter->filter($element, null, null, null, null));
        $this->assertFalse($filter->filter($element, false, null, null, null));
    }

    /**
     * Tests that arrays are handed back untouched, including nested ones.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('returns arrays untouched, including nested ones')]
    public function testArraysArePassedThrough(): void
    {
        $filter  = new RawFilter();
        $element = new \SimpleXMLElement('<field name="unittest" type="text" filter="raw" />');

        $this->assertSame([], $filter->filter($element, [], null, null, null));

        $nested = ['a' => ['b' => '<script>alert(1)</script>']];

        $this->assertSame($nested, $filter->filter($element, $nested, null, null, null));
    }

    /**
     * Tests that an object is handed back as the very same instance rather than a copy.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    #[TestDox('returns the very same object instance, not a copy')]
    public function testObjectIsNotCopied(): void
    {
        $filter  = new RawFilter();
        $element = new \SimpleXMLElement('<field name="unittest" type="text" filter="raw" />');
        $object  = (object) ['title' => 'Test'];

        $this->assertSame($object, $filter->filter($element, $object, null, null, null));
    }
}
