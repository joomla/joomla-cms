<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  User.contactcreator
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\User\ContactCreator\Extension;

use Joomla\CMS\Event\User\AfterSaveEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Contact\Administrator\Table\ContactTable;
use Joomla\Event\SubscriberInterface;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class for Contact Creator
 *
 * A tool to automatically create and synchronise contacts with a user
 *
 * @since  1.6
 */
final class ContactCreator extends CMSPlugin implements SubscriberInterface
{
    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return array
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onUserAfterSave' => 'onUserAfterSave',
        ];
    }

    /**
     * Utility method to act on a user after it has been saved.
     *
     * This method creates a contact for the saved user
     *
     * @param   AfterSaveEvent $event  The event instance.
     *
     * @return  void
     *
     * @since   1.6
     */
    public function onUserAfterSave(AfterSaveEvent $event): void
    {
        $user    = $event->getUser();
        $isnew   = $event->getIsNew();
        $success = $event->getSavingResult();

        // If the user wasn't stored we don't resync
        if (!$success) {
            return;
        }

        // If the user isn't new we don't sync
        if (!$isnew) {
            return;
        }

        // Ensure the user id is really an int
        $user_id = (int) $user['id'];

        // If the user id appears invalid then bail out just in case
        if (empty($user_id)) {
            return;
        }

        // Load plugin language files
        $this->loadLanguage();

        $categoryId = $this->params->get('category', 0);

        if (empty($categoryId)) {
            $this->getApplication()->enqueueMessage($this->getApplication()->getLanguage()->_('PLG_CONTACTCREATOR_ERR_NO_CATEGORY'), 'error');

            return;
        }

        if ($contact = $this->getContactTable()) {
            /**
             * Try to pre-load a contact for this user. Apparently only possible if other plugin creates it
             * Note: $user_id is cleaned above
             */
            if (!$contact->load(['user_id' => (int) $user_id])) {
                $contact->published = $this->params->get('autopublish', 0);
            }

            $contact->name     = $user['name'];
            $contact->user_id  = $user_id;
            $contact->email_to = $user['email'];
            $contact->catid    = $categoryId;
            $contact->access   = (int) $this->getApplication()->get('access');
            $contact->language = '*';
            $contact->generateAlias();

            // Check if the contact already exists to generate new name & alias if required
            if ($contact->id == 0) {
                list($name, $alias) = $this->generateAliasAndName($contact->alias, $contact->name, $categoryId);

                $contact->name  = $name;
                $contact->alias = $alias;
            }

            $autowebpage = $this->params->get('autowebpage', '');

            if (!empty($autowebpage)) {
                // Search terms
                $search_array = ['[name]', '[username]', '[userid]', '[email]'];

                // Replacement terms, urlencoded
                $replace_array = array_map('urlencode', [$user['name'], $user['username'], $user['id'], $user['email']]);

                // Now replace it in together
                $contact->webpage = str_replace($search_array, $replace_array, $autowebpage);
            }

            if ($contact->check() && $contact->store()) {
                return;
            }
        }

        $this->getApplication()->enqueueMessage($this->getApplication()->getLanguage()->_('PLG_CONTACTCREATOR_ERR_FAILED_CREATING_CONTACT'), 'error');
    }

    /**
     * Method to change the name & alias if alias is already in use
     *
     * @param   string   $alias       The alias.
     * @param   string   $name        The name.
     * @param   integer  $categoryId  Category identifier
     *
     * @return  array  Contains the modified title and alias.
     *
     * @since   3.2.3
     */
    private function generateAliasAndName($alias, $name, $categoryId)
    {
        $table = $this->getContactTable();

        while ($table->load(['alias' => $alias, 'catid' => $categoryId])) {
            if ($name === $table->name) {
                $name = StringHelper::increment($name);
            }

            $alias = StringHelper::increment($alias, 'dash');
        }

        return [$name, $alias];
    }

    /**
     * Get an instance of the contact table
     *
     * @return  ContactTable|null
     *
     * @since   3.2.3
     */
    private function getContactTable()
    {
        return $this->getApplication()->bootComponent('com_contact')->getMVCFactory()->createTable('Contact', 'Administrator');
    }
}
