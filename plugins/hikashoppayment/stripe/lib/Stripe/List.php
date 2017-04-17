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

class Stripe_List extends Stripe_Object
{
  public function all($params=null)
  {
    list($url, $params) = $this->extractPathAndUpdateParams($params);

    $requestor = new Stripe_ApiRequestor($this->_apiKey);
    list($response, $apiKey) = $requestor->request('get', $url, $params);
    return Stripe_Util::convertToStripeObject($response, $apiKey);
  }

  public function create($params=null)
  {
    list($url, $params) = $this->extractPathAndUpdateParams($params);

    $requestor = new Stripe_ApiRequestor($this->_apiKey);
    list($response, $apiKey) = $requestor->request('post', $url, $params);
    return Stripe_Util::convertToStripeObject($response, $apiKey);
  }

  public function retrieve($id, $params=null)
  {
    list($url, $params) = $this->extractPathAndUpdateParams($params);

    $requestor = new Stripe_ApiRequestor($this->_apiKey);
    $id = Stripe_ApiRequestor::utf8($id);
    $extn = urlencode($id);
    list($response, $apiKey) = $requestor->request(
        'get', "$url/$extn", $params
    );
    return Stripe_Util::convertToStripeObject($response, $apiKey);
  }

  private function extractPathAndUpdateParams($params)
  {
    $url = parse_url($this->url);
    if (!isset($url['path'])) {
      throw new Stripe_APIError("Could not parse list url into parts: $url");
    }

    if (isset($url['query'])) {
      $query = array();
      parse_str($url['query'], $query);
      $params = array_merge($params ? $params : array(), $query);
    }

    return array($url['path'], $params);
  }
}
