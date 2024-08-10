<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Base
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Document;

use Joomla\CMS\Document\Document;
use Joomla\CMS\Document\DocumentAwareTrait;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\Document\DocumentAwareTrait
 *
 * @package     Joomla.UnitTest
 * @subpackage  MVC
 * @since       4.4.0
 */
class DocumentAwareTraitTest extends UnitTestCase
{
    /**
     * @testdox  The user document can be set and accessed by the trait
     *
     * @return  void
     *
     * @since   4.4.0
     */
    public function testGetSetDocument()
    {
        $document = new Document();
        $trait    = new class () {
            use DocumentAwareTrait;

            public function get(): Document
            {
                return $this->getDocument();
            }
        };

        $trait->setDocument($document);

        $this->assertEquals($document, $trait->get());
    }

    /**
     * @testdox  The user document can be set and accessed by the trait
     *
     * @return  void
     *
     * @since   4.4.0
     */
    public function testGetDocumentThrowsException()
    {
        $this->expectException(\UnexpectedValueException::class);

        $trait = new class () {
            use DocumentAwareTrait;

            public function get(): Document
            {
                return $this->getDocument();
            }
        };

        $trait->get();
    }
}
