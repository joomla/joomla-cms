<?php
defined('_JEXEC') or die;

abstract class ExternalAssets{
	public static function getCoreAssets() {
		 return array(
			'jquery' => array('version' => '2.2.4','dependencies' => ''),
			'jquery-migrate' => array('version' => '1.4.1','dependencies' => 'jquery'),
			'bootstrap' => array('version' => '~4.0.0-alpha.5','dependencies' => 'jquery, tether'),
			'tether' => array('version' => '1.3.7','dependencies' => 'jquery, tether'),
			'font-awesome' => array('version' => '4.6.3','dependencies' => ''),
			'jquery-minicolors' => array('version' => '2.1.10','dependencies' => 'jquery'),
			'jquery-sortable' => array('version' => '0.9.13','dependencies' => 'jquery'),
			'jquery-ui' => array('version' => '1.12.1','dependencies' => 'jquery'),
			'mediaelement' => array('version' => '2.22.0','dependencies' => 'jquery'),
			'punycode' => array('version' => '1.4.1','dependencies' => ''),
			'tinymce' => array('version' => '4.4.3','dependencies' => ''),
			'awesomplete' => array('version' => '1.1.1','dependencies' => ''),
			'dragula' => array('version' => '3.7.2','dependencies' => ''),
			'selectize' => array('version' => '0.12.4','dependencies' => ''),
			'codemirror' => array('version' => '5.19.0','dependencies' => ''),
			'jcrop' => array('version' => '2.0.4','dependencies' => ''),
			'autocomplete' => array('version' => '1.2.26','dependencies' => ''),
			
		);
	}
}
