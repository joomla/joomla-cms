<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_banners
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<div class="bannergroup<?php echo $params->get('moduleclass_sfx') ?>">

<?php if ($headerText) : ?>
	<div class="bannerheader"><?php echo $headerText ?></div>
<?php endif;

foreach($list as $item) :

	?><div class="banneritem<?php echo $params->get('moduleclass_sfx') ?>"><?php
	echo modBannersHelper::renderBanner($params, $item);
	?><div class="clr"> </div>
	</div>
<?php endforeach; ?>

<?php if ($footerText) : ?>
	<div class="bannerfooter<?php echo $params->get('moduleclass_sfx') ?>">
		 <?php echo $footerText ?>
	</div>
<?php endif; ?>
</div>