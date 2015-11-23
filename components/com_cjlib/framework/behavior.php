<?php
/**
 * @version		$Id: behavior.php 01 2013-12-21 11:37:09Z maverick $
 * @package		CoreJoomla.CJLib
 * @subpackage	Components.framework
 * @copyright	Copyright (C) 2009 - 2013 corejoomla.com. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */

// no direct access
defined('_JEXEC') or die;

class CjLibBehavior 
{	
	public static function jquery($custom_tag = true)
	{
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		
		// Load jQuery if it is not already loaded
		if(!isset($app->jquery))
		{
			if(APP_VERSION < 3)
			{
				CJFunctions::add_script(CJLIB_MEDIA_URI.'/jquery/jquery.min.js', $custom_tag);
			}
			else 
			{
				JHtml::_('jquery.framework');
			}
			
			CJFunctions::add_script(CJLIB_MEDIA_URI.'/jquery/jquery.noconflict.js', $custom_tag);
			$app->jquery = true;
		}
	}
	
	public static function bootstrap($load_bsjs, $load_bscss, $local_css, $custom_tag = true)
	{		
		$app = JFactory::getApplication();

		// Load Bootstrap JavaScript
		if($load_bsjs && !isset($app->cjbsjs)) 
		{
			if(APP_VERSION < 3)
			{
				// make sure jquery is loaded
				CjLibBehavior::jquery();
				
				// now load bootstrap
				CJFunctions::add_script(CJLIB_MEDIA_URI.'/bootstrap/js/bootstrap.min.js', $custom_tag);
			}
			else
			{
				JHtml::_('bootstrap.framework');
			}
			
			CJFunctions::add_script(CJLIB_MEDIA_URI.'/bootstrap/js/respond.min.js', $custom_tag);
			$app->cjbsjs = true;
		}
		
		// Load Bootstrap CSS
		if($load_bscss && !isset($app->cjbscss)) 
		{
			$doc = JFactory::getDocument();
			CJFunctions::add_css_to_document($doc, JUri::root(true).'/media/jui/css/bootstrap.min.css', $custom_tag);
			CJFunctions::add_css_to_document($doc, JUri::root(true).'/media/jui/css/bootstrap-responsive.min.css', $custom_tag);
			$app->cjbscss = true;
		}
		
	}
	
	public static function bscore($custom_tag = true){
		
		$doc = JFactory::getDocument();
		CJFunctions::add_css_to_document($doc, CJLIB_MEDIA_URI.'/bootstrap/css/bootstrap.core.min.css', $custom_tag);
	}
	
	public static function fontawesome($custom_tag = true)
	{
		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		
		// Load FontAwesome if it not already loaded
		if(!isset($app->cjfa)) 
		{
			$doc->addStyleSheet(CJLIB_MEDIA_URI.'/fontawesome/css/font-awesome.min.css');
			$app->cjfa = true;
		}
	}
}