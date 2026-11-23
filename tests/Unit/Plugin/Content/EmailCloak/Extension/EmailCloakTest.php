<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Extension
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Plugin\Content\EmailCloak\Extension;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Event\Content\ContentPrepareEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Language;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\WebAsset\WebAssetManager;
use Joomla\Event\Dispatcher;
use Joomla\Plugin\Content\EmailCloak\Extension\EmailCloak;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for the EmailCloak plugin
 *
 * @since       6.2.1
 */
#[TestDox('The EmailCloak plugin')]
class EmailCloakTest extends UnitTestCase
{
    /**
     * @var    mixed
     * @since  6.2.1
     */
    private $backupDocument;

    /**
     * @var    array
     * @since  6.2.1
     */
    private $backupServer;

    /**
     * @return  void
     *
     * @since   6.2.1
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->backupDocument = Factory::$document;
        $this->backupServer   = $_SERVER;

        // The cloaking element contains the base URL
        $_SERVER['HTTP_HOST']   = 'localhost';
        $_SERVER['REQUEST_URI'] = '/';
        $this->resetUri();

        // The web asset manager handles useScript() via __call(), the test does not need real assets
        $assets = new class () extends WebAssetManager {
            public function __construct()
            {
            }

            public function __call($method, $arguments)
            {
                return $this;
            }
        };

        $document = $this->createStub(Document::class);
        $document->method('getWebAssetManager')->willReturn($assets);

        Factory::$document = $document;
    }

    /**
     * @return  void
     *
     * @since   6.2.1
     */
    protected function tearDown(): void
    {
        Factory::$document = $this->backupDocument;
        $_SERVER           = $this->backupServer;
        $this->resetUri();

        parent::tearDown();
    }

    /**
     * Drops the cached URI instances, so the changed server variables are used and do not leak into other tests.
     *
     * @return  void
     *
     * @since   6.2.1
     */
    private function resetUri(): void
    {
        foreach (['instances', 'root'] as $name) {
            (new \ReflectionProperty(Uri::class, $name))->setValue(null, []);
        }
    }

    /**
     * Runs the plugin on a text.
     *
     * @param   string  $text  The text to prepare
     *
     * @return  string
     *
     * @since   6.2.1
     */
    private function prepare(string $text): string
    {
        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('isClient')->willReturn(false);
        $app->method('getLanguage')->willReturn($this->createStub(Language::class));

        $plugin = new EmailCloak(new Dispatcher(), ['params' => ['mode' => 1]]);
        $plugin->setApplication($app);

        $item       = new \stdClass();
        $item->text = $text;

        $plugin->onContentPrepare(new ContentPrepareEvent('onContentPrepare', [
            'context' => 'com_content.article',
            'subject' => $item,
            'params'  => new \stdClass(),
            'page'    => 0,
        ]));

        return $item->text;
    }

    /**
     * @return  void
     *
     * @since   6.2.1
     */
    #[TestDox('cloaks plain addresses and mailto links')]
    public function testCloaksAddressesAndLinks()
    {
        $result = $this->prepare('<p>a@example.invalid</p><a href="mailto:b@example.invalid">Write me</a>');

        $this->assertSame(2, substr_count($result, '<joomla-hidden-mail'));
        $this->assertStringNotContainsString('@example.invalid', $result);
        $this->assertStringStartsWith('<p><joomla-hidden-mail', $result);
        $this->assertStringContainsString(base64_encode('Write me'), $result);
    }

    /**
     * @return  void
     *
     * @since   6.2.1
     */
    #[TestDox('cloaks links with a line break or a tab after the tag name')]
    public function testCloaksLinksWithWhitespaceAfterTagName()
    {
        foreach (["\n", "\t", "\r\n", '  '] as $whitespace) {
            $result = $this->prepare('<p><a' . $whitespace . 'href="mailto:user@example.invalid">mail</a></p>');

            $this->assertStringNotContainsString('user@example.invalid', $result);
            $this->assertSame(1, substr_count($result, '<joomla-hidden-mail'));
        }
    }

    /**
     * @return  void
     *
     * @since   6.2.1
     */
    #[TestDox('keeps the markup around the cloaked addresses')]
    public function testKeepsSurroundingMarkup()
    {
        $result = $this->prepare('<p>start</p> a@example.invalid and b@example.invalid <b>end</b>');

        $this->assertSame(2, substr_count($result, '<joomla-hidden-mail'));
        $this->assertStringStartsWith('<p>start</p> <joomla-hidden-mail', $result);
        $this->assertStringContainsString('</joomla-hidden-mail> and <joomla-hidden-mail', $result);
        $this->assertStringEndsWith('</joomla-hidden-mail> <b>end</b>', $result);
    }

    /**
     * @return  void
     *
     * @since   6.2.1
     */
    #[TestDox('removes the {emailcloak=off} marker and does not cloak the text')]
    public function testEmailcloakOff()
    {
        $this->assertSame('a@example.invalid', $this->prepare('{emailcloak=off}a@example.invalid'));
    }

    /**
     * @return  void
     *
     * @since   6.2.1
     */
    #[TestDox('leaves the text unchanged when it is not valid UTF-8')]
    public function testInvalidUtf8()
    {
        $text = "a@example.invalid \xff";

        $this->assertSame($text, $this->prepare($text));
    }

    /**
     * @return  void
     *
     * @since   6.2.1
     */
    #[TestDox('cloaks many addresses in linear time')]
    public function testManyAddresses()
    {
        $text = str_repeat('user@example.invalid ', 4000);

        $start  = microtime(true);
        $result = $this->prepare($text);
        $time   = microtime(true) - $start;

        $this->assertSame(4000, substr_count($result, '<joomla-hidden-mail'));
        // The quadratic version needs several seconds for this input, a linear one a small fraction of a second
        $this->assertLessThan(1.0, $time);
    }

    /**
     * @return  void
     *
     * @since   6.2.1
     */
    #[TestDox('runs in linear time for many unclosed tags without JIT')]
    #[RunInSeparateProcess]
    public function testManyUnclosedTagsWithoutJit()
    {
        // Own process: PHP caches the compiled patterns, so pcre.jit has to be off before their first use
        ini_set('pcre.jit', '0');

        $text = 'x@ ' . str_repeat('<a ', 4000) . 'href="mailto:a@b.cd">a@b.cd';

        $start = microtime(true);
        $this->prepare($text);
        $time = microtime(true) - $start;

        // The quadratic patterns need many seconds for this input without JIT, linear ones a few milliseconds
        $this->assertLessThan(1.0, $time);
    }
}
