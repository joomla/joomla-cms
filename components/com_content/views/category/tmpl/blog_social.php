<?php
/**
 * @package		Jokte.Site
 * @subpackage	com_content
 * @copyright	Copyleft 2012 - 2014 Comunidad Juuntos.
 * @license		GNU General Public License version 3
 */

// Acceso directo prohibido
defined('_JEXEC') or die;

// Atajo para los parámetros.
$params = $this->item->params;
$title = $this->item->title;
$link = JRoute::_(ContentHelperRoute::getArticleRoute($this->item->slug, $this->item->catid, $this->item->language));

?>
<div style="clear:both"></div>
<div class="socialButtons" style="text-align: center;float: <?php echo $params->get('align_socialbuttons') ?>; clear:right;">
	<?php 
	if ($params->get('show_facebook')) : 
		echo JHtml::_('utiles.sbFacebook',$title, $link, $params); 
	endif;
	?>
	<?php 
	if ($params->get('show_twitter')) : 
		echo JHtml::_('utiles.sbTwitter',$title, $link, $params); 
	endif;
	?>
	<?php 
	if ($params->get('show_one')) : 
		echo JHtml::_('utiles.sbGooglePlus',$title, $link, $params); 
	endif;
	?>
</div>