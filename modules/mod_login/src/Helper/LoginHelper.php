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
        // Stay on the same page
        $url = Uri::getInstance()->toString();

        $returnMenuId = $params->get($type, 0);

        if ($returnMenuId > 0) {
            $item = $app->getMenu()->getItem($returnMenuId);

            if ($item) {
                $lang = '';

                if ($item->language !== '*' && Multilanguage::isEnabled()) {
                    $lang = '&lang=' . $item->language;
                }

                $url = 'index.php?Itemid=' . $item->id . $lang;
            }
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
}
