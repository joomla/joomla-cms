<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Microdata
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Microdata;

use Joomla\CMS\Microdata\Microdata;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for JMicrodata
 *
 * @package     Joomla.UnitTest
 * @subpackage  Microdata
 * @since       3.2
 */
// phpcs:disable PSR1.Classes.ClassDeclaration
class MicrodataTest extends UnitTestCase
{
    /**
     * Test the default settings
     *
     * @return  void
     *
     * @since   3.2
     */
    public function testDefaultType()
    {
        $microdata = new MicrodataStub();

        $this->assertEquals('Thing', $microdata->getType());
        $this->assertTrue($microdata->isEnabled());
    }

    /**
     * @return  void
     * @since   4.0.0
     */
    public function testOverrideTypeDuringConstruction()
    {
        $type         = 'some-type';
        $expectedType = ucfirst(trim($type));
        $microdata    = new MicrodataStub('some-type', true, [$expectedType => []]);

        $this->assertEquals($expectedType, $microdata->getType());
        $this->assertTrue($microdata->isEnabled());
    }

    /**
     * @return  void
     * @since   4.0.0
     */
    public function testDisableDuringConstruction()
    {
        $microdata = new MicrodataStub('', false);

        $this->assertFalse($microdata->isEnabled());
    }

    /**
     * Test the setType() function
     *
     * @return  void
     * @since   3.2
     */
    public function testSetType()
    {
        $type      = 'Article';
        $microdata = new MicrodataStub('', true, [$type => []]);

        $microdata->setType($type);

        // Test if the current Type is 'Article'
        $this->assertEquals($type, $microdata->getType());
    }

    /**
     * Test the setType() function
     *
     * @return  void
     * @since   3.2
     */
    public function testSetInvalidTypeSetsDefaultType()
    {
        $type      = 'Article';
        $microdata = new MicrodataStub();

        $microdata->setType($type);

        $this->assertEquals('Thing', $microdata->getType());
    }

    /**
     * Test the fallback() function
     *
     * @return  void
     * @since   3.2
     */
    public function testFallback()
    {
        $microdata = new MicrodataStub(
            '',
            true,
            [
                'Article' => [
                    'properties' => [
                        'articleBody' => [],
                    ],
                ],
            ]
        );

        // Test fallback values
        $microdata->fallback('Article', 'articleBody');
        $this->assertEquals('Article', $microdata->getFallbackType());
        $this->assertEquals('articleBody', $microdata->getFallbackProperty());
    }

    /**
     * Test the fallback() function
     *
     * @return  void
     * @since   4.0.0
     */
    public function testFallbackWithNotExistingProperty()
    {
        $microdata = new MicrodataStub(
            '',
            true,
            [
                'Article' => [
                    'extends'    => '',
                    'properties' => [
                        'articleBody' => [],
                    ],
                ],
            ]
        );

        // Test if the Fallback Property fallbacks when it isn't available in the $Type
        $microdata->fallback('Article', 'anUnavailableProperty');
        $this->assertEquals('Article', $microdata->getFallbackType());
        $this->assertNull($microdata->getFallbackProperty());
    }

    /**
     * Test the fallback() function
     *
     * @return  void
     * @since   4.0.0
     */
    public function testFallbackWithNotExistingTypeAndProperty()
    {
        $microdata = new MicrodataStub('', true, []);

        // Test if the Fallback Type fallbacks to the 'Thing' Type
        $microdata->fallback('anUnavailableType', 'anUnavailableProperty');
        $this->assertEquals('Thing', $microdata->getFallbackType());
        $this->assertNull($microdata->getFallbackProperty());
    }

    /**
     * Test the display() function
     *
     * @return  void
     * @since   3.2
     */
    public function testDisplayIsEmptyByDefault()
    {
        // Test display() with all null params
        $microdata = new MicrodataStub();

        $this->assertEquals('', $microdata->display());
    }

    /**
     * @return  void
     * @since   4.0.0
     */
    public function testDisplayResetsParams()
    {
        $content   = 'Some Content';
        $microdata = new MicrodataStub();

        $microdata->setType('Article')
            ->content($content)
            ->property('name')
            ->fallback('Thing', 'url')
            ->display();

        $this->assertNull($microdata->getFallbackProperty());
        $this->assertNull($microdata->getFallbackType());
        $this->assertNull($microdata->getProperty());
        $this->assertNull($microdata->getContent());
    }

