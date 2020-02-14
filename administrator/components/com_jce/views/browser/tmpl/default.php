<?php

/**
 * @copyright 	Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;
?>
<div class="row">
	<?php if (!empty($this->sidebar)) : ?>
	<div id="j-sidebar-container" class="j-sidebar-container span2 col-md-2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="j-main-container span10 col-md-10">
	<?php else : ?>
	<div id="j-main-container">
	<?php endif; ?>
		<div class="ui-jce row-fluid">
			<iframe src="<?php echo $this->state->get('url');?>" frameborder="0" class="wf-admin-browser"></iframe>
		</div>
	</div>
</div>