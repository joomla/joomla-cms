<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_popular
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<div class="tagssimilar<?php echo $moduleclass_sfx; ?>">
<?php if ($list)
{ ?>
	<ul >
	<?php foreach ($list as $item) :	?>
		<li>
			<a href="<?php echo  $item->itemUrl; ?>">
				<?php
				if (!empty($item->itemData['title']))
				{
					echo $item->itemData['title'];
				}
				elseif (!empty($item->itemData['name']))
				{
					echo $item->itemData['name'];
				}
				else
				{
					echo $item->item_name;
				}
				?></a>
		</li>
	<?php endforeach; ?>
	</ul>
<?php }
else
{ ?>
	<span> <?php echo JText::_('MOD_TAGS_SIMILAR_NO_MATCHING_TAGS') ?></span>
<?php } ?>
</div>
