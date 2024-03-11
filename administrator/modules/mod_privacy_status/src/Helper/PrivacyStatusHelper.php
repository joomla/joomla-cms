<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_privacy_status
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\PrivacyStatus\Administrator\Helper;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Privacy\CheckPrivacyPolicyPublishedEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper class for admin privacy status module
 *
 * @since  4.0.0
 */
class PrivacyStatusHelper
{
    /**
     * Get the information about the published privacy policy
     *
     * @return  array  Array containing a status of whether a privacy policy is set and a link to the policy document for editing
     *
     * @since   4.0.0
     */
    public static function getPrivacyPolicyInfo()
    {
        $dispatcher = Factory::getApplication()->getDispatcher();
        $policy     = [
            'published'        => false,
            'articlePublished' => false,
            'editLink'         => '',
        ];

        /*
         * Prior to 3.9.0 it was common for a plugin such as the User - Profile plugin to define a privacy policy or
         * terms of service article, therefore we will also import the user plugin group to process this event.
         */
        PluginHelper::importPlugin('privacy', null, true, $dispatcher);
        PluginHelper::importPlugin('user', null, true, $dispatcher);

        return $dispatcher->dispatch(
            'onPrivacyCheckPrivacyPolicyPublished',
            new CheckPrivacyPolicyPublishedEvent('onPrivacyCheckPrivacyPolicyPublished', [
                'subject' => &$policy, // @todo: Remove reference in Joomla 6, see CheckPrivacyPolicyPublishedEvent::__constructor()
            ])
        )->getArgument('subject', $policy);
    }

    /**
     * Check whether there is a menu item for the request form
     *
     * @return  array  Array containing a status of whether a menu is published for the request form and its current link
     *
     * @since   4.0.0
     */
    public static function getRequestFormPublished()
    {
        $status = [
            'exists'    => false,
            'published' => false,
            'link'      => '',
        ];
        $lang = '';

        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('id'),
                    $db->quoteName('published'),
                    $db->quoteName('language'),
                ]
            )
            ->from($db->quoteName('#__menu'))
            ->where(
                [
                    $db->quoteName('client_id') . ' = 0',
                    $db->quoteName('link') . ' = ' . $db->quote('index.php?option=com_privacy&view=request'),
                ]
            )
            ->setLimit(1);
        $db->setQuery($query);

        $menuItem = $db->loadObject();

        // Check if the menu item exists in database
        if ($menuItem) {
            $status['exists'] = true;

            // Check if the menu item is published
            if ($menuItem->published == 1) {
                $status['published'] = true;
            }

            // Add language to the url if the site is multilingual
            if (Multilanguage::isEnabled() && $menuItem->language && $menuItem->language !== '*') {
                $lang = '&lang=' . $menuItem->language;
            }
        }

        $linkMode = Factory::getApplication()->get('force_ssl', 0) == 2 ? Route::TLS_FORCE : Route::TLS_IGNORE;

        if (!$menuItem) {
            if (Multilanguage::isEnabled()) {
                // Find the Itemid of the home menu item tagged to the site default language
                $params              = ComponentHelper::getParams('com_languages');
                $defaultSiteLanguage = $params->get('site');

                $db    = Factory::getDbo();
                $query = $db->getQuery(true)
                    ->select($db->quoteName('id'))
                    ->from($db->quoteName('#__menu'))
                    ->where(
                        [
                            $db->quoteName('client_id') . ' = 0',
                            $db->quoteName('home') . ' = 1',
                            $db->quoteName('language') . ' = :language',
                        ]
                    )
                    ->bind(':language', $defaultSiteLanguage)
                    ->setLimit(1);
                $db->setQuery($query);

                $homeId = (int) $db->loadResult();
                $itemId = $homeId ? '&Itemid=' . $homeId : '';
            } else {
                $itemId = '';
            }

            $status['link'] = Route::link('site', 'index.php?option=com_privacy&view=request' . $itemId, true, $linkMode);
        } else {
            $status['link'] = Route::link('site', 'index.php?Itemid=' . $menuItem->id . $lang, true, $linkMode);
        }

        return $status;
    }

    /**
     * Method to return number privacy requests older than X days.
     *
     * @return  integer
     *
     * @since   4.0.0
     */
    public static function getNumberUrgentRequests()
    {
        // Load the parameters.
        $params = ComponentHelper::getComponent('com_privacy')->getParams();
        $notify = (int) $params->get('notify', 14);
        $now    = Factory::getDate()->toSql();
        $period = '-' . $notify;

        $db    = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('COUNT(*)')
            ->from($db->quoteName('#__privacy_requests'))
            ->where(
                [
                    $db->quoteName('status') . ' = 1',
                    $query->dateAdd($db->quote($now), $period, 'DAY') . ' > ' . $db->quoteName('requested_at'),
                ]
            );
        $db->setQuery($query);

        return (int) $db->loadResult();
    }
}
