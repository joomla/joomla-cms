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
defined('_JEXEC') or die ('Restricted access');

class modDJImageSliderHelper
{
    static function getImagesFromFolder(&$params) {
    	
    	if(!is_numeric($max = $params->get('max_images'))) $max = 20;
        $folder = $params->get('image_folder');
        if(!$dir = @opendir($folder)) return null;
        while (false !== ($file = readdir($dir)))
        {
            if (preg_match('/.+\.(jpg|jpeg|gif|png)$/i', $file)) {
            	// check with getimagesize() which attempts to return the image mime-type 
            	$path = JPath::clean(JPATH_ROOT.DS.$folder.DS.$file);
            	if(getimagesize($path)!==FALSE) $files[filemtime($path).$file] = $file;
			}
        }
        closedir($dir);
        
        $sort = $params->get('sort_by');
        
        switch($sort) {
        	case 0:
        		shuffle($files);
        		break;
        	case 3:
        	case 4:
        		ksort($files);
        		break;
        	default:
        		natcasesort($files);
        		break;
        }
        	
        if($sort == 2 || $sort == 4) {
        	$files = array_reverse($files);
        } 
        
		$images = array_slice($files, 0, $max);
		
		$target = modDJImageSliderHelper::getSlideTarget($params->get('link'));
		
		foreach($images as $image) {
			$slides[] = (object) array('title'=>'', 'description'=>'', 'image'=>$folder.'/'.$image, 'link'=>$params->get('link'), 'alt'=>$image, 'target'=>$target);
		}
				
		return $slides;
    }
	
	static function getImagesFromDJImageSlider(&$params) {
		
		if(!is_numeric($max = $params->get('max_images'))) $max = 20;
        $catid = $params->get('category',0);
		
		// build query to get slides
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('a.*');
		$query->from('#__djimageslider AS a');
		
		if (is_numeric($catid)) {
			$query->where('a.catid = ' . (int) $catid);
		}
		
		// Filter by start and end dates.
		$nullDate	= $db->Quote($db->getNullDate());
		$nowDate	= $db->Quote(JFactory::getDate()->format($db->getDateFormat()));
		
		$query->where('a.published = 1');
		$query->where('(a.publish_up = '.$nullDate.' OR a.publish_up <= '.$nowDate.')');
		$query->where('(a.publish_down = '.$nullDate.' OR a.publish_down >= '.$nowDate.')');
		
		switch($params->get('sort_by',1)) {
			case 1:
				$query->order('a.ordering ASC');
				break;
			case 2:
				$query->order('a.ordering DESC');
				break;
			case 3:
				$query->order('a.publish_up ASC');
				break;
			case 4:
				$query->order('a.publish_up DESC');
				break;
			default:
				$query->order('RAND()');
				break;
		}

		$db->setQuery($query, 0 , $max);
		$slides = $db->loadObjectList();
		
		foreach($slides as $slide){
			$slide->params = new JRegistry($slide->params);
			$slide->link = modDJImageSliderHelper::getSlideLink($slide);
			$slide->description = modDJImageSliderHelper::getSlideDescription($slide, $params->get('limit_desc'));
			$slide->alt = $slide->params->get('alt_attr', $slide->title);
			$slide->img_title = $slide->params->get('title_attr');
			$slide->target = $slide->params->get('link_target','');
			$slide->rel = $slide->params->get('link_rel','');
			if(empty($slide->target)) $slide->target = modDJImageSliderHelper::getSlideTarget($slide->link);
		}
		
		return $slides;
    }
	
	static function getSlideLink(&$slide) {
		$link = '';
		$db = JFactory::getDBO();
		$app = JFactory::getApplication();
		
		switch($slide->params->get('link_type', '')) {
			case 'menu':
				if ($menuid = $slide->params->get('link_menu',0)) {
					
					$menu = $app->getMenu();
					$menuitem = $menu->getItem($menuid);
					if($menuitem) switch($menuitem->type) {
						case 'component': 
							$link = JRoute::_($menuitem->link.'&Itemid='.$menuid);
							break;
						case 'url':
						case 'alias':
							$link = JRoute::_($menuitem->link);
							break;
					}	
				}
				break;
			case 'url':
				if($itemurl = $slide->params->get('link_url',0)) {
					$link = JRoute::_($itemurl);
				}
				break;
			case 'article':
				if ($artid = $slide->params->get('id',$slide->params->get('link_article',0))) {
					jimport('joomla.application.component.model');
					require_once(JPATH_BASE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');
					JModelLegacy::addIncludePath(JPATH_BASE.DS.'components'.DS.'com_content'.DS.'models');
					$model = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request'=>true));
					$model->setState('params', $app->getParams());
					$model->setState('filter.article_id', $artid);
					$model->setState('filter.article_id.include', true); // Include
					$items = $model->getItems();
					if($items && $item = $items[0]) {
						$item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
						$link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid));
						$slide->introtext = $item->introtext;
					}
				}
				break;
		}
		
