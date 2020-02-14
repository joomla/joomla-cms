<?php
/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2016 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

class SppagebuilderHelperLanguages {

	public static function language_list() {

		$language_api = 'http://sppagebuilder.com/api/languages/languages.json';

		if( ini_get('allow_url_fopen') ) {
			$components = json_decode(file_get_contents($language_api));
		} elseif(extension_loaded('curl')) {
			$components = json_decode(self::getCurlData($language_api));
		} else {
			$report['message'] = JText::_('Please enable \'cURL\' or url_fopen in PHP or Contact with your Server or Hosting administrator.');
			die(json_encode($report));
		}

		$languages = new stdClass;
		foreach ($components as $key => $component) {
				$languages->$key = $component;
		}

		return $languages;
	}

	private static function getCurlData($url) {
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	    $data = curl_exec($ch);
	    curl_close($ch);
	    return $data;
	}

}
