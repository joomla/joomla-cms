<?php defined('_JEXEC') or die('Restricted access'); ?>
<div style="float:<?php echo $float; ?>;">
	<div class="icon">
		<a href="<?php echo JRoute::_($link); ?>">
			<?php echo JHtml::_('image.site',  $image, '/templates/'. $template .'/images/header/', NULL, NULL, $text); ?>
			<span><?php echo $text; ?></span></a>
	</div>
</div>