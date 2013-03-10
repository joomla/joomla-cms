<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_specific
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div class="tagsspecific<?php echo $moduleclass_sfx; ?>">
<ul >
<?php foreach ($list as $item) : 
	$explodedTypeAlias = explode('.', $item->type_alias);
	$item->link = 'index.php?option=' . $explodedTypeAlias[0] . '&view=' . $explodedTypeAlias[1] . '&id=' . $item->content_item_id . ':' . $item->itemData['alias'];
?>
<li>
	<a href="<?php echo JRoute::_($item->link); ?>">
		<?php echo htmlspecialchars($item->itemData['title']); ?></a>
</li>
<?php endforeach; ?>
</ul>
</div>
