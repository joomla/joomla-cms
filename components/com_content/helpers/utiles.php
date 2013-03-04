<?php
/**
 * @package		Jokte.Site
 * @subpackage	com_content
 * @copyright	Copyleft 2012 - 2013 Comunidad Juuntos.
 * @license		GNU General Public License version 3
 */

// Acceso directo a este archivo prohibido.
defined('_JEXEC') or die;

/**
 * Content Component Utiles Helper
 *
 * @static
 * @package		Jokte.Site
 * @subpackage	com_content
 * @since 1.2.2
 */
class JHtmlUtiles
{

	/**
	 * Función Gravatar
	 * Nuevo en Jokte v1.2.2
	 */
	static function avatar($item, $params)
	{
		// Parámetros globales
		$imgdefault_g 	= JURI::root().$params->get('avatar_default');
		$imgsize_g		= $params->get('avatar_size');
		
		// Correo del autor
		$autor 			= JFactory::getUser($item->created_by);
		$title_g 		= $item->created_by_alias ? $item->created_by_alias : $item->author;
		
		// Creo hash
		$hash			= md5(strtolower(trim($autor->email)));
		
		// Cargo link a Gravatar
		$str 			= file_get_contents( 'http://www.gravatar.com/'.$hash.'.php' );
		$profile 		= unserialize($str);				
		if (is_array($profile) && isset( $profile['entry'])) {
			$link = $profile['entry'][0]['profileUrl'];
		}
		
		// Position and style
		$position = $params->get('avatar_position');
		$style =''; 
		switch ($position) {			
			case '2':
				$style = 'float:right';
				break;			
			default:
				$style = 'float:left';				
				break;
		}
				
		// Imagen Gravatar o por defecto
		$imgautor		= "http://www.gravatar.com/avatar/avatar.php?gravatar_id=" . $hash."?d=".$imgdefault_g. "&s=" . $imgsize_g;		
		$gravatar		= '<div class="gravatar" style="'.$style.'">'.
								'<div class="img_gravatar">'.
									'<a href="'.$link.'" target="_blank"">'.JHtml::_('image', $imgautor, JText::_('COM_CONTACT_IMAGE_DETAILS'), array('align' => 'middle')).'</a>'.
								'</div>'.
								'<div class="name_gravatar"><small>';
		
		if ($params->get('avatar_link')) {
			$gravatar	.='		<a href="'.$link.'" target="_blank"">'.$title_g.'</a></small></div></div>';
		} else {
			$gravatar	.=		$title_g.'</small></div></div>';
		}
		

		return $gravatar;
	}

	/**
	 * Función Simple Tags
	 * Nuevo en Jokte v1.2.2
	 */
	static function simpletags($metakey)
	{
		$itemid = JRequest::getVar('Itemid');
			
		$keys 	= explode(',',$metakey);
		$tags 	= array();				  
		foreach ($keys as $tag){
			$link = 'index.php?searchword='.$tag.'&searchphrase=all&Itemid='.$itemid.'&option=com_search';
			$tags[] = '<a href="'.JRoute::_($link).'" title="'.Jtext::_('SEARCH_TAGS_TITLE').'">'.$tag.'</a>';
		}
		return $tags;
	}
	
	
	/**
	 * Función Social Buttons
	 * Nuevo en Jokte v1.2.2
	 */
	static function socialbuttons($item)
	{
		$itemid = JRequest::getVar('Itemid');
			
		$keys 	= explode(',',$metakey);
		$tags 	= array();				  
		foreach ($keys as $tag){
			$link = 'index.php?searchword='.$tag.'&searchphrase=all&Itemid='.$itemid.'&option=com_search';
			$tags[] = '<a href="'.JRoute::_($link).'" title="'.Jtext::_('SEARCH_TAGS_TITLE').'">'.$tag.'</a>';
		}
		return $buttons;
	}
	
	
	/**
	 * Función Disqus
	 * Nuevo en Jokte v1.2.2
	 */
	static function disqus($item,$params)
	{
			
		//Disqus config
		$subdomain      = $params->get('disqus_domain');
		$devmode        = $params->get('disqus_debug',0);
		
		$devcode = "";
		$html = "";
		if ($devmode)
			$devcode = "var disqus_developer = \"1\";";
		
		$url = str_replace(JURI::root(true), '', JURI::root());
        $url = preg_replace('/\/+$/', '', $url);
        
		$sefUrl = $url.$item->readmore_link;
				
		$html .= '<div id="disqus_thread"></div>
					<script type="text/javascript">
					'.$devcode.'
					 var disqus_url= "'.$sefUrl.'";
					 var disqus_identifier = "'.$item->readmore_link.'";
					 var disqus_title = "'.$item->title.'";
      				 (function() {
       					var dsq = document.createElement("script"); dsq.type = "text/javascript"; dsq.async = true;
       					dsq.src = "http://'.$subdomain.'.disqus.com/embed.js";
       					(document.getElementsByTagName("head")[0] || document.getElementsByTagName("body")[0]).appendChild(dsq);
      				})();
    				</script>
    				<noscript>Por favor, habilite Javascript para ver el <a href="http://disqus.com/?ref_noscript="'.$subdomain.'">sistema de comentarios potenciados por Disqus.</a></noscript>
					<a href="http://disqus.com" class="dq-powered">Sistemas de Comentarios Potenciado por Disqus</a>';

		return $html;
	}	
} 
