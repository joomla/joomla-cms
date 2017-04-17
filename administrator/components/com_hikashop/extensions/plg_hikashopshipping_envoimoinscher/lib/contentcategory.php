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

class Env_ContentCategory extends Env_WebService {

  public $categories = array();

  public $contents = array();

  public function getCategories() { 
    $this->setOptions(array("action" => "/api/v1/content_categories",
		));
    $this->doCatRequest();
  }

  public function getContents() { 
    $this->setOptions(array("action" => "/api/v1/contents",
		));
    $this->doConRequest();
  }

  private function doCatRequest() {
    $source = parent::doRequest();




    if($source !== false) {
      parent::parseResponse($source);
	  	if(count($this->respErrorsList) == 0) {


				$categories = $this->xpath->query("/content_categories/content_category");
				foreach($categories as $c => $category) {
					$code = $this->xpath->query("./code",$category)->item(0)->nodeValue;
					$this->categories[$code] = array(
						'label' => $this->xpath->evaluate("./label",$category)->item(0)->nodeValue,
						'code' => $code
						);
				}
			}
    }
  }

  private function doConRequest() {
    $source = parent::doRequest();




    if($source !== false) {
      parent::parseResponse($source);
	  	if(count($this->respErrorsList) == 0) {


				$contents = $this->xpath->query("/contents/content");
				foreach($contents as $c => $content) {
					$categoryId = $this->xpath->query('./category',$content)->item(0)->nodeValue;
					$i = count($this->contents[$categoryId]);
					$this->contents[$categoryId][$i] = array(
						'code' => $this->xpath->query('./code',$content)->item(0)->nodeValue,
						'label' => $this->xpath->query('./label',$content)->item(0)->nodeValue,
						'category' => $categoryId
						);
				}
			}
    }
  }

  public function getChild($code) {
    return $this->contents[$code];
  }

}
