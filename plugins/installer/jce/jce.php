<?php
/**
 *  @copyright Copyright (c)2016 Ryan Demmer
 *  @license GNU General Public License version 2, or later
 */
defined('_JEXEC') or die;

/**
 * Handle commercial extension update authorization.
 *
 * @since       2.6
 */
class plgInstallerJce extends JPlugin
{
    /**
     * Handle adding credentials to package download request.
     *
     * @param string $url     url from which package is going to be downloaded
     * @param array  $headers headers to be sent along the download request (key => value format)
     *
     * @return bool true if credentials have been added to request or not our business, false otherwise (credentials not set by user)
     *
     * @since   3.0
     */
    public function onInstallerBeforePackageDownload(&$url, &$headers)
    {
        $app = JFactory::getApplication();

        $uri = JUri::getInstance($url);
        $host = $uri->getHost();

        if ($host !== 'www.joomlacontenteditor.net') {
            return true;
        }

        // Get the subscription key
        JLoader::import('joomla.application.component.helper');
        $component = JComponentHelper::getComponent('com_jce');

        $key = $component->params->get('updates_key', '');

        if (empty($key) && strpos($url, 'pkg_jce_pro') !== false) {
            $language = JFactory::getLanguage();
            $language->load('plg_installer_jce', JPATH_ADMINISTRATOR);

            $app->enqueueMessage(JText::_('PLG_INSTALLER_JCE_KEY_WARNING'), 'notice');

            return true;
        }

        // Append the subscription key to the download URL
        $uri->setVar('key', $key);
        $url = $uri->toString();

        return true;
    }
}
