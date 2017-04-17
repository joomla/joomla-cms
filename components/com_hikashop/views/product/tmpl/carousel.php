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

$mainDivName=$this->params->get('main_div_name');
$carouselEffect=$this->params->get('carousel_effect');
$pagination_type=$this->params->get('pagination_type');
$duration=$this->params->get('carousel_effect_duration');
if(empty($duration)) $duration=500;

$limit=$this->params->get('limit');

$columns=$this->params->get('columns');
$products=$this->params->get('item_by_slide');
if(empty($products)){ $products=$columns; }

$margin=$this->params->get('margin');
$oneByone=$this->params->get('one_by_one');
$autoSlideDuration=$this->params->get('auto_slide_duration');
$transitionEffect=$this->params->get('transition_effect');
$direction=$this->params->get('slide_direction');
$pane_height=$this->params->get('pane_height');
$height=$this->newSizes->height;
$width=$this->newSizes->width;

if($pagination_type=='thumbnails'){
	$thnumbnailHeight=$this->params->get('pagination_image_height');
	$thnumbnailWidth=$this->params->get('pagination_image_width');
	$imageHelper = hikashop_get('helper.image');
	if(empty($thnumbnailWidth) && empty($thnumbnailHeight)){
		$thnumbnailHeight=$this->image->main_thumbnail_y/4;
		$thnumbnailWidth=$this->image->main_thumbnail_x/4;
	}else if(empty($thnumbnailWidth)){
		$thnumbnailHeight=$this->params->get('pagination_image_height');
		list($theImage->width, $theImage->height) = getimagesize($this->image->main_uploadFolder.$this->rows[0]->file_path);
		list($thnumbnailWidth, $thnumbnailHeight) = $imageHelper->scaleImage($theImage->width, $theImage->height, 0, $thnumbnailHeight);

	}else if(empty($thnumbnailHeight)){
		$thnumbnailWidth=$this->params->get('pagination_image_width');
		list($theImage->width, $theImage->height) = getimagesize($this->image->main_uploadFolder.$this->rows[0]->file_path);
		list($thnumbnailWidth, $thnumbnailHeight) = $imageHelper->scaleImage($theImage->width, $theImage->height, $thnumbnailWidth, 0 );
	}else{
		$thnumbnailHeight=$this->params->get('pagination_image_height');
		$thnumbnailWidth=$this->params->get('pagination_image_width');
	}
}

if(empty($autoSlideDuration)) $autoSlideDuration=1000;
if($transitionEffect=='bounce') $transition='Bounce.easeOut';
if($transitionEffect=='linear') $transition='linear';
if($transitionEffect=='elastic') $transition='Elastic.easeOut';
if($transitionEffect=='sin') $transition='Sine.easeInOut';
if($transitionEffect=='quad') $transition='Quad.easeInOut';
if($transitionEffect=='expo') $transition='Expo.easeOut';
if($transitionEffect=='back') $transition='Back.easeOut';

$nbProduct=0;
foreach($this->rows as $row){
	$nbProduct++;
}
if($nbProduct>$limit) $nbProduct=$limit;

if(!empty($oneByone) && $carouselEffect=='slide'){
	if($this->params->get('div_item_layout_type')=='img_desc' || $this->params->get('div_item_layout_type')=='img_desc_2' || $this->params->get('div_item_layout_type')=='img_title' || $this->params->get('div_item_layout_type')=='img'){
		$height=$height+$pane_height;
	}
	if($this->params->get('div_item_layout_type')=='img_desc' || $this->params->get('div_item_layout_type')=='img_desc_2'){
		$width=$width*2;
	}
	$widthProd=($width*$columns)+($margin*$columns*2);
	$lines=ceil($products/$columns);
	$heightProd=$height*($lines)+($margin*$lines*2);
	$slideWidth=$widthProd*(ceil($nbProduct/$products));
	$slideHeight=$heightProd*(ceil($nbProduct/$products));
	$slides=ceil($nbProduct/$products)-1;
	if($direction=='horizontal'){
		$increment=($width+2*$margin);
		$max=round(($nbProduct-$products)/$lines);
		$divWidth=($width+$margin*2)*$columns;
		$nbDiv=$widthProd/$divWidth;
	}
	else{
		$increment=($height+2*$margin);
		$max=round(($nbProduct-$products)/$columns);
		$divHeight=($height+$margin*2)*$lines;
		$nbDiv=$heightProd/$divHeight;
	}
	$columnTot=round($nbProduct/$lines);
}
else{
	if($this->params->get('div_item_layout_type')=='img_desc' || $this->params->get('div_item_layout_type')=='img_desc_2' || $this->params->get('div_item_layout_type')=='img_title' || $this->params->get('div_item_layout_type')=='img'){
		$height=$height+$pane_height;
	}
	if($this->params->get('div_item_layout_type')=='img_desc' || $this->params->get('div_item_layout_type')=='img_desc_2'){
		$width=$width*2;
	}
	$nbDiv=1;
	$lines=ceil($products/$columns);
	$slideWidth=($width*$columns+($margin*$columns*2))*(ceil($nbProduct/$products));
	$slideHeight=($height*$lines+($margin*$lines*2))*(ceil($nbProduct/$products));
	$slides=ceil($nbProduct/$products)-1;
	$widthProd=($width*$columns)+($margin*$columns*2);
	$divWidth=$widthProd;
	$heightProd=$height*($lines)+($margin*$lines*2);
	if($direction=='horizontal'){
		$increment=$widthProd;
		$divWidth=$widthProd;
	}
	else{
		$increment=$heightProd;
		$divHeight=$heightProd;
	}
	$max=$slides;
}


