<?php
/**
 * @version		$Id: functions.php 01 2012-04-01 11:37:09Z maverick $
 * @package		CoreJoomla.Framework
 * @subpackage	Components.cjlib
 * @copyright	Copyright (C) 2009 - 2012 corejoomla.com, Inc. All rights reserved.
 * @author		Maverick
 * @link		http://www.corejoomla.com/
 * @license		License GNU General Public License version 2 or later
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
require_once 'api.php';

class CJFunctions {

	private static $_country_names = array();
	
	/**
	 * Generates the pagination, given the non-Sef Joomla url (with itemid), start page number, current page number, total number of pages and number of rows per page.
	 *
	 * @param string $nonSefUrl
	 * @param int $start
	 * @param int $current
	 * @param int $total
	 * @param int $count
	 */
	public static function get_pagination($nonSefUrl, $start, $current, $total, $count=20, $boostrap = false){

		$content = '';
		
		if($total == 1) return $content;
		
		if($boostrap){

			$content = $content.'<div class="cleafix">';
			$content = $content.'<div class="pull-right">'.sprintf(JText::_('COM_CJLIB_PAGINATION_PAGES_COUNT'), $current, $total).'</div>';
			$content = $content.'<div class="pagination pagination-small hidden-phone hidden-sm hidden-xs"><ul>';
			
			if( $total > 0 ){
				
				if($current == 1){
					
					$content = $content.'
							<li class="disabled">
								<a href="#" onclick="return false;" title="'.JText::_('COM_CJLIB_PAGINATION_START').'" class="tooltip-hover"><i class="icon-step-backward"></i></a>
							</li>';
					$content = $content.'
							<li class="disabled">
								<a href="#" onclick="return false;" title="'.JText::_('COM_CJLIB_PAGINATION_PREVIOUS').'" class="tooltip-hover"><i class="icon-backward"></i></a>
							</li>';
				} else {
					
					$content = $content.'
							<li>
								<a href="'.JRoute::_( $nonSefUrl.'&start=0' ).'" 
									title="'.JText::_('COM_CJLIB_PAGINATION_START').'" class="tooltip-hover"><i class="icon-step-backward"></i></a>
							</li>';
					$content = $content.'
							<li>
								<a href="'.JRoute::_( $nonSefUrl.'&start='.( ($current - 2)*$count )).'" 
									title="'.JText::_('COM_CJLIB_PAGINATION_PREVIOUS').'" class="tooltip-hover"><i class="icon-backward"></i></a>
							</li>';
				}
				
				for($i = $start; $i <= $total && $i <= $start + 9; $i++){
					
					$url = JRoute::_( $nonSefUrl.'&start='.( ( $i - 1 ) * $count ) );
					
					if($i == $current){
						
						$content = $content.'<li class="active"><a class="current" href="'.$url.'">'.$i.'</a></li>';
					} else {
						
						$content = $content.'<li><a href="'.$url.'">'.$i.'</a></li>';
					}
				}
				
				if($current == $total){
					
					$content = $content.'
							<li class="disabled">
								<a href="#" title="'.JText::_('COM_CJLIB_PAGINATION_NEXT').'" onclick="return false;" class="tooltip-hover"><i class="icon-forward"></i></a>
							</li>';
					$content = $content.'
							<li class="disabled">
								<a href="#" title="'.JText::_('COM_CJLIB_PAGINATION_LAST').'" onclick="return false;" class="tooltip-hover"><i class="icon-step-forward"></i></a>
							</li>';
				}else{
					
					$content = $content.'
							<li>
								<a href="'.JRoute::_( $nonSefUrl.'&start='.($current * $count) ).'" 
									title="'.JText::_('COM_CJLIB_PAGINATION_NEXT').'" class="tooltip-hover"><i class="icon-forward"></i></a>
							</li>';
					$content = $content.'
							<li>
								<a href="'.JRoute::_( $nonSefUrl.'&start='.(($total - 1) * $count) ).'" 
									title="'.JText::_('COM_CJLIB_PAGINATION_LAST').'" class="tooltip-hover"><i class="icon-step-forward"></i></a>
							</li>';
				}
			}
			
			$content = $content.'</ul></div>';
			
			$content = $content.'<div class="pagination visible-phone visible-sm visible-xs">';
			$content = $content.'<ul class="paginationpager">';
			if( $total > 0 ){
				
				if($current == 1){
					
					$content = $content.'
							<li class="disabled">
								<a href="#" onclick="return false;" 
									title="'.JText::_('COM_CJLIB_PAGINATION_PREVIOUS').'" class="tooltip-hover">'.JText::_('COM_CJLIB_PAGINATION_PREVIOUS').'</i></a>
							</li>';
				} else {
					
					$content = $content.'
							<li>
								<a href="'.JRoute::_( $nonSefUrl.'&start='.( ($current - 2)*$count )).'" 
									title="'.JText::_('COM_CJLIB_PAGINATION_PREVIOUS').'" class="tooltip-hover">'.JText::_('COM_CJLIB_PAGINATION_PREVIOUS').'</a>
							</li>';
				}
				
				if($current == $total){
					
					$content = $content.'
							<li class="disabled">
								<a href="#" title="'.JText::_('COM_CJLIB_PAGINATION_NEXT').'" onclick="return false;" class="tooltip-hover">'.JText::_('COM_CJLIB_PAGINATION_NEXT').'</a>
							</li>';
				}else{
					
					$content = $content.'
							<li>
								<a href="'.JRoute::_( $nonSefUrl.'&start='.($current * $count) ).'" 
									title="'.JText::_('COM_CJLIB_PAGINATION_NEXT').'" class="tooltip-hover">'.JText::_('COM_CJLIB_PAGINATION_NEXT').'</a>
							</li>';
				}
			}
			$content = $content.'</ul></div></div>';
		} else {
			
			$content = $content.'<div class="cjpagination">';
			$content = $content.'<div class="float-right">'.sprintf(JText::_('COM_CJLIB_PAGINATION_PAGES_COUNT'), $current, $total).'</div>';
	
			if( $total > 0 ){
	
				$first_disabled = ( $current == 1 ) ? ' disabled' : '';
				$last_disabled = ( $current == $total ) ? ' disabled' : ''; 
				
				$content = $content.'<div class="page-main">';
				$content = $content.'<a class="first'.$first_disabled.'" href="'.JRoute::_( $nonSefUrl.'&start=0' ).'" 
										title="'.JText::_('COM_CJLIB_PAGINATION_START').'">'.JText::_('COM_CJLIB_PAGINATION_START').'</a>';
				$content = $content.'<a class="previous'.$first_disabled.'" href="'.JRoute::_( $nonSefUrl.'&start='.( $current > 1 ? ( $current - 2 ) * $count : 0 ) ).'" 
										title="'.JText::_('COM_CJLIB_PAGINATION_PREVIOUS').'">'.JText::_('COM_CJLIB_PAGINATION_PREVIOUS').'</a>';
	
				for($i = $start; $i <= $total && $i < $start + 10; $i++){
	
					$url = JRoute::_( $nonSefUrl.'&start='.( ( $i - 1 ) * $count ) );
	
					if($i == $current){
	
						$content = $content.'<a class="current" href="'.$url.'">'.$i.'</a>';
					} else{
	
						$content = $content.'<a href="'.$url.'">'.$i.'</a>';
					}
				}
	
				$content = $content.'<a class="next'.$last_disabled.'" href="'.JRoute::_( $nonSefUrl.'&start='.( $current < $total ? $current * $count : 0 ) ).'"
										title="'.JText::_('COM_CJLIB_PAGINATION_NEXT').'">'.JText::_('COM_CJLIB_PAGINATION_NEXT').'</a>';
				$content = $content.'<a class="last'.$last_disabled.'" href="'.JRoute::_( $nonSefUrl.'&start='.( ( $total - 1 ) * $count ) ).'" 
										title="'.JText::_('COM_CJLIB_PAGINATION_LAST').'">'.JText::_('COM_CJLIB_PAGINATION_LAST').'</a>';
				$content = $content.'</div>';
			}
	
			$content = $content.'<div class="clear"></div>';
			$content = $content.'</div>';
		}

		return $content;
	}
	
	/**
	 * Loads the jquery library and set of jquery plugins passed as parameters to the function.
	 *
	 * The required jquery plugins should be passed as associative array of names with name as libs
	 * Ex:
	 * <code>
	 * $params = array('libs'=>array('ui', 'form', 'validate', 'treeview', 'menu', 'waypoints', 'tags', 'inlinelabel', 'scrollto'), 'theme'=>'start');
	 * CJFunctions::load_jquery($params);
	 * </code>
	 *
	 * @param array $params
	 */
	public static function load_jquery($params=array()){

		$app = JFactory::getApplication();
		$document = JFactory::getDocument();
		
		$plugins = !empty($app->jqueryplugins) ? $app->jqueryplugins : array();
		$custom_tag = isset($params['custom_tag']) ? true : false;

		if(!in_array('baseloc', $plugins)){
				
			$document->addScriptDeclaration('var cjlib_loc = "'.CJLIB_URI.'";');
			$plugins[] = 'baseloc';
		}
		
		if(APP_VERSION <= 2.5 && !$app->get('jquery', false)){

			CJFunctions::add_script_to_document($document, 'jquery.min.js', $custom_tag);
			CJFunctions::add_script_to_document($document, 'jquery.noconflict.js', $custom_tag);
			$app->set('jquery', true);
		} else if(APP_VERSION > 2.5){
			
			JHtml::_('jquery.framework');
		}

		if(!in_array('ui', $plugins) && in_array('ui', $params['libs'])){

			if(APP_VERSION <= 2.5){

				$theme = (!empty($params['theme']) && strcmp($params['theme'], 'default') != 0) ? JFile::makeSafe($params['theme']) : 'start';
				
				if($theme != 'none'){

					$document->addStyleSheet(CJLIB_URI.'/jquery/themes/'.$theme.'/jquery-ui.css');
					CJFunctions::add_script_to_document($document, 'jquery-ui.min.js', $custom_tag);
				} else {
					
					$document->addStyleSheet(CJLIB_URI.'/jquery/themes/no-theme/jquery-ui.css');
					CJFunctions::add_script_to_document($document, 'jquery-ui-core.min.js', $custom_tag);
				}
			} else{
				
				JHtml::_('jquery.ui', array('core', 'sortable'));
			}
			
			$plugins[] = 'ui';
		}
		
		if(!in_array('extras', $plugins)){
			
			if(in_array('extras', $params['libs'])){
				
				$document->addStyleSheet(CJLIB_URI.'/jquery/jquery.extras.min.css');
				CJFunctions::add_script_to_document($document, 'jquery.extras.min.js', $custom_tag);
				CJFunctions::add_script_to_document($document, 'jquery.form.min.js', $custom_tag);
				$plugins[] = 'extras';
				$plugins[] = 'form';
				$plugins[] = 'validate';
				$plugins[] = 'treeview';
			} else {
	
				/** deprecated */
				if(in_array('menu', $params['libs'])){
				
					CJFunctions::add_script_to_document($document, 'jquery.extras.min.js', $custom_tag);
					$document->addStyleSheet(CJLIB_URI.'/jquery/jquery.extras.min.css');
					$plugins[] = 'extras';
				}
			}
		}

		if(!in_array('json', $plugins) && in_array('json', $params['libs'])){
		
			CJFunctions::add_script_to_document($document, 'json2.js', $custom_tag);
			$plugins[] = 'json';
		}
		
		if(!in_array('validate', $plugins) && in_array('validate', $params['libs'])){
			
			$lang = JFactory::getLanguage()->getTag();
			$locale = strstr($lang, '-', true);
			CJFunctions::add_script('https://ajax.aspnetcdn.com/ajax/jquery.validate/1.13.1/jquery.validate.min.js', $custom_tag);
			if($locale != 'en')
			{
				CJFunctions::add_script('https://ajax.aspnetcdn.com/ajax/jquery.validate/1.13.1/localization/messages_'.$locale.'.js', $custom_tag);
			}
			//CJFunctions::add_script_to_document($document, 'jquery.validate.min.js', $custom_tag);
			$plugins[] = 'validate';
		}
		
		if(!in_array('rating', $plugins) && in_array('rating', $params['libs'])){
			
			CJFunctions::add_script_to_document($document, 'jquery.raty.min.js', $custom_tag);
			$plugins[] = 'rating';
		}

		if(!in_array('form', $plugins) && in_array('form', $params['libs'])){

			CJFunctions::add_script(CJLIB_MEDIA_URI.'/jquery/jquery.form.min.js', $custom_tag);
			$plugins[] = 'form';
		}

// 		if(!in_array('morris', $plugins) && in_array('morris', $params['libs'])){
		
// 			CJFunctions::add_script_to_document($document, 'raphael-min.js', $custom_tag);
// 			CJFunctions::add_script_to_document($document, 'morris.min.js', $custom_tag);
// 			$plugins[] = 'morris';
// 		}
		
		if(!in_array('datepicker', $plugins) && in_array('datepicker', $params['libs'])){
		
			$document->addStyleSheet(CJLIB_URI.'/jquery/datepicker.css');
			CJFunctions::add_script_to_document($document, 'bootstrap-datepicker.js', $custom_tag);
			$plugins[] = 'datepicker';
		}

		if(!in_array('treeview', $plugins) && in_array('treeview', $params['libs'])){
		
			$document->addStyleSheet(CJLIB_MEDIA_URI.'/treeview/jquery.treeview.css');
			CJFunctions::add_script_to_document($document, 'jquery.treeview.js', $custom_tag, CJLIB_MEDIA_URI.'/treeview/');
			$plugins[] = 'treeview';
		}
		
		if(!in_array('colorbox', $plugins) && in_array('colorbox', $params['libs'])){
		
			$document->addStyleSheet(CJLIB_MEDIA_URI.'/colorbox/colorbox.css');
			CJFunctions::add_script_to_document($document, 'jquery.colorbox.min.js', $custom_tag, CJLIB_MEDIA_URI.'/colorbox/');
			$plugins[] = 'colorbox';
		}

		if(!in_array('social', $plugins) && in_array('social', $params['libs'])){
		
			$document->addStyleSheet(CJLIB_URI.'/jquery/social/socialcount-with-icons.min.css');
			CJFunctions::add_script_to_document($document, 'socialcount.min.js', $custom_tag, CJLIB_URI.'/jquery/social/');
			$plugins[] = 'social';
		}

		if(!in_array('chosen2', $plugins) && in_array('chosen2', $params['libs'])){
		
			if(APP_VERSION <= 2.5){
				$document->addStyleSheet(CJLIB_URI.'/jquery/chosen/chosen.css');
				CJFunctions::add_script_to_document($document, 'chosen.jquery.min.js', $custom_tag, CJLIB_URI.'/jquery/chosen/');
				CJFunctions::add_script_to_document($document, 'ajax-chosen.min.js', $custom_tag, CJLIB_URI.'/jquery/chosen/');
				
				if(in_array('chosentags', $params['libs']))
				{
					CJFunctions::add_script_to_document($document, 'cj.chosentags.js', $custom_tag, CJLIB_URI.'/jquery/chosen/');
				}
			}
			else 
			{
				$document->addStyleSheet(JUri::root(true).'/media/jui/css/chosen.css');
				$document->addScript(JUri::root(true).'/media/system/js/core.js');
				$document->addScript(JUri::root(true).'/media/jui/js/chosen.jquery.js');
				
				if(in_array('chosentags', $params['libs']))
				{
					$document->addScript(JUri::root(true).'/media/jui/js/ajax-chosen.min.js');
					$document->addScript(CJLIB_URI.'/jquery/chosen/cj.chosentags.js');
				}
			}
			
			$plugins[] = 'chosen2';
		}
		
		if(!in_array('chosen', $plugins) && in_array('chosen', $params['libs'])){
		
			if(APP_VERSION <= 2.5){
				$document->addStyleSheet(CJLIB_URI.'/jquery/chosen/chosen.css');
				CJFunctions::add_script_to_document($document, 'chosen.jquery.min.js', $custom_tag, CJLIB_URI.'/jquery/chosen/');
				CJFunctions::add_script_to_document($document, 'ajax-chosen.min.js', $custom_tag, CJLIB_URI.'/jquery/chosen/');
				CJFunctions::add_script_to_document($document, 'cj.chosentags.js', $custom_tag, CJLIB_URI.'/jquery/chosen/');
			}
			else
			{
				$document->addStyleSheet(JUri::root(true).'/media/jui/css/chosen.css');
				$document->addScript(JUri::root(true).'/media/system/js/core.js');
				$document->addScript(JUri::root(true).'/media/jui/js/chosen.jquery.js');
				$document->addScript(JUri::root(true).'/media/jui/js/ajax-chosen.min.js');
				$document->addScript(CJLIB_URI.'/jquery/chosen/cj.chosentags.js');
			}
			
			$plugins[] = 'chosen';
		}
		
		if(!in_array('tags', $plugins) && in_array('tags', $params['libs'])){
		
			$document->addStyleSheet(CJLIB_URI.'/jquery/cj.tags.min.css');
			CJFunctions::add_script_to_document($document, 'cj.tags.js', $custom_tag);
			$plugins[] = 'tags';
		}
		
		if(!in_array('backbone', $plugins) && in_array('backbone', $params['libs'])){
		
			CJFunctions::add_script_to_document($document, 'underscore-min.js', $custom_tag);
			CJFunctions::add_script_to_document($document, 'backbone-min.js', $custom_tag);
			$plugins[] = 'backbone';
		}

		if(!in_array('bootstrap', $plugins) && in_array('bootstrap', $params['libs']) && !CJLib::$_bootstrap_loaded){

			if(APP_VERSION == '2.5'){
			
				CJFunctions::add_script_to_document($document, 'bootstrap.min.js', $custom_tag, CJLIB_MEDIA_URI.'/bootstrap/js/');
				CjFunctions::add_css_to_document($document, CJLIB_MEDIA_URI.'/bootstrap/css/bootstrap.min.css', $custom_tag);
			} else {
				
				JHtml::_('bootstrap.framework');
				CjFunctions::add_css_to_document($document, CJLIB_MEDIA_URI.'/bootstrap/css/bootstrap.min.css', $custom_tag);
// 				JHtmlBootstrap::loadCss(true, $document->direction);
			}
			
			$document->addScriptDeclaration('<!--[if lt IE 9]><script type="text/javascript" src="'.CJLIB_MEDIA_URI.'/bootstrap/js/respond.min.js"><![endif]-->');
			
			$plugins[] = 'bootstrap';
			CJLib::$_bootstrap_loaded = true;
		}
		
		if(!in_array('fontawesome', $plugins) && in_array('fontawesome', $params['libs'])){

// 			CjFunctions::add_css_to_document($document, '//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css', $custom_tag);
			CjFunctions::add_css_to_document($document, CJLIB_MEDIA_URI.'/fontawesome/css/font-awesome.min.css', $custom_tag);
			$plugins[] = 'fontawesome';
		}
		
		if(!in_array('blockui', $plugins) && in_array('blockui', $params['libs'])){
		
			CJFunctions::add_script(CJLIB_MEDIA_URI.'/jquery/jquery.blockui.js', $custom_tag);
			$plugins[] = 'blockui';
		}

		if(!in_array('message', $plugins) && in_array('message', $params['libs'])){
		
			CJFunctions::add_script(CJLIB_MEDIA_URI.'/jquery/jquery.message.min.js', $custom_tag);
			CjFunctions::add_css_to_document($document, CJLIB_MEDIA_URI.'/jquery/jquery.message.css', $custom_tag);
			$plugins[] = 'message';
		}

		if(!in_array('dygraph', $plugins) && in_array('dygraph', $params['libs'])){
		
			CJFunctions::add_script(CJLIB_MEDIA_URI.'/jquery/dygraph-combined.js', $custom_tag);
			$plugins[] = 'dygraph';
		}
		
		$app->jqueryplugins = $plugins;
	}
	
	public static function add_script_to_document($doc, $script, $custom_tag, $base = null)
	{
		$base = !$base ? CJLIB_URI.'/jquery/' : $base;
		if(method_exists($doc, 'addCustomTag') && $doc->getType() != 'feed') 
		{
			if($custom_tag)
			{
				$doc->addCustomTag('<script src="'.$base.$script.'" type="text/javascript"></script>');
			} 
			else 
			{
				$doc->addScript($base.$script);
			}
		}
	}
	
	public static function add_script($script, $custom_tag)
	{
		$doc = JFactory::getDocument();
		if(method_exists($doc, 'addCustomTag') && $doc->getType() != 'feed') 
		{
			if($custom_tag)
			{
				$doc->addCustomTag('<script src="'.$script.'" type="text/javascript"></script>');
			} 
			else 
			{
				$doc->addScript($script);
			}
		}
	}

	public static function add_css_to_document($doc, $css, $custom_tag)
	{
		if(method_exists($doc, 'addCustomTag') && $doc->getType() != 'feed') 
		{
			if($custom_tag)
			{
				$doc->addCustomTag('<link rel="stylesheet" href="'.$css.'" type="text/css" />');
			} 
			else 
			{
				$doc->addStyleSheet($css);
			}
		}
	}
	
	/**
	 * Returns the editor html markup based on the <code>editor</code> type choosen, bbcode - BBCode Editor, wysiwyg - Joomla default editor, none - plain text area.
	 *  
	 * @param string $editor editor type
	 * @param int $id id of the editor/textarea tag
	 * @param string $name name of the editor/textarea tag
	 * @param string $html default content to be populated in editor/textarea
	 * @param int $rows number of rows of the textarea
	 * @param int $cols number of columns of the textarea
	 * @param string $width width of the editor in pixels or percentage  
	 * @param string $height height of the editor in pixels or percentage
	 * @param string $class css class applied to the editor 
	 * @param string $style style applied to the editor
	 * 
	 * @return string output of the loaded editor markup 
	 */
	public static function load_editor($editor, $id, $name, $html, $rows, $cols, $width=null, $height=null, $class=null, $style=null, $custom_tag = false){
	
		$style = $style ? ' style="'.$style.'"' : '';
		$class = $class ? ' class="'.$class.'"' : '';
		$width = $width ? $width : '450px';
		$height = $height ? $height : '200px';
	
		if($editor == 'bbcode') {
				
			$content = '<style type="text/css"><!-- .markItUpHeader ul { margin: 0; } .markItUpHeader ul li	{ list-style:none; float:left; position:relative; background: none;	line-height: 100%; margin: 0; padding: 0; } --></style>';
			$content .= '<div style="width: '.$width.';"><textarea name="'.$name.'" id="'.$id.'" rows="'.$rows.'" cols="'.$cols.'"'.$style.$class.'>'.$html.'</textarea></div>';
				
			$document = JFactory::getDocument();

			CJFunctions::add_script_to_document($document, 'jquery.markitup.js', $custom_tag, CJLIB_URI.'/lib/markitup/');
			CJFunctions::add_script_to_document($document, 'set.js', $custom_tag, CJLIB_URI.'/lib/markitup/sets/bbcode/');
				
			$document->addStyleSheet(CJLIB_URI.'/lib/markitup/skins/markitup/style.css');
			$document->addStyleSheet(CJLIB_URI.'/lib/markitup/sets/bbcode/style.css');
				
			$document->addScriptDeclaration('jQuery(document).ready(function($){$("#'.$id.'").markItUp(cjbbcode)});;');
		} else if($editor == 'wysiwyg' || $editor == 'default') {
				
			$jeditor = JFactory::getEditor();
			$content = '<div style="overflow: hidden; clear: both;">'.$jeditor->display( $name, $html, $width, $height, $cols, $rows, true, $id ).'</div>';
		}else if($editor == 'wysiwygbb'){
			
			$document = JFactory::getDocument();
			CJFunctions::add_css_to_document($document, CJLIB_MEDIA_URI.'/sceditor/minified/themes/square.min.css', $custom_tag);
			CJFunctions::add_script(CJLIB_MEDIA_URI.'/sceditor/minified/jquery.sceditor.bbcode.min.js', $custom_tag);
			
			$document->addCustomTag('
					<script type="text/javascript">
					jQuery(document).ready(function($){
						$("#'.$id.'").sceditor({
							plugins: "bbcode", 
							style: "'.JUri::root(true).'/media/com_cjlib/sceditor/minified/jquery.sceditor.default.min.css",
							emoticonsRoot: "'.JUri::root(true).'/media/com_cjlib/sceditor/",
							width: "98%",
							autoUpdate: true
						});
						$("#'.$id.'").sceditor("instance").rtl('.($document->direction == 'rtl' ? 'true' : 'false').');
					});
					</script>');
			$content = '<textarea name="'.$name.'" id="'.$id.'" rows="5" cols="50"'.$style.$class.'>'.$html.'</textarea>';
		} else {
			
			$content = '<textarea name="'.$name.'" id="'.$id.'" rows="5" cols="50"'.$style.$class.'>'.$html.'</textarea>';
		}
	
		return $content;
	}
	
	/**
	 * Processes BBCode content if the <code>bbcode</code> value is set, else returns <code>content</code>
	 * 
	 * @param string $content html or bbcode content
	 * @param boolean $bbcode flag indicating the content type is bbocde or not 
	 * @param boolean $process_content_plugins flag to enable processing of Joomla(r) content plugins
	 */
	public static function process_html($content, $bbcode = false, $process_content_plugins = false, $autolink = true){
	
		if($bbcode){
				
			if(!function_exists('BBCode2Html')){
	
				require_once CJLIB_PATH.'/lib/markitup/bbcodeparser.php';
			}
				
			$content = BBCode2Html($content);
		}

		if($autolink){
			
			require_once 'lib_autolink.php';
			$content = autolink_urls($content, 50, ' rel="nofollow"');
		}
				
		if($process_content_plugins){

			$content = JHTML::_('content.prepare', $content);
		}
		
		return $content;
	}
	
	public static function parse_html($content, $process_content_plugins = false, $bbcode = true, $autolink = true){
		
		if($bbcode){
			
			require_once CJLIB_PATH.'/lib/nbbc/nbbc_main.php';
			$bbcode = new BBCode();
	
			$bbcode->SetSmileyURL(CJLIB_MEDIA_URI.'/smileys');
			$bbcode->SetSmileyDir(CJLIB_MEDIA_PATH.'/smileys');

			$bbcode->SetTagMarker('[');
			$bbcode->SetAllowAmpersand(false);
			$bbcode->SetEnableSmileys(true);
			$bbcode->SetDetectURLs($autolink);
			$bbcode->SetPlainMode(false);
			$bbcode->SetDebug(false);
			
			$content = $bbcode->Parse($content);
		}else if($autolink){
			
			require_once 'lib_autolink.php';
			$content = autolink_urls($content, 50, ' rel="nofollow"');
		}
		
		if($process_content_plugins){
		
			$content = JHTML::_('content.prepare', $content);
		}
		
		return $content;
	}
	
	public static function preprocessHtml($content, $plugins = false, $bbcode = false, $autolink = false)
	{
		if($bbcode)
		{
			require_once CJLIB_PATH.'/lib/jbbcode/Parser.php';
			require_once CJLIB_PATH.'/lib/jbbcode/custom/CjCustomCodeDefinitions.php';
			
			$parser = new JBBCode\Parser();
			$parser->addCodeDefinitionSet(new JBBCode\DefaultCodeDefinitionSet());
			$parser->addCodeDefinitionSet(new JBBCode\CjCodeDefinitionSet());
			
			$content = $parser->parse(nl2br(htmlspecialchars($content, ENT_COMPAT, 'UTF-8')))->getAsHtml();
		}
		
		if($autolink)
		{
			require_once 'lib_autolink.php';
			$content = autolink_urls($content, 50, ' rel="nofollow"');
		}
		
		if($plugins)
		{
			$content = JHTML::_('content.prepare', $content);
		}
		
		return $content;
	}
	
	/**
	 * Function to get available theme names from jquery ui library
	 *
	 * @return array list of theme names available on jquery ui library
	 */
	public static function get_ui_theme_names(){

		$themes = array();
		$path = CJLIB_PATH.'/jquery/themes';

		if(file_exists($path)){
				
			$themes = JFolder::folders($path);
		}

		return $themes;
	}

	/**
	 * Award points to the user using selected points system. The user id and required parameters should be passed based on points system selected.
	 *
	 * Parameters array should be an associative array which can include
	 * <ul>
	 * 	<li>function: the function name used to award points.</li>
	 * 	<li>points: points awarded to the user. For jomsocial this has no effect as the points are taken from xml rule.</li>
	 * 	<li>reference: the reference string for AUP rule</li>
	 * 	<li>info: Brief information about this point.</li>
	 * </ul>
	 *
	 * @param string $system
	 * @param int $userid
	 * @param array $params
	 */
	public static function award_points($system, $userid, $params=array()){

		switch ($system){
			
			case 'cjblog':
				
				$api = JPATH_ROOT.'/components/com_cjblog/api.php';
				
				if(file_exists($api)){
					
					include_once $api;
					$points = !empty($params['points']) ? $params['points'] : 0;
					$reference = !empty($params['reference']) ? $params['reference'] : null;
					$description = !empty($params['info']) ? $params['info'] : null;
					
					CjBlogApi::award_points($params['function'], $userid, $points, $reference, $description);
				}
				
				break;

			case 'jomsocial':

				$api = JPATH_ROOT.'/components/com_community/libraries/core.php';
				if( file_exists($api) && !empty($params['function']) ){

					include_once $api;
					include_once JPATH_ROOT.'/components/com_community/libraries/userpoints.php';
					CuserPoints::assignPoint( $params['function'], $userid );
				}
				break;

			case 'aup':

				$api = JPATH_ROOT.'/components/com_alphauserpoints/helper.php';
				if ( file_exists($api) && !empty($params['function']) && isset($params['info']) ){

					require_once $api;
					$reference = !empty($params['reference']) ? $params['reference'] : null;
					$aupid = AlphaUserPointsHelper::getAnyUserReferreID( $userid );
					AlphaUserPointsHelper::newpoints( $params['function'], $aupid, $reference, $params['info'], $params['points'] );
				}
				break;

			case 'touch':

				$api = JPATH_ROOT.'/components/com_community/api.php';
				if ( file_exists($api) && !empty($params['points']) ){

					require_once $api;
					JSCommunityApi::increaseKarma( $userid, $params['points'] );
				}
				break;

			case 'easysocial':
				
				$api = JPATH_ADMINISTRATOR.'/components/com_easysocial/includes/foundry.php';

				if( file_exists($api) ) {
					
					require_once $api;
					Foundry::points()->assign( $params['function'] , $params['component'] , $userid );
				}
				
				break;
		}
	}

	/**
	 * Streams activity to selected extension stream. The parameters should include required values based on extension as associative array
	 *
	 * <ul>
	 * <li>command: command for jomsocial activity, mostly component_name.action</li>
	 * <li>title: title of the stream</li>
	 * <li>description: short description. if full text is passed it will be stripped upto length <code>$params['length']</code></li>
	 * <li>length: max length of the activity description</li>
	 * <li>href: url of the stream</li>
	 * <li>icon: icon to be used for mighty touch stream</li>
	 * <li>component: component name i.e.com_yourcomponentname</li>
	 * <li>group: group name for touch stream. ex. Articles</li>
	 * </ul>
	 *
	 * @param string $system Component to stream
	 * @param int $userid User id
	 * @param array $params params based on component type
	 */
	public static function stream_activity($system, $userid, $params=array()){

		switch($system){

			case 'jomsocial':

				$api = JPATH_ROOT.'/components/com_community/libraries/core.php';
				if( file_exists($api) && !empty($params['title']) && !empty($params['command']) ){

					include_once $api;
					CFactory::load('libraries', 'activities');
						
					$act = new stdClass();
					$act->cmd			= 'wall.write';
					$act->target		= 0;
					$act->app			= 'wall';
					$act->cid			= 0;
					$act->comment_id	= CActivities::COMMENT_SELF;
					$act->like_id		= CActivities::LIKE_SELF;
					$act->actor			= $userid;
					$act->title			= $params['title'];
					$act->comment_type	= $params['command'];
					$act->like_type		= $params['command'];
					$act->access		= 0;
						
					if( !empty($params['description']) && !empty($params['length']) ){

						$content = CJFunctions::substrws( $params['description'], $params['length'] );
						
						if(!empty($params['href'])){
							
							$act->content = $content.'
									<div style="margin-top: 5px;">
										<div style="float: right; font-weight: bold; font-size: 12px;">
											<a href="'.$params['href'].'">'.JText::_('COM_CJLIB_READ_MORE').'</a>
										</div>
										<div style="clear: both;"></div>
									</div>';
						} else {
							
							$act->content = $content;
						}
					}

					CActivityStream::add($act);
				}
				break;
				
			case 'cb':
				
				global $_CB_framework, $_CB_database, $ueConfig, $mainframe;
				$api = JPATH_ADMINISTRATOR.'/components/com_comprofiler/plugin.foundation.php';
				
				if (!is_file($api)) return;
				require_once ($api);
				
				cbimport ( 'cb.database' );
				cbimport ( 'cb.tables' );
				cbimport ( 'cb.field' );
				cbimport ( 'language.front' );
				
				$activity = new cbactivityActivity( $_CB_database );
				
				$activity->set( 'user_id', (int) $userid );
				$activity->set( 'type', 'profile' );
				$activity->set( 'subtype', 'registration' );
				$activity->set( 'title', 'has joined [sitename_linked]' );
				$activity->set( 'icon', 'nameplate' );
				$activity->set( 'date', cbactivityClass::getUTCDate() );
				
				$activity->store();
				
				break;

			case 'easysocial':
				
				$api = JPATH_ADMINISTRATOR.'/components/com_easysocial/includes/foundry.php';

				if( file_exists($api) ) {
					
					require_once $api;
					
					$stream = Foundry::stream();
					$template = $stream->getTemplate();
					$content = $params['length'] > 0 ? CJFunctions::substrws( $params['description'], $params['length'] ) : $params['description'];
					
					$template->setActor( $userid , 'user' );
					$template->setContext($params['item_id'], $params['context']);
					$template->setTitle($params['title']);
					$template->setContent($content);
					$template->setVerb( 'create' );
// 					$template->setSideWide( true );
					$template->setType('full');
					
					$stream->add( $template );
				}
				break;
		}
	}

	/**
	 * Gets the user avatar of the selected <code>system</code>
	 * 
	 * @param string $system Avatar system to be used
	 * @param int $userid user id
	 * @param string $username username or name
	 * @param int $height height of the avatar
	 * 
	 * @return string user avatar
	 * @deprecated use CjLibApi()->getUserAvatar
	 */
	public static function get_user_avatar($system, $userid, $displayname = 'username', $height = 48, $email = null, $attribs = array(), $img_attribs = array())
	{
		$api = new CjLibApi();
		return $api->getUserAvatar($system, $system, $userid, $displayname, $height, $email, $attribs, $img_attribs);
	}
	
	/**
	 * Gets the user avatar image without any link
	 * 
	 * @param unknown $system
	 * @param unknown $userid
	 * @param unknown $alt
	 * @param number $height
	 * @param string $email
	 * @param string $path_only
	 * @param unknown $attribs
	 * @return void|Ambigous <string, unknown, NULL, string>
	 * 
	 * @deprecated use CjLibApi()->get_user_avatar_image
	 */
	public static function get_user_avatar_image($system, $userid, $alt, $height = 48, $email = null, $path_only = true, $attribs = array())
	{
		$api = new CjLibApi();
		return $api->getUserAvatarImage($system, $userid, $email, $height, $path_only, $alt, $attribs);
	}
	
	/**
	 * @deprecated use CjLibApi()->prefetchUserProfiles
	 * @param unknown $system
	 * @param unknown $ids
	 */
	public static function load_users($system, $ids)
	{
		$api = new CjLibApi();
		$api->prefetchUserProfiles($system, $ids);
	}
	
	/**
	 * Gets the user profile url of selected <code>system</code>. Currently supported systems are <br><br> 
	 * 
	 * JomSocial - jomsocial, Community Builder - cb, Touch - touch, Kunena - kunena, Alpha User Points - aup
	 * 
	 * @param string $system User profile system
	 * @param int $userid user id
	 * @param string $username User name to be used to display with link
	 * @param array $links array of links for mighty touch
	 * @param path_only boolean want to retrive just the url or the full html hyperlink markup?
	 * 
	 * @return string user profile url
	 * @deprecated use CjLibApi()->get_user_profile_url
	 */
	public static function get_user_profile_url($system, $userid = 0, $username = 'Guest', $path_only = true, $attribs = array())
	{
		$api = new CjLibApi();
		return $api->getUserProfileUrl($system, $userid, $path_only, $username, $attribs);
	}
	
	/**
	 * deprecated, use get_user_profile_url instead
	 * @deprecated use get_user_profile_url instead
	 */
	public static function get_user_profile_link($system, $userid = 0, $username = 'Guest', $links = array(), $alias = null, $path_only = false, $attribs = array()){
		
		return CJFunctions::get_user_profile_url($system, $userid, $username, $path_only, $attribs);
	}
	
	public static function get_localized_date($strdate, $format = 'Y-m-d'){
		
		if(empty($strdate) || $strdate == '0000-00-00 00:00:00'){
				
			return JText::_('LBL_NA');
		}
		
		jimport('joomla.utilities.date');
		$date = JFactory::getDate($strdate);
		
		return $date->format($format, true);
	}
	
	/**
	 * Gets the human friendly date string from a date
	 * 
	 * @param string $strdate date
	 * 
	 * @return string formatted date string
	 * @deprecated use CjLibUtils::getHumanReadableDate
	 */
	public static function get_formatted_date($strdate) {
		
		require_once JPATH_ROOT.'/components/com_cjlib/lib/corejoomla/dateutils.php';
		return CjLibDateUtils::getHumanReadableDate($strdate);
	}
	
	/**
	 * Returns date/time in short format. i.e. 6m, 6h, 6d, 6w, 6m, 6y etc
	 * @param unknown $date
	 * @return Ambigous <string, string, mixed, multitype:>|Ambigous <string, string, mixed>
	 */
	public static function get_short_date_time_diff($date)
	{
		if(empty($strdate) || $strdate == '0000-00-00 00:00:00')
		{
			return JText::_('LBL_NA');
		}
		
		jimport('joomla.utilities.date');
		$user = JFactory::getUser();

		// Given time
		$date = new JDate(JHtml::date($strdate, 'Y-m-d H:i:s'));
		$compareTo = new JDate(JHtml::date('now', 'Y-m-d H:i:s'));
		$diff = $compareTo->toUnix() - $date->toUnix();
		
		$diff = abs($diff);
		$dayDiff = floor($diff/86400);

		if($dayDiff == 0) 
		{
			if($diff < 120) 
			{
				return '1m';
			} 
			elseif($diff < 3600) 
			{
				return floor($diff/60).'m';
			} 
			elseif($diff < 7200) 
			{
				return floor($diff/3600).'h';
			}
		} elseif($dayDiff < 7) 
		{
			return $dayDiff.'d';
		} 
		elseif($dayDiff < (7*6)) 
		{
			return ceil($dayDiff/7).'w';
		} 
		elseif($dayDiff < 365) 
		{
			return ceil($dayDiff/(365/12)).'m';
		} 
		else 
		{
			return round($dayDiff/365).'y';
		}
	}
	
	/**
	 * Gets the difference between two dates in human readable format.
	 * 
	 * @param string $date1
	 * @param string $date2
	 */
	public static function get_date_difference($strdate1, $strdate2){
		
		$diff = strtotime($strdate2) - strtotime($strdate1);
		$days = floor($diff / 86400);
		$hours = floor(($diff % 86400) / 3600);
		$minutes = floor(($diff % 3600) / 60);
		$seconds = floor($diff % 60);
		
		if($diff <= 0){
			
			return JText::_('COM_CJLIB_NOT_COMPLETED');
		}else if($days > 0) {
			
			return JText::sprintf('COM_CJLIB_DATE_DIFF_DAYS', $days, $hours, $minutes, $seconds);
		} else if($hours > 0){
			
			return JText::sprintf('COM_CJLIB_DATE_DIFF_HOURS', $hours, $minutes, $seconds);
		} else {
			
			return JText::sprintf('COM_CJLIB_DATE_DIFF_MINUTES', $minutes, $seconds);
		}
	}

	/**
	 * word-sensitive substring function with html tags awareness
	 *
	 * @param string text The text to cut
	 * @param int len The maximum length of the cut string
	 * @param array Array of tags to exclude
	 *
	 * @return string The modified html content
	 * @deprecated use CjLibUtils::substrws
	 */
	public static function substrws( $text, $len=180, $tags=array()) {

		if(function_exists('mb_strlen')){
			
			if( (mb_strlen($text, 'UTF-8') > $len) ) {
			
				$whitespaceposition = mb_strpos($text, ' ', $len, 'UTF-8')-1;
			
				if( $whitespaceposition > 0 ) {
			
					$chars = count_chars(mb_substr($text, 0, $whitespaceposition + 1, 'UTF-8'), 1);
			
					if (!empty($chars[ord('<')]) && $chars[ord('<')] > $chars[ord('>')]){
							
						$whitespaceposition = mb_strpos($text, '>', $whitespaceposition, 'UTF-8') - 1;
					}
			
					$text = mb_substr($text, 0, $whitespaceposition + 1, 'UTF-8');
				}
			
				// close unclosed html tags
				if( preg_match_all("|<([a-zA-Z]+)|",$text,$aBuffer) ) {
			
					if( !empty($aBuffer[1]) ) {
			
						preg_match_all("|</([a-zA-Z]+)>|",$text,$aBuffer2);
			
						if( count($aBuffer[1]) != count($aBuffer2[1]) ) {
			
							foreach( $aBuffer[1] as $index => $tag ) {
			
								if( empty($aBuffer2[1][$index]) || $aBuffer2[1][$index] != $tag){
			
									$text .= '</'.$tag.'>';
								}
							}
						}
					}
				}
			}
		} else {
			
			if( (strlen($text) > $len) ) {
			
				$whitespaceposition = strpos($text, ' ', $len)-1;
			
				if( $whitespaceposition > 0 ) {
			
					$chars = count_chars(substr($text, 0, $whitespaceposition + 1), 1);
			
					if ($chars[ord('<')] > $chars[ord('>')]){

						$whitespaceposition = strpos($text, '>', $whitespaceposition) - 1;
					}
			
					$text = substr($text, 0, $whitespaceposition + 1);
				}
			
				// close unclosed html tags
				if( preg_match_all("|<([a-zA-Z]+)|",$text,$aBuffer) ) {
			
					if( !empty($aBuffer[1]) ) {
			
						preg_match_all("|</([a-zA-Z]+)>|",$text,$aBuffer2);
			
						if( count($aBuffer[1]) != count($aBuffer2[1]) ) {
			
							foreach( $aBuffer[1] as $index => $tag ) {
			
								if( empty($aBuffer2[1][$index]) || $aBuffer2[1][$index] != $tag){
			
									$text .= '</'.$tag.'>';
								}
							}
						}
					}
				}
			}
		}
		
		return preg_replace('#<p[^>]*>(\s|&nbsp;?)*</p>#', '', $text);;
	}

	/**
	 * Gets the ip address of the user from request
	 *
	 * @return string ip address
	 */
	public static function get_user_ip_address() {

		$ip = '';

		if( !empty($_SERVER['HTTP_X_FORWARDED_FOR']) AND strlen($_SERVER['HTTP_X_FORWARDED_FOR'])>6 ){
				
			$ip = strip_tags($_SERVER['HTTP_X_FORWARDED_FOR']);
		}elseif( !empty($_SERVER['HTTP_CLIENT_IP']) AND strlen($_SERVER['HTTP_CLIENT_IP'])>6 ){
				
			$ip = strip_tags($_SERVER['HTTP_CLIENT_IP']);
		}elseif(!empty($_SERVER['REMOTE_ADDR']) AND strlen($_SERVER['REMOTE_ADDR'])>6){
				
			$ip = strip_tags($_SERVER['REMOTE_ADDR']);
		}

		return trim($ip);
	}
	
	/**
	 * Gets the array containing location details matching the ip address.
	 * 
	 * @param string $ip IP address
	 * 
	 * @return array An array containing city, country and country code (country_code) information
	 */
	public static function get_user_location($ip)
	{
		return CjLibUtils::getUserLocation($ip);
	}
	
	/**
	 * Gets the clean content from the request variable named <code>var</code>. If the second parameter passed as false, html tags will be stripped out.
	 *
	 * @param string $var
	 * @param boolean $html
	 * @return Ambigous <string, mixed>
	 */
	public static function get_clean_var($var, $html = true, $default = ''){
		
		$value = $html ? JRequest::getVar($var, $default, 'post', 'string', JREQUEST_ALLOWRAW) : JRequest::getVar($var, $default, 'post', 'string');
		$value = empty($_POST[$var]) ? $default : $_POST[$var];
		
		return CJFunctions::clean_value($value);
	}
	
	/**
	 * Gets the clean content from the value given. If the second parameter passed as false, html tags will be stripped out.
	 *
	 * @param string $var
	 * @param boolean $html
	 * @return Ambigous <string, mixed>
	 */
	public static function clean_value($value, $html=true){
		
		$filter = new CleanXSS();
		return $filter->sanitize($value);
	}

	/**
	 * Returns unicode alias string from the <code>title</code> passed as an argument. If the Joomla version is less than 1.6, the function will gracefully degrades and outputs normal alias.
	 *
	 * @param string $title
	 */
	public static function get_unicode_alias($title){

		if (JFactory::getConfig()->get('unicodeslugs') == 1) {
		
			return JFilterOutput::stringURLUnicodeSlug($title);
		} else {
		
			return JFilterOutput::stringURLSafe($title);
		}
	}

	/**
	 * A wrapper function for the Joomla mail API to send emails.
	 *
	 * @param string $from from email address
	 * @param string $fromname name of the sender
	 * @param string $recipient reciepient email
	 * @param string $subject email subject
	 * @param string $body body of the email
	 * @param boolean $mode true if html mode enabled, false otherwise
	 * @param string $cc email addresses in cc
	 * @param string $bcc email addresses in bcc
	 * @param string $attachment attachment
	 * @param string $replyto replyto email address
	 * @param string $replytoname reply to name
	 *
	 * @return mixed True if successful, a JError object otherwise
	 */
	public static function send_email($from, $fromname, $recipient, $subject, $body, $mode=0, $cc=null, $bcc=null, $attachment=null, $replyto=null, $replytoname=null){

		// Get a JMail instance
		$mail = JFactory::getMailer();

		$mail->setSender(array($from, $fromname));
		$mail->setSubject($subject);
		$mail->setBody($body);

		// Are we sending the email as HTML?
		if ($mode) {
				
			$mail->IsHTML(true);
		}

		if(!is_array($recipient)){
		
			$recipient = explode(',', $recipient);
		}

		$mail->addRecipient($recipient);
		$mail->addCC($cc);
		$mail->addBCC($bcc);
		$mail->addAttachment($attachment);

		// Take care of reply email addresses
		if (is_array($replyto)) {
				
			$numReplyTo = count($replyto);
				
			for ($i=0; $i < $numReplyTo; $i++){

				$mail->addReplyTo(array($replyto[$i], $replytoname[$i]));
			}
		} elseif (isset($replyto)) {
				
			$mail->addReplyTo(array($replyto, $replytoname));
		}

		return  $mail->Send();
	}

	/**
	 * Loads the modules assigned to position <code>$position</code>
	 *
	 * @param string $position
	 * @return string The output of the script
	 */
	public static function load_module_position($position) {

		jimport( 'joomla.application.module.helper' );
		
		if(JModuleHelper::getModules($position)) {
				
			$document	= JFactory::getDocument();
			$renderer	= $document->loadRenderer('modules');
			$options	= array('style' => 'xhtml');
				
			return $renderer->render($position, $options, null);
		}else {
				
			return '';
		}
	}

	/**
	 * Generate a random character string
	 *
	 * @param int $length length of the string to be generated
	 * @param string $chars characters to be considered, default alphanumeric characters.
	 *
	 * @return string randomly generated string
	 * @deprecated use CjLibUtils::getRandomKey
	 */
	public static function generate_random_key($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890'){

		return CjLibUtils::getRandomKey($length, $chars);
	}

	/**
	 * Throws new exception with message and error code for j1.6 or above, calls <code>JError::raiseError</code> otherwise.
	 *
	 * @param string $msg message
	 * @param int $code error code
	 * 
	 * @throws Exception
	 */
	public static function throw_error($msg, $code){

		if(APP_VERSION == '1.5'){
				
			JError::raiseError( $code, $msg);
		}else{
				
			throw new Exception($msg, $code);
		}
	}

	/**
	 * Convert special characters to HTML entities with UTF-8 encoding.
	 * 
	 * @param string $var content to be escaped
	 */
	public static function escape($var){

		return htmlspecialchars($var, ENT_COMPAT, 'UTF-8');
	}

	/**
	 * Triggers a Joomla event
	 * 
	 * @param string $group The plugin type, relates to the sub-directory in the plugins directory
	 * @param string $event The event to trigger
	 * @param array $params An array of arguments
	 */
	public static function trigger_event($group, $event, $params=null){

		JPluginHelper::importPlugin( $group );

		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger($event, $params);
	}
	
	/**
	 * Returns menu id of active menu. If the <code>itemid</code> is set, Itemid request variable name is prepended for use in JRoute urls.
	 * 
	 * @param boolean $itemid Returns Itemid appended text if set to true else int id of active menu
	 * @param string $url The url from which the menu id to be retrieved instead of active menu item
	 * @param boolean $cat_request If true, catid from request is prepended to the itemid text. Not applicable if <code>itemid</code> set to false. 
	 * 
	 * @return Ambigous <string, number, mixed> active menu id if <code>itemid</code> not set, else return Itemid request variable or empty if not found.
	 */
	public static function get_active_menu_id($itemid = true, $url = null, $cat_request = false){
		
		$menu = JFactory::getApplication()->getMenu('site');
		$active = $menu->getActive();
		$menuid = 0;
		$catparam = '';
		
		if(empty($url)){
			
			if(!empty($active->id)){
				
				$menuid = $active->id;
			}
		
			if($menuid <= 0){
				
				$menuid = JRequest::getInt('Itemid', 0);
			}
		} else {
			
			if(!empty($active->id)){
					
				$menuitems = $menu->getItems('link', $url, false);
				
				if(!empty($menuitems)){
			
					foreach ($menuitems as $menuitem){
							
						if(!empty($menuitem->id) && $menuitem->id == $active->id){
			
							$menuid = $menuitem->id;
							break;
						}
					}
					
					if($menuid == 0 && !empty($menuitems[0]->id)){
						
						$menuid = $menuitems[0]->id;
					}
				}
			} else {
					
				$menuitem = $menu->getItems('link', $url, true);
					
				if(!empty($menuitem)){
			
					$menuid = $menuitem->id;
				}
			}
		}
		
		if( $itemid && $cat_request ){
			
			$catid = JRequest::getInt('catid', 0);
			$catparam = $catid > 0 ? '&catid='.$catid : '';
		}
		
		return $menuid > 0 ? ( $itemid ? $catparam.'&Itemid='.$menuid : $menuid ) : '';
	}
	
	/**
	 * Loads the comments from the installed/selected comment system. The comment system <code>type</code> should tell which comment system need to be used to load the comments from. The possible values are:<br><br>
	 * jcomment - JComments (id and title are required) <br>
	 * fbcomment - Facebook comment system (url is required)<br>
	 * disqus - Disqus comment system (id, title, identifier and url are required)<br>
	 * intensedebate - Intense Debate comment system (id, title, identifier and url are required)<br>
	 * jacomment - JAComment system (id and title are required)<br>
	 * jomcomment - JomComment (id is required)<br><br>
	 * Passing any other value will silently skips the code. In all the above cases, <code>type</code> and <code>app_name</code> are required parameters. 
	 * While <code>type</code> specifies the comment system to be used, <code>app_name</code> is the Joomla extension name (ex: com_appname) which is loading the comments for its content.
	 *  
	 * @param string $type comment system type
	 * @param string $app_name extension name
	 * @param int $id id of the content for which the comments are being loaded
	 * @param string $title title of the content
	 * @param string $url page url in case of facebook/disqus/intensedebate comment system.
	 * @param string $identifier disqus username in case of disqus/intensedebate comment system.
	 * @param object $item the item object for kommento
	 * 
	 * @return string the code to render the comments.
	 */
	public static function load_comments($type, $app_name, $id=0, $title='', $url = '', $identifier='', $item = null){
		
		switch ($type){
			
			case 'jcomment':
			
				$app = JFactory::getApplication();
				$path = JPATH_ROOT.'/components/com_jcomments/jcomments.php';
				
				if (file_exists($path)) {
					
					require_once($path);
					return JComments::showComments($id, $app_name, $title);
				}
				break;

			case 'fbcomment':
				
				return '
					<div id="fb-root"></div>
					<script type="text/javascript">
						(function(d, s, id) {
							var js, fjs = d.getElementsByTagName(s)[0]; 
							if (d.getElementById(id)) return; 
							js = d.createElement(s); 
							js.id = id;
							js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.3";
							fjs.parentNode.insertBefore(js, fjs); 
						}(document, "script", "facebook-jssdk"));
					</script>
					<div class="fb-comments" data-href="'.$url.'" data-num-posts="5" data-width="640"></div>';
				
			case 'disqus':
				
				return '
					<div id="disqus_thread"></div>
					<script type="text/javascript">
						var disqus_shortname = "'.$identifier.'";
// 						var disqus_developer = 1;
						var disqus_identifier = "'.$id.'";
						var disqus_url = "'.$url.'";
						var disqus_title = "'.$title.'";
						(function() {
							var dsq = document.createElement("script"); 
							dsq.type = "text/javascript"; 
							dsq.async = true;
							dsq.src = "http://" + disqus_shortname + ".disqus.com/embed.js";
							(document.getElementsByTagName("head")[0] || document.getElementsByTagName("body")[0]).appendChild(dsq);
						})();
					</script>
					<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>';
				
			case 'intensedebate':
				
				return '
					<script>
						var idcomments_acct = "'.$identifier.'";
						var idcomments_post_id = "'.$id.'";
						var idcomments_post_url = "'.$url.'";
					</script>
					<span id="IDCommentsPostTitle" style="display:none"></span>
					<script type="text/javascript" src="http://www.intensedebate.com/js/genericCommentWrapperV2.js"></script>';
				break;
			
			case 'jacomment':

				if(!JRequest::getInt('print') && file_exists(JPATH_SITE.'/components/com_jacomment/jacomment.php') && file_exists(JPATH_SITE.'/plugins/system/jacomment.php')){
					
					$_jacCode = "#{jacomment(.*?) contentid=(.*?) option=(.*?) contenttitle=(.*?)}#i";
					$_jacCodeDisableid = "#{jacomment(\s)off.*}#i";
					$_jacCodeDisable = "#{jacomment(\s)off}#i";
					
					if(!preg_match($_jacCode, $title) && !preg_match($_jacCodeDisable, $title) && !preg_match($_jacCodeDisableid, $title)) {
						
						return '{jacomment contentid='.$id.' option='.$app_name.' contenttitle='.$title.'}';
					}
				}
				
				break;

			case 'jomcomment':
				
				$path = JPATH_PLUGINS.'/content/jom_comment_bot.php';
				
				if(file_exists($path)) {
					
					include_once( $path );
					return jomcomment($id, $app_name);
				}
				
				break;
				
			case 'kommento':
				
				$api = JPATH_ROOT.'/components/com_komento/bootstrap.php';
				
				if(file_exists($api)){
					
					require_once $api;
					$item->text = $item->introtext = !empty($item->description) ? $item->description : '';
					return Komento::commentify( $app_name, $item );
				}
				
				break;
				
			case 'ccomment':
				
				$utils = JPATH_ROOT . '/components/com_comment/helpers/utils.php';
				
				if(file_exists($utils)) {
					
					JLoader::discover('ccommentHelper', JPATH_ROOT . '/components/com_comment/helpers');
					return ccommentHelperUtils::commentInit($app_name, $item);
				}
				
				break;
		}
	}
	
	public static function get_comment_count($type, $app_name, $id=0, $title='', $url = '', $identifier='', $item = null)
	{
		switch ($type){
				
			case 'jcomment':
				$app = JFactory::getApplication();
				$path = JPATH_ROOT.'/components/com_jcomments/jcomments.php';
				
				if (file_exists($path)) {
						
					require_once($path);
					return JComments::getCommentsCount($id, $app_name);
				}
				break;
		
			case 'fbcomment':
				$json = json_decode(file_get_contents('https://graph.facebook.com/?ids=' . $url));
				return isset($json->$url->comments) ? $json->$url->comments : 0;
		
			case 'disqus':
// 				$document = JFactory::getDocument();
// 				$document->addScript('http://' + $identifier + '.disqus.com/count.js');
				break;
		
			case 'intensedebate':
				break;
					
			case 'jacomment':
				break;
		
			case 'kommento':
				break;
		
			case 'ccomment':
				break;
		}
	}
	
	/**
	 * Checks the reCaptcha request variables
	 *
	 * @param string $private_key reCaptcha private key
	 * @return true if entered captcha is correct, false otherwise.
	 */
	public static function verify_captcha($private_key){
	
		if(!function_exists('_recaptcha_qsencode')){
	
			require_once CJLIB_PATH.'/framework/recaptchalib.php';
		}
	
		$recaptcha_challenge_field = JRequest::getVar('recaptcha_challenge_field', null, 'post', 'string');
		$recaptcha_response_field = JRequest::getVar('recaptcha_response_field', null, 'post', 'string');
	
		$resp = recaptcha_check_answer($private_key, $_SERVER['REMOTE_ADDR'], $recaptcha_challenge_field, $recaptcha_response_field);
	
		if(!$resp->is_valid){
	
			return false;
		}
	
		return true;
	}
	
	/**
	 * Gets the version check update from corejoomla servers. Returns associative array with values of <ul>
	 * <li>version - version on servers</li>
	 * <li>released - release date of the version on server</li>
	 * <li>changelog - for future use</li>
	 * <li>status - boolean true if the version is equal, false otherwise</li>
	 * <li>connect - true if corejoomla.com is connected with the function, false otherwise.</li></ul>
	 * 
	 * @param string $component component to check for update
	 * @param version $current_version current version of the component to compare
	 * 
	 * return array update status of the component 
	 */
	public static function get_component_update_check($component, $current_version){
		
		$url = 'http://www.corejoomla.com/extensions.xml';
		$data = '';
		$check = array();
		$check['connect'] = 0;
		$check['current_version'] = $current_version;
		
		//try to connect via cURL
		if(function_exists('curl_init') && function_exists('curl_exec')) {
			
			$ch = @curl_init();
		
			@curl_setopt($ch, CURLOPT_URL, $url);
			@curl_setopt($ch, CURLOPT_HEADER, 0);
			
			//http code is greater than or equal to 300 ->fail
			@curl_setopt($ch, CURLOPT_FAILONERROR, 1);
			@curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			
			//timeout of 5s just in case
			@curl_setopt($ch, CURLOPT_TIMEOUT, 5);
			$data = @curl_exec($ch);
			
			@curl_close($ch);
		}
		
		//try to connect via fsockopen
		if($data == '' && function_exists('fsockopen')) {
		
			$errno = 0;
			$errstr = '';
		
			//timeout handling: 5s for the socket and 5s for the stream = 10s
			$fsock = @fsockopen("www.corejoomla.com", 80, $errno, $errstr, 5);
		
			if ($fsock) {
				
				@fputs($fsock, "GET /extensions.xml HTTP/1.1\r\n");
				@fputs($fsock, "HOST: www.corejoomla.com\r\n");
				@fputs($fsock, "Connection: close\r\n\r\n");
		
				//force stream timeout...
				@stream_set_blocking($fsock, 1);
				@stream_set_timeout($fsock, 5);
		
				$get_info = false;
				
				while (!@feof($fsock)) {
					
					if ($get_info) {
						
						$data .= @fread($fsock, 8192);
					} else {
						
						if (@fgets($fsock, 8192) == "\r\n") {
							
							$get_info = true;
						}
					}
				}
				
				@fclose($fsock);

				//need to check data cause http error codes aren't supported here
				if(!strstr($data, '<corejoomla>')) {
					
					$data = '';
				}
			}
		}
		
		//try to connect via fopen
		if ($data == '' && function_exists('fopen') && ini_get('allow_url_fopen')) {
		
			//set socket timeout
			ini_set('default_socket_timeout', 5);
		
			$handle = @fopen ($url, 'r');
		
			//set stream timeout
			@stream_set_blocking($handle, 1);
			@stream_set_timeout($handle, 5);
		
			$data	= @fread($handle, 8192);
		
			@fclose($handle);
		}
		
		if( !empty($data) && strstr($data, '<corejoomla>') ) {
			
			$xml = new SimpleXMLElement($data);
			
			foreach($xml->extension as $extension){
				
				if($extension['name'] == $component && $extension['jversion'] == '1.7'){
					
					$check['version']		= $extension->version;
					$check['released']		= $extension->released;
					$check['changelog']		= $extension->changelog;
					$check['status']		= version_compare( $check['current_version'], $check['version'] );
					$check['connect']		= 1;
					
					break;
				}
			}
		} else {
			
			$check['version']		= 'N/A';
			$check['released']		= 'N/A';
			$check['changelog']		= 'N/A';
			$check['status']		= '0';
			$check['connect']		= 0;
		}
		
		return $check;		
	}
	
	public static function get_user_groups_tree($id, $name, $value){
		
		$groups = array();
		
		if(APP_VERSION != '1.5'){
			
			$db = JFactory::getDbo();
			$query = '
				select
					concat( repeat(\'..\', count(parent.id) - 1), node.title) as text, node.id as value
				from 
					#__usergroups as node, #__usergroups as parent
				where
					node.lft between parent.lft and parent.rgt
				group by
					node.id
				order by
					node.lft';
		
			$db->setQuery($query);
			$groups = $db->loadObjectList();
		}else{
				
			$acl	= JFactory::getACL();
			$groups	= $acl->get_group_children_tree( null, 'USERS', false );
		}
			 
		$attribs	= ' ';
		$attribs	.= 'size="'.count($groups).'"';
		$attribs	.= 'class="inputbox"';
		$attribs	.= 'multiple="multiple"';
			
		return JHTML::_('select.genericlist', $groups, $name, $attribs, 'value', 'text', $value, $id );
	}
	
	public static function get_jomsocial_groups($id, $name, $value){
		
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		
		$query = '
			select 
				id as value, name as text 
			from 
				#__community_groups g
			left join
				#__community_groups_members m on g.id = m.groupid
			where
				m.memberid='.$user->id.' and approved = 1';
		
		$db->setQuery($query);
		$groups = $db->loadObjectList();
		
		$attribs	= ' ';
		$attribs	.= 'size="10"';
		$attribs	.= 'class="inputbox"';
		$attribs	.= 'multiple="multiple"';
			
		return JHTML::_('select.genericlist', $groups, $name, $attribs, 'value', 'text', $value, $id );
	}
	
	public static function get_jomsocial_group_users($gid){
		
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		
		$query = 'select count(*) from #__community_groups_members where memberid = '.$user->id.' and group_id='.$gid.' and approved = 1';
		$db->setQuery($query);
		$count = $db->loadResult();
		
		if($count > 0){
			
			$query = '
				select 
					u.id, u.name, u.email
				from
					#__community_groups_members m
				left join
					#__users u on m.memberid = u.id
				where
					m.groupid = '.$gid;
			
			$db->setQuery($query);
			$users = $db->loadObjectList();
			
			return $users;
		}
		
		return false;
	}
	
	/**
	 * Function to get the login redirect url based on Joomla version.
	 * 
	 * @param string $redirect_url redirect url used after login
	 * @param string $itemid itemid
	 */
	public static function get_login_url($redirect_url, $itemid){
		
		return APP_VERSION == '1.5' 
				? JRoute::_("index.php?option=com_user&view=login".$itemid."&return=".$redirect_url) 
				: JRoute::_("index.php?option=com_users&view=login".$itemid."&return=".$redirect_url);
	}
	
	/**
	 * Function to get browser information. 
	 * Courtesy: 
	 * 	http://www.php.net/manual/en/function.get-browser.php#101125
	 * 	http://www.geekpedia.com/code47_Detect-operating-system-from-user-agent-string.html
	 */
	public static function get_browser($u_agent = null){
		
		$u_agent = !empty($u_agent) ? $u_agent : $_SERVER['HTTP_USER_AGENT'];
		$bname = 'Unknown';
		$platform = 'Unknown';
		$version= "";
		
		$OSList = array(
				'Windows 3.11' => '(Win16)',
				'Windows 95' => '(Windows 95)|(Win95)|(Windows_95)',
				'Windows 98' => '(Windows 98)|(Win98)',
				'Windows 2000' => '(Windows NT 5.0)|(Windows 2000)',
				'Windows 2000 Service Pack 1' => '(Windows NT 5.01)',
				'Windows XP' => '(Windows NT 5.1)|(Windows XP)',
				'Windows Server 2003' => '(Windows NT 5.2)',
				'Windows Vista' => '(Windows NT 6.0)|(Windows Vista)',
				'Windows 7' => '(Windows NT 6.1)|(Windows 7)',
				'Windows 8' => '(Windows NT 6.2)|(Windows 8)',
				'Windows 8.1' => '(Windows NT 6.3)|(Windows 8)',
				'Windows 10' => '(Windows NT 10)|(Windows 10)',
				'Windows NT 4.0' => '(Windows NT 4.0)|(WinNT4.0)|(WinNT)|(Windows NT)',
				'Windows ME' => '(Windows ME)|(Windows 98; Win 9x 4.90 )',
				'Windows CE' => '(Windows CE)',
				'Mac OS X Kodiak (beta)' => '(Mac OS X beta)',
				'Mac OS X Cheetah' => '(Mac OS X 10.0)',
				'Mac OS X Puma' => '(Mac OS X 10.1)',
				'Mac OS X Jaguar' => '(Mac OS X 10.2)',
				'Mac OS X Panther' => '(Mac OS X 10.3)',
				'Mac OS X Tiger' => '(Mac OS X 10.4)',
				'Mac OS X Leopard' => '(Mac OS X 10.5)',
				'Mac OS X Snow Leopard' => '(Mac OS X 10.6)',
				'Mac OS X Lion' => '(Mac OS X 10.7)',
				'Mac OS X Mountain Lion' => '(Mac OS X 10.8)',
				'Mac OS X Mavericks' => '(Mac OS X 10.9)',
				'Mac OS X Yosemite' => '(Mac OS X 10.10)',
				'Mac OS X' => '(Mac OS X)',
				'Mac OS' => '(Mac_PowerPC)|(PowerPC)|(Macintosh)',
				'Open BSD' => '(OpenBSD)',
				'SunOS' => '(SunOS)',
				'Solaris 11' => '(Solaris\/11)|(Solaris11)',
				'Solaris 10' => '((Solaris\/10)|(Solaris10))',
				'Solaris 9' => '((Solaris\/9)|(Solaris9))',
				'CentOS' => '(CentOS)',
				'QNX' => '(QNX)',
				'UNIX' => '(UNIX)',
				'Ubuntu 14.04 LTS' => '(Ubuntu\/14.04)|(Ubuntu 14.04)',
				'Ubuntu 12.10' => '(Ubuntu\/12.10)|(Ubuntu 12.10)',
				'Ubuntu 12.04 LTS' => '(Ubuntu\/12.04)|(Ubuntu 12.04)',
				'Ubuntu 11.10' => '(Ubuntu\/11.10)|(Ubuntu 11.10)',
				'Ubuntu 11.04' => '(Ubuntu\/11.04)|(Ubuntu 11.04)',
				'Ubuntu 10.10' => '(Ubuntu\/10.10)|(Ubuntu 10.10)',
				'Ubuntu 10.04 LTS' => '(Ubuntu\/10.04)|(Ubuntu 10.04)',
				'Ubuntu 9.10' => '(Ubuntu\/9.10)|(Ubuntu 9.10)',
				'Ubuntu 9.04' => '(Ubuntu\/9.04)|(Ubuntu 9.04)',
				'Ubuntu 8.10' => '(Ubuntu\/8.10)|(Ubuntu 8.10)',
				'Ubuntu 8.04 LTS' => '(Ubuntu\/8.04)|(Ubuntu 8.04)',
				'Ubuntu 6.06 LTS' => '(Ubuntu\/6.06)|(Ubuntu 6.06)',
				'Red Hat Linux' => '(Red Hat)',
				'Red Hat Enterprise Linux' => '(Red Hat Enterprise)',
				'Fedora 17' => '(Fedora\/17)|(Fedora 17)',
				'Fedora 16' => '(Fedora\/16)|(Fedora 16)',
				'Fedora 15' => '(Fedora\/15)|(Fedora 15)',
				'Fedora 14' => '(Fedora\/14)|(Fedora 14)',
				'Chromium OS' => '(ChromiumOS)',
				'Google Chrome OS' => '(ChromeOS)',
				'Linux' => '(Linux)|(X11)',
				'OpenBSD' => '(OpenBSD)',
				'FreeBSD' => '(FreeBSD)',
				'NetBSD' => '(NetBSD)',
				'Andriod' => '(Android)',
				'iPod' => '(iPod)',
				'iPhone' => '(iPhone)',
				'iPad' => '(iPad)',
				'OS/8' => '(OS\/8)|(OS8)',
				'Older DEC OS' => '(DEC)|(RSTS)|(RSTS\/E)',
				'WPS-8' => '(WPS-8)|(WPS8)',
				'BeOS' => '(BeOS)|(BeOS r5)',
				'BeIA' => '(BeIA)',
				'OS/2 2.0' => '(OS\/220)|(OS\/2 2.0)',
				'OS/2' => '(OS\/2)|(OS2)',
				'BlackBerry' => '(blackberry)',
				'Search engine or robot' => '(nuhk)|(Googlebot)|(Yammybot)|(Openbot)|(Slurp)|(msnbot)|(Ask Jeeves\/Teoma)|(ia_archiver)'
		);
		
		foreach($OSList as $os=>$match){
			
			// Find a match
			if (preg_match('/'.$match.'/i', $u_agent)){

				$platform = $os;
				break;
			}
		}

		// Next get the name of the useragent yes seperately and for good reason
		$ub = '';
		if(preg_match('/MSIE|trident/i',$u_agent) && !preg_match('/Opera/i',$u_agent)){
			
			$bname = 'Internet Explorer';
			$ub = "MSIE";
		} elseif(preg_match('/Edge/i',$u_agent)){
			
			$bname = 'Microsoft Edge';
			$ub = "Edge";
		} elseif(preg_match('/Firefox/i',$u_agent)){
			
			$bname = 'Mozilla Firefox';
			$ub = "Firefox";
		} elseif(preg_match('/Chrome/i',$u_agent)){
			
			$bname = 'Google Chrome';
			$ub = "Chrome";
		} elseif(preg_match('/Safari/i',$u_agent)){
			
			$bname = 'Apple Safari';
			$ub = "Safari";
		} elseif(preg_match('/Opera/i',$u_agent)){
			
			$bname = 'Opera';
			$ub = "Opera";
		} elseif(preg_match('/Netscape/i',$u_agent)) {
			
			$bname = 'Netscape';
			$ub = "Netscape";
		}
		 
		// finally get the correct version number
		$known = array('Version', $ub, 'other');
		$pattern = '#(?<browser>' . join('|', $known).')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
		
		if (!preg_match_all($pattern, $u_agent, $matches)) {
			
			// we have no matching number just continue
		}
		 
		// see how many we have
		$i = count($matches['browser']);
		
		if ($i != 1) {
			
			//we will have two since we are not using 'other' argument yet
			//see if version is before or after the name
			if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
				
				$version= $matches['version'][0];
			} else {
				
				$version= $matches['version'][1];
			}
		} else {
			
			$version= $matches['version'][0];
		}
		 
		// check if we have a number
		if ($version==null || $version=="") {
			
			$version="Unknown";
		}
		 
		return array(
				'userAgent' => $u_agent,
				'name'      => $bname,
				'version'   => $version,
				'platform'  => $platform,
				'pattern'    => $pattern
		);
	}
	
	/**
	 * Downloads the geolite database file from maxmind database source location.
	 * 
	 * GeoLite data created by MaxMind, available from http://www.maxmind.com
	 */
	public static function download_geoip_databases(){

		$path = JPATH_ROOT.'/media/com_cjlib/geoip/';
		
		if(file_exists($path.'GeoLite2-City.mmdb')){
			
			$filemtime = filemtime($path.'GeoLite2-City.mmdb');
			
			if((time() - $filemtime) >= 30*86400){
			
				JFile::delete($path.'GeoLite2-City.mmdb');
			} else {
				
				return false;
			}
		}
		
		CJFunctions::download_file('http://geolite.maxmind.com/download/geoip/database/GeoLite2-City.mmdb.gz', $path, 'GeoLite2-City.mmdb.gz');
		CJFunctions::uncompress($path.'GeoLite2-City.mmdb.gz', $path.'GeoLite2-City.mmdb');
		
		return true;
	}
	
	/**
	 * Uncompresses a gzipped file to destination.
	 * 
	 * @param string $source source file path
	 * @param string $target destination file path
	 */
	public static function uncompress($source, $target) {
		
		$sfp = gzopen($source, "rb");
		$fp = fopen($target, "w");
	
		while ($string = gzread($sfp, 4096)) {
			fwrite($fp, $string, strlen($string));
		}
		
		gzclose($sfp);
		fclose($fp);
	}
	
	/**
	 * Downloads the file from remote server to specified location on local server. Uses regular file operations or cURL or sockets (whichever available first) to download the file.
	 * 
	 * @param url $source_url url of the source file
	 * @param string $target_folder target folder name, should end with / 
	 * @param string $target_file target file name.
	 */
	public static function download_file($source_url, $target_folder, $target_file){
		
		if(file_exists($target_file)){
			
			JFile::delete($target_file);
		} else {
			
			JFolder::create($target_folder);
		}
		
		//try to connect via fopen
		if (function_exists('fopen') && ini_get('allow_url_fopen')) {
		
			//set socket timeout
			ini_set('default_socket_timeout', 5);

			$handle = fopen ($source_url, 'rb');
			
			if($handle){
				
				$download = fopen($target_folder.$target_file, "wb");
				
				if($download){
					
					while(!feof($handle)) {
						
						fwrite($download, fread($handle, 1024 * 8 ), 1024 * 8 );
					}
				}
			}
			
			if($handle){
				
				fclose($handle);
			}
		}
		
		//try to connect via cURL
		else if(function_exists('curl_init') && function_exists('curl_exec')) {
			
			$fh = fopen ($target_folder.$target_file, "w");
			
			$options = array(
					CURLOPT_FILE => $fh,
					CURLOPT_URL => $source_url,
					CURLOPT_TIMEOUT => 28800,
					CURLOPT_FAILONERROR => 1,
					CURLOPT_HEADER => 0,
					CURLOPT_TIMEOUT => 5
			);
			
			$ch = curl_init();
			curl_setopt_array($ch, $options);
			curl_exec($ch);
			curl_close($ch);
			fclose($fh);
		}
		
		//try to connect via fsockopen
		else if(function_exists('fsockopen') && $data == '') {

			$errno = 0;
			$errstr = '';
			
			$parts = JString::parse_url($source_url);
			$hostname = $parts['host'];
			unset($parts['scheme']);
			unset($parts['host']);
			$filename = CJFunctions::join_url($parts);
			
			//timeout handling: 5s for the socket and 5s for the stream = 10s
			$fsock = fsockopen($hostname, 80, $errno, $errstr, 5);
			
			if ($fsock) {
			
				fputs($fsock, 'GET '.$filename.' HTTP/1.1\r\n');
				fputs($fsock, 'HOST: '.$hostname.'\r\n');
				fputs($fsock, 'Connection: close\r\n\r\n');
			
				//force stream timeout...
				stream_set_blocking($fsock, 1);
				stream_set_timeout($fsock, 5);
			
				$get_info = false;
				$download = fopen($target_folder.$target_file, 'wb');
			
				while (!feof($fsock)) {
						
					if ($get_info) {
			
						fwrite($download, fread($handle, 1024 * 8 ), 1024 * 8 );
					} else {
			
						if (fgets($fsock, 8192) == '\r\n') {
								
							$get_info = true;
						}
					}
				}

				fclose($fsock);
			}
		}
	}
	
	private static function join_url($parts, $encode=true) {
		
		if ( $encode ){
			
			if ( isset( $parts['user'] ) ){
				
				$parts['user'] = rawurlencode( $parts['user'] );
			}
			
			if ( isset( $parts['pass'] ) ){
				
				$parts['pass'] = rawurlencode( $parts['pass'] );
			}
			
			if ( isset( $parts['host'] ) && !preg_match( '!^(\[[\da-f.:]+\]])|([\da-f.:]+)$!ui', $parts['host'] ) ){
				
				$parts['host'] = rawurlencode( $parts['host'] );
			}
			
			if ( !empty( $parts['path'] ) ){
				
				$parts['path'] = preg_replace( '!%2F!ui', '/', rawurlencode( $parts['path'] ) );
			}
			
			if ( isset( $parts['query'] ) ){
				
				$parts['query'] = rawurlencode( $parts['query'] );
			}
			
			if ( isset( $parts['fragment'] ) ){
				
				$parts['fragment'] = rawurlencode( $parts['fragment'] );
			}
		}

		$url = '';
		
		if ( !empty( $parts['scheme'] ) ){
			
			$url .= $parts['scheme'] . ':';
		}
		
		if ( isset( $parts['host'] ) ){
			
			$url .= '//';
			
			if ( isset( $parts['user'] ) ){
				
				$url .= $parts['user'];
				
				if ( isset( $parts['pass'] ) ){ 
					
					$url .= ':' . $parts['pass'];
				}
				
				$url .= '@';
			}
			
			if ( preg_match( '!^[\da-f]*:[\da-f.:]+$!ui', $parts['host'] ) ){
				
				$url .= '[' . $parts['host'] . ']'; // IPv6
			} else {
				
				$url .= $parts['host'];             // IPv4 or name
			}
			
			if ( isset( $parts['port'] ) ){
				
				$url .= ':' . $parts['port'];
			}
			
			if ( !empty( $parts['path'] ) && $parts['path'][0] != '/' ){
				
				$url .= '/';
			}
		}
		
		if ( !empty( $parts['path'] ) ){
			
			$url .= $parts['path'];
		}
		
		if ( isset( $parts['query'] ) ){
			
			$url .= '?' . $parts['query'];
		}
		
		if ( isset( $parts['fragment'] ) ){
			
			$url .= '#' . $parts['fragment'];
		}
		
		return $url;
	}
	
	public static function send_messages_from_queue($records = 60, $delay = 0, $simulated = true, $force_ids = array()){
		
		if($simulated){

			$cjconfig = CJLib::get_cjconfig();
			
			if($cjconfig['manual_cron'] == '1'){
			
				require_once CJLIB_PATH.'/framework/virtualcron.php';
				
				$delay = $delay > 0 ? $delay : intval($cjconfig['cron_delay']);
				$vcron = new virtualcron($delay, 'virtualcron.txt');
				
				if (!$vcron->allowAction()){
					
					return false;
				}
			} else {
				
				return false;
			}
		}

		$db = JFactory::getDbo();
		$app = JFactory::getApplication();
		
		$from = $app->getCfg('mailfrom' );
		$fromname = $app->getCfg('fromname' );
		$message_ids = array();
		$sent = array();
		
		$query = $db->getQuery(true);
		$query
			->select('id, to_addr, cc_addr, bcc_addr, html, message_id, params')
			->from('#__corejoomla_messagequeue');
		
		if(!empty($force_ids)){
			
			$query->where('id in ('.implode(',', $force_ids).') and status = 0');
		} else {
			
			$query->where('status = 0');
		}
		
		$db->setQuery($query, 0, $records);
		$queue_items = array();
		
		try {
		
			$queue_items = $db->loadObjectList();
		} catch (Exception $e){
			
			return false;
		}

		if(!empty($queue_items)){
			
			foreach($queue_items as $item){
				
				$message_ids[] = $item->message_id;
			}
			
			$message_ids = array_unique($message_ids);
			
			$query = $db->getQuery(true);
			
			$query
				->select('id, asset_id, asset_name, subject, description, params')
				->from('#__corejoomla_messages')
				->where('id in ('.implode(',', $message_ids).')');
			
			$db->setQuery($query);
			$messages = array();
			
			try{
					
				$messages = $db->loadObjectList('id');
			} catch (Exception $e){
			
				return false;
			}
			
			if(!empty($messages)){
				
				$template_path = CJLIB_PATH.'/framework/mail_templates/';
				
				foreach ($messages as &$message){
					
					$params = json_decode($message->params);
					
					if(!empty($params->template) && JFile::exists($template_path.$params->template)){
						
						$template = file_get_contents($template_path.$params->template);
						
						if(!empty($params->placeholders)){
							
							foreach ($params->placeholders as $key=>$value){
								
								$template = str_replace($key, $value, $template);
							}
						}
						
						$message->description = str_replace('{description}', $message->description,  $template);
					}
				}
				
				$ids = array();
				
				foreach ($queue_items as $item){
					
					$ids[] = $item->id;
					
					if(!empty($messages[$item->message_id])){
						
						$description = $messages[$item->message_id]->description;

						if(!empty($item->params)){
							
							$params = json_decode($item->params);
							
							if(!empty($params->placeholders)){
								
								foreach ($params->placeholders as $key=>$value){
									
									$description = str_replace($key, $value, $description);
								}
							}
						}
						
						try{
							
							$return = CJFunctions::send_email(
									$from, $fromname, $item->to_addr, $messages[$item->message_id]->subject, $description, $item->html, $item->cc_addr, $item->bcc_addr);
							
							if($return === true){
								
								$sent[] = $item->id;
							}
						}catch (Exception $e){
							
							// Add logger
							$date = JFactory::getDate()->format('Y.m.d');
							JLog::addLogger(array('text_file' => 'com_cjlib.'.$date.'.log.php'), JLog::ALL, 'com_cjlib');
							JLog::add('Send Messages From Queue - Error: '.print_r($e, true), JLog::ERROR, 'com_cjlib');
						}
					}
				}
				
				if(!empty($ids)){
					
					$created = JFactory::getDate()->toSql();
					$query = $db->getQuery(true);
					
					$query
						->update($db->qn('#__corejoomla_messagequeue'))
						->set($db->qn('status').' = 1, processed = '.$db->q($created))
						->where('id in ('.implode(',', $ids).')');
					
					$db->setQuery($query);
					
					try{
					
						$db->execute();
					} catch (Exception $e){
						
						return false;
					}
				}
			}
		}
		
		return $sent;
	}
	
	/**
	 * This function will add paragraph tags around textual content of an HTML file, leaving the HTML itself intact.
	 * This function assumes that the HTML syntax is correct and that the '<' and '>' characters
	 * are not used in any of the values for any tag attributes. If these assumptions are not met, mass paragraph chaos may ensue. Be safe.
	 * 
	 * credits: http://stackoverflow.com/questions/5961217/how-do-i-surround-all-text-pieces-with-paragraph-tags
	 * 
	 * @param unknown_type $str
	 */
	public static function nl2p_html($str) {
	
		// If we find the end of an HTML header, assume that this is part of a standard HTML file. Cut off everything including the
		// end of the head and save it in our output string, then trim the head off of the input. This is mostly because we don't
		// want to surrount anything like the HTML title tag or any style or script code in paragraph tags.
		if(strpos($str,'</head>')!==false) {
			$out=substr($str,0,strpos($str,'</head>')+7);
			$str=substr($str,strpos($str,'</head>')+7);
		}
	
		// First, we explode the input string based on wherever we find HTML tags, which start with '<'
		$arr=explode('<',$str);
	
		// Next, we loop through the array that is broken into HTML tags and look for textual content, or
		// anything after the >
		for($i=0;$i<count($arr);$i++) {
			
			if(strlen(trim($arr[$i]))>0) {
				
				// Add the '<' back on since it became collateral damage in our explosion as well as the rest of the tag
				$html='<'.substr($arr[$i],0,strpos($arr[$i],'>')+1);
	
				// Take the portion of the string after the end of the tag and explode that by newline. Since this is after
				// the end of the HTML tag, this must be textual content.
				$sub_arr=explode("\n",substr($arr[$i],strpos($arr[$i],'>')+1));
	
				// Initialize the output string for this next loop
				$paragraph_text='';
	
				// Loop through this new array and add paragraph tags (<p>...</p>) around any element that isn't empty
				for($j=0;$j<count($sub_arr);$j++) {
					
					if(strlen(trim($sub_arr[$j]))>0)
						
						$paragraph_text.='<p>'.trim($sub_arr[$j]).'</p>';
				}
	
				// Put the text back onto the end of the HTML tag and put it in our output string
				$out.=$html.$paragraph_text;
			}
	
		}
	
		// Throw it back into our program
		return $out;
	}
	
	public static function get_supported_avatars(){
		
		return array(
				'NA' => JText::_('COM_CJLIB_NONE'),
				'cjblog' => JText::_('COM_CJLIB_EXTENSION_CJBLOG'),
				'gravatar' => JText::_('COM_CJLIB_EXTENSION_GRAVATAR'),
				'jomsocial' => JText::_('COM_CJLIB_EXTENSION_JOMSOCIAL'),
				'cb' => JText::_('COM_CJLIB_EXTENSION_COMMUNITY_BUILDER'),
				'kunena' => JText::_('COM_CJLIB_EXTENSION_KUNENA'),
				'aup' => JText::_('COM_CJLIB_EXTENSION_ALPHA_USERPOINTS'),
				'touch' => JText::_('COM_CJLIB_EXTENSION_MIGHTY_TOUCH')
			);
	}
	
	public static function get_supported_streams(){
	
		return array(
				'NA' => JText::_('COM_CJLIB_NONE'),
				'jomsocial' => JText::_('COM_CJLIB_EXTENSION_JOMSOCIAL'),
				'touch' => JText::_('COM_CJLIB_EXTENSION_MIGHTY_TOUCH')
		);
	}
	
	public static function get_supported_point_systems(){
	
		return array(
				'NA' => JText::_('COM_CJLIB_NONE'),
				'cjblog' => JText::_('COM_CJLIB_EXTENSION_CJBLOG'),
				'jomsocial' => JText::_('COM_CJLIB_EXTENSION_JOMSOCIAL'),
				'aup' => JText::_('COM_CJLIB_EXTENSION_ALPHA_USERPOINTS'),
				'touch' => JText::_('COM_CJLIB_EXTENSION_MIGHTY_TOUCH')
		);
	}
	
	public static function get_available_email_templates(){
		
		$templates = array();
		$path = CJLIB_PATH.'/framework/mail_templates';
		
		if(file_exists($path)){
		
			$templates = JFolder::files($path, '*.tpl');
		}
		
		return $templates;
	}
	
	public static function get_supported_editors(){
	
		return array(
				'default' => JText::_('COM_CJLIB_WYSIWYG_EDITOR'),
				'bbcode' => JText::_('COM_CJLIB_BBCODE_EDITOR')
		);
	}
	
	public static function store_rating($asset_id, $item_id, $rating, $user_id = 0, $action_id = 0){
		
		$db = JFactory::getDBO();
		
		if($user_id > 0){
			
			$query = 'select count(*) from '.T_CJ_RATING_DETAILS.' where item_id = '.$item_id.' and created_by = '.$user_id.' and asset_id = 1';
			$db->setQuery($query);
			$count = $db->loadResult();
			
			if($count > 0){
				
				return -1;
			}
		}
		
		$createdate = JFactory::getDate()->toSql();
		
		$query = '
				insert into
					#__corejoomla_rating_details(asset_id, item_id, action_id, rating, created_by, created)
				values
					('.$asset_id.','.$item_id .','.$action_id.','.$rating.','.$user_id.','.$db->quote($createdate).')';
		
		$db->setQuery($query);
			
		if($db->query() && $db->getAffectedRows() > 0){
		
			$query = '
				insert into
					#__corejoomla_rating(item_id, asset_id, total_ratings, sum_rating, rating)
				values
					('.$item_id.','.$asset_id.', total_ratings + 1, sum_rating + '.$rating.', sum_rating / total_ratings)
				on duplicate key update
					total_ratings = total_ratings + 1,
					sum_rating = sum_rating + '.$rating.',
					rating = sum_rating / total_ratings';
		
			$db->setQuery($query);
			
			if($db->query()){
				
				return true;
			}
		}
		
		return false;
	}
	
	public static function get_rating($asset_id, $item_id){
		
		$db = JFactory::getDbo();
		
		$query = '
			select 
				total_ratings, sum_rating, rating 
			from 
				#__corejoomla_rating 
			where 
				item_id = '.$item_id.' and asset_id = '.$asset_id;
		
		$db->setQuery($query, 0, 1);
		$rating = $db->loadAssoc();
		
		$rating['total_ratings'] = !empty($rating['total_ratings']) ? $rating['total_ratings'] : '0';
		$rating['sum_rating'] = !empty($rating['sum_rating']) ? $rating['sum_rating'] : '0';
		$rating['rating'] = !empty($rating['rating']) ? $rating['rating'] : '0';
		
		return $rating;
	}
	
	public static function quoteName($name, $db){
		
		return APP_VERSION == '1.5' ? $db->nameQuote($name) : $db->quoteName($name);
	}
	
	public static function get_categories_table_markup($categories, $options = array()){
		
		$max_columns = isset($options['max_columns']) ? $options['max_columns'] : 3;
		$max_children = isset($options['max_children']) ? $options['max_children'] : 0;
		$base_url = isset($options['base_url']) ? $options['base_url'] : '';
		$menu_id = isset($options['menu_id']) ? $options['menu_id'] : '';
		$attribs = isset($options['link_attribs']) ? $options['link_attribs'] : array();
		$stat_primary = isset($options['stat_primary']) ? $options['stat_primary'] : null;
		$stat_secondary = isset($options['stat_secondary']) ? $options['stat_secondary'] : null;
		$stat_tooltip = isset($options['stat_tooltip']) ? $options['stat_tooltip'] : null;
		
		$num_rows = ceil(count($categories) / $max_columns);
		$table = '<div class="row-fluid">';
		$spanNum = 12/$max_columns;
		$colspan = 'span'.$spanNum.' col-lg-'.$spanNum.' col-md-'.$spanNum.' col-sm-'.$spanNum;
		$itemid = 0;
		
		foreach($categories as $category){
			
			if($itemid % $num_rows == 0) $table = $table.'<div class="'.$colspan.' no-space-left">';
			
			$url = JRoute::_($base_url.'&id='.$category['id'].':'.$category['alias'].$menu_id);
			$title = CJFunctions::escape($category['title']);
			
			if(!empty($stat_primary) && !empty($stat_secondary)){
				
				$title = $title.' <span class="muted text-muted">('.$category[$stat_primary].'/'.$category[$stat_secondary].')</span>';
				$attribs['title'] = !empty($stat_tooltip) ? JText::sprintf($stat_tooltip, $category['title'], $category[$stat_primary], $category[$stat_secondary]) : '';
				$attribs['class'] = !empty($attribs['class']) ? $attribs['class'].' tooltip-hover' : 'tooltip-hover';
			} else if(!empty($stat_primary)){
				
				$title = $title.' <span class="muted text-muted">('.$category[$stat_primary].')</span>';
				$attribs['title'] = !empty($stat_tooltip) ? JText::sprintf($stat_tooltip, $category['title'], $category[$stat_primary]) : '';
				$attribs['class'] = !empty($attribs['class']) ? $attribs['class'].' tooltip-hover' : 'tooltip-hover';
			}
			
			$table = $table.'<ul class="unstyled list-unstyled no-space-left">';
			$table = $table.'<li class="parent-item">'.JHtml::link($url, $title, $attribs).'</li>';
			
			if($max_children > 0 && count($category['children']) > 0){
				
				$child_count = 0;
				
				foreach($category['children'] as $child){
					
					$url = JRoute::_($base_url.'&id='.$child['id'].':'.$child['alias'].$menu_id);
					$title = CJFunctions::escape($child['title']);
					
					if(!empty($stat_primary) && !empty($stat_secondary)){
						
						$title = $title.' ('.$child[$stat_primary].' / '.$child[$stat_secondary].')';
						$attribs['title'] = !empty($stat_tooltip) ? JText::sprintf($stat_tooltip, $child['title'], $stat_primary, $stat_secondary) : '';
						$attribs['class'] = !empty($attribs['class']) ? $attribs['class'].' tooltip-hover' : 'tooltip-hover';
					} else if(!empty($stat_primary)){
						
						$title = $title.' ('.$child[$stat_primary].')';
						$attribs['title'] = !empty($stat_tooltip) ? JText::sprintf($stat_tooltip, $child['title'], $stat_primary) : '';
						$attribs['class'] = !empty($attribs['class']) ? $attribs['class'].' tooltip-hover' : 'tooltip-hover';
					}
					
					$table = $table.'<li class="child-item">'.JHtml::link($url, $title, $attribs).'</li>';
					
					if($child_count + 1 == $max_children) break;
					
					$child_count++;
				}
			}
			
			$table = $table.'</ul>';
			
			if(($itemid % $num_rows == $num_rows - 1) || ($itemid + 1 == count($categories))) $table = $table.'</div>';
			
			$itemid++;
		}
		
		$table = $table.'</div>';
		
		return $table;
	}

	public static function get_joomla_categories_table_markup($categories, $options = array()){
	
		$max_columns = isset($options['max_columns']) ? $options['max_columns'] : 3;
		$max_children = isset($options['max_children']) ? $options['max_children'] : 0;
		$base_url = isset($options['base_url']) ? $options['base_url'] : '';
		$menu_id = isset($options['menu_id']) ? $options['menu_id'] : '';
		$attribs = isset($options['link_attribs']) ? $options['link_attribs'] : array();
		$stat_primary = isset($options['stat_primary']) ? $options['stat_primary'] : null;
		$stat_secondary = isset($options['stat_secondary']) ? $options['stat_secondary'] : null;
		$stat_tooltip = isset($options['stat_tooltip']) ? $options['stat_tooltip'] : null;
	
		$num_rows = ceil(count($categories) / $max_columns);
		$table = '<div class="row-fluid">';
		$colspan = 'span'.(12/$max_columns);
		$itemid = 0;
	
		foreach($categories as $category){
				
			if($itemid % $num_rows == 0) $table = $table.'<div class="'.$colspan.'">';
				
			$url = JRoute::_($base_url.'&id='.$category->id.':'.$category->alias.$menu_id);
			$title = CJFunctions::escape($category->title);
				
			if(!empty($stat_primary) && !empty($stat_secondary)){
	
				$title = $title.' <small><span class="muted text-muted">('.$category->$stat_primary.'/'.$category->$stat_secondary.')</span></small>';
				$attribs['title'] = !empty($stat_tooltip) ? JText::sprintf($stat_tooltip, $category->title, $category->$stat_primary, $category->$stat_secondary) : '';
				$attribs['class'] = !empty($attribs['class']) ? $attribs['class'].' tooltip-hover' : 'tooltip-hover';
			} else if(!empty($stat_primary)){
	
				$title = $title.' <small><span class="muted text-muted">('.$category->$stat_primary.')</span></small>';
				$attribs['title'] = !empty($stat_tooltip) ? JText::sprintf($stat_tooltip, $category->title, $category->$stat_primary) : '';
				$attribs['class'] = !empty($attribs['class']) ? $attribs['class'].' tooltip-hover' : 'tooltip-hover';
			}
				
			$table = $table.'<ul class="unstyled list-unstyled no-space-left">';
			$table = $table.'<li class="parent-item">'.JHtml::link($url, $title, $attribs).'</li>';
				
			if($max_children > 0 && count($category->children) > 0){
	
				$child_count = 0;
	
				foreach($category->children as $child){
						
					$url = JRoute::_($base_url.'&id='.$child['id'].':'.$child['alias'].$menu_id);
					$title = CJFunctions::escape($child['title']);
						
					if(!empty($stat_primary) && !empty($stat_secondary)){
	
						$title = $title.' ('.$child[$stat_primary].' / '.$child[$stat_secondary].')';
						$attribs['title'] = !empty($stat_tooltip) ? JText::sprintf($stat_tooltip, $child['title'], $stat_primary, $stat_secondary) : '';
						$attribs['class'] = !empty($attribs['class']) ? $attribs['class'].' tooltip-hover' : 'tooltip-hover';
					} else if(!empty($stat_primary)){
	
						$title = $title.' ('.$child[$stat_primary].')';
						$attribs['title'] = !empty($stat_tooltip) ? JText::sprintf($stat_tooltip, $child['title'], $stat_primary) : '';
						$attribs['class'] = !empty($attribs['class']) ? $attribs['class'].' tooltip-hover' : 'tooltip-hover';
					}
						
					$table = $table.'<li class="child-item">'.JHtml::link($url, $title, $attribs).'</li>';
						
					if($child_count + 1 == $max_children) break;
						
					$child_count++;
				}
			}
				
			$table = $table.'</ul>';
				
			if(($itemid % $num_rows == $num_rows - 1) || ($itemid + 1 == count($categories))) $table = $table.'</div>';
				
			$itemid++;
		}
	
		$table = $table.'</div>';
	
		return $table;
	}
	
	/**
	 * A private function that is used to initialize kunena app
	 * 
	 * @return boolean true if success, false if not compatible kunena installation found
	 */
	private static function _initialize_kunena(){
		
		if (!(class_exists('KunenaForum') && KunenaForum::isCompatible('2.0') && KunenaForum::installed())) {
			
			return false;
		}
		
		KunenaForum::setup();
		
		return true;
	}
	
	/**
	 * Gets the html markup for listing Joomla categories in a column list format, found in cjblog categories listing page
	 * 
	 * @param list $categories the categories flat list to be displayed
	 * @param JObject $params the params object of the component, should contain
	 * <pre>
	 *      <strong>exclude_categories:</strong> array of category ids to exclude from adding to table
	 *      <strong>max_category_columns:</strong> maximum number of columns displayed in table
	 *      <strong>show_cat_num_articles:</strong> if set, displays number of items in the category
	 *      <strong>show_base_description:</strong> if set, displays the description of the item
	 *      <strong>show_base_image:</strong> if category has params, displays image set in params 
	 *      <strong>max_category_subitems:</strong> number of child items need to be displayed below each category
	 * </pre> 
	 * 
	 * @param array $options list of options to alter behavior
	 * <pre>
	 *      <strong>class:</strong> the class name added to the container, default is category-table
	 *      <strong>base_url:</strong> the url should be used as base to build href for each category, id:alias is automatically added to it
	 *      <strong>itemid:</strong> the itemid to be added to the url before passing to JRoute
	 * </pre>
	 * @return string the final markup of the column formatted categories listing 
	 */
	public static function get_joomla_categories_table($categories, $params, $options){
		
		if(empty($categories)) return '';
		
		//************************** PARAMS *********************************//
		$class = isset($options['class']) ? $options['class'] : 'category-table';
		$base_url = $options['base_url'];
		$itemid = $options['itemid'];
		$categories_excluded = $params->get('exclude_categories', array());
		//************************** PARAMS *********************************//
		$count_of_excluded = 0;
		
		foreach ($categories as $category){
			
			if(in_array($category->id, $categories_excluded)){
				
				$count_of_excluded++;
			}
		}
		
		$content = '<div class="'.$class.'" id="'.$class.'"><div class="row-fluid">';
		$column_span = 12 / $params->get('max_category_columns', 3);
		$categories_per_column = ceil((count($categories) - $count_of_excluded) / $params->get('max_category_columns', 3));
		$num_subcategories = 0;
		$i = 0;
		
		foreach ($categories as $category){
			
			if(!in_array($category->id, $categories_excluded)){
				
				if($i % $categories_per_column == 0){
					
					$content = $content .'<div class="span'.$column_span.'">';
				}
				
				$content = $content . '<ul class="category"><li class="parent">';
				$content = $content . '<a href="'.JRoute::_($base_url.'&id='.$category->id.':'.$category->alias.$itemid).'">'.CJFunctions::escape($category->title).'</a>';
				
				if($params->get('show_cat_num_articles')){
					
					$content = $content . ' <span class="muted text-muted">('.$category->numitems.')</span>';
				}
				
				if($params->get('show_base_description')){
					
					$content = $content . '<div>'.$category->description.'</div>';
				}
				
				
				if($params->get('show_base_image')){
					
					$category_params = json_decode($category->params);
					
					if(!empty($category_params) && !empty($category_params->image)){
						
						$content = $content . '<img class="img-polaroid padbottom-5" src="'.$category_params->image.'"/>';
					}
				}
				
				$content = $content . '</li>';
				
				$children = $category->getChildren();
				
				if(!empty($children)){
					
					$num_subcategories = 1;
					
					foreach ($children as $child){
						
						if(!in_array($child->id, $categories_excluded)){
							
							$content = $content.'<li>';
							$content = $content.'<a href="'.JRoute::_($base_url.'&id='.$child->id.':'.$child->alias.$itemid).'">'.CJFunctions::escape($child->title).'</a>';
										
							if($params->get('show_cat_num_articles')){
								
								$content = $content.' <span class="muted text-muted">('.$child->numitems.')</span></li>';
							}
							
							$content = $content.'</li>';
						}
						
						if($num_subcategories == $params->get('max_category_subitems')){
							
							break;
						}
					}
				}
				
				$content = $content . '</ul>';
				
				if(($i % $categories_per_column == $categories_per_column - 1) || ($i+1 == count($categories))){
					
					$content = $content .'</div>';
				}
				
				$i++;
			} // end if the category not excluded
		} // end for
		
		$content = $content . '</div></div>';
		
		return $content;
	}
	
	/**
	 * Loads the language strings of the component in the order en-GB, en-GB.overrides, current languages, current language overrides
	 * @param string $component the component name for which language should be loaded
	 * @param string $site if set loads site language else loads admin languages 
	 */
	public static function load_component_language($component, $site = true){

		$location = $site ? JPATH_ROOT : JPATH_ADMINISTRATOR;
		
		//************************ Language loading *************************************/
		$user = JFactory::getUser();
		$jlang = JFactory::getLanguage();
		
		$jlang->load($component, $location, 'en-GB', true);
		$jlang->load($component.'.override', $location, 'en-GB', true);
		$jlang->load($component, $location, $jlang->getTag(), true);
		$jlang->load($component.'.override', $location, $jlang->getTag(), true);
		
		if(!$user->guest){
			
			jimport('joomla.registry.registry');
			
			$uparams = is_object($user->params) ? $user->params : new JRegistry($user->params);
			$userlang = '';
		
			if(APP_VERSION >= 3) {
				$userlang = $uparams->get('language', '');
			} else {
				$userlang = $uparams->getValue('language', '');
			}
		
			if(!empty($userlang)) {
		
				$jlang->load($component, $location, $userlang, true);
				$jlang->load($component.'.override', $location, $userlang, true);
			}
		}
		//************************ Language loading *************************************/
	}
	
	/**
	 * Checks if the countries table has the language specific country names added. If yes, returnes the language code otherwise returns *
	 * @return string language code if the coutry codes exist for the current user language else *
	 */
	public static function get_country_language(){
		
		$db = JFactory::getDbo();
		$language = JFactory::getLanguage();
		
		$query = $db->getQuery(true);
		$query
			->select('count(*)')
			->from('#__corejoomla_countries')
			->where('language = '.$db->quote($language->getTag()));
		
		$db->setQuery($query);
		$count = $db->loadResult();
		
		return $count > 0 ? $language->getTag() : '*';
	}
	
	/**
	 * Gets the list of countries filtered by the user language if available, otherwise gets countries of language *
	 * 
	 * @return Array List of countries 
	 */
	public static function get_country_names(){
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$language = CJFunctions::get_country_language();
		
		$query
			->select('country_code, country_name')
			->from('#__corejoomla_countries')
			->where('language = '.$db->q($language))
			->order('country_name');
		
		$db->setQuery($query);
		$countries = $db->loadObjectList('country_code');
		
		return !empty($countries) ? $countries : array();
	}
	
	public static function get_country_name($country_code){
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$language = CJFunctions::get_country_language();
		
		$query
			->select('country_name')
			->from('#__corejoomla_countries')
			->where('country_code = '.$db->q($country_code).' and language = '.$db->q($language));
		
		$db->setQuery($query);
		$country = $db->loadResult();
		
		return $country;
	}
	
	/**
	 * Gets the first image location in the html data provided, if the data contains img tags.
	 * 
	 * @param string $html the data where the search is performed
	 * @return string src attribute of the first img tag if found
	 */
	public static function get_first_image($html){
	
		preg_match_all('/<img .*src=["|\']([^"|\']+)/i', $html, $matches);
	
		foreach ($matches[1] as $key=>$value) {
				
			return $value;
		}
	
		return '';
	}
	
	public static function compare_string_dates($compare, $compare_to){
		
		$date1 = JFactory::getDate($compare);
		$date2 = JFactory::getDate($compare_to);
		
		return $date1 > $date2 ? 1 : ($date1 == $date2 ? 0 : -1);
	}
	
	/**
	 * Compare str_date1 with str_date2 and return true if str_date1 is later than str_date2 else false, str_date1 must not be null value.
	 * Returns default value if the second date is empty or blank date
	 * 
	 * @param string $str_date1
	 * @param string $str_date2
	 */
	public static function is_first_date_later($str_date1, $str_date2, $tz, $default = true){
		
		if(!empty($str_date2) && ($str_date2 != '0000-00-00 00:00:00')) {

			$date_1 = JFactory::getDate();
			$date_1->setTimeZone($tz);
			 
			$date_2 = JFactory::getDate($str_date2);
			$date_2->setTimeZone($tz);
			
			return $date_1 > $date_2;
		}
		
		return $default;
	}
	
	public static function get_hash($seed){
		
		// replace this with JApplicationHelper::getHash when Joomla 2.5 support no more exist
		return md5(JFactory::getConfig()->get('secret') . $seed);
	}
	
	public static function get_user_points($system, $userid = 0){
		
		if(!$userid) return 0;
		$app = JFactory::getApplication ();
		
		switch ($system){
			
			case 'cjblog':
				
				$api = JPATH_ROOT.'/components/com_cjblog/api.php';
				if(file_exists($api)){
					
					include_once $api;
					$profile = CjBlogApi::get_user_profile($userid);
					
					if(!empty($profile)){
						
						return $profile['points'];
					}
				}
				
				break;
				
			case 'aup':
				
				$api_AUP = JPATH_SITE.'/components/com_alphauserpoints/helper.php';
				if ( file_exists($api_AUP)){
					
					require_once ($api_AUP);
					$profile = AlphaUserPointsHelper::getUserInfo('', $userid);
					
					if(!empty($profile)){
						
						return $profile->points;
					}
				}
				
				break;
				
			case 'jomsocial':
				
				$db = JFactory::getDbo();
				$query = 'select points from #__community_users where userid='.((int)$userid);
				$db->setQuery($query);
				return (int)$db->loadResult();
				
			case 'easysocial':
				
				require_once JPATH_ADMINISTRATOR . '/components/com_easysocial/includes/foundry.php';
				$my = Foundry::user($userid);
				$points = $my->getPoints();
				return $points;
				
			default:
				
				return 0;
		}
		
		return -1;
	}
	
	public static function format_number ($num)
	{
		$num = (int) $num;
		if ($num < 1000) 
		{
			return $num;
		}
		
		if ($num < 10000) 
		{
			return substr($num, 0, 1).','.substr($num, 1);
		}
		
		return round($num/1000, 1).'k';
	}
}