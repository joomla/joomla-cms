<?php defined('_JEXEC') or die; ?>
<div style="float:<?php echo $float; ?>;">
	<div class="icon">
		<a href="<?php echo $button['link']; ?>">
			<?php echo JHtml::_('image.site', $button['image'], '/templates/'. $template .'/images/header/', NULL, NULL, $button['text']); ?>
			<span><?php echo $button['text']; ?></span></a>
	</div>
</div>