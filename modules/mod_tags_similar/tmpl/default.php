<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_popular
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<div class="tagssimilar<?php echo $moduleclass_sfx; ?>">
<?php if ($list)
{  ?>
	<ul >
	<?php foreach ($list as $i => $item) : ?>
		<li>
			<a href="<?php echo JRoute::_($item->itemData[$i]['itemUrl']); ?>">
				<?php
				if (!empty($item->itemData[$i]['title']))
				{
					echo htmlspecialchars($item->itemData[0]['title']);
				}
				elseif (!empty($item->itemData[$i]['name']))
				{
					echo htmlspecialchars($item->itemData[$i]['name']);
				}
				else
				{
					echo htmlspecialchars($item->itemData[$i]['item_name']);
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
