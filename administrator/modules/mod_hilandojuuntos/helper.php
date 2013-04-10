<?php
/**
 * @version		Hilos Juuntos v1.0.1
 * @copyleft	Comunidad Juuntos - juuntos.net
 * @licencia	GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JHTML::_('stylesheet', 'hilandojuuntos.css', 'modules/mod_hilandojuuntos/', array(' media' => 'screen, projection'));

abstract class modHilandojuuntosHelper
{
	public static function render($params)
	{
		// Parámetros del módulo
		$rssurl0			= $params->get('rssurl0', '');
		$url0sep			= $params->get('url0sep', '');
		$rssurl1			= $params->get('rssurl1', '');
		$url1sep			= $params->get('url1sep', '');
		$rssurl2			= $params->get('rssurl2', '');
		$url2sep			= $params->get('url2sep', '');
		$rssurl3			= $params->get('rssurl3', '');
		$url3sep			= $params->get('url3sep', '');
		$rssurl4			= $params->get('rssurl4', '');
		$url4sep			= $params->get('url4sep', '');
		
		$canal0 ='';
		if (strlen($rssurl0)>0){
			$canal0	= modHilandojuuntosHelper::getCanalRss($rssurl0,$url0sep,$params);				
		}
		$canal1 ='';
		if (strlen($rssurl1)>0){
			$canal1	= modHilandojuuntosHelper::getCanalRss($rssurl1,$url1sep,$params);				
		}
		$canal2 ='';
		if (strlen($rssurl2)>0){
			$canal2	= modHilandojuuntosHelper::getCanalRss($rssurl2,$url2sep,$params);				
		}
		$canal3 ='';
		if (strlen($rssurl3)>0){
			$canal3	= modHilandojuuntosHelper::getCanalRss($rssurl3,$url3sep,$params);				
		}
		$canal4 ='';
		if (strlen($rssurl4)>0){
			$canal4	= modHilandojuuntosHelper::getCanalRss($rssurl4,$url4sep,$params);			
		}		
		$canal = $canal0.$canal1.$canal2.$canal3.$canal4;
		return $canal;
	}
	
	// Generar Doc	
	public static function getCanalRss($rssurl,$urlsep,$params)
	{
		$rssdesc			= $params->get('rssdesc', 1);
		$rssimage			= $params->get('rssimage', 1);
		$rssitemdesc		= $params->get('rssitemdesc', 1);
		$words				= $params->def('word_count', 0);
		$rsstitle			= $params->get('rsstitle', 1);
		$moduleclass_sfx	= $params->get('moduleclass_sfx', '');
		$rssitems			= $params->get('rssitems', 5);
		$filter = JFilterInput::getInstance();
		
		// get RSS parsed object
		$cache_time = 0;
		if ($params->get('cache')) {
			$cache_time  = $params->get('cache_time', 15) * 60;
		}

		$rssDoc = JFactory::getFeedParser($rssurl, $cache_time);


		if ($rssDoc != false)
		{
			// Cabecera de canal
			$canal['title'] = $filter->clean($rssDoc->get_title());
			$canal['link'] = $filter->clean($rssDoc->get_link());
			$canal['description'] = $filter->clean($rssDoc->get_description());
			
			// Imagen si existe
			$image['url'] = $rssDoc->get_image_url();
			$image['title'] = $rssDoc->get_image_title();

			//Manipulación de la imagen
			$iUrl	= isset($image['url']) ? $image['url'] : null;
			$iTitle = isset($image['title']) ? $image['title'] : null;

			// Items
			$items = $rssDoc->get_items();

			// Elementos de los hilos
			$items = array_slice($items, 0, $rssitems);
					
			$filter = JFilterInput::getInstance();
			$html ='';			
			$html .='<div class="moduletable'.htmlspecialchars($params->get('moduleclass_sfx')).'">';
			
			// Enlace y título si se optó
			if (!is_null($canal['title']) && $rsstitle) { 
				$html .='<div class="jh-feed">
							<div class="jh-sep">'.$urlsep.'</div>
							<div class="jh-title">
								<a href="'.htmlspecialchars(str_replace('&', '&amp;', $canal['link'])).'" target="_blank">'.htmlspecialchars($canal['title']).'</a>
							 </div>';
			}
							
			// Descripción
			if ($rssdesc) {
				$html 	.='<div class="jh-desc">'.$canal['description'].'</div';			
			}
			// Imagen
			if ($rssimage && $iUrl) {
				$html 	.=' <div class="jh-image"><img src="'.htmlspecialchars($iUrl).'" alt="'.htmlspecialchars(@$iTitle).'"/></div>';
			}
			
			$actualItems = count($items);
			$setItems = $rssitems;

			if ($setItems > $actualItems) {
				$totalItems = $actualItems;
			} else {
				$totalItems = $setItems;
			}
			$html 	.=' <div class="jh-items">
							<ul class="newsfeed'.htmlspecialchars($moduleclass_sfx).'"  >';
				
			for ($j = 0; $j < $totalItems; $j ++)
			{
				$currItem = & $items[$j];
		
				// Comienzo de un item
				$html 	.='	<li>';					
				if (!is_null($currItem->get_link())) 
				{
					// Titulo enlazable de la noticia		
					$html 	.='<a href="'.htmlspecialchars($currItem->get_link()).'" target="_child">'.htmlspecialchars($currItem->get_title()).'</a>';
				}

				// Descripción de la noticia
				if ($rssitemdesc)
				{
					$text = $filter->clean(html_entity_decode($currItem->get_description(), ENT_COMPAT, 'UTF-8'));
					$text = str_replace('&apos;', "'", $text);
					// limite de palabras
					if ($words) {
						$texts = explode(' ', $text);
						$count = count($texts);
						if ($count > $words) {
							$text = '';
							for ($i = 0; $i < $words; $i ++)
							{
								$text .= ' '.$texts[$i];
							}
							$text .= '...';
						}
					}
					$html 	.='<p class="jh-text">'.$text.'</p>';
			
				}
				// Fin de un item
				$html 	.='</li>';
			}
			// Fin de noticias
			$html 	.='	</ul></div>';
						
			// Cierres del cuerpo
			$html .= '	</div>';			
			
			return $html;
		}
		
	}
		
}