ob_start();
if($carouselEffect=='slide'){
	if($direction=='horizontal'){
		if($this->params->get('display_button')) echo '<table style="margin:auto;"><tr><th><a id="hikashop_previous_button_'.$mainDivName.'"><img class="hikashop_slider_button hikashop_slider_button" src="'.HIKASHOP_IMAGES.'icons/prev.png" alt="hikashop_previous_button_'.$mainDivName.'" /></a></th><th>';
		if($this->params->get('div_item_layout_type')=='fade' || $this->params->get('div_item_layout_type')=='slider_vertical' || $this->params->get('div_item_layout_type')=='slider_horizontal' || $this->params->get('div_item_layout_type')=='img_pane'){
			echo '<div id="hikashop_main_slider_'.$mainDivName.'" class="hikashop_main_carousel_div hikashop_subcontainer '.$this->borderClass.'" style="padding-bottom:0px; padding-top:0px; margin: auto; overflow:hidden; position:relative; width:'.$widthProd.'px; height:'.$heightProd.'px;">';
			echo '<ul id="hikashop_only_products_'.$mainDivName.'" style="position:relative; margin:0px; margin-top: 0px;padding-left:0px;list-style:none; width:'.$slideWidth.'px; height:'.$heightProd.'px;">';
		}
		else{
			echo '<div id="hikashop_main_slider_'.$mainDivName.'" class="hikashop_main_carousel_div hikashop_subcontainer '.$this->borderClass.'" style="padding-bottom:0px; padding-top:0px; margin: auto; overflow:hidden; position:relative; width:'.$widthProd.'px;">';
			echo '<ul id="hikashop_only_products_'.$mainDivName.'" style="position:relative; margin:0px; margin-top: 0px;padding-left:0px;list-style:none; width:'.$slideWidth.'px; ">';
		}
	}
	else{
		if($this->params->get('display_button')) echo '<table style="margin:auto;"><tr><th style="text-align:center;"><a id="hikashop_previous_button_'.$mainDivName.'"><img class="hikashop_slider_button hikashop_slider_button" src="'.HIKASHOP_IMAGES.'icons/up.png" style="margin:auto; text-align:center;" alt="up"/></a></th></tr><tr><th>';
		if($this->params->get('div_item_layout_type')=='fade' || $this->params->get('div_item_layout_type')=='slider_vertical' || $this->params->get('div_item_layout_type')=='slider_horizontal' || $this->params->get('div_item_layout_type')=='img_pane'){
			echo '<div id="hikashop_main_slider_'.$mainDivName.'" class="hikashop_main_carousel_div hikashop_subcontainer '.$this->borderClass.'" style=" padding-bottom:0px; padding-top:0px; margin: auto; overflow:hidden; position:relative; width:'.$widthProd.'px; height:'.$heightProd.'px;">';
			echo '<ul id="hikashop_only_products_'.$mainDivName.'" style="position:relative; margin:0px; margin-top: 0px;padding-left:0px;list-style:none; width:'.$widthProd.'px; height:'.$slideHeight.'px;">';
		}
		else{
			echo '<div id="hikashop_main_slider_'.$mainDivName.'" class="hikashop_main_carousel_div hikashop_subcontainer '.$this->borderClass.'" style="padding-bottom:0px; padding-top:0px;  overflow:hidden; position:relative; width:'.$widthProd.'px; height:'.$heightProd.'px;">';
			echo '<ul id="hikashop_only_products_'.$mainDivName.'" style="position:relative; margin:0px; margin-top: 0px;padding-left:0px;list-style:none; width:'.$widthProd.'px; height:'.$slideHeight.'px;">';
		}
	}

	$count=0; $nbSlide=0; $currentLine=0; $currentColumn=0; $newCount=1;
	$thumbnailOrder=array();

	foreach($this->rows as $row){
		if($count==0){
			if($direction=='horizontal'){
				if($this->params->get('div_item_layout_type')=='fade' || $this->params->get('div_item_layout_type')=='slider_vertical' || $this->params->get('div_item_layout_type')=='slider_horizontal' || $this->params->get('div_item_layout_type')=='img_pane'){
					echo '<li style="float:left;width:'.$divWidth.'px; height:'.$heightProd.'px;"><ul style="padding-left:0px;">';
				}
				else{
					echo '<li style="float:left;width:'.$divWidth.'px; "><ul style="margin:0px; padding-left:0px;">';
				}
				$nbSlide++;
			}
			else{
				if($this->params->get('div_item_layout_type')=='fade' || $this->params->get('div_item_layout_type')=='slider_vertical' || $this->params->get('div_item_layout_type')=='slider_horizontal' || $this->params->get('div_item_layout_type')=='img_pane'){
					echo '<li style="padding:0px; float:left;width:'.$widthProd.'px; height:'.$divHeight.'px;"><ul style="margin:0px; padding-left:0px;">';
				}
				else{
					echo '<li style="padding:0px; float:left;width:'.$widthProd.'px; height:'.$heightProd.'px; "><ul style="margin:0px; padding-left:0px;">';
				}
				$nbSlide++;
			}
		}
		$this->row =& $row;
		if($this->params->get('div_item_layout_type')=='fade' || $this->params->get('div_item_layout_type')=='slider_vertical' || $this->params->get('div_item_layout_type')=='slider_horizontal' || $this->params->get('div_item_layout_type')=='img_pane'){
			?><li style="margin: <?php echo $margin; ?>px; height:<?php echo $height; ?>px; width:<?php echo $width; ?>px;float:left; list-style:none; position:relative;"><?php
		}
		else{
			?><li style="margin: <?php echo $margin; ?>px; width:<?php echo $width; ?>px;float:left; list-style:none; text-align:center"><?php
		}
		$this->setLayout('listing_'.$this->params->get('div_item_layout_type'));
		echo $this->loadTemplate();
		?></li><?php
		$nbDiv=1;
		$thumbnailOrder[$currentColumn][$currentLine]=$this->row->product_id;
		if($newCount%$columns<=$columns){
			$currentColumn++;
		}
		if($newCount%$products==0){
			$currentLine=0;
			$currentColumn=($nbSlide*$columns);
		}else

		if($currentColumn%$columns==0){
			$currentLine++;
			$currentColumn=($nbSlide-1)*$columns;
		}
		$newCount++;
		if($count==round($products/$nbDiv-1)){
			echo '</ul></li>';
			$count=0;
		}
		else{
			$count++;
		}
	}
	if($count!=0){ echo '</ul></li>'; }
	echo '</ul></div>';
}
else if($carouselEffect=='fade'){
	if($direction=='horizontal'){
		if($this->params->get('display_button')) echo '<table style="margin:auto; "><tr><th><a id="hikashop_previous_button_'.$mainDivName.'"><img class="hikashop_slider_button hikashop_slider_button" src="'.HIKASHOP_IMAGES.'icons/prev.png" alt="hikashop_previous_button_'.$mainDivName.'" /></a></th><th>';
		echo '<div id="hikashop_slideshow_fade_'.$mainDivName.'" class="hikashop_main_carousel_div hikashop_subcontainer '.$this->borderClass.'" style="padding-bottom:0px; padding-top:0px; overflow:hidden; width:'.$widthProd.'px; height:'.$heightProd.'px; position:relative;">';
	}
	else{
		if($this->params->get('display_button'))  echo '<table style="margin:auto;"><tr><th style="text-align:center;"><a id="hikashop_previous_button_'.$mainDivName.'"><img class="hikashop_slider_button hikashop_slider_button" src="'.HIKASHOP_IMAGES.'icons/up.png" style="margin:auto; text-align:center;" alt="up" /></a></th></tr><tr><th>';
		echo '<div id="hikashop_slideshow_fade_'.$mainDivName.'" class="hikashop_main_carousel_div hikashop_subcontainer '.$this->borderClass.'" style="overflow:hidden; width:'.$widthProd.'px; height:'.$heightProd.'px; position:relative;">';
	}

	$count=0; $nbSlide=0;
	foreach($this->rows as $row){
		if($count==0){
			$zindex = 3;
			if($nbSlide){
				$zindex = 1;
			}
			echo '<div class="product_slide" style="width:'.$widthProd.'px; height:'.$heightProd.'px; position:absolute; top:0; left:0;z-index:'.$zindex.';">';
			$nbSlide++;
		}
		$this->row =& $row;
		?><div style="margin: <?php echo $margin; ?>px; height:<?php echo $height; ?>px; width:<?php echo $width; ?>px;float:left; "><?php
		$this->setLayout('listing_'.$this->params->get('div_item_layout_type'));
		echo $this->loadTemplate();
		?></div><?php
		if($count==$products-1){
			echo '</div>';
			$count=0;
		}
		else{
			$count++;
		}
	}
	if($count!=0){ echo '</div></div>'; }
	else{ echo '</div>';}
}