    /**
     * @return  void
     * @since   4.0.0
     */
    public function testDisplaySimple()
    {
        $microdata = new MicrodataStub(
            'Article',
            true,
            [
                'Article' => [
                    'properties' => [
                        'url' => [
                            'expectedTypes' => ['URL'],
                        ],
                    ],
                ],
            ]
        );

        // Test for a simple display
        $html = $microdata
            ->property('url')
            ->display();

        $this->assertEquals("itemprop='url'", $html);
    }

    /**
     * @return  void
     * @since   4.0.0
     */
    public function testDisplaySimpleWithContent()
    {
        $content   = 'Some Content';
        $microdata = new MicrodataStub(
            'Article',
            true,
            [
                'Article' => [
                    'properties' => [
                        'url' => [
                            'expectedTypes' => ['URL'],
                        ],
                    ],
                ],
            ]
        );

        // Test for a simple display
        $html = $microdata
            ->property('url')
            ->content($content)
            ->display();

        $this->assertEquals("<span itemprop='url'>$content</span>", $html);
    }

    /**
     * @return  void
     * @since   4.0.0
     */
    public function testDisplaySimpleWithEmptyContent()
    {
        $content   = '';
        $microdata = new MicrodataStub(
            'Article',
            true,
            [
                'Article' => [
                    'properties' => [
                        'url' => [
                            'expectedTypes' => ['URL'],
                        ],
                    ],
                ],
            ]
        );

        // Test for a simple display
        $html = $microdata
            ->property('url')
            ->content($content)
            ->display();

        $this->assertEquals("<span itemprop='url'>$content</span>", $html);
    }

    /**
     * @return  void
     * @since   4.0.0
     */
    public function testDisplayNested()
    {
        $microdata = new MicrodataStub(
            'Article',
            true,
            [
                'Article' => [
                    'properties' => [
                        'author' => [
                            'expectedTypes' => ['Organization', 'Person'],
                        ],
                    ],
                ],
            ]
        );

        // Test for a simple display
        $html = $microdata
            ->property('author')
            ->display();

        $this->assertEquals("itemprop='author' itemscope itemtype='https://schema.org/Organization'", $html);
    }

    /**
     * @return  void
     * @since   4.0.0
     */
    public function testDisplayNestedWithContent()
    {
        $content   = 'some content';
        $microdata = new MicrodataStub(
            'Article',
            true,
            [
                'Article' => [
                    'properties' => [
                        'author' => [
                            'expectedTypes' => ['Organization', 'Person'],
                        ],
                    ],
                ],
            ]
        );

        // Test for a simple display
        $html = $microdata
            ->property('author')
            ->content($content)
            ->display();

        $this->assertEquals(
            "<span itemprop='author' itemscope itemtype='https://schema.org/Organization'>$content</span>",
            $html
        );
    }

    /**
     * @return  void
     * @since   4.0.0
     */
    public function testDisplayNestedWithContentAndFallback()
    {
        $content   = 'some content';
        $microdata = new MicrodataStub(
            'Article',
            true,
            [
                'Article' => [
                    'properties' => [
                        'author' => [
                            'expectedTypes' => [
                                'Organization',
                                'Person',
                            ],
                        ],
                    ],
                ],
                'Person' => [
                    'properties' => [
                        'name' => [
                            'expectedTypes' => ['Text'],
                        ],
                    ],
                ],
            ]
        );

        // Test for a simple display
        $html = $microdata
            ->fallback('Person', 'name')
            ->property('author')
            ->content($content)
            ->display();

        $this->assertEquals(
            "<span itemprop='author' itemscope itemtype='https://schema.org/Person'><span itemprop='name'>$content</span></span>",
            $html
        );
    }

    /**
     * @return  void
     * @since   4.0.0
     */
    public function testDisplayNestedWithFallback()
    {
        $microdata = new MicrodataStub(
            'Article',
            true,
            [
                'Article' => [
                    'properties' => [
                        'author' => [
                            'expectedTypes' => [
                                'Organization',
                                'Person',
                            ],
                        ],
                    ],
                ],
                'Person' => [
                    'properties' => [
                        'name' => [
                            'expectedTypes' => ['Text'],
                        ],
                    ],
                ],
            ]
        );

        // Test for a simple display
        $html = $microdata
            ->fallback('Person', 'name')
            ->property('author')
            ->display();

        $this->assertEquals("itemprop='author' itemscope itemtype='https://schema.org/Person' itemprop='name'", $html);
    }

