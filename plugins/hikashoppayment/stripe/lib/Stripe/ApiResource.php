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

abstract class Stripe_ApiResource extends Stripe_Object
{
  public static function baseUrl()
  {
    return Stripe::$apiBase;
  }

  protected static function _scopedRetrieve($class, $id, $options=null)
  {
    $opts = Stripe_RequestOptions::parse($options);
    $instance = new $class($id, $opts->apiKey);
    $instance->refresh();
    return $instance;
  }

  public function refresh()
  {
    $requestor = new Stripe_ApiRequestor($this->_apiKey, self::baseUrl());
    $url = $this->instanceUrl();

    list($response, $apiKey) = $requestor->request(
        'get',
        $url,
        $this->_retrieveOptions
    );
    $this->refreshFrom($response, $apiKey);
    return $this;
  }

  public function parseOptions($options)
  {
    $opts = Stripe_RequestOptions::parse($options);
    $key = ($opts->apiKey ? $opts->apiKey : $this->_apiKey);
    $opts->apiKey = $key;
    return $opts;
  }

  public static function className($class)
  {
    if ($postfixNamespaces = strrchr($class, '\\')) {
      $class = substr($postfixNamespaces, 1);
    }
    if ($postfixFakeNamespaces = strrchr($class, 'Stripe_')) {
      $class = $postfixFakeNamespaces;
    }
    if (substr($class, 0, strlen('Stripe')) == 'Stripe') {
      $class = substr($class, strlen('Stripe'));
    }
    $class = str_replace('_', '', $class);
    $name = urlencode($class);
    $name = strtolower($name);
    return $name;
  }

  public static function classUrl($class)
  {
    $base = self::_scopedLsb($class, 'className', $class);
    return "/v1/${base}s";
  }

  public function instanceUrl()
  {
    $id = $this['id'];
    $class = get_class($this);
    if ($id === null) {
      $message = "Could not determine which URL to request: "
               . "$class instance has invalid ID: $id";
      throw new Stripe_InvalidRequestError($message, null);
    }
    $id = Stripe_ApiRequestor::utf8($id);
    $base = $this->_lsb('classUrl', $class);
    $extn = urlencode($id);
    return "$base/$extn";
  }

  private static function _validateCall($method, $params=null, $options=null)
  {
    if ($params && !is_array($params)) {
      $message = "You must pass an array as the first argument to Stripe API "
               . "method calls.  (HINT: an example call to create a charge "
               . "would be: \"StripeCharge::create(array('amount' => 100, "
               . "'currency' => 'usd', 'card' => array('number' => "
               . "4242424242424242, 'exp_month' => 5, 'exp_year' => 2015)))\")";
      throw new Stripe_Error($message);
    }

    if ($options && (!is_string($options) && !is_array($options))) {
      $message = 'The second argument to Stripe API method calls is an '
               . 'optional per-request apiKey, which must be a string, or '
               . 'per-request options, which must be an array. '
               . '(HINT: you can set a global apiKey by '
               . '"Stripe::setApiKey(<apiKey>)")';
      throw new Stripe_Error($message);
    }
  }

  protected static function _scopedAll($class, $params=null, $options=null)
  {
    self::_validateCall('all', $params, $options);
    $base = self::_scopedLsb($class, 'baseUrl');
    $url = self::_scopedLsb($class, 'classUrl', $class);
    $opts = Stripe_RequestOptions::parse($options);
    $requestor = new Stripe_ApiRequestor($opts->apiKey, $base);
    list($response, $apiKey) = 
      $requestor->request('get', $url, $params, $opts->headers);
    return Stripe_Util::convertToStripeObject($response, $apiKey);
  }

  protected static function _scopedCreate($class, $params=null, $options=null)
  {
    self::_validateCall('create', $params, $options);
    $opts = Stripe_RequestOptions::parse($options);
    $base = self::_scopedLsb($class, 'baseUrl');
    $requestor = new Stripe_ApiRequestor($opts->apiKey, $base);
    $url = self::_scopedLsb($class, 'classUrl', $class);
    list($response, $apiKey) = 
      $requestor->request('post', $url, $params, $opts->headers);
    return Stripe_Util::convertToStripeObject($response, $apiKey);
  }

  protected function _scopedSave($class, $options=null)
  {
    self::_validateCall('save', null, $options);
    $opts = Stripe_RequestOptions::parse($options);
    $key = ($opts->apiKey ? $opts->apiKey : $this->_apiKey);
    $requestor = new Stripe_ApiRequestor($key, self::baseUrl());
    $params = $this->serializeParameters();

    if (count($params) > 0) {
      $url = $this->instanceUrl();
      list($response, $apiKey) = $requestor->request('post', $url, $params);
      $this->refreshFrom($response, $apiKey);
    }
    return $this;
  }

  protected function _scopedDelete($class, $params=null, $options=null)
  {
    self::_validateCall('delete', $params, $options);
    $opts = Stripe_RequestOptions::parse($options);
    $key = ($opts->apiKey ? $opts->apiKey : $this->_apiKey);
    $requestor = new Stripe_ApiRequestor($key, self::baseUrl());
    $url = $this->instanceUrl();
    list($response, $apiKey) = $requestor->request('delete', $url, $params);
    $this->refreshFrom($response, $apiKey);
    return $this;
  }
}
