<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php

abstract class Stripe
{
  public static $apiKey;
  public static $apiBase = 'https://api.stripe.com';
  public static $apiUploadBase = 'https://uploads.stripe.com';
  public static $apiVersion = null;
  public static $verifySslCerts = true;
  const VERSION = '1.18.0';

  public static function getApiKey()
  {
    return self::$apiKey;
  }

  public static function setApiKey($apiKey)
  {
    self::$apiKey = $apiKey;
  }

  public static function getApiVersion()
  {
    return self::$apiVersion;
  }

  public static function setApiVersion($apiVersion)
  {
    self::$apiVersion = $apiVersion;
  }

  public static function getVerifySslCerts()
  {
    return self::$verifySslCerts;
  }

  public static function setVerifySslCerts($verify)
  {
    self::$verifySslCerts = $verify;
  }
}