    /**
     * @return  void
     * @since   4.0.0
     */
    public function testDisplayMeta()
    {
        $microdata = new MicrodataStub(
            'Article',
            true,
            [
                'Article' => [
                    'properties' => [
                        'datePublished' => [
                            'expectedTypes' => [
                                'Date',
                            ],
                        ],
                    ],
                ],
            ]
        );

        // Test for a simple display
        $html = $microdata
            ->property('datePublished')
            ->display();

        $this->assertEquals("itemprop='datePublished'", $html);
    }

    /**
     * @return  void
     * @since   4.0.0
     */
    public function testDisplayMetaWithContent()
    {
        $content   = '01 January 2011';
        $microdata = new MicrodataStub(
            'Article',
            true,
            [
                'Article' => [
                    'properties' => [
                        'datePublished' => [
                            'expectedTypes' => [
                                'Date',
                            ],
                        ],
                    ],
                ],
            ]
        );

        // Test for a simple display
        $html = $microdata
            ->property('datePublished')
            ->content($content)
            ->display();

        $this->assertEquals("<meta itemprop='datePublished' content='$content'>$content", $html);
    }

    /**
     * @return  void
     * @since   4.0.0
     */
    public function testDisplayMetaWithHumanAndMachineContent()
    {
        $content        = '01 January 2011';
        $machineContent = "2011-01-01T00:00:00+00:00";
        $microdata      = new MicrodataStub(
            'Article',
            true,
            [
                'Article' => [
                    'properties' => [
                        'datePublished' => [
                            'expectedTypes' => [
                                'Date',
                            ],
                        ],
                    ],
                ],
            ]
        );

        // Test for a simple display
        $html = $microdata
            ->property('datePublished')
            ->content($content, $machineContent)
            ->display();

        $this->assertEquals("<meta itemprop='datePublished' content='$machineContent'>$content", $html);
    }

    /**
     * @return  void
     * @since   4.0.0
     */
    public function testDisplayMetaReturnsEmptyStringWhenPropertyDoesNotExist()
    {
        $microdata = new MicrodataStub(
            'Article',
            true,
            [
                'Article' => [
                    'extends'    => '',
                    'properties' => [],
                ],
            ]
        );

        // Test for a simple display
        $html = $microdata
            ->content('en-GB')
            ->property('doesNotExist')
            ->display('meta', true);

        $this->assertEmpty($html);
    }

    /**
     * @return  void
     * @since   4.0.0
     */
    public function testDisplayContentWhenDisabled()
    {
        $content   = 'Some Content';
        $microdata = new MicrodataStub('Article', false, []);

        // Test for a simple display
        $html = $microdata
            ->content($content)
            ->fallback('Article', 'about')
            ->property('datePublished')
            ->display();

        $this->assertEquals($content, $html);
    }

    /**
     * @return  void
     * @since   4.0.0
     */
    public function testDisplayMetaContentWhenDisabled()
    {
        $microdata = new MicrodataStub('Article', false, []);

        // Test for a simple display
        $html = $microdata
            ->content('en-GB')
            ->property('inLanguage')
            ->fallback('Language', 'name')
            ->display('meta', true);

        $this->assertEmpty($html);
    }

    /**
     * @return  void
     * @since   4.0.0
     */
    public function testDisplayFallbackForNotExistingProperties()
    {
        $microdata = new MicrodataStub(
            'Article',
            true,
            [
                'Article' => [
                    'extends'    => '',
                    'properties' => [
                        'about' => [
                            'expectedTypes' => [
                                'Thing',
                            ],
                        ],
                    ],
                ],
            ]
        );

        // Test for a simple display
        $html = $microdata
            ->property('notExisting')
            ->fallback('Article', 'about')
            ->display();

        $this->assertEquals("itemscope itemtype='https://schema.org/Article' itemprop='about'", $html);
    }

