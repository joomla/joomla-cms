<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Base
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Mail;

use Joomla\CMS\Mail\MailerFactoryAwareTrait;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\Mail\MailerInterface;
use Joomla\Registry\Registry;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for \Joomla\CMS\Mail\MailerFactoryAwareTrait
 *
 * @package     Joomla.UnitTest
 * @subpackage  Mail
 * @since       4.4.0
 */
class MailerFactoryAwareTraitTest extends UnitTestCase
{
    /**
     * @testdox  The mailer factory can be set and accessed by the trait
     *
     * @return  void
     *
     * @since   4.4.0
     */
    public function testGetSetMailerFactory()
    {
        $mailerFactory = new class () implements MailerFactoryInterface {
            public function createMailer(?Registry $configuration = null): MailerInterface
            {
                return new class () implements MailerInterface {
                    public function send()
                    {
                    }

                    public function setSender(string $fromEmail, string $name = '')
                    {
                    }

                    public function setSubject(string $subject)
                    {
                    }

                    public function setBody(string $content)
                    {
                    }

                    public function addRecipient(string $recipientEmail, string $name = '')
                    {
                    }

                    public function addCc(string $ccEmail, string $name = '')
                    {
                    }

                    public function addBcc(string $bccEmail, string $name = '')
                    {
                    }

                    public function addAttachment(string $data, $name = '', $encoding = 'base64', $type = 'application/octet-stream')
                    {
                    }

                    public function addReplyTo(string $replyToEmail, string $name = '')
                    {
                    }
                };
            }
        };

        $trait = new class () {
            use MailerFactoryAwareTrait;

            public function getFactory(): MailerFactoryInterface
            {
                return $this->getMailerFactory();
            }
        };

        $trait->setMailerFactory($mailerFactory);

        $this->assertEquals($mailerFactory, $trait->getFactory());
    }

    /**
     * @testdox  The mailer factory can be set and accessed by the trait
     *
     * @return  void
     *
     * @since   4.4.0
     */
    public function testGetMailerFactoryThrowsException()
    {
        $this->expectException(\UnexpectedValueException::class);

        $trait = new class () {
            use MailerFactoryAwareTrait;

            public function getFactory(): MailerFactoryInterface
            {
                return $this->getMailerFactory();
            }
        };

        $trait->getFactory();
    }
}
