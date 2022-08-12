<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Extension
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Plugin\Captcha\Recaptcha\Extension;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Form\Field\CaptchaField;
use Joomla\CMS\Language\Language;
use Joomla\Event\Dispatcher;
use Joomla\Input\Input;
use Joomla\Plugin\Captcha\Recaptcha\Extension\Recaptcha;
use Joomla\Tests\Unit\UnitTestCase;
use Joomla\Utilities\IpHelper;
use ReCaptcha\RequestMethod;
use ReCaptcha\RequestParameters;
use ReCaptcha\Response;
use RuntimeException;
use SimpleXMLElement;

/**
 * Test class for Recaptcha plugin
 *
 * @package     Joomla.UnitTest
 * @subpackage  Recaptcha
 *
 * @testdox     The Recaptcha plugin
 *
 * @since       __DEPLOY_VERSION__
 */
class RecaptchaPluginTest extends UnitTestCase
{
    /**
     * @testdox  can display a field
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testDisplayCaptcha()
    {
        $document = new HtmlDocument();
        $language = $this->createStub(Language::class);
        $language->method('getTag')->willReturn('en');

        $app = $this->createStub(CMSApplication::class);
        $app->method('getLanguage')->willReturn($language);
        $app->method('getDocument')->willReturn($document);

        $plugin = new Recaptcha(new Dispatcher(), ['params' => ['public_key' => 'test']], $this->getRequestMethod());
        $plugin->setApplication($app);
        $xml = $plugin->display('unit', 'test');

        $this->assertStringContainsString('unit', $xml);
        $this->assertStringContainsString('test', $xml);
        $this->assertStringContainsString(
            'google.com/recaptcha/api.js',
            $document->getWebAssetManager()->getAsset('script', 'plg_captcha_recaptcha.api')->getUri()
        );
    }

    /**
     * @testdox  can initialize empty public key
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testDisplayWithEmptyPublicKey()
    {
        $this->expectException(RuntimeException::class);

        $app = $this->createStub(CMSApplication::class);
        $app->method('getLanguage')->willReturn($this->createStub(Language::class));

        $plugin = new Recaptcha(new Dispatcher(), ['params' => []], $this->getRequestMethod());
        $plugin->setApplication($app);
        $plugin->display('unit', 'test');
    }

    /**
     * @testdox  can initialize empty public key
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testDisplayWithWrongApplication()
    {
        $this->expectException(RuntimeException::class);

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($this->createStub(Language::class));

        $plugin = new Recaptcha(new Dispatcher(), ['params' => ['public_key' => 'test']], $this->getRequestMethod());
        $plugin->setApplication($app);
        $plugin->display('unit', 'test');
    }

    /**
     * @testdox  can check successful answer
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testCheckSuccessfulAnswer()
    {
        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($this->createStub(Language::class));
        $app->input = new Input();

        IpHelper::setIp('test');

        $plugin = new Recaptcha(new Dispatcher(), ['params' => ['private_key' => 'test']], $this->getRequestMethod());
        $plugin->setApplication($app);
        $success = $plugin->checkAnswer('unit test');

        $this->assertTrue($success);
    }

    /**
     * @testdox  can check error answer
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testCheckErrorAnswer()
    {
        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($this->createStub(Language::class));
        $app->input = new Input();

        IpHelper::setIp('test');

        $plugin = new Recaptcha(new Dispatcher(), ['params' => ['private_key' => 'test']], $this->getRequestMethod(['error-codes' => []]));
        $plugin->setApplication($app);
        $success = $plugin->checkAnswer('unit test');

        $this->assertFalse($success);
    }

    /**
     * @testdox  can check error answer with error codes
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testCheckErrorAnswerWithErrorCodes()
    {
        $this->expectException(RuntimeException::class);

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($this->createStub(Language::class));
        $app->input = new Input();

        IpHelper::setIp('test');

        $plugin = new Recaptcha(new Dispatcher(), ['params' => ['private_key' => 'test']], $this->getRequestMethod(['error-codes' => ['test']]));
        $plugin->setApplication($app);
        $plugin->checkAnswer('unit test');
    }

    /**
     * @testdox  can check error answer with no IP available
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testCheckAnswerWithNoIPAvailable()
    {
        $this->expectException(RuntimeException::class);

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($this->createStub(Language::class));
        $app->input = new Input();

        IpHelper::setIp(false);

        $plugin = new Recaptcha(new Dispatcher(), ['params' => []], $this->getRequestMethod());
        $plugin->setApplication($app);
        $plugin->checkAnswer('unit test');
    }

    /**
     * @testdox  can check error answer with no IP available
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testCheckAnswerWithErrorResponse()
    {
        $this->expectException(RuntimeException::class);

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($this->createStub(Language::class));
        $app->input = new Input(['g-recaptcha-response' => '']);

        IpHelper::setIp('test');

        $plugin = new Recaptcha(new Dispatcher(), ['params' => []], $this->getRequestMethod());
        $plugin->setApplication($app);
        $plugin->checkAnswer();
    }

    /**
     * @testdox  can check error answer with no IP available
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testAdminCapabilities()
    {
        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($this->createStub(Language::class));

        $plugin = new Recaptcha(new Dispatcher(), ['params' => []], $this->getRequestMethod());
        $plugin->setApplication($app);
        $strings = $plugin->onPrivacyCollectAdminCapabilities();

        $this->assertNotEmpty($strings);
    }

    /**
     * @testdox  can check error answer with no IP available
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testSetupField()
    {
        $plugin = new Recaptcha(new Dispatcher(), ['params' => []], $this->getRequestMethod());
        $plugin->setApplication($this->createStub(CMSApplicationInterface::class));
        $result = $plugin->setupField(new CaptchaField(), new SimpleXMLElement('<test/>'));

        $this->assertEmpty($result);
    }

    private function getRequestMethod($data = ['success' => true]) {
        return new class($data) implements RequestMethod {

            private $data = [];

            public function __construct(array $data)
            {
                $this->data = $data;
            }

            public function submit(RequestParameters $params) {
                return json_encode($this->data);
            }
        };
    }
}
