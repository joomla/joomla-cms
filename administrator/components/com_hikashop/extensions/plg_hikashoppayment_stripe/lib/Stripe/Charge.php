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

class Stripe_Charge extends Stripe_ApiResource
{
  public static function retrieve($id, $options=null)
  {
    $class = get_class();
    return self::_scopedRetrieve($class, $id, $options);
  }

  public static function all($params=null, $options=null)
  {
    $class = get_class();
    return self::_scopedAll($class, $params, $options);
  }

  public static function create($params=null, $options=null)
  {
    $class = get_class();
    return self::_scopedCreate($class, $params, $options);
  }

  public function save($options=null)
  {
    $class = get_class();
    return self::_scopedSave($class, $options);
  }

  public function refund($params=null, $options=null)
  {
    $opts = $this->parseOptions($options);
    $requestor = new Stripe_ApiRequestor($opts->apiKey);
    $url = $this->instanceUrl() . '/refund';
    list($response, $apiKey) = 
      $requestor->request('post', $url, $params, $opts->headers);
    $this->refreshFrom($response, $apiKey);
    return $this;
  }

  public function capture($params=null, $options=null)
  {
    $opts = $this->parseOptions($options);
    $requestor = new Stripe_ApiRequestor($opts->apiKey);
    $url = $this->instanceUrl() . '/capture';
    list($response, $apiKey) = 
      $requestor->request('post', $url, $params, $opts->headers);
    $this->refreshFrom($response, $apiKey);
    return $this;
  }

  public function updateDispute($params=null, $option=null)
  {
    $opts = $this->parseOptions($options);
    $requestor = new Stripe_ApiRequestor($opts->apiKey);
    $url = $this->instanceUrl() . '/dispute';
    list($response, $apiKey) = 
      $requestor->request('post', $url, $params, $headers);
    $this->refreshFrom(array('dispute' => $response), $apiKey, true);
    return $this->dispute;
  }

  public function closeDispute($options=null)
  {
    $opts = $this->parseOptions($options);
    $requestor = new Stripe_ApiRequestor($opts->apiKey);
    $url = $this->instanceUrl() . '/dispute/close';
    list($response, $apiKey) = 
      $requestor->request('post', $url, null, $opts->headers);
    $this->refreshFrom($response, $apiKey);
    return $this;
  }

  public function markAsFraudulent()
  {
    $params = array('fraud_details' => array('user_report' => 'fraudulent'));
    $requestor = new Stripe_ApiRequestor($this->_apiKey);
    $url = $this->instanceUrl();
    list($response, $apiKey) = $requestor->request('post', $url, $params);
    $this->refreshFrom($response, $apiKey);
    return $this;
  }

  public function markAsSafe()
  {
    $params = array('fraud_details' => array('user_report' => 'safe'));
    $requestor = new Stripe_ApiRequestor($this->_apiKey);
    $url = $this->instanceUrl();
    list($response, $apiKey) = $requestor->request('post', $url, $params);
    $this->refreshFrom($response, $apiKey);
    return $this;
  }
}
