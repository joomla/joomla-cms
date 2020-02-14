<?php
/**
 * @version $Id$
 * @package DJ-ImageSlider
 * @subpackage DJ-ImageSlider Component
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 *
 * DJ-ImageSlider is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-ImageSlider is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-ImageSlider. If not, see <http://www.gnu.org/licenses/>.
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
jimport('joomla.filesystem.file');

// Include the syndicate functions only once
require_once (dirname(__FILE__).DS.'helper.php');
$app = JFactory::getApplication();
$document = JFactory::getDocument();

// taking the slides from the source
if($params->get('slider_source')==1) {
	jimport('joomla.application.component.helper');
	if(!JComponentHelper::isEnabled('com_djimageslider', true)){
		$app->enqueueMessage(JText::_('MOD_DJIMAGESLIDER_NO_COMPONENT'),'notice');
		return;
	}
	$slides = modDJImageSliderHelper::getImagesFromDJImageSlider($params);
	if($slides==null) {
		$app->enqueueMessage(JText::_('MOD_DJIMAGESLIDER_NO_CATEGORY_OR_ITEMS'),'notice');
		return;
	}
} else {
	$slides = modDJImageSliderHelper::getImagesFromFolder($params);
	if($slides==null) {
		$app->enqueueMessage(JText::_('MOD_DJIMAGESLIDER_NO_CATALOG_OR_FILES'),'notice');
		return;
	}
}

$direction = $document->direction;
// direction integration with joomla monster templates
if ($app->input->get('direction') == 'rtl'){
	$direction = 'rtl';
} else if ($app->input->get('direction') == 'ltr') {
	$direction = 'ltr';
} else {
	if (isset($_COOKIE['jmfdirection'])) {
		$direction = $_COOKIE['jmfdirection'];
	} else {
		$direction = $app->input->get('jmfdirection', $direction);
	}
}
$params->set('direction', $direction);

$theme = $params->get('theme', 'default');

if($theme != '_override') {
	$css = 'modules/mod_djimageslider/themes/'.$theme.'/css/djimageslider.css';
} else {
	$theme = 'override';
	$css = 'templates/'.$app->getTemplate().'/css/djimageslider.css';
}
// add only if theme file exists
if(JFile::exists(JPATH_ROOT . DS . $css)) {
	$document->addStyleSheet(JURI::root(true).'/'.$css);
}
if($direction == 'rtl') { // load rtl css if exists in theme or joomla template
	$css_rtl = JFile::stripExt($css).'_rtl.css';
	if(JFile::exists(JPATH_ROOT . DS . $css_rtl)) {
		$document->addStyleSheet(JURI::root(true).'/'.$css_rtl);
	}
}

$jquery = version_compare(JVERSION, '3.0.0', '>=');
$canDefer = preg_match('/(?i)msie [6-9]/', @$_SERVER['HTTP_USER_AGENT']) ? false : true;

$db = JFactory::getDBO();
$db->setQuery("SELECT manifest_cache FROM #__extensions WHERE element='mod_djimageslider' LIMIT 1");
$ver = json_decode($db->loadResult());
$ver = $ver->version;

if ($jquery) {
	JHTML::_('jquery.framework');
	if (version_compare(JVERSION, '4', '>=')) {
		$document->addScript(JURI::root(true).'/media/djextensions/jquery.easing-1.4.1/jquery.easing.min.js', array('mime'=>'text/javascript', 'defer'=>$canDefer));
	} else {
		$document->addScript(JURI::root(true).'/media/djextensions/jquery-easing/jquery.easing.min.js', array('mime'=>'text/javascript', 'defer'=>$canDefer));
	}
	$document->addScript(JURI::root(true).'/modules/mod_djimageslider/assets/js/slider.js?v='.$ver, array('mime'=>'text/javascript', 'defer'=>$canDefer));
} else {
	JHTML::_('behavior.framework', true);
	$document->addScript(JURI::root(true).'/modules/mod_djimageslider/assets/js/moo.slider.js?v='.$ver, array('mime'=>'text/javascript', 'defer'=>$canDefer));
}

if($params->get('link_image',1) > 1) {
	if($jquery) {
		$document->addScript(JURI::root(true).'/media/djextensions/magnific/magnific.js', array('mime'=>'text/javascript', 'defer'=>$canDefer));
		$document->addStyleSheet(JURI::root(true).'/media/djextensions/magnific/magnific.css');
		$document->addScript(JURI::root(true).'/modules/mod_djimageslider/assets/js/magnific-init.js', array('mime'=>'text/javascript', 'defer'=>$canDefer));
	} else {
		$document->addScript(JURI::root(true).'/modules/mod_djimageslider/assets/slimbox/js/slimbox.js', array('mime'=>'text/javascript', 'defer'=>$canDefer));
		$document->addStyleSheet(JURI::root(true).'/modules/mod_djimageslider/assets/slimbox/css/slimbox.css');
	}
}

if(!is_numeric($width = $params->get('image_width'))) $width = 240;
if(!is_numeric($height = $params->get('image_height'))) $height = 180;
if(!is_numeric($max = $params->get('max_images'))) $max = 20;
if(!is_numeric($count = $params->get('visible_images'))) $count = 3;
if(!is_numeric($spacing = $params->get('space_between_images'))) $spacing = 10;
if(!is_numeric($preload = $params->get('preload'))) $preload = 800;
if($count>count($slides)) $count = count($slides);
if($count<1) $count = 1;
if($count>$max) $count = $max;
$mid = $module->id;
$slider_type = $params->get('slider_type',0);
switch($slider_type){
	case 2:
		$slide_size = $width;
		$count = 1;
		break;
	case 1:
		$slide_size = $height + $spacing;
		break;
	case 0:
	default:
		$slide_size = $width + $spacing;
		break;
}

$animationOptions = modDJImageSliderHelper::getAnimationOptions($params);
$moduleSettings = json_encode(array('id' => $mid, 'slider_type' => $slider_type, 'slide_size' => $slide_size, 'visible_slides' => $count, 'direction' => $direction == 'rtl' ? 'right':'left',
	'show_buttons' => $params->get('show_buttons',1), 'show_arrows' => $params->get('show_arrows',1), 'preload' => $preload, 'css3' => $params->get('css3', 0)
));

$style = modDJImageSliderHelper::getStyles($params);
$navigation = modDJImageSliderHelper::getNavigation($params,$mid);
$show = (object) array('arr'=>$params->get('show_arrows'), 'btn'=>$params->get('show_buttons'), 'idx'=>$params->get('show_custom_nav'));

require JModuleHelper::getLayoutPath('mod_djimageslider', $params->get('layout','default'));