if($this->params->get('display_button') && $direction=='horizontal'){ echo '</th><th><a id="hikashop_next_button_'.$mainDivName.'"><img class="hikashop_slider_button hikashop_slider_button" src="'.HIKASHOP_IMAGES.'icons/next.png" alt="hikashop_next_button_'.$mainDivName.'" /></a></th></tr></table>';}
else if($this->params->get('display_button') && $direction=='vertical'){ echo '</th></tr><tr><th style="text-align:center;"><a id="hikashop_next_button_'.$mainDivName.'"><img class="hikashop_slider_button hikashop_slider_button" src="'.HIKASHOP_IMAGES.'icons/down.png" alt="down" /></a></th></tr></table>'; }
$html=ob_get_clean();

ob_start();
$lastSlideProducts=$nbProduct%$products;
$emptyColumns=$columns-$lastSlideProducts;
$emptyLines=$lines-ceil($lastSlideProducts/$columns);
if($lastSlideProducts==0 || $emptyColumns<0){
	$emptyColumns=0;
}
if($lastSlideProducts==0 || $emptyLines<0){
	$emptyLines=0;
}
if($pagination_type!="no_pagination"){
	$specialPagination=false;
	$done=false;
	$reorder=0;
	$pagination_position=$this->params->get('pagination_position');
	if($pagination_position=='top' || $pagination_position=='bottom'){
		if($pagination_type=='numbers' || $pagination_type=='rounds'){
			if($lines==1){
				$selectedPage=1;
			}else{
				$selectedPage=$products;
			}
			$oldI=0;
			echo '<div id="hikashop_slider_pagination_'.$mainDivName.'">';
			echo '<div class="hikashop_slider_pagination" style="text-align:center; margin:auto; margin-top:10px; margin-bottom:10px; width:auto">';
			if(!$oneByone || ($oneByone && $carouselEffect=='fade')){
				for($i=0;$i<$nbSlide;$i++){
					$this->pagination_display($pagination_type, $mainDivName, $oldI, $i, $pagination_position, 1);
					$oldI++;
				}
			}
			else if($products==1){
				for($i=0;$i<$nbProduct;$i++){
					$this->pagination_display($pagination_type, $mainDivName, $i, $i, $pagination_position, $selectedPage);
				}
			}
			else{
				if($direction=='horizontal' && $limit%$products!=$products){$limit=$limit+$limit%$products; }
				for($i=0;$i<$limit;$i++){
					if(((($lines==1 && $direction=='horizontal') || ($columns==1 && $direction=='vertical')) && ($oldI<=($nbSlide*$columns)-$columns-$emptyColumns || $oldI<=($nbSlide*$lines)-$lines-$emptyLines))){
						if($i<($limit-$products+1)){
							$this->pagination_display($pagination_type, $mainDivName, $i, $i, $pagination_position, 1);
							$oldI=$i+1;
						}
					}
					else if(($lines!=1 && $direction=='horizontal') || ($columns!=1 && $direction=='vertical')){
						if($direction=='horizontal' && ($i%$products==0 || ($i%$products==$lines && $i!=$lines) || ($i%($products+$lines)==$lines && $i!=$lines)) && $oldI<=($nbSlide*$columns)-$columns-$emptyColumns){
							$this->pagination_display($pagination_type, $mainDivName, $oldI, $i, $pagination_position, $selectedPage);
							$oldI++;
						}
						if($direction=='vertical' && ($i%$products==0 || ($i%$products==$columns && $i!=$columns)) && $oldI<=($nbSlide*$lines)-$lines-$emptyLines){
							$this->pagination_display($pagination_type, $mainDivName, $oldI, $i, $pagination_position, $selectedPage);
							$oldI++;
						}
					}
				}
				$nbSlide=$oldI;
				$max=$nbSlide-1;

			}
			echo '</div></div>';
		}

		if($pagination_type=='thumbnails' || $pagination_type=='names'){
			echo '<div id="hikashop_slider_pagination_'.$mainDivName.'">';
			echo '<div class="hikashop_slider_pagination" style="text-align:center; margin:auto; margin-top:10px; margin-bottom:10px; width:auto">';
			$oldI=0;
			foreach($this->rows as $i=>$row){
				if($i<$limit){
					if((($oneByone && $columns==1 && $direction=='vertical') || ($oneByone && $lines==1 && $direction=='horizontal')) && $carouselEffect=='slide' ){
						$specialPagination=true;
						if($i!=0) echo '</span>';
						$this->pagination_display($pagination_type, $mainDivName, $i, $i, $pagination_position, $products);
						$oldI=$i+1;
						$nbSlide=$oldI;
						if($products==1 && !$done && $direction=='horizontal'){
							$increment=$increment*$columns; $done=true;
							$max=$nbSlide-1;
						}else{
							$max=$nbSlide-$products;
						}
					}
					else if(!$oneByone || ($oneByone && $carouselEffect=='fade' && $products!=1) || $products==1){
						if(($products!=1 && $i%$products==0) || $products==1){
							if($oldI!=0) echo '</span>';
							$this->pagination_display($pagination_type, $mainDivName, $oldI, $i, $pagination_position, $products);
							$oldI=$oldI+1;
						}
					}
					else if($direction=='horizontal'){
						if($oneByone){ //ou $products!=$columns*$lines
							$reorder=1;
							break;
						}
						if($i%$products==0 || $i%$products==$lines){
							if($i!=0 && $i!=$lines){ echo '</span>'; }
							if($i==$products){
								echo '</span>';
								$this->pagination_display($pagination_type, $mainDivName, $oldI, $i, $pagination_position, $products);
								$oldI=$oldI+1;
							}
							else if($i!=$lines){
								$this->pagination_display($pagination_type, $mainDivName, $oldI, $i, $pagination_position, $products);
								$oldI=$oldI+1;
							}
						}
						$nbSlide=$oldI;
						$max=$nbSlide-1;
					}
					else if($direction=='vertical'){
						if($i%$products==0 || $i%$products==$columns){
							if($i!=0 && $i!=$columns){ echo '</span>'; }
							if($i==$products){
								$this->pagination_display($pagination_type, $mainDivName, $oldI, $i, $pagination_position, $products);
								$oldI=$oldI+1;
							}
							else if($i!=$columns){
								$this->pagination_display($pagination_type, $mainDivName, $oldI, $i, $pagination_position, $products);
								$oldI=$oldI+1;
							}
						}
						$nbSlide=$oldI;
						$max=$nbSlide-1;
					}
					if($pagination_type=='thumbnails'){
						echo '<a style="margin-left:5px; margin-right:5px; cursor:pointer;">';
						echo $this->image->display(@$this->row->file_path,false,$this->escape($this->row->product_name), '' , '' , $thnumbnailWidth, $thnumbnailHeight);
						echo '</a>';
					}
					else{
						echo '<span style="margin-left:5px; margin-right:5px; cursor:pointer;">'.($i+1).'. '.$row->product_name.'</span>';
					}
				}
			}
			if($reorder==1){
				$firstColumnSize=count($thumbnailOrder[0]);
				$i=0;
				$oldI=0;
				foreach($thumbnailOrder as $theColumn){
					foreach($theColumn as $x=>$id){
						if($x==0 && $oldI!=count($thumbnailOrder)-1){
							$this->pagination_display($pagination_type, $mainDivName, $oldI, $i, $pagination_position, $firstColumnSize);
						}
						$i++;
						foreach($this->rows as $row){
							if($row->product_id==$id){
								if($pagination_type=='thumbnails'){
									echo '<a style="margin-left:5px; margin-right:5px; cursor:pointer;">';
									echo $this->image->display(@$this->row->file_path,false,$this->escape($this->row->product_name), '' , '' , $thnumbnailWidth, $thnumbnailHeight);
									echo '</a>';
								}
								else{
									echo '<span style="margin-left:5px; margin-right:5px; cursor:pointer;">'.($i+1).'. '.$row->product_name.'</span>';
								}
							}
						}
						if($x==count($theColumn)-1){
							if($oldI!=(count($thumbnailOrder)-2)){
								echo '</span>';
							}
							$oldI++;
						}
					}
				}
				$nbSlide=$oldI-1;
				$max=$nbSlide-1;
			}
			echo '</span></div></div>';
		}
	}
	else{
		if($pagination_type=='numbers' || $pagination_type=='rounds'){
			if($lines==1){
				$selectedPage=1;
			}else{
				$selectedPage=$products;
			}
			$oldI=0;
			echo '<div id="hikashop_slider_pagination_'.$mainDivName.'">';
			echo '<div class="hikashop_slider_pagination">';
			if(!$oneByone || ($oneByone && $carouselEffect=='fade')){
				for($i=0;$i<$nbSlide;$i++){
					$this->pagination_display($pagination_type, $mainDivName, $oldI, $i, $pagination_position, 1);
					$oldI++;
				}
			}
			else if($products==1){
				for($i=0;$i<$nbProduct;$i++){
					$this->pagination_display($pagination_type, $mainDivName, $i, $i, $pagination_position, 1);
				}
			}
			else{
				if($direction=='horizontal' && $limit%$products!=$products){ $limit=$limit+$limit%$products;}
				for($i=0;$i<$limit;$i++){
					if(((($lines==1 && $direction=='horizontal') || ($columns==1 && $direction=='vertical')) && ($oldI<=($nbSlide*$columns)-$columns-$emptyColumns || $oldI<=($nbSlide*$lines)-$lines))){
						if($i<($limit-$products+1)){
							$this->pagination_display($pagination_type, $mainDivName, $i, $i, $pagination_position, 1);
							$oldI=$i+1;
						}
					}
					else if((($lines!=1 && $direction=='horizontal') || ($columns!=1 && $direction=='vertical'))){
						if($direction=='horizontal' && ($i%$products==0 || ($i%$products==$lines && $i!=$lines) || ($i%($products+$lines)==$lines && $i!=$lines)) && $oldI<=($nbSlide*$columns)-$columns-$emptyColumns){
							$this->pagination_display($pagination_type, $mainDivName, $oldI, $i, $pagination_position, $selectedPage);
							$oldI++;
						}
						if($direction=='vertical' && ($i%$products==0 || ($i%$products==$columns && $i!=$columns)) && $oldI<=($nbSlide*$lines)-$lines){
							$this->pagination_display($pagination_type, $mainDivName, $oldI, $i, $pagination_position, $selectedPage);
							$oldI++;
						}
					}
				}
				$nbSlide=$oldI;
				$max=$nbSlide-1;
			}
			echo '</div></div>';
			$nbSlide=$oldI;
		}

		if($pagination_type=='thumbnails' || $pagination_type=='names'){
			if($pagination_type=='thumbnails'){
				echo '<div id="hikashop_slider_pagination_'.$mainDivName.'">';
				echo '<div class="hikashop_slider_pagination">';
			}
			else{
				echo '<div id="hikashop_slider_pagination_'.$mainDivName.'">';
				echo '<div class="hikashop_slider_pagination" style="text-align:center; margin-left: 10px; margin-right: 10px; width:auto">';
			}
			$oldI=0;
			foreach($this->rows as $i=>$row){
				if($i<$limit){
					if((($oneByone && $columns==1 && $direction=='vertical') || ($oneByone && $lines==1 && $direction=='horizontal')) && $carouselEffect=='slide'){
						$specialPagination=true;
						if($i!=0) echo '</span>';
						$this->pagination_display($pagination_type, $mainDivName, $i, $i, $pagination_position, $products);
						if($products==1 && !$done && $direction=='horizontal'){ $increment=$increment*$columns; $done=true; }
						$oldI=$i+1;
					}
					else if(!$oneByone || ($oneByone && $carouselEffect=='fade')){
						if($products!=1 && $i%$products==0){
							if($oldI!=0) echo '</span>';
							$this->pagination_display($pagination_type, $mainDivName, $oldI, $i, $pagination_position, $products);
							$oldI=$oldI+1;
						}
					}
					else if($direction=='horizontal'){
						if($oneByone){ //ou $products!=$columns*$lines
							$reorder=1;
							break;
						}
						if($i%$products==0 || $i%$products==$lines){
							if($i!=0 && $i!=$lines){ echo '</span>'; }
							if($i==$products){
								echo '</span>';
								$this->pagination_display($pagination_type, $mainDivName, $oldI, $i, $pagination_position, $products);
								$oldI=$oldI+1;
							}
							else if($i!=$lines){
								$this->pagination_display($pagination_type, $mainDivName, $oldI, $i, $pagination_position, $products);
								$oldI=$oldI+1;
							}
						}
					}
					else if($direction=='vertical'){
						if($i%$products==0 || $i%$products==$columns){
							if($i!=0 && $i!=$columns){ echo '</span>'; }
							if($i==$products){
								$this->pagination_display($pagination_type, $mainDivName, $oldI, $i, $pagination_position, $products);
								$oldI=$oldI+1;
							}
							else if($i!=$columns){
								$this->pagination_display($pagination_type, $mainDivName, $oldI, $i, $pagination_position, $products);
								$oldI=$oldI+1;
							}
						}
					}
					if($pagination_type=='thumbnails'){
						echo '<span class="hikashop_pagination_images_block"><a style="margin-top:5px; margin-bottom:5px; cursor:pointer;">';
						echo $this->image->display(@$this->row->file_path,false,$this->escape($this->row->product_name), '' , '' , $thnumbnailWidth, $thnumbnailHeight);
						echo '</a></span><br/>';
					}
					else{
						echo '<span style="margin-top:10px; cursor:pointer;">'.($i+1).'. '.$row->product_name.'</span><br/>';
					}
				}
			}
			if($reorder!=1){
				echo '</span></div></div>';
			}
			$nbSlide=$oldI;
			$max=$nbSlide-1;
		}
		if($reorder==1){
			$firstColumnSize=count($thumbnailOrder[0]);
			$i=0;
			$oldI=0;
			foreach($thumbnailOrder as $theColumn){
				foreach($theColumn as $x=>$id){
					if($x==0 && $oldI!=count($thumbnailOrder)-1){
						$this->pagination_display($pagination_type, $mainDivName, $oldI, $i, $pagination_position, $firstColumnSize);
					}
					$i++;
					foreach($this->rows as $row){
						if($row->product_id==$id){
							if($pagination_type=='thumbnails'){
								echo '<span class="hikashop_pagination_images_block"><a style="margin-top:5px; margin-bottom:5px; cursor:pointer;">';
								echo $this->image->display(@$this->row->file_path,false,$this->escape($this->row->product_name), '' , '' , $thnumbnailWidth, $thnumbnailHeight);
								echo '</a></span><br/>';
							}
							else{
								echo '<span style="margin-bottom:5px; margin-top:5px; cursor:pointer;">'.($i+1).'. '.$row->product_name.'</span><br/>';
							}
						}
					}
					if($x==count($theColumn)-1){
						if($oldI!=(count($thumbnailOrder)-2)){
							echo '</span>';
						}
						$oldI++;
					}
				}
			}
			echo '</div></div>';
			$nbSlide=$oldI-1;
			$max=$nbSlide-1;
		}
	}
}

