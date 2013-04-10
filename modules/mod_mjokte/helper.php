<?php
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class modMJOKTE
{	
	static function createOutput()
	{
		$output = '<a href="http://jokte.org" target="_blank" title="JOKTE! CMS 100% Libre" style="background:url('.JURI::root().'/modules/mod_mjokte/jokte.gif)
		 no-repeat 50% 50%;display:block;width:150px;height:50px;text-decoration:none;margin: 0 auto; padding-top: 10px; padding-left: 0px; font-size: 10px; color: #000"></a>';
	
		return $output;
	}
}