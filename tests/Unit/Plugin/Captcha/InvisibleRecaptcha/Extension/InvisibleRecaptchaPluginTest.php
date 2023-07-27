<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Extension
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Plugin\Captcha\InvisibleRecaptcha\Extension;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Form\Field\CaptchaField;
use Joomla\CMS\Language\Language;
use Joomla\Event\Dispatcher;
use Joomla\Input\Input;
use Joomla\Plugin\Captcha\InvisibleReCaptcha\Extension\InvisibleReCaptcha;
use Joomla\Tests\Unit\UnitTestCase;
use ReCaptcha\RequestMethod;

/**
 * Test class for InvisibleReCaptcha plugin
 *
 * @package     Joomla.UnitTest
 * @subpackage  InvisibleReCaptcha
 *
 * @testdox     The InvisibleReCaptcha plugin
 *
 * @since       4.3.0
 */
class InvisibleRecaptchaPluginTest extends UnitTestCase
{
    /**
     * Setup
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function setUp(): void
    {
        if (empty($_SERVER['REMOTE_ADDR'])) {
            $_SERVER['REMOTE_ADDR'] = 'unit.test';
        }
    }

    /**
     * Cleanup
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function tearDown(): void
    {
        if (!empty($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] === 'unit.test') {
            unset($_SERVER['REMOTE_ADDR']);
        }
    }

    /**
     * @testdox  can init the captcha
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testInit()
    {
        $document = new HtmlDocument();
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturn('test');

        $app = $this->createStub(CMSWebApplicationInterface::class);
        $app->method('getDocument')->willReturn($document);
        $app->method('getLanguage')->willReturn($language);

        $plugin = new InvisibleReCaptcha(new Dispatcher(), ['params' => ['public_key' => 'test']], $this->createStub(RequestMethod::class));
        $plugin->setApplication($app);

        $this->assertTrue($plugin->onInit());
        $this->assertNotEmpty($document->getWebAssetManager()->getAsset('script', 'plg_captcha_recaptchainvisible'));
        $this->assertNotEmpty($document->getWebAssetManager()->getAsset('script', 'plg_captcha_recaptchainvisible.api'));
    }

    /**
     * @testdox  can init the captcha with a wrong application
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testInitWrongApplication()
    {
        $plugin = new InvisibleReCaptcha(new Dispatcher(), ['params' => ['public_key' => 'test']], $this->createStub(RequestMethod::class));
        $plugin->setApplication($this->createStub(CMSApplicationInterface::class));

        $this->assertFalse($plugin->onInit());
    }

    /**
     * @testdox  can init the captcha with an empty public key
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testInitEmptyPublicKey()
    {
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturn('test');

        $app = $this->createStub(CMSWebApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $plugin = new InvisibleReCaptcha(new Dispatcher(), ['params' => []], $this->createStub(RequestMethod::class));
        $plugin->setApplication($app);

        $this->expectException(\RuntimeException::class);

        $plugin->onInit();
    }

    /**
     * @testdox  can display the captcha
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testDisplay()
    {
        $plugin = new InvisibleReCaptcha(new Dispatcher(), ['params' => []], $this->createStub(RequestMethod::class));
        $plugin->setApplication($this->createStub(CMSWebApplicationInterface::class));

        $html = $plugin->onDisplay();

        $this->assertNotEmpty($html);
        $this->assertNotEquals($html, strip_tags($html));
    }

    /**
     * @testdox  can check successful answer
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testResponse()
    {
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturn('test');

        $app = $this->createStub(CMSWebApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);
        $app->method('getInput')->willReturn(new Input(['g-recaptcha-response' => 'test']));

        $method = $this->createStub(RequestMethod::class);
        $method->method('submit')->willReturn(json_encode(['success' => true]));

        $plugin = new InvisibleReCaptcha(new Dispatcher(), ['params' => ['private_key' => 'test']], $method);
        $plugin->setApplication($app);

        $this->assertTrue($plugin->onCheckAnswer());
    }

    /**
     * @testdox  can check answer with an empty private key
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testResponseEmptyPrivateKey()
    {
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturn('test');

        $app = $this->createStub(CMSWebApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $plugin = new InvisibleReCaptcha(new Dispatcher(), ['params' => []], $this->createStub(RequestMethod::class));
        $plugin->setApplication($app);

        $this->expectException(\RuntimeException::class);

        $plugin->onCheckAnswer();
    }

    /**
     * @testdox  can detect spam
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testResponseSpam()
    {
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturn('test');

        $app = $this->createStub(CMSWebApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);
        $app->method('getInput')->willReturn(new Input(['g-recaptcha-response' => '']));

        $plugin = new InvisibleReCaptcha(new Dispatcher(), ['params' => ['private_key' => 'test']], $this->createStub(RequestMethod::class));
        $plugin->setApplication($app);

        $this->expectException(\RuntimeException::class);

        $plugin->onCheckAnswer();
    }

    /**
     * @testdox  can check successful answer
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testFailedResponse()
    {
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturn('test');

        $app = $this->createStub(CMSWebApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);
        $app->method('getInput')->willReturn(new Input(['g-recaptcha-response' => 'test']));

        $method = $this->createStub(RequestMethod::class);
        $method->method('submit')->willReturn(json_encode(['success' => false]));

        $plugin = new InvisibleReCaptcha(new Dispatcher(), ['params' => ['private_key' => 'test']], $method);
        $plugin->setApplication($app);

        $this->expectException(\RuntimeException::class);

        $plugin->onCheckAnswer();
    }

    /**
     * @testdox  can setup field
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testSetupField()
    {
        $plugin = new InvisibleReCaptcha(new Dispatcher(), ['params' => ['private_key' => 'test']], $this->createStub(RequestMethod::class));
        $plugin->setApplication($this->createStub(CMSWebApplicationInterface::class));

        $this->assertNull($plugin->onSetupField(new CaptchaField(), new \SimpleXMLElement('<test/>')));
    }

    /**
     * @testdox  can return admin capabilities
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function testPrivacy()
    {
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturn('test');

        $app = $this->createStub(CMSWebApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $plugin = new InvisibleReCaptcha(new Dispatcher(), ['params' => ['private_key' => 'test']], $this->createStub(RequestMethod::class));
        $plugin->setApplication($app);

        $caps = $plugin->onPrivacyCollectAdminCapabilities();

        $this->assertNotEmpty($caps);
    }
}