		return $link;
	}
	
	static function getSlideDescription($slide, $limit) {
		$sparams = new JRegistry($slide->params);
		if($sparams->get('link_type','')=='article' && empty($slide->description)){ // if article and no description then get introtext as description
			if(isset($slide->introtext)) $slide->description = $slide->introtext;
		}
		
		$desc = strip_tags($slide->description);
		
		if($limit && $limit - strlen($desc) < 0) {
			// don't cut in the middle of the word unless it's longer than 20 chars
			if($pos = strpos($desc, ' ', $limit)) $limit = ($pos - $limit > 20) ? $limit : $pos;
			// cut text and add dots
			if(function_exists('mb_substr')) {
				$desc = mb_substr($desc, 0, $limit);
			} else {
				$desc = substr($desc, 0, $limit);
			}
			if(preg_match('/[a-zA-Z0-9]$/', $desc)) $desc.='&hellip;';
			$desc = '<p>'.nl2br($desc).'</p>';
		} else { // no limit or limit greater than description
			$desc = $slide->description;
		}

		return $desc;
	}

	private function truncateDescription($text, $limit) {
		
		$text = preg_replace('/{djmedia\s*(\d*)}/i', '', $text);
		
		$desc = strip_tags($text);
		
		if($limit && $limit - strlen($desc) < 0) {
			$desc = substr($desc, 0, $limit);
			// don't cut in the middle of the word unless it's longer than 20 chars
			if($pos = strrpos($desc, ' ')) {
				$limit = ($limit - $pos > 20) ? $limit : $pos;
				$desc = substr($desc, 0, $limit);
			}
			// cut text and add dots
			if(preg_match('/[a-zA-Z0-9]$/', $desc)) $desc.='&hellip;';
			$desc = '<p>'.nl2br($desc).'</p>';
		} else { // no limit or limit greater than description
			$desc = $text;
		}

		return $desc;
	}
	
	static function getAnimationOptions(&$params) {
		$transition = $params->get('effect');
		$easing = $params->get('effect_type');
		if(!is_numeric($duration = $params->get('duration'))) $duration = 0;
		if(!is_numeric($delay = $params->get('delay'))) $delay = 3000;
		$autoplay = $params->get('autoplay');
		$looponce = $params->get('looponce', 0);
		if($params->get('slider_type')==2 && !$duration) {
			$transition = 'Sine';
			$easing = 'easeInOut';
			$duration = 400;
		} else switch($transition){
			case 'Linear':
				$easing = '';
				$transition = 'linear';
				if(!$duration) $duration = 400;
				break;
			case 'Back':
				if(!$easing) $easing = 'easeIn';
				if(!$duration) $duration = 400;
				break;
			case 'Bounce':
				if(!$easing) $easing = 'easeOut';
				if(!$duration) $duration = 800;
				break;
			case 'Elastic':
				if(!$easing) $easing = 'easeOut';
				if(!$duration) $duration = 1000;
				break;
			default: 
				if(!$easing) $easing = 'easeInOut';
				if(!$duration) $duration = 400;
		}
		// add transition duration to delay
		$delay = $delay + $duration;
		$css3transition = $params->get('css3') ? modDJImageSliderHelper::getCSS3Transition($transition, $easing) : '';
		
		if (version_compare(JVERSION, '3.0.0', '<')) { // Joomla!2.5 - Mootools
			if($transition=='ease') $transition = 'Sine';
			$transition = $transition.(!empty($easing) ? '.'.$easing : '');
			$transition = modDJImageSliderHelper::getMooTransition($transition);
		} else { // Joomla!3 - jQuery
			if($transition=='ease') {
				$transition = 'swing';
				$easing = '';
			}
			$transition = $easing.$transition;
		}
		
		$options = json_encode(array('auto' => $autoplay, 'looponce' => $looponce, 'transition' => $transition, 'css3transition' => $css3transition, 'duration' => $duration, 'delay' => $delay));
		
		return $options;
	}
	
	static function getMooTransition($transition) {
		
		$parts = explode('.', $transition);
		
		$easing = '';
		if(isset($parts[1])) {
			switch($parts[1]) {
				case 'easeIn': $easing = ':in'; break;
				case 'easeOut': $easing = ':out'; break;
				default: $easing = ':in:out'; break;
			}
		}
		
		return strtolower($parts[0]).$easing;
		
	}
	
	static function getCSS3Transition($transition, $easing) {
		
		switch($easing) {
			
			case '': return 'linear';
			case 'easeInOut':
				switch($transition) {
					case 'Quad': 	return 'cubic-bezier(0.455, 0.030, 0.515, 0.955)';
					case 'Cubic': 	return 'cubic-bezier(0.645, 0.045, 0.355, 1.000)';
					case 'Quart':	return 'cubic-bezier(0.645, 0.045, 0.355, 1.000)';
					case 'Quint': 	return 'cubic-bezier(0.860, 0.000, 0.070, 1.000)';
					case 'Sine': 	return 'cubic-bezier(0.445, 0.050, 0.550, 0.950)';
					case 'Expo': 	return 'cubic-bezier(1.000, 0.000, 0.000, 1.000)';
					case 'Circ': 	return 'cubic-bezier(0.785, 0.135, 0.150, 0.860)';
					case 'Back': 	return 'cubic-bezier(0.680, -0.550, 0.265, 1.550)';
					default: 		return 'ease-in-out';
				}
			case 'easeOut':
				switch($transition) {
					case 'Quad': 	return 'cubic-bezier(0.250, 0.460, 0.450, 0.940)';
					case 'Cubic': 	return 'cubic-bezier(0.215, 0.610, 0.355, 1.000)';
					case 'Quart':	return 'cubic-bezier(0.165, 0.840, 0.440, 1.000)';
					case 'Quint': 	return 'cubic-bezier(0.230, 1.000, 0.320, 1.000)';
					case 'Sine': 	return 'cubic-bezier(0.390, 0.575, 0.565, 1.000)';
					case 'Expo': 	return 'cubic-bezier(0.190, 1.000, 0.220, 1.000)';
					case 'Circ': 	return 'cubic-bezier(0.075, 0.820, 0.165, 1.000)';
					case 'Back': 	return 'cubic-bezier(0.175, 0.885, 0.320, 1.275)';
					default: 		return 'ease-out';
				}
			case 'easeIn':
				switch($transition) {
					case 'Quad': 	return 'cubic-bezier(0.550, 0.085, 0.680, 0.530)';
					case 'Cubic': 	return 'cubic-bezier(0.550, 0.055, 0.675, 0.190)';
					case 'Quart':	return 'cubic-bezier(0.895, 0.030, 0.685, 0.220)';
					case 'Quint': 	return 'cubic-bezier(0.755, 0.050, 0.855, 0.060)';
					case 'Sine': 	return 'cubic-bezier(0.470, 0.000, 0.745, 0.715)';
					case 'Expo': 	return 'cubic-bezier(0.950, 0.050, 0.795, 0.035)';
					case 'Circ': 	return 'cubic-bezier(0.600, 0.040, 0.980, 0.335)';
					case 'Back': 	return 'cubic-bezier(0.600, -0.280, 0.735, 0.045)';
					default: 		return 'ease-in';
				}
			default: return 'ease';
		}
	}
	
	static function getSlideTarget($link) {
		
		if(preg_match("/^http/",$link) && !preg_match("/^".str_replace(array('/','.','-'), array('\/','\.','\-'),JURI::base())."/",$link)) {
			$target = '_blank';
		} else {
			$target = '_self';
		}
		
		return $target;
	}
	
	static function getNavigation(&$params, &$mid) {
		
		$prev = $params->get('left_arrow');
		$next = $params->get('right_arrow');
		$play = $params->get('play_button');
		$pause = $params->get('pause_button');
		
		$theme = $params->get('theme', 'default');
		
		if($params->get('slider_type')==1) {			
			if(empty($prev) || !file_exists(JPATH_ROOT.DS.$prev)) $prev = 'modules/mod_djimageslider/themes/'.$theme.'/images/up.png';			
			if(empty($next) || !file_exists(JPATH_ROOT.DS.$next)) $next = 'modules/mod_djimageslider/themes/'.$theme.'/images/down.png';
		} else {			
			if(empty($prev) || !file_exists(JPATH_ROOT.DS.$prev)) $prev = 'modules/mod_djimageslider/themes/'.$theme.'/images/prev.png';			
			if(empty($next) || !file_exists(JPATH_ROOT.DS.$next)) $next = 'modules/mod_djimageslider/themes/'.$theme.'/images/next.png';
		}
		if(empty($play) || !file_exists(JPATH_ROOT.DS.$play)) $play = 'modules/mod_djimageslider/themes/'.$theme.'/images/play.png';
		if(empty($pause) || !file_exists(JPATH_ROOT.DS.$pause)) $pause = 'modules/mod_djimageslider/themes/'.$theme.'/images/pause.png';
		
		$navi = (object) array('prev'=>$prev,'next'=>$next,'play'=>$play,'pause'=>$pause);
		
		return $navi;
	}
	
	static function getStyles($params) {
		if(!is_numeric($slide_width = $params->get('image_width'))) $slide_width = 240;
		if(!is_numeric($slide_height = $params->get('image_height'))) $slide_height = 160;
		if(!is_numeric($max = $params->get('max_images'))) $max = 20;
		if(!is_numeric($count = $params->get('visible_images'))) $count = 2;
		if(!is_numeric($spacing = $params->get('space_between_images'))) $spacing = 0;
		if($count<1) $count = 1;
		if($count>$max) $count = $max;
		
		
		$desc_width = $params->get('desc_width', $slide_width);
		if(strstr($desc_width, '%') == false && $desc_width > $slide_width) $desc_width = $slide_width;
		$desc_bottom = $params->get('desc_bottom', 0);
		$desc_left = $params->get('desc_horizontal', 0);
		$arrows_top = $params->get('arrows_top', '50%');
		$arrows_horizontal = $params->get('arrows_horizontal', 5);
		
		switch($params->get('slider_type')){
			case 2:
				$slider_width = $slide_width;
				$slider_height = $slide_height;
				$image_width = 'width: 100%';
				$image_height = 'height: auto';
				$padding_right = 0;
				$padding_bottom = 0;
				break;
			case 1:
				$slider_width = $slide_width;
				$slider_height = $slide_height * $count + $spacing * ($count - 1);
				$image_width = 'width: auto';
				$image_height = 'height: 100%';
				$padding_right = 0;
				$padding_bottom = $spacing;
				break;
			case 0:
			default:
				$slider_width = $slide_width * $count + $spacing * ($count - 1);
				$slider_height = $slide_height;
				$image_width = 'width: 100%';
				$image_height = 'height: auto';
				$padding_right = $spacing;
				$padding_bottom = 0;
				break;
		}
		
		if(strstr($desc_width, '%') == false) $desc_width = (($desc_width / $slide_width) * 100) .'%';
		if(strstr($desc_left, '%') == false) $desc_left = (($desc_left / $slide_width) * 100) .'%';
		if(strstr($desc_bottom, '%') == false) $desc_bottom = (($desc_bottom / $slide_height) * 100) .'%';
		if(strstr($arrows_top, '%') == false) $arrows_top = (($arrows_top / $slider_height) * 100) .'%';
		if(strstr($arrows_horizontal, '%') == false) $arrows_horizontal = (($arrows_horizontal / $slider_width) * 100) .'%';
		
		if($params->get('fit_to')==1) {
			$image_width = 'width: 100%';
			$image_height = 'height: auto';
		} else if($params->get('fit_to')==2) {
			$image_width = 'width: auto';
			$image_height = 'height: 100%';
		}
		
		$style = array();
		$style['slider'] = 'height: '.$slider_height.'px; width: '.$slider_width.'px;';
		if(!$params->get('full_width', 0)) $style['slider'].= ' max-width: '.$slider_width.'px !important;';
		$style['image'] = $image_width.'; '.$image_height.';';		
		$style['navi'] = 'top: '.$arrows_top.'; margin: 0 '.$arrows_horizontal.';';
		$style['desc'] = 'bottom: '.$desc_bottom.'; left: '.$desc_left.'; width: '.$desc_width.';';
		if($params->get('direction') == 'rtl') {
			$style['slide'] = 'margin: 0 0 '.$padding_bottom.'px '.$padding_right.'px !important; height: '.$slide_height.'px; width: '.$slide_width.'px;';
		} else {
			$style['slide'] = 'margin: 0 '.$padding_right.'px '.$padding_bottom.'px 0 !important; height: '.$slide_height.'px; width: '.$slide_width.'px;';
			
		}
		
		return $style;
	}

}
