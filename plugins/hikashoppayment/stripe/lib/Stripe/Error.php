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

class Stripe_Error extends Exception
{
  public function __construct($message, $httpStatus=null,
      $httpBody=null, $jsonBody=null
  )
  {
    parent::__construct($message);
    $this->httpStatus = $httpStatus;
    $this->httpBody = $httpBody;
    $this->jsonBody = $jsonBody;
  }

  public function getHttpStatus()
  {
    return $this->httpStatus;
  }

  public function getHttpBody()
  {
    return $this->httpBody;
  }

  public function getJsonBody()
  {
    return $this->jsonBody;
  }
}
