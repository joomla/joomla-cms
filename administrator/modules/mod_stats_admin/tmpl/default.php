<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_stats_admin
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('jquery.framework');
JFactory::getDocument()->addScriptDeclaration('
	jQuery(document).ready(function($) {
		$("a.js-revert").on("click", function(e) {
			e.preventDefault();
			e.stopPropagation();

			var activeTab = [];
			activeTab.push("#" + e.target.href.split("#")[1]);

			localStorage.removeItem(e.target.href.replace(/&return=[a-zA-Z0-9%]+/, "").replace(/&[a-zA-Z-_]+=[0-9]+/, ""));
			localStorage.setItem("/administrator/" + e.target.href.split("/administrator/")[1].split("#")[0], JSON.stringify(activeTab));
			return window.location.href = e.target.href.split("#")[0];
		});
	});
');
?>
<ul class="list-striped list-condensed stats-module<?php echo $moduleclass_sfx ?>">
	<?php foreach ($list as $item) : ?>
		<?php if(isset($item->link)) : ?>
			<li><span class="icon-<?php echo $item->icon; ?>" title="<?php echo $item->title; ?>"></span> <?php echo $item->title . ' '; ?><a class="badge badge-info js-revert" href ="<?php echo $item->link; ?>"><?php echo $item->data; ?></a></li>
		<?php else : ?>
			<li><span class="icon-<?php echo $item->icon; ?>" title="<?php echo $item->title; ?>"></span> <?php echo $item->title; ?> <?php echo $item->data; ?></li>
		<?php endif; ?>
	<?php endforeach; ?>
</ul>
