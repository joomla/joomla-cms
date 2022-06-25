<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Quickicon.privacycheck
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt

 * @phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

/**
 * Plugin to check privacy requests older than 14 days
 *
 * @since  3.9.0
 */
class PlgQuickiconPrivacyCheck extends CMSPlugin
{
    /**
     * Load plugin language files automatically
     *
     * @var    boolean
     * @since  3.9.0
     */
    protected $autoloadLanguage = true;

    /**
     * Application object.
     *
     * @var    \Joomla\CMS\Application\CMSApplication
     * @since  4.0.0
     */
    protected $app;

    /**
     * Check privacy requests older than 14 days.
     *
     * @param   string  $context  The calling context
     *
     * @return  array   A list of icon definition associative arrays
     *
     * @since   3.9.0
     */
    public function onGetIcons($context)
    {
        if (
            $context !== $this->params->get('context', 'update_quickicon')
            || !$this->app->getIdentity()->authorise('core.admin', 'com_privacy')
            || !ComponentHelper::isEnabled('com_privacy')
        ) {
            return array();
        }

        $token    = Session::getFormToken() . '=' . 1;
        $privacy  = 'index.php?option=com_privacy';

        $options  = array(
            'plg_quickicon_privacycheck_url'      => Uri::base() . $privacy . '&view=requests&filter[status]=1&list[fullordering]=a.requested_at ASC',
            'plg_quickicon_privacycheck_ajax_url' => Uri::base() . $privacy . '&task=getNumberUrgentRequests&format=json&' . $token,
            'plg_quickicon_privacycheck_text'     => array(
                "NOREQUEST"            => Text::_('PLG_QUICKICON_PRIVACYCHECK_NOREQUEST'),
                "REQUESTFOUND"         => Text::_('PLG_QUICKICON_PRIVACYCHECK_REQUESTFOUND'),
                "ERROR"                => Text::_('PLG_QUICKICON_PRIVACYCHECK_ERROR'),
                "REQUESTFOUND_MESSAGE" => Text::_('PLG_QUICKICON_PRIVACYCHECK_REQUESTFOUND_MESSAGE'),
                "REQUESTFOUND_BUTTON"  => Text::_('PLG_QUICKICON_PRIVACYCHECK_REQUESTFOUND_BUTTON'),
            ),
        );

        $this->app->getDocument()->addScriptOptions('js-privacy-check', $options);

        $this->app->getDocument()->getWebAssetManager()
            ->registerAndUseScript('plg_quickicon_privacycheck', 'plg_quickicon_privacycheck/privacycheck.js', [], ['defer' => true], ['core']);

        return array(
            array(
                'link'  => $privacy . '&view=requests&filter[status]=1&list[fullordering]=a.requested_at ASC',
                'image' => 'icon-users',
                'icon'  => '',
                'text'  => Text::_('PLG_QUICKICON_PRIVACYCHECK_CHECKING'),
                'id'    => 'plg_quickicon_privacycheck',
                'group' => 'MOD_QUICKICON_USERS',
            ),
        );
    }
}
