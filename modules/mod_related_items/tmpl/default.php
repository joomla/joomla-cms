<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_related_items
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
JHTML::_('behavior.tooltip');
$showDate = $params->get('showDate', 'none') != 'none';
$showCount = $params->get('showMatchCount', 0);
$showMatchList = $params->get('showMatchList', 0);
$dateFormat = $params->get('dateFormat', JText::_('DATE_FORMAT_LC4'));
$showTooltip = $params->get('showTooltip', '1');
$titleLinkable = $params->get('titleLinkable', '1'); ?>

<?php if ($subtitle) : ?> 
	<p class="relateditems<?php echo $params->get('moduleclass_sfx'); ?>">
	<?php echo $subtitle; ?></p><br />
<?php endif; ?> 
<?php if (count($list)) : ?>
	<ul class="relateditems<?php echo $params->get('moduleclass_sfx'); ?>">
	<?php foreach ($list as $item) : ?>
		<li>
		<?php if (($showTooltip) && ($titleLinkable)) : ?>
			<a href="<?php echo $item->route; ?>" class="relateditems<?php echo $params->get('moduleclass_sfx'); ?>">
			<span class="hasTip" title="<?php echo $item->title;?>::<?php echo $item->introtext; ?>">
			<?php echo $item->title; 
			if ($showDate) echo ' - ' . JHTML::_('date', $item->date, $dateFormat);
			if ($showCount) {
				echo ($item->match_count == 1) ? ' (1 '. JText::_('match') . ')' : 
					' (' . $item->match_count . ' ' . JText::_('matches') . ')';
			} ?>
			</span></a>	
		<?php endif; ?>
		<?php if (!($showTooltip) && ($titleLinkable)) :?>
			<a href="<?php echo $item->route; ?>" class="relateditems<?php echo $params->get('moduleclass_sfx'); ?>">
		 	<?php echo $item->title; 
			if ($showDate) echo ' - ' . JHTML::_('date', $item->date, $dateFormat);
			if ($showCount) {
				echo ($item->match_count == 1) ? ' (1 '. JText::_('match') . ')' : 
					' (' . $item->match_count . ' ' . JText::_('matches') . ')';
			} ?>
			</a>
		<?php endif; ?>

		<?php if (($showTooltip) && !($titleLinkable)) : ?>
			<span class="relateditems<?php echo $params->get('moduleclass_sfx'); ?>">
			<span class="hasTip" title="<?php echo $item->title;?>::<?php echo $item->introtext; ?>">
			<?php echo $item->title; 
			if ($showDate) echo ' - ' . JHTML::_('date', $item->date, $dateFormat);
			if ($showCount) {
				echo ($item->match_count == 1) ? ' (1 '. JText::_('match') . ')' : 
					' (' . $item->match_count . ' ' . JText::_('matches') . ')';
			} ?>
			</span></span>	
		<?php endif; ?>	

		<?php if (!($showTooltip) && !($titleLinkable)) : ?>
			<span class="relateditems<?php echo $params->get('moduleclass_sfx'); ?>">
			<?php echo $item->title; 
			if ($showDate) echo ' - ' . JHTML::_('date', $item->date, $dateFormat);
			if ($showCount) {
				echo ($item->match_count == 1) ? ' (1 '. JText::_('match') . ')' : 
					' (' . $item->match_count . ' ' . JText::_('matches') . ')';
			} ?>
			</span>	
		<?php endif; ?>	

		<?php if($showMatchList) : ?>
			<ul>
			<?php // replace category, author and author alias
				$temp_list = modRelatedItemsHelper::getKeywordArray($item);
				foreach ($temp_list as $this_keyword) : ?>
					<li> <?php echo $this_keyword; ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
		</li>
	<?php endforeach; ?>
</ul>
<?php endif; ?>