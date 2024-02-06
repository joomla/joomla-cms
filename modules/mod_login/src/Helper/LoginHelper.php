<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Login\Site\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Uri\Uri;

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
     * @param   \Joomla\Registry\Registry  $params  module parameters
     * @param   string                     $type    return type
     *
     * @return  string
     */
    public static function getReturnUrl($params, $type)
    {
        $item = Factory::getApplication()->getMenu()->getItem($params->get($type));

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
     * @return string
     */
    public static function getType()
    {
        $user = Factory::getUser();

        return (!$user->get('guest')) ? 'logout' : 'login';
    }

    /**
     * Retrieve the URL for the registration page
     *
     * @param   \Joomla\Registry\Registry  $params  module parameters
     *
     * @return  string
     */
    public static function getRegistrationUrl($params)
    {
        $regLink       = 'index.php?option=com_users&view=registration';
        $regLinkMenuId = $params->get('customRegLinkMenu');

        // If there is a custom menu item set for registration => override default
        if ($regLinkMenuId) {
            $item = Factory::getApplication()->getMenu()->getItem($regLinkMenuId);

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
