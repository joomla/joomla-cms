<?php
/**
 * @package   Usagestats
 * @copyright Copyright (c)2014-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

class AkeebaUsagestats
{
    /** @var string Unique identifier for the site, created from server variables */
    private $siteId;
    /** @var array Associative array of data being sent */
    private $data = array();
    /** @var string Remote url to upload the stats */
    private $remoteUrl = 'https://abrandnewsite.com/index.php';

    public function setSiteId($siteId)
    {
        $this->siteId = $siteId;
    }

    /**
     * Sets the value of a collected variable. Use NULL as value to unset it
     *
     * @param   string  $key        Variable name
     * @param   string  $value      Variable value
     */
    public function setValue($key, $value)
    {
        if(is_null($value) && isset($this->data[$key]))
        {
            unset($this->data[$key]);
        }
        else
        {
            $this->data[$key] = $value;
        }
    }

    /**
     * Uploads collected data to the remote server
     *
     * @param   bool    $useIframe  Should I create an iframe to upload data or should I use cURL/fopen?
     *
     * @return  string|bool     The HTML code if an iframe is requested or a boolean if we're using cURL/fopen
     */
    public function sendInfo($useIframe = false)
    {
        // No site ID? Well, simply do nothing
        if(!$this->siteId)
        {
            return '';
        }

        // First of all let's add the siteId
        $this->setValue('sid', $this->siteId);

        // Then let's create the url
        $url = array();

        foreach($this->data as $param => $value)
        {
            $url[] .= $param.'='.$value;
        }

        $url = $this->remoteUrl.'?'.implode('&', $url);

        // Should I create an iframe?
        if($useIframe)
        {
            return '<!-- Anonymous usage statistics collection for Akeeba software --><iframe style="display: none" src="'.$url.'"></iframe>';
        }
        else
        {
            // Do we have cURL installed?
            if(function_exists('curl_init'))
            {
                $ch = curl_init($url);

                curl_setopt($ch, CURLOPT_TIMEOUT, 5);

                return curl_exec($ch);
            }
            else
            {
                // Nope, let's try with fopen and cross our fingers
                return @fopen($url, 'r');
            }
        }
    }
}
