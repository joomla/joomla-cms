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
use PHPUnit\Framework\Attributes\TestDox;

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
     * @return  void
     *
     * @since   4.4.0
     */
    #[TestDox('The mailer factory creates the correct object')]
    public function testMailer()
    {
        $factory = new MailerFactory(new Registry());
        $mail    = $factory->createMailer();

        $this->assertNotNull($mail);
        $this->assertInstanceOf(Mail::class, $mail);
    }

    /**
     * @return  void
     *
     * @since   4.4.0
     */
    #[TestDox('The mailer factory creates an object with the default settings')]
    public function testMailerHasDefaultSettings()
    {
        $factory = new MailerFactory(new Registry(['mailfrom' => 'test@example.com']));

        /** @var Mail $mail */
        $mail = $factory->createMailer();

        $this->assertEquals('test@example.com', $mail->From);
    }

    /**
     * @return  void
     *
     * @since   4.4.0
     */
    #[TestDox('The mailer factory creates an object with the passed settings')]
    public function testMailerHasPassedSettings()
    {
        $factory = new MailerFactory(new Registry());

        /** @var Mail $mail */
        $mail = $factory->createMailer(new Registry(['mailfrom' => 'test@example.com']));

        $this->assertEquals('test@example.com', $mail->From);
    }

    /**
     * @return  void
     *
     * @since   4.4.0
     */
    #[TestDox('The mailer factory creates an object with an invalid from address')]
    public function testMailerHasInvalidFromAddress()
    {
        $factory = new MailerFactory(new Registry(['mailfrom' => 'testüumlaut@example.com']));

        /** @var Mail $mail */
        $mail = $factory->createMailer();

        $this->assertEmpty($mail->From);
    }

    /**
     * @return  void
     *
     * @since   4.4.0
     */
    #[TestDox('The mailer factory creates an object with the passed settings overriding the default settings')]
    public function testMailerHasOverriddenSettings()
    {
        $factory = new MailerFactory(new Registry(['mailfrom' => 'default@example.com']));

        /** @var Mail $mail */
        $mail = $factory->createMailer(new Registry(['mailfrom' => 'test@example.com']));

        $this->assertEquals('test@example.com', $mail->From);
    }

    /**
     * @return  void
     *
     * @since   4.4.0
     */
    #[TestDox('The mailer factory creates an object with mail type smtp')]
    public function testMailerIsSMTP()
    {
        $factory = new MailerFactory(new Registry(['mailer' => 'smtp', 'smtphost' => 'localhost']));

        /** @var Mail $mail */
        $mail = $factory->createMailer();

        $this->assertEquals('smtp', $mail->Mailer);
    }
}
