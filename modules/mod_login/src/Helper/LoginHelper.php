<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Login\Site\Helper;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\User;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_login
 *
 * @since  1.5
 */
class LoginHelper
{
    /**
     * Retrieve the URL where the user should be returned after logging in
     *
     * @param   Registry  $params  module parameters
     * @param   string    $type    return type
     * @param   CMSApplicationInterface  $app  The application
     *
     * @since   5.4.0
     *
     * @return  string
     */
    public function getReturnUrlString(Registry $params, $type, CMSApplicationInterface $app): string
    {
        $item = $app->getMenu()->getItem($params->get($type));

        // Stay on the same page
        $url = Uri::getInstance()->toString();

        if ($item) {
            $lang = '';

            if ($item->language !== '*' && Multilanguage::isEnabled()) {
                $lang = '&lang=' . $item->language;
            }

            $url = 'index.php?Itemid=' . $item->id . $lang;
        }

        return base64_encode($url);
    }

    /**
     * Returns the current users type
     *
     * @param   User  $user  The user object
     *
     * @return string
     *
     * @since 5.4.0
     */
    public function getUserType(User $user): string
    {
        return (!$user->guest) ? 'logout' : 'login';
    }

    /**
     * Retrieve the URL for the registration page
     *
     * @param   Registry  $params  module parameters
     *
     * @since   5.4.0
     *
     * @return  string
     */
    public function getRegistrationUrlString(Registry $params, CMSApplicationInterface $app): string
    {
        $regLink       = 'index.php?option=com_users&view=registration';
        $regLinkMenuId = $params->get('customRegLinkMenu');

        // If there is a custom menu item set for registration => override default
        if ($regLinkMenuId) {
            $item = $app->getMenu()->getItem($regLinkMenuId);

            if ($item) {
                $regLink = 'index.php?Itemid=' . $regLinkMenuId;

                if ($item->language !== '*' && Multilanguage::isEnabled()) {
                    $regLink .= '&lang=' . $item->language;
                }
            }
        }

        return $regLink;
    }

    /**
     * Retrieve the URL where the user should be returned after logging in
     *
     * @param   Registry  $params  module parameters
     * @param   string    $type    return type
     *
     * @return  string
     *
     * @deprecated 5.4.0 will be removed in 7.0
     *             Use the non-static method getReturnUrlString
     *             Example: Factory::getApplication()->bootModule('mod_login', 'site')
     *                          ->getHelper('LoginHelper')
     *                          ->getReturnUrlString($params, $type, Factory::getApplication())
     */
    public static function getReturnUrl($params, $type)
    {
        return (new self())->getReturnUrlString($params, $type, Factory::getApplication());
    }

    /**
     * Returns the current users type
     *
     * @return     string
     *
     * @deprecated 5.4.0 will be removed in 7.0
     *             Use the non-static method getUserType
     *             Example: Factory::getApplication()->bootModule('mod_login', 'site')
     *                          ->getHelper('LoginHelper')
     *                          ->getUserType(Factory::getApplication())
     */
    public static function getType()
    {
        $user = Factory::getApplication()->getIdentity();

        return (new self())->getUserType($user);
    }

    /**
     * Retrieve the URL for the registration page
     *
     * @param      Registry  $params  module parameters
     *
     * @return     string
     *
     * @deprecated 5.4.0 will be removed in 7.0
     *             Use the non-static method getRegistrationUrlString
     *             Example: Factory::getApplication()->bootModule('mod_login', 'site')
     *                          ->getHelper('LoginHelper')
     *                          ->getRegistrationUrlString($params, Factory::getApplication())
     */
    public static function getRegistrationUrl($params)
    {
        return (new self())->getRegistrationUrlString($params, Factory::getApplication());
    }
}
