<?php
/**
 * @package     corejoomla.site
 * @subpackage  plg_cjupdater
 *
 * @copyright   Copyright (C) 2009 - 2015 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class PlgInstallerCjupdater extends JPlugin
{

    private $baseUrl = 'www.corejoomla.com';

    /**
     *
     * @var String your extension identifier, to retrieve its params
     */
    private $extension = 'com_cjlib';

    /**
     * Handle adding credentials to package download request
     *
     * @param string $url
     *            from which package is going to be downloaded
     * @param array $headers
     *            to be sent along the download request (key => value format)
     *            
     * @return boolean if credentials have been added to request or not our business, false otherwise (credentials not set by user)
     */
    public function onInstallerBeforePackageDownload (&$url, &$headers)
    {
        // are we trying to update our extension?
        if (strpos($url, $this->baseUrl) === false)
        {
            return true;
        }
        
        // fetch download id from extension parameters, or
        // wherever you want to store them
        // Get the component information from the #__extensions table
        JLoader::import('joomla.application.component.helper');
        $component = JComponentHelper::getComponent($this->extension);
        
        $downloadId = $component->params->get('update_credentials_download_id', '');
        
        // bind credentials to request by appending it to the download url
        if (! empty($downloadId) && !stripos($url, '.zip'))
        {
            $separator = strpos($url, '?') !== false ? '&' : '?';
            $url .= $separator . 'dlid=' . $downloadId;
        }
//         var_dump($url);
//         jexit();
        
        return true;
    }
}
