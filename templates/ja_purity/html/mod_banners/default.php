<?php // no direct access
defined('_JEXEC') or die; ?>
<div class="advs bannergroup<?php echo $params->get('moduleclass_sfx') ?>">

<?php if ($headerText) : ?>
  <div class="bannerheader"><?php echo $headerText ?></div>
<?php endif;

foreach($list as $item) :

	?><div class="banneritem<?php echo $params->get('moduleclass_sfx') ?>"><?php
	echo modBannersHelper::renderBanner($params, $item);
	?>
	</div>
<?php endforeach; ?>

<?php if ($footerText) : ?>
	<div class="bannerfooter<?php echo $params->get('moduleclass_sfx') ?>">
		 <?php echo $footerText ?>
	</div>
<?php endif; ?>
</div>