    /**
     * @return  void
     * @since   4.0.0
     */
    public function testDisplayFallbackForNotExistingPropertiesWithContent()
    {
        $content   = 'Some content';
        $microdata = new MicrodataStub(
            'Article',
            true,
            [
                'Article' => [
                    'extends'    => '',
                    'properties' => [
                        'about' => [
                            'expectedTypes' => [
                                'Thing',
                            ],
                        ],
                    ],
                ],
            ]
        );

        // Test for a simple display
        $html = $microdata
            ->content($content)
            ->property('notExisting')
            ->fallback('Article', 'about')
            ->display();

        $this->assertEquals(
            "<span itemscope itemtype='https://schema.org/Article'><span itemprop='about'>$content</span></span>",
            $html
        );
    }

    /**
     * @return  void
     * @since   4.0.0
     */
    public function testDisplayFallbackForNotExistingPropertiesAndFallbacks()
    {
        $microdata = new MicrodataStub(
            'Article',
            true,
            [
                'Article' => [
                    'extends'    => '',
                    'properties' => [],
                ],
            ]
        );

        // Test for a simple display
        $html = $microdata
            ->property('notExisting')
            ->fallback('Article', 'notExisting')
            ->display();

        $this->assertEquals("itemscope itemtype='https://schema.org/Article'", $html);
    }

    /**
     * @return  void
     * @since   4.0.0
     */
    public function testDisplayFallbackWhenThePropertyDoesNotExistInTheType()
    {
        $microdata = new MicrodataStub(
            'Article',
            true,
            [
                'Article' => [
                    'extends'    => 'Thing',
                    'properties' => [],
                ],
                'Thing' => [
                    'extends'    => '',
                    'properties' => [
                        'datePublished' => [
                            'expectedTypes' => ['Date'],
                        ],
                    ],
                ],
            ]
        );

        // Test without $content if fallbacks, the $Property isn't available in the current $Type
        $html = $microdata
            ->property('notExisting')
            ->fallback('Article', 'datePublished')
            ->display();

        $this->assertEquals("itemscope itemtype='https://schema.org/Article' itemprop='datePublished'", $html);
    }

    /**
     * @return  void
     * @since   4.0.0
     */
    public function testDisplayFallbackWithContentWhenThePropertyDoesNotExistInTheType()
    {
        $content   = 'Some content';
        $microdata = new MicrodataStub(
            'Article',
            true,
            [
                'Article' => [
                    'extends'    => 'Thing',
                    'properties' => [],
                ],
                'Thing' => [
                    'extends'    => '',
                    'properties' => [
                        'datePublished' => [
                            'expectedTypes' => ['Date'],
                        ],
                    ],
                ],
            ]
        );

        // Test for a simple display
        $html = $microdata
            ->content($content)
            ->property('notExisting')
            ->fallback('Article', 'datePublished')
            ->display();

        $this->assertEquals(
            "<meta itemscope itemtype='https://schema.org/Article' itemprop='datePublished' content='$content'>",
            $html
        );
    }

    /**
     * @return array
     * @since 4.0.0
     *
     */
    public function displayTypes(): array
    {
        return [
            ['inline', "itemprop='datePublished'"],
            ['div', "<div itemprop='datePublished'></div>"],
            ['span', "<span itemprop='datePublished'></span>"],
            ['meta', "<meta itemprop='datePublished' content=''>"],
        ];
    }

    /**
     * @param   string   $type      Type
     * @param   string   $expected  Expected
     *
     * @since        4.0.0
     * @return  void
     * @dataProvider displayTypes
     */
    public function testDisplayTypes($type, $expected)
    {
        $microdata = new MicrodataStub(
            'Article',
            true,
            [
                'Article' => [
                    'extends'    => 'Thing',
                    'properties' => [
                        'datePublished' => [
                            'expectedTypes' => ['Date'],
                        ],
                    ],
                ],
            ]
        );

        // Test for a simple display
        $html = $microdata
            ->property('datePublished')
            ->display($type);

        $this->assertEquals($expected, $html);
    }

    /**
     * @return array
     * @since 4.0.0
     *
     */
    public function displayTypesWithContent(): array
    {
        return [
            ['inline', 'Some content', "itemprop='datePublished'"],
            ['div', 'Some content', "<div itemprop='datePublished'>Some content</div>"],
            ['span', 'Some content', "<span itemprop='datePublished'>Some content</span>"],
            ['meta', 'Some content', "<meta itemprop='datePublished' content='Some content'>"],
        ];
    }

