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

class Env_User extends Env_WebService {

  public $userConfiguration = array('emails' => array());

  public function getEmailConfiguration() {
    $this->setOptions(array('action' => '/api/v1/emails_configuration'));
    $this->setEmailConfiguration();
  }

  public function postEmailConfiguration($params) {
    $this->setOptions(array('action' => '/api/v1/emails_configuration'));
    $this->param = $params;
    $this->setPost();
    $this->setEmailConfiguration();
  }

  private function setEmailConfiguration() {
    $source = parent::doRequest();
    if($source !== false) {
      parent::parseResponse($source);
      foreach($this->xpath->evaluate('/user/mails')->item(0)->childNodes as $configLine) {
        if(!($configLine instanceof DOMText)) {
          $this->userConfiguration['emails'][$configLine->nodeName] = $configLine->nodeValue;
        }
      }
    }
  }

}
