<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Base
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Document;

use Joomla\CMS\Document\DocumentAwareTrait;
use Joomla\CMS\Document\Document;
use Joomla\Tests\Unit\UnitTestCase;
use UnexpectedValueException;

/**
 * Test class for \Joomla\CMS\Document\DocumentAwareTrait
 *
 * @package     Joomla.UnitTest
 * @subpackage  MVC
 * @since       __DEPLOY_VERSION__
 */
class DocumentAwareTraitTest extends UnitTestCase
{
    /**
     * @testdox  The user document can be set and accessed by the trait
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
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
     * @since   __DEPLOY_VERSION__
     */
    public function testGetDocumentThrowsException()
    {
        $this->expectException(UnexpectedValueException::class);

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
