<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Extension
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Plugin\Content\ConfirmConsent\Extension;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Event\Model\PrepareFormEvent;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Language;
use Joomla\CMS\User\User;
use Joomla\Event\Dispatcher;
use Joomla\Plugin\Content\ConfirmConsent\Extension\ConfirmConsent;
use Joomla\Tests\Unit\UnitTestCase;
use PHPUnit\Framework\Attributes\TestDox;

/**
 * Test class for ConfirmConsent plugin
 *
 * @package     Joomla.UnitTest
 * @subpackage  ConfirmConsent
 *
 * @since       4.3.0
 */
#[TestDox('The ConfirmConsent plugin')]
class ConfirmConsentTest extends UnitTestCase
{
    /**
     * @return  void
     *
     * @since   4.3.0
     */
    #[TestDox('that the consent field is loaded into the form')]
    public function testLoadConsentFieldInForm()
    {
        $form = new Form('com_contact.contact');
        $form->setCurrentUser(new User());

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('getLanguage')->willReturn($this->createStub(Language::class));

        $dispatcher = new Dispatcher();
        $plugin     = new ConfirmConsent($dispatcher, ['params' => []]);
        $plugin->setApplication($app);
        $plugin->onContentPrepareForm(new PrepareFormEvent('onContentPrepareForm', [
            'subject' => $form,
            'data'    => [],
        ]));

        $this->assertNotFalse($form->getField('consentbox'));
    }

    /**
     * @return  void
     *
     * @since   4.3.0
     */
    #[TestDox('that the consent field is not loaded into the form when wrong form name')]
    public function testLoadConsentFieldInFormWrongContext()
    {
        $form = new Form('invalid');
        $form->load('<form/>');

        $dispatcher = new Dispatcher();
        $plugin     = new ConfirmConsent($dispatcher, ['params' => []]);
        $plugin->setApplication($this->createStub(CMSApplicationInterface::class));
        $plugin->onContentPrepareForm(new PrepareFormEvent('onContentPrepareForm', [
            'subject' => $form,
            'data'    => [],
        ]));

        $this->assertFalse($form->getField('consentbox'));
    }

    /**
     * @return  void
     *
     * @since   4.3.0
     */
    #[TestDox('that the consent field is not loaded into the form when wrong application')]
    public function testLoadConsentFieldInFormWrongApplication()
    {
        $form = new Form('com_contact.contact');
        $form->load('<form/>');

        $app = $this->createStub(CMSApplicationInterface::class);
        $app->method('isClient')->willReturn(true);

        $dispatcher = new Dispatcher();
        $plugin     = new ConfirmConsent($dispatcher, ['params' => []]);
        $plugin->setApplication($app);
        $plugin->onContentPrepareForm(new PrepareFormEvent('onContentPrepareForm', [
            'subject' => $form,
            'data'    => [],
        ]));

        $this->assertFalse($form->getField('consentbox'));
    }
}
