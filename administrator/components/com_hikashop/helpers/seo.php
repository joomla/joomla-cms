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
class hikashopSeoHelper {
	public function autoFillKeywordMeta(&$element, $object) {
		$config =& hikashop_config();
		$max = $config->get('max_size_of_metadescription','');
		$description = $object."_description";
		$meta_description = $object."_meta_description";
		$keyword = $object."_keywords";
		if(!empty($element->$description)){
			if(empty($element->$meta_description)){
				if(!empty($max)){
					if((int)$max > 254) $max = 254;
					else if ((int)$max < 1) $max = 1;
				}
				else $max=254;

				$meta = substr($this->clean($element->$description),0,$max);
				$element->$meta_description = $meta;
			}
			if(empty($element->$keyword)){
				$txt=$this->clean($element->$description);
				$words = array();
				if(preg_match_all('~\p{L}+~',$txt,$matches) > 0){
					foreach ($matches[0] as $w){
						$words[$w] = isset($words[$w]) === false ? 1 : $words[$w] + 1;
					}
				}
				arsort($words);
				$i=0;
				$max_keywords = $config->get('keywords_number','');
				$excluded_words = $config->get('keywords_exclusion_list',array());
				$excluded_words = explode(',',$excluded_words);
				$keywords = array();
				foreach($words as $word => $nb){
					if(strlen($word)<3){
						continue;
					}
					$skip=false;
					foreach($excluded_words as $excluded_word){
						if($word == trim($excluded_word)){
							$skip=true; break;
						}
					}
					if($skip==true) continue;
					$i++;
					if($i > $max_keywords){
						break;
					}
					$keywords[$i]=$word;
				}
				$element->$keyword = implode($keywords,',');
			}
		}
	}

	function clean($str) {
		$str = strip_tags($str);
		if(function_exists('mb_strtolower'))
			$str = mb_strtolower($str, 'utf-8');
		else
			$str = strtolower($str);
		return $str;
	}
}
