<?php

/**
 * @package        Joomla.UnitTest
 *
 * @copyright      (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Mail;

use Joomla\CMS\Mail\Mail;
use Joomla\CMS\Mail\MailerFactory;
use Joomla\Registry\Registry;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\Mail\MailerFactory
 *
 * @package     Joomla.UnitTest
 * @subpackage  Mail
 * @since       4.4.0
 */
class MailerFactoryTest extends UnitTestCase
{
    /**
     * @testdox  The mailer factory creates the correct object
     *
     * @return  void
     *
     * @since   4.4.0
     */
    public function testMailer()
    {
        $factory = new MailerFactory(new Registry());
        $mail    = $factory->createMailer();

        $this->assertNotNull($mail);
        $this->assertInstanceOf(Mail::class, $mail);
    }

    /**
     * @testdox  The mailer factory creates an object with the default settings
     *
     * @return  void
     *
     * @since   4.4.0
     */
    public function testMailerHasDefaultSettings()
    {
        $factory = new MailerFactory(new Registry(['mailfrom' => 'test@example.com']));

        /** @var Mail $mail */
        $mail = $factory->createMailer();

        $this->assertEquals('test@example.com', $mail->From);
    }

    /**
     * @testdox  The mailer factory creates an object with the passed settings
     *
     * @return  void
     *
     * @since   4.4.0
     */
    public function testMailerHasPassedSettings()
    {
        $factory = new MailerFactory(new Registry());

        /** @var Mail $mail */
        $mail = $factory->createMailer(new Registry(['mailfrom' => 'test@example.com']));

        $this->assertEquals('test@example.com', $mail->From);
    }

    /**
     * @testdox  The mailer factory creates an object with an invalid from address
     *
     * @return  void
     *
     * @since   4.4.0
     */
    public function testMailerHasInvalidFromAddress()
    {
        $factory = new MailerFactory(new Registry(['mailfrom' => 'testüumlaut@example.com']));

        /** @var Mail $mail */
        $mail = $factory->createMailer();

        $this->assertEmpty($mail->From);
    }

    /**
     * @testdox  The mailer factory creates an object with the passed settings overriding the default settings
     *
     * @return  void
     *
     * @since   4.4.0
     */
    public function testMailerHasOverriddenSettings()
    {
        $factory = new MailerFactory(new Registry(['mailfrom' => 'default@example.com']));

        /** @var Mail $mail */
        $mail = $factory->createMailer(new Registry(['mailfrom' => 'test@example.com']));

        $this->assertEquals('test@example.com', $mail->From);
    }

    /**
     * @testdox  The mailer factory creates an object with mail type smtp
     *
     * @return  void
     *
     * @since   4.4.0
     */
    public function testMailerIsSMTP()
    {
        $factory = new MailerFactory(new Registry(['mailer' => 'smtp', 'smtphost' => 'localhost']));

        /** @var Mail $mail */
        $mail = $factory->createMailer();

        $this->assertEquals('smtp', $mail->Mailer);
    }
}
