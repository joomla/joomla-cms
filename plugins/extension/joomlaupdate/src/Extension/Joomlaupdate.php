<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Extension.joomlaupdate
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Extension\Joomlaupdate\Extension;

use Joomla\CMS\Event\Model\AfterSaveEvent;
use Joomla\CMS\Event\User\BeforeSaveEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Joomlaupdate\Administrator\Enum\AutoupdateRegisterResultState;
use Joomla\Component\Joomlaupdate\Administrator\Enum\AutoupdateRegisterState;
use Joomla\Component\Joomlaupdate\Administrator\Enum\AutoupdateState;
use Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The extension plugin for com_joomlaupdate
 *
 * @since   5.3.0
 */
final class Joomlaupdate extends CMSPlugin implements SubscriberInterface
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     *
     * @since   5.3.0
     */
    protected $autoloadLanguage = true;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return array
     *
     * @since   5.3.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onExtensionBeforeSave' => 'onExtensionBeforeSave',
            'onExtensionAfterSave'  => 'onExtensionAfterSave',
        ];
    }

    /**
     * Handles subscribe or unsubscribe from automated updates
     *
     * @param BeforeSaveEvent $event
     *
     * @return void
     * @throws \Exception
     *
     * @since 5.4.0
     */
    public function onExtensionBeforeSave(\Joomla\CMS\Event\Model\BeforeSaveEvent $event)
    {
        $context = $event->getArgument('context');
        $table   = $event->getArgument('subject');

        if ($context !== 'com_config.component') {
            return;
        }

        $data = new Registry($table->params);

        $autoupdateStatus         = (int) $data->get('autoupdate');
        $autoupdateRegisterStatus = (int) $data->get('autoupdate_status');

        if ($data->get('updatesource') !== 'default' || $data->get('minimum_stability') !== '4') {
            // If we are not using Joomla Update Server or not using Stable Version disable the autoupdate
            if ($autoupdateRegisterStatus !== AutoupdateRegisterState::Unsubscribed->value) {
                $data->set('autoupdate_status', AutoupdateRegisterState::Unsubscribe->value);
            }
        } elseif ($autoupdateStatus === AutoupdateState::Enabled->value) {
            // If autoupdate is enabled and we are not subscribed force subscription process
            if ($autoupdateRegisterStatus !== AutoupdateRegisterState::Subscribed->value) {
                $data->set('autoupdate_status', AutoupdateRegisterState::Subscribe->value);
            }
        } elseif ($autoupdateRegisterStatus !== AutoupdateRegisterState::Unsubscribed->value) {
            // If autoupdate is disabled and we are not unsubscribed force unsubscription process
            $data->set('autoupdate_status', AutoupdateRegisterState::Unsubscribe->value);
        }

        $table->params = $data->toString();
    }

    /**
     * After update of an extension
     *
     * @param   AfterSaveEvent $event  Event instance.
     *
     * @return  void
     *
     * @since 5.4.0
     */
    public function onExtensionAfterSave(AfterSaveEvent $event): void
    {
        $context = $event->getArgument('context');
        $table   = $event->getArgument('subject');

        if ($context !== 'com_config.component') {
            return;
        }

        if ($table->element !== 'com_joomlaupdate') {
            return;
        }

        /** @var UpdateModel $updateModel */
        $updateModel = $this->getApplication()->bootComponent('com_joomlaupdate')
            ->getMVCFactory()->createModel('Update', 'Administrator', ['ignore_request' => true]);

        if (!$updateModel) {
            return;
        }

        $data = new Registry($table->params);

        // Apply updated config
        $updateModel->applyUpdateSite(
            $data->get('updatesource'),
            $data->get('customurl'),
        );

        // Handle autoupdate register changes
        $autoupdateRegisterStatus = AutoupdateRegisterState::from((int)$data->get('autoupdate_status'));

        // Check if action is required
        if (
            $autoupdateRegisterStatus === AutoupdateRegisterState::Unsubscribed
            || $autoupdateRegisterStatus === AutoupdateRegisterState::Subscribed
        ) {
            return;
        }

        $registerStatus = $updateModel->changeAutoUpdateRegistration($autoupdateRegisterStatus);

        if ($registerStatus !== AutoupdateRegisterResultState::Success) {
            return;
        }

        // Load the messages
        $this->getApplication()->getLanguage()->load('com_joomlaupdate');

        Factory::getApplication()->enqueueMessage(
            Text::_(
                $autoupdateRegisterStatus === AutoupdateRegisterState::Subscribe
                    ? 'COM_JOOMLAUPDATE_AUTOUPDATE_REGISTER_SUCCESS'
                    : 'COM_JOOMLAUPDATE_AUTOUPDATE_UNREGISTER_SUCCESS'
            ),
            'info'
        );
    }
}
