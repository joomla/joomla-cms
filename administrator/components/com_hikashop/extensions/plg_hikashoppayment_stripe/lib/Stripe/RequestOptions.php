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

class Stripe_RequestOptions
{
  public $headers;
  public $apiKey;

  public function __construct($key, $headers)
  {
    $this->apiKey = $key;
    $this->headers = $headers;
  }

  public static function parse($options)
  {
    if (is_null($options)) {
      return new Stripe_RequestOptions(null, array());
    }

    if (is_string($options)) {
      return new Stripe_RequestOptions($options, array());
    }

    if (is_array($options)) {
      $headers = array();
      $key = null;
      if (array_key_exists('api_key', $options)) {
        $key = $options['api_key'];
      }
      if (array_key_exists('idempotency_key', $options)) {
        $headers['Idempotency-Key'] = $options['idempotency_key'];
      }
      return new Stripe_RequestOptions($key, $headers);
    }

    throw new Stripe_Error("options must be a string, an array, or null");
  }
}