    /**
     * @param   string   $type      Type
     * @param   string   $content   Content
     * @param   string   $expected  Expected
     *
     * @since        4.0.0
     * @return  void
     * @dataProvider displayTypesWithContent
     */
    public function testDisplayTypesWithContent($type, $content, $expected)
    {
        $microdata = new MicrodataStub(
            'Article',
            true,
            [
                'Article' => [
                    'extends'    => 'Thing',
                    'properties' => [
                        'datePublished' => [
                            'expectedTypes' => ['Date'],
                        ],
                    ],
                ],
            ]
        );

        // Test for a simple display
        $html = $microdata
            ->content($content)
            ->property('datePublished')
            ->display($type);

        $this->assertEquals($expected, $html);
    }

    /**
     * Test the isTypeAvailable() function
     *
     * @return  void
     *
     * @since   3.2
     */
    public function testIsTypeAvailable()
    {
        $this->assertTrue(MicrodataStub::isTypeAvailable('Article'));
        $this->assertFalse(MicrodataStub::isTypeAvailable('DoesNotExist'));
    }

    /**
     * Test the isPropertyInType() function
     *
     * @return  void
     *
     * @since   3.2
     */
    public function testIsPropertyInType()
    {
        MicrodataStub::setTypes(
            [
                'Article' => [
                    'extends'    => '',
                    'properties' => [
                        'datePublished' => [
                            'expectedTypes' => ['Date'],
                        ],
                    ],
                ],
            ]
        );

        $this->assertTrue(MicrodataStub::isPropertyInType('Article', 'datePublished'));
        $this->assertFalse(MicrodataStub::isPropertyInType('Article', 'aPropertyThatDoesNotExist'));
        $this->assertFalse(MicrodataStub::isPropertyInType('aTypeThatDoesNotExist', 'aPropertyThatDoesNotExist'));
    }

    /**
     * Test the expectedDisplayType() function
     *
     * @return  void
     *
     * @since   3.2
     * @throws \ReflectionException
     */
    public function testExpectedDisplayType()
    {
        $microdata = new MicrodataStub();
        $microdata::setTypes(
            [
                'Article' => [
                    'extends'    => '',
                    'properties' => [
                        'articleBody' => [
                            'expectedTypes' => ['Text'],
                        ],
                        'about' => [
                            'expectedTypes' => ['Thing'],
                        ],
                        'datePublished' => [
                            'expectedTypes' => ['Date'],
                        ],
                    ],
                ],
            ]
        );

        // Use reflection to test protected method (it's easier than testing this using the public interface)
        $reflectionClass = new \ReflectionClass($microdata);
        $method          = $reflectionClass->getMethod('getExpectedDisplayType');
        $method->setAccessible(true);

        $this->assertEquals('normal', $method->invoke($microdata, 'Article', 'articleBody'));
        $this->assertEquals('nested', $method->invoke($microdata, 'Article', 'about'));
        $this->assertEquals('meta', $method->invoke($microdata, 'Article', 'datePublished'));
    }

    /**
     * Test the displayScope() function
     *
     * @return  void
     * @since   3.2
     */
    public function testDisplayScope()
    {
        $type      = 'Article';
        $microdata = new MicrodataStub(
            $type,
            true,
            [
                'Article' => [
                    'extends'    => '',
                    'properties' => [],
                ],
            ]
        );

        $this->assertEquals("itemscope itemtype='https://schema.org/$type'", $microdata->displayScope());
    }

    /**
     * Test the displayScope() function
     *
     * @return  void
     * @since   3.2
     */
    public function testDisplayScopeIsEmptyWhenDisabled()
    {
        $type      = 'Article';
        $microdata = new MicrodataStub($type, false);

        $this->assertEquals("", $microdata->displayScope());
    }

    /**
     * Test the getAvailableTypes() function
     *
     * @return  void
     *
     * @since   3.2
     */
    public function testGetAvailableTypes()
    {
        $microdata = new MicrodataStub();
        $microdata::setTypes(
            [
                'Article' => [
                    'extends'    => '',
                    'properties' => [
                        'articleBody' => [
                            'expectedTypes' => ['Text'],
                        ],
                        'about' => [
                            'expectedTypes' => ['Thing'],
                        ],
                        'datePublished' => [
                            'expectedTypes' => ['Date'],
                        ],
                    ],
                ],
            ]
        );

        $types = $microdata::getAvailableTypes();

        $this->assertCount(1, $types);
        $this->assertEquals('Article', $types[0]);
    }

