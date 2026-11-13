<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Document
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Document;

use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\WebAsset\WebAssetManager;
use Joomla\CMS\WebAsset\WebAssetRegistry;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Tests for HTML document head data.
 */
class HtmlDocumentTest extends UnitTestCase
{
    /**
     * Metadata attributes and their expected content after a head-data merge.
     *
     * @return  array
     */
    public static function metaDataProvider(): array
    {
        return [
            'Open Graph'        => ['property', 'og:title', 'Title', 'Title'],
            'Open Graph array'  => ['property', 'og:image', ['first.jpg', 'second.jpg'], ['first.jpg', 'second.jpg']],
            'Microdata'         => ['itemprop', 'headline', 'Title', 'Title'],
            'Microdata array'   => ['itemprop', 'image', ['first.jpg', 'second.jpg'], ['first.jpg', 'second.jpg']],
            'Name'              => ['name', 'keywords', 'joomla', 'joomla'],
            'Legacy name array' => ['name', 'keywords', ['first', 'last'], 'last'],
            'HTTP equivalent'   => ['http-equiv', 'refresh', '30', '30'],
            'Legacy HTTP array' => ['http-equiv', 'refresh', ['10', '30'], '30'],
        ];
    }

    /**
     * Merging head data preserves metadata attributes and legacy array handling.
     *
     * @param   string        $attribute  Metadata attribute
     * @param   string        $name       Metadata name
     * @param   string|array  $content    Original content
     * @param   string|array  $expected   Content after merging
     *
     * @return  void
     */
    #[DataProvider('metaDataProvider')]
    public function testMergeHeadDataPreservesMetaData(string $attribute, string $name, $content, $expected): void
    {
        $document = new HtmlDocument(['webAssetManager' => new WebAssetManager(new WebAssetRegistry())]);
        $document->setMetaData($name, $content, $attribute);

        $head = $document->getHeadData();
        $document->resetHeadData();

        $this->assertSame($document, $document->mergeHeadData($head));
        $this->assertSame($expected, $document->getMetaData($name, $attribute));

        if (!\in_array($attribute, ['name', 'http-equiv'], true)) {
            $this->assertSame('', $document->getMetaData($name));
        }
    }
}
