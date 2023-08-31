<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Extension
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Plugin\Captcha\InvisibleRecaptcha\Extension;

use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Captcha\CaptchaRegistry;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Event\Captcha\CaptchaSetupEvent;
use Joomla\CMS\Form\Field\CaptchaField;
use Joomla\CMS\Language\Language;
use Joomla\Event\Dispatcher;
use Joomla\Input\Input;
use Joomla\Plugin\Captcha\InvisibleReCaptcha\Extension\InvisibleReCaptcha;
use Joomla\Plugin\Captcha\InvisibleReCaptcha\Provider\InvisibleReCaptchaProvider;
use Joomla\Registry\Registry;
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
     * @testdox  Captcha Setup event test
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function testOnSetUpEvent(): void
    {
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturn('test');

        $app = $this->createStub(CMSWebApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $dispatcher = new Dispatcher();
        $registry   = new CaptchaRegistry();
        $event      = new CaptchaSetupEvent('onCaptchaSetup', ['subject' => $registry]);

        $plugin = new InvisibleReCaptcha($dispatcher, ['name' => 'test', 'params' => []]);
        $plugin->setApplication($app);
        $plugin->onCaptchaSetup($event);

        $this->assertTrue($registry->has('recaptcha_invisible'), 'The captcha provider are registered.');
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
        $document = new HtmlDocument();
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturn('test');

        $app = $this->createStub(CMSWebApplicationInterface::class);
        $app->method('getDocument')->willReturn($document);
        $app->method('getLanguage')->willReturn($language);

        $params  = new Registry(['public_key' => 'test']);
        $captcha = new InvisibleReCaptchaProvider($params, $app);

        $html = $captcha->display();

        $this->assertNotEmpty($html);
        $this->assertNotEquals($html, strip_tags($html));

        $this->assertNotEmpty($document->getWebAssetManager()->getAsset('script', 'plg_captcha_recaptchainvisible'));
        $this->assertNotEmpty($document->getWebAssetManager()->getAsset('script', 'plg_captcha_recaptchainvisible.api'));
    }

    /**
     * @testdox  can init the captcha with an empty public key
     *
     * @return  void
     *
     * @since   5.0.0
     */
    public function testDisplayEmptyPublicKey()
    {
        $language = $this->createStub(Language::class);
        $language->method('_')->willReturn('test');

        $app = $this->createStub(CMSWebApplicationInterface::class);
        $app->method('getLanguage')->willReturn($language);

        $params  = new Registry(['public_key' => '']);
        $captcha = new InvisibleReCaptchaProvider($params, $app);

        $this->expectException(\RuntimeException::class);

        $captcha->display();
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

        $params  = new Registry(['private_key' => 'test']);
        $captcha = new InvisibleReCaptchaProvider($params, $app, $method);

        $this->assertTrue($captcha->checkAnswer());
        $this->assertTrue($captcha->checkAnswer('test'));
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

        $params  = new Registry(['private_key' => '']);
        $captcha = new InvisibleReCaptchaProvider($params, $app);

        $this->expectException(\RuntimeException::class);

        $captcha->checkAnswer('test');
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

        $params  = new Registry(['private_key' => 'test']);
        $captcha = new InvisibleReCaptchaProvider($params, $app, $this->createStub(RequestMethod::class));

        $this->expectException(\RuntimeException::class);

        $captcha->checkAnswer();
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

        $params  = new Registry(['private_key' => 'test']);
        $captcha = new InvisibleReCaptchaProvider($params, $app, $method);

        $this->expectException(\RuntimeException::class);

        $captcha->checkAnswer();
        $captcha->checkAnswer('test');
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
        $params  = new Registry(['private_key' => 'test']);
        $captcha = new InvisibleReCaptchaProvider($params, $this->createStub(CMSWebApplicationInterface::class));

        $fieldXml = new \SimpleXMLElement('<test/>');
        $captcha->setupField(new CaptchaField(), $fieldXml);

        $this->assertEquals('true', (string) $fieldXml['hiddenLabel'], 'setupField() method should set correct attribute.');
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

        $dispatcher = new Dispatcher();

        $plugin = new InvisibleReCaptcha($dispatcher, ['name' => 'test', 'params' => []]);
        $plugin->setApplication($app);

        // @TODO: The event should be changed to what the Privacy component provide.
        $event = new \Joomla\Event\Event('onPrivacyCollectAdminCapabilities', ['result' => []]);
        $plugin->onPrivacyCollectAdminCapabilities($event);

        $this->assertNotEmpty($event['result']);
    }
}