$navigation=ob_get_clean();

echo '<div class="hikashop_product_carousel">';
if($pagination_type!="no_pagination"){
	if($pagination_position=='top'){ echo $navigation; echo $html; }
	else if($pagination_position=='bottom'){ echo $html; echo $navigation; }
	else if($pagination_position=='left'){ ?><table style="margin: auto"><tr><th><?php echo $navigation;?></th><th><?php echo $html; ?></th></tr></table> <?php }
	else if($pagination_position=='right'){ ?><table style="margin: auto" ><tr><th><?php echo $html;?></th><th><?php echo $navigation; ?></th></tr></table> <?php }
	else { echo $html; echo $navigation; }
}
else{
	echo $html;
}
echo '</div>';

if($products<$nbProduct){
	if(!HIKASHOP_J30)
		JHTML::_('behavior.mootools');
	else
		JHTML::_('behavior.framework');
	if(HIKASHOP_PHP5){
		$doc = JFactory::getDocument();
	}else{
		$doc =& JFactory::getDocument();
	}

	if($carouselEffect=='slide'){
		$button='';
		$opt='';
		$numbers='';
		$auto_slide='';
		$numbersAutoSlide='';
		$maj='';
		$fixedSlide=floor($products/2);
		$ignoredSlide=$fixedSlide-1;
		if($direction=='horizontal'){ $slideDirection='margin-left'; }
		else{ $slideDirection='margin-top'; }

		$function='
			try{
				var slide_Myfx_'.$mainDivName.' = new Fx.Morph("hikashop_only_products_'.$mainDivName.'", {
					duration: '.$duration.',
					link: "cancel",
					transition: Fx.Transitions.'.$transition.',
					wait: true
				});
			}catch(err){
				var slide_fx_'.$mainDivName.' = new Fx.Style("hikashop_only_products_'.$mainDivName.'", "'.$slideDirection.'", {
					duration: '.$duration.',
					transition: Fx.Transitions.'.$transition.',
					wait: true
				});
			}';

		$start='
			try{
				slide_Myfx_'.$mainDivName.'.start({
					"'.$slideDirection.'": totIncrement
				});
			}catch(err){
				slide_fx_'.$mainDivName.'.stop()
				slide_fx_'.$mainDivName.'.start(totIncrement);
			}';

		if($pagination_type!="no_pagination"){
			if($specialPagination){
				if($pagination_type=='thumbnails'){
					$divSelected="hikashop_pagination_image_selected";
					$divBase="hikashop_pagination_images";
				}else{
					$divSelected="hikashop_slide_numbers hikashop_slide_pagination_selected";
					$divBase="hikashop_slide_numbers";
				}
				if($pagination_position=='left' || $pagination_position=='right'){
					$max--;
				}

				for($i=0;$i<$nbSlide;$i++){
					if($i<$fixedSlide){
						$incr=0;
						$slideNo=1;
						$clicked=0;
					}
					else if($i>$nbSlide-$fixedSlide-1){
						$incr=$nbSlide-1;
						$slideNo=$nbSlide-2;
						$clicked=$nbSlide-1;
					}
					else{
						$incr=$i-$fixedSlide;
						$slideNo=$i;
						$clicked=$i;
					}
					$numbers.='
						$("slide_number_'.$mainDivName.'_'.$i.'").addEvents({
							"click" : function(event){
								totIncrement = increment*(-'.($incr).');
								if(totIncrement<maxRightIncrement){
									totIncrement=maxRightIncrement;
								}
								'.$start.'
								for(i=0;i<'.$nbSlide.';i++){
									$("slide_number_'.$mainDivName.'_"+i).setAttribute("class", "'.$divBase.'");
								}
								var slideNo='.$clicked.';
								if(slideNo==0){
									$("slide_number_'.$mainDivName.'_"+slideNo).setAttribute("class", "'.$divSelected.'");
									for(i=0;i<'.$products.'-1;i++){
										$("slide_number_'.$mainDivName.'_"+(slideNo+i+1)).setAttribute("class", "'.$divSelected.'");
									}
								}else if(slideNo=='.$nbSlide.'-1){
									$("slide_number_'.$mainDivName.'_"+slideNo).setAttribute("class", "'.$divSelected.'");
									for(i=0;i<'.$products.';i++){
										$("slide_number_'.$mainDivName.'_"+(slideNo-i)).setAttribute("class", "'.$divSelected.'");
									}
								}else{
									$("slide_number_'.$mainDivName.'_'.$i.'").setAttribute("class", "'.$divSelected.'");
									var count=1;
									for(i=1;i<'.$products.';i++){
										if(i%2==1){
											$("slide_number_'.$mainDivName.'_"+(slideNo-count)).setAttribute("class", "'.$divSelected.'");
										}
										else{
											$("slide_number_'.$mainDivName.'_"+(slideNo+count)).setAttribute("class", "'.$divSelected.'");
											count++;
										}
									}
								}
							}
						});';

					$maj.='
						slide = (Math.ceil((totIncrement/maxRightIncrement)*'.$max.'))+1;
						if(slide>'.($nbSlide-$fixedSlide).'){ slide='.$nbSlide.';}
						else{ slide=slide+'.$ignoredSlide.';}
						for(i=0;i<'.$nbSlide.';i++){
							$("slide_number_'.$mainDivName.'_"+i).setAttribute("class", "'.$divBase.'");
						}
						if(slide==0){
							$("slide_number_'.$mainDivName.'_"+slide).setAttribute("class", "'.$divSelected.'");
							for(i=0;i<'.$products.'-1;i++){
								$("slide_number_'.$mainDivName.'_"+(slide+i+1)).setAttribute("class", "'.$divSelected.'");
							}
						}else if(slide=='.$nbSlide.'){
							$("slide_number_'.$mainDivName.'_"+slide).setAttribute("class", "'.$divSelected.'");
							for(i=0;i<'.$products.'-1;i++){
								if(i%2==1){
									$("slide_number_'.$mainDivName.'_"+(slide+i+1)).setAttribute("class", "'.$divSelected.'");
								}else{
									$("slide_number_'.$mainDivName.'_"+(slide-i+1)).setAttribute("class", "'.$divSelected.'");
								}
							}
						}else{
							$("slide_number_'.$mainDivName.'_"+slide).setAttribute("class", "'.$divSelected.'");
							var count=1;
							for(i=1;i<'.$products.';i++){
								if(i%2==1){
									$("slide_number_'.$mainDivName.'_"+(slide-count)).setAttribute("class", "'.$divSelected.'");
								}else{
									$("slide_number_'.$mainDivName.'_"+(slide+count)).setAttribute("class", "'.$divSelected.'");
									count++;
								}
							}
						}';
				}
			}else{
				if($pagination_type=='names' || $pagination_type=='numbers'){
					for($i=0;$i<$nbSlide;$i++){
						$numbers.='
							$("slide_number_'.$mainDivName.'_'.$i.'").addEvents({
								"click" : function(event){
									totIncrement = increment*(-'.$i.');
									if(totIncrement<maxRightIncrement){
										totIncrement=maxRightIncrement;
									}
									'.$start.'
									for(i=0;i<'.$nbSlide.';i++){
										$("slide_number_'.$mainDivName.'_"+i).setAttribute("class", "hikashop_slide_numbers");
									}
									$("slide_number_'.$mainDivName.'_'.$i.'").setAttribute("class", "hikashop_slide_numbers hikashop_slide_pagination_selected");
								}
							});';
					}
					$maj.='
						slide = (Math.ceil((totIncrement/maxRightIncrement)*'.$nbSlide.'))-1
						if(slide<0) slide=0
						for(i=0;i<'.$nbSlide.';i++){
							$("slide_number_'.$mainDivName.'_"+i).setAttribute("class", "hikashop_slide_numbers");
						}
						$("slide_number_'.$mainDivName.'_"+slide).setAttribute("class", "hikashop_slide_numbers hikashop_slide_pagination_selected");';

				}else if($pagination_type=='rounds'){
					for($i=0;$i<$nbSlide;$i++){
						$numbers.='
							$("slide_number_'.$mainDivName.'_'.$i.'").addEvents({
								"click" : function(event){
									totIncrement = increment*(-'.$i.');
									if(totIncrement<maxRightIncrement){
										totIncrement=maxRightIncrement;
									}
									'.$start.'
									for(i=0;i<'.$nbSlide.';i++){
										$("slide_number_'.$mainDivName.'_"+i).setAttribute("class", "hikashop_slide_dot_basic");
									}
									$("slide_number_'.$mainDivName.'_'.$i.'").setAttribute("class", "hikashop_slide_dot_basic hikashop_slide_dot_selected");
								}
							});';

						$maj.='
							slide = (Math.ceil((totIncrement/maxRightIncrement)*'.$nbSlide.'))-1
							if(slide<0) slide=0
							if(slide>'.$nbSlide.') slide='.$nbSlide.'
							for(i=0;i<'.$nbSlide.';i++){
								$("slide_number_'.$mainDivName.'_"+i).setAttribute("class", "hikashop_slide_dot_basic");
							}
							$("slide_number_'.$mainDivName.'_"+slide).setAttribute("class", "hikashop_slide_dot_basic hikashop_slide_dot_selected");';

					}
				}else{
					for($i=0;$i<$nbSlide;$i++){
						$numbers.='
							$("slide_number_'.$mainDivName.'_'.$i.'").addEvents({
								"click" : function(event){
									totIncrement = increment*(-'.$i.');
									if(totIncrement<maxRightIncrement){
										totIncrement=maxRightIncrement;
									}
									'.$start.'
									for(i=0;i<'.$nbSlide.';i++){
										$("slide_number_'.$mainDivName.'_"+i).setAttribute("class", "hikashop_pagination_images");
									}
									$("slide_number_'.$mainDivName.'_'.$i.'").setAttribute("class", "hikashop_pagination_image_selected");
								}
							});';

						$maj.='
							slide = (Math.ceil((totIncrement/maxRightIncrement)*'.$nbSlide.'))-1
							if(slide<0) slide=0
							if(slide>'.$nbSlide.') slide='.$nbSlide.'
							for(i=0;i<'.$nbSlide.';i++){
								$("slide_number_'.$mainDivName.'_"+i).setAttribute("class", "hikashop_pagination_images");
							}
							$("slide_number_'.$mainDivName.'_"+slide).setAttribute("class", "hikashop_pagination_image_selected");';
					}
				}
			}


			$numbersAutoSlide='
				$("hikashop_slider_pagination_'.$mainDivName.'").addEvents({
					mouseenter: function() {
						clearInterval(periodicalID);
					},
					mouseleave: begin
				});';
		}

		if($this->params->get('display_button')){
			$button='
				$("hikashop_previous_button_'.$mainDivName.'").addEvents({
					"click" : function(event){
						if(totIncrement<0){
							totIncrement = totIncrement + increment;
							'.$start.'
							'.$maj.'
						}
					}
				});
					$("hikashop_next_button_'.$mainDivName.'").addEvents({
						"click" : function(event){
							if(totIncrement>maxRightIncrement){
								totIncrement = totIncrement - increment;
								'.$start.'
								'.$maj.'
							}
						}
					});';

			$opt='
				$("hikashop_next_button_'.$mainDivName.'").addEvents({
					mouseenter: function() {
						clearInterval(periodicalID);
					},
					mouseleave: begin
				});
				$("hikashop_previous_button_'.$mainDivName.'").addEvents({
					mouseenter: function() {
						clearInterval(periodicalID);
					},
				mouseleave: begin
				});';
		}

		if($this->params->get('auto_slide')){
			$auto_slide='
				var periodicalID;
				var begin = function() {
					periodicalID = (function() {
						if(totIncrement>maxRightIncrement && direction==1){
							totIncrement = totIncrement - increment;
							'.$start.'
							'.$maj.'
						}else{
							direction=0;
						}
						if(totIncrement<0 && direction==0){
							totIncrement = totIncrement + increment;
							'.$start.'
							'.$maj.'
						}else{
							direction=1;
						}
						if(totIncrement==0 && direction==1){
							totIncrement = totIncrement - increment;
							'.$start.'
							'.$maj.'
						}
					}).periodical('.$autoSlideDuration.');
				}

				begin();
				$("hikashop_main_slider_'.$mainDivName.'").addEvents({
					mouseenter: function() {
						clearInterval(periodicalID);
					},
					mouseleave: begin
				});
				'.$opt.'
				'.$numbersAutoSlide.'';
		}

		$js='
			window.hikashop.ready( function(){
				var totIncrement = 0;
				var direction=1;
				var slide=0;
				var currentSlide=0;
				var increment = '.$increment.';
				var maxRightIncrement = increment*(-'.$max.');
				'.$function.'
				'.$button.'
				'.$auto_slide.'
				'.$numbers.'
			});';
	}
	if($carouselEffect=='fade'){
		$auto_slide='';
		$numbers='';
		$button='';
		$numbersAutoSlide='';
		$maj='';
		$opt='';

		$fadeStartNext='
			try{
				slides[currentIndex].fade("out").style.zIndex=1;
				slides[currentIndex = currentIndex < slides.length - 1 ? currentIndex+1 : 0].fade("in").style.zIndex=3;
			}catch(err){
				slides[currentIndex].effect("opacity", {duration: 400, transition: Fx.Transitions.linear}).start(0);
				slides[currentIndex = currentIndex < slides.length - 1 ? currentIndex+1 : 0].effect("opacity", {duration: 400, transition: Fx.Transitions.linear}).start(1);
			}';

		$fadeStartPrev='
			try{
				slides[currentIndex].fade("out").style.zIndex=1;
				slides[currentIndex = currentIndex-1 < 0 ? slides.length-1 : currentIndex-1].fade("in").style.zIndex=3;
			}catch(err){
				slides[currentIndex].effect("opacity", {duration: 400, transition: Fx.Transitions.linear}).start(0);
				slides[currentIndex = currentIndex-1 < 0 ? slides.length-1 : currentIndex-1].effect("opacity", {duration: 400, transition: Fx.Transitions.linear}).start(1);
			}';

		$fadeAutoStart='
			try{
				slides[currentIndex].fade("out").style.zIndex=1;
				slides[currentIndex = currentIndex < slides.length - 1 ? currentIndex+1 : 0].fade("in").style.zIndex=3;
			}catch(err){
				slides[currentIndex].effect("opacity", {duration: 400, transition: Fx.Transitions.linear}).start(0);
				slides[currentIndex = currentIndex < slides.length - 1 ? currentIndex+1 : 0].effect("opacity", {duration: 400, transition: Fx.Transitions.linear}).start(1);
			}';

		if($pagination_type!="no_pagination"){
			if($pagination_type=='names' || $pagination_type=='numbers'){
				for($i=0;$i<$nbSlide;$i++){
					$numbers.='
						$("slide_number_'.$mainDivName.'_'.$i.'").addEvents({
							"click" : function(event){
								try{
									slides[currentIndex].fade("out").style.zIndex=1;
									currentIndex='.$i.';
									slides['.$i.'].fade("in").style.zIndex=3;
								}catch(err){
									slides[currentIndex].effect("opacity", {duration: 400, transition: Fx.Transitions.linear}).start(0);
									currentIndex='.$i.';
									slides['.$i.'].effect("opacity", {duration: 400, transition: Fx.Transitions.linear}).start(1);
								}

								for(i=0;i<'.$nbSlide.';i++){
									$("slide_number_'.$mainDivName.'_"+i).setAttribute("class", "hikashop_slide_numbers");
								}
								$("slide_number_'.$mainDivName.'_'.$i.'").setAttribute("class", "hikashop_slide_numbers hikashop_slide_pagination_selected");
							}
						});';

					$maj.='
						slide = currentIndex
						for(i=0;i<'.$nbSlide.';i++){
							$("slide_number_'.$mainDivName.'_"+i).setAttribute("class", "hikashop_slide_numbers");
						}
						$("slide_number_'.$mainDivName.'_"+slide).setAttribute("class", "hikashop_slide_numbers hikashop_slide_pagination_selected");';
				}
			}else if($pagination_type=='rounds'){
				for($i=0;$i<$nbSlide;$i++){
					$numbers.='
						$("slide_number_'.$mainDivName.'_'.$i.'").addEvents({
							"click" : function(event){
								try{
									slides[currentIndex].fade("out").style.zIndex=1;
									currentIndex='.$i.';
									slides['.$i.'].fade("in").style.zIndex=3;
								}catch(err){
									slides[currentIndex].effect("opacity", {duration: 400, transition: Fx.Transitions.linear}).start(0);
									currentIndex='.$i.';
									slides['.$i.'].effect("opacity", {duration: 400, transition: Fx.Transitions.linear}).start(1);
								}
								for(i=0;i<'.$nbSlide.';i++){
									$("slide_number_'.$mainDivName.'_"+i).setAttribute("class", "hikashop_slide_dot_basic");
								}
								$("slide_number_'.$mainDivName.'_'.$i.'").setAttribute("class", "hikashop_slide_dot_basic hikashop_slide_dot_selected");
							}
						});';

					$maj.='
						slide = currentIndex
						for(i=0;i<'.$nbSlide.';i++){
							$("slide_number_'.$mainDivName.'_"+i).setAttribute("class", "hikashop_slide_dot_basic");
						}
						$("slide_number_'.$mainDivName.'_"+slide).setAttribute("class", "hikashop_slide_dot_basic hikashop_slide_dot_selected");';
				}
			}else{
				for($i=0;$i<$nbSlide;$i++){
					$numbers.='
						$("slide_number_'.$mainDivName.'_'.$i.'").addEvents({
							"click" : function(event){
								try{
									slides[currentIndex].fade("out").style.zIndex=1;
									currentIndex='.$i.';
									slides['.$i.'].fade("in").style.zIndex=3;
								}catch(err){
									slides[currentIndex].effect("opacity", {duration: 400, transition: Fx.Transitions.linear}).start(0);
									currentIndex='.$i.';
									slides['.$i.'].effect("opacity", {duration: 400, transition: Fx.Transitions.linear}).start(1);
								}
								for(i=0;i<'.$nbSlide.';i++){
									$("slide_number_'.$mainDivName.'_"+i).setAttribute("class", "hikashop_pagination_images");
								}
								$("slide_number_'.$mainDivName.'_'.$i.'").setAttribute("class", "hikashop_pagination_image_selected");
							}
						});';

					$maj.='
						slide = currentIndex
						for(i=0;i<'.$nbSlide.';i++){
							$("slide_number_'.$mainDivName.'_"+i).setAttribute("class", "hikashop_pagination_images");

						}
						$("slide_number_'.$mainDivName.'_"+slide).setAttribute("class", "hikashop_pagination_image_selected");
				';
				}
			}

			$numbersAutoSlide='
				$("hikashop_slider_pagination_'.$mainDivName.'").addEvents({
					mouseenter: function() {
						clearInterval(interval);
					},
					mouseleave: show
				});';
		}

		if($this->params->get('display_button')){
			$button='
				$("hikashop_previous_button_'.$mainDivName.'").addEvents({
					"click" : function(event){
						'.$fadeStartPrev.'
						'.$maj.'
					}
				});

				$("hikashop_next_button_'.$mainDivName.'").addEvents({
					"click" : function(event){
						'.$fadeStartNext.'
						'.$maj.'
					}
				});';

			$opt='
				$("hikashop_next_button_'.$mainDivName.'").addEvents({
					mouseenter: function() {
						clearInterval(interval);
					},
					mouseleave: show
				});
				$("hikashop_previous_button_'.$mainDivName.'").addEvents({
					mouseenter: function() {
						clearInterval(interval);
					},
					mouseleave: show
				});';
		}

		if($this->params->get('auto_slide')){
			$auto_slide='
				var show	 = function() {
					interval = (function(){
						'.$fadeAutoStart.'
						slides[currentIndex].style.display="block";
						'.$maj.'
					}).periodical(showDuration);
				};
				show();';

			$auto_slide.='
				$("hikashop_slideshow_fade_'.$mainDivName.'").addEvents({
					mouseenter: function() {
						clearInterval(interval);
					},
					mouseleave: show
				});
			'.$opt.'
			'.$numbersAutoSlide.'';
		}

		$fadeFunction="";
		for($i=0;$i<$nbSlide;$i++){
			$fadeFunction .= 'var fadeEffect_'.$i.' = new Fx.Style("product_slide_'.$i.'", "opacity", {duration:'.$duration.'});';
		}

		$js='window.hikashop.ready( function(){

			var slide=0;
			var showDuration = '.$autoSlideDuration.';
			var container = $("hikashop_slideshow_fade_'.$mainDivName.'");
			var slides = container.getElements(".product_slide");
			var currentIndex = 0;
			var interval;

			slides.each(function(item,i){
				if(i>0){
					slides[i].setStyle("opacity",0);
				}
			});

			'.$button.'
			'.$auto_slide.'
			'.$numbers.'
		});';
	}
	$doc->addScriptDeclaration("\n<!--\n".$js."\n//-->\n");
	}