    /**
     * Test the static htmlMeta() function
     *
     * @return  void
     *
     * @since   3.2
     */
    public function testHtmlMeta()
    {
        $scope    = 'Article';
        $content  = 'anything';
        $property = 'datePublished';

        // Test with all params
        $this->assertEquals(
            "<meta itemscope itemtype='https://schema.org/$scope' itemprop='$property' content='$content'>",
            MicrodataStub::htmlMeta($content, $property, $scope)
        );

        // Test with the $inverse mode
        $this->assertEquals(
            "<meta itemprop='$property' itemscope itemtype='https://schema.org/$scope' content='$content'>",
            MicrodataStub::htmlMeta($content, $property, $scope, true)
        );

        // Test without the $scope
        $this->assertEquals(
            "<meta itemprop='$property' content='$content'>",
            MicrodataStub::htmlMeta($content, $property)
        );
    }

    /**
     * Test the htmlDiv() function
     *
     * @return  void
     *
     * @since   3.2
     */
    public function testHtmlDiv()
    {
        // Setup
        $scope    = 'Article';
        $content  = 'microdata';
        $property = 'about';

        // Test with all params
        $this->assertEquals(
            "<div itemscope itemtype='https://schema.org/$scope' itemprop='$property'>$content</div>",
            MicrodataStub::htmlDiv($content, $property, $scope)
        );

        // Test with the $inverse mode
        $this->assertEquals(
            "<div itemprop='$property' itemscope itemtype='https://schema.org/$scope'>$content</div>",
            MicrodataStub::htmlDiv($content, $property, $scope, true)
        );

        // Test without the $scope
        $this->assertEquals(
            "<div itemprop='$property'>$content</div>",
            MicrodataStub::htmlDiv($content, $property)
        );

        // Test without the $property
        $this->assertEquals(
            "<div itemprop='$property' itemscope itemtype='https://schema.org/$scope'>$content</div>",
            MicrodataStub::htmlDiv($content, $property, $scope, true)
        );

        // Test without the $scope, $property
        $this->assertEquals(
            "<div>$content</div>",
            MicrodataStub::htmlDiv($content)
        );
    }

    /**
     * Test the htmlSpan() function
     *
     * @return  void
     *
     * @since   3.2
     */
    public function testHtmlSpan()
    {
        // Setup
        $scope    = 'Article';
        $content  = 'anything';
        $property = 'about';

        // Test with all params
        $this->assertEquals(
            "<span itemscope itemtype='https://schema.org/$scope' itemprop='$property'>$content</span>",
            MicrodataStub::htmlSpan($content, $property, $scope)
        );

        // Test with the inverse mode
        $this->assertEquals(
            "<span itemprop='$property' itemscope itemtype='https://schema.org/$scope'>$content</span>",
            MicrodataStub::htmlSpan($content, $property, $scope, true)
        );

        // Test without the $scope
        $this->assertEquals(
            "<span itemprop='$property'>$content</span>",
            MicrodataStub::htmlSpan($content, $property)
        );

        // Test without the $property
        $this->assertEquals(
            "<span itemprop='$property' itemscope itemtype='https://schema.org/$scope'>$content</span>",
            MicrodataStub::htmlSpan($content, $property, $scope, true)
        );

        // Test without the $scope, $property
        $this->assertEquals(
            "<span>$content</span>",
            MicrodataStub::htmlSpan($content)
        );
    }
}

/**
 * MicrodataStub
 *
 * @since   4.0.0
 */
class MicrodataStub extends Microdata
{
    /**
     * MicrodataStub constructor.
     *
     * Set the types to avoid file_get_contents
     *
     * @param   string   $type   Type
     * @param   bool     $flag   Flag
     * @param   array    $types  Types
     *
     * @since   4.0.0
     */
    public function __construct($type = '', $flag = true, $types = [])
    {
        self::$types = $types;

        parent::__construct($type, $flag);
    }

    /**
     * Public test helper to set the types
     *
     * @param   array   $types  Types
     *
     * @return void
     * @since   4.0.0
     */
    public static function setTypes($types)
    {
        self::$types = $types;
    }

    /**
     * Override the loadTypes method
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected static function loadTypes()
    {
        // DO nothing, use the already loaded types to avoid filesystem access during tests
    }
}
