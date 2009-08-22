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
$showDate 			= $params->get('showDate', 'none') != 'none';
$showCount 			= $params->get('showMatchCount', 0);
$matchAuthor 		= $params->get('matchAuthor', 0);
$matchAuthorAlias 	= $params->get('matchAuthorAlias', 0);
$matchCategory 		= $params->get('matchCategory');
$mainKeys 			= modRelatedItemsHelper::$_mainArticleKeywords; // get keyword array for main article
$mainArticleAlias 	= modRelatedItemsHelper::$_mainArticleAlias; // alias value for main article
$mainArticleAuthor 	= modRelatedItemsHelper::$_mainArticleAuthor; // author id of main article
$mainArticleCategory = modRelatedItemsHelper::$_mainArticleCategory; // category id of main article
$keywordLabel 		= $params->get('keywordLabel', '');
$dateFormat 		= $params->get('dateFormat', JText::_('DATE_FORMAT_LC4'));
$showTooltip 		= $params->get('showTooltip', '1');
$titleLinkable 		= $params->get('titleLinkable');
$thisWord 			= '';
 ?>

<ul class="relateditems<?php echo $params->get('moduleclass_sfx'); ?>">
<?php foreach ($outputArray as $thisKeyword => $articleList) : ?>
	<?php if ($thisKeyword)  : ?>
		<li><strong>
		<?php echo (($keywordLabel) ? $keywordLabel . ' ' : '') . $thisKeyword; ?>
		</strong>
		<ul>
		<?php foreach ($articleList as $thisArticle) : ?>
			<li>
			<?php if (($showTooltip) && ($titleLinkable)) : ?>
				<a href="<?php echo $thisArticle->route;?>" class="relateditems<?php echo $params->get('moduleclass_sfx');?>">
				<span class="hasTip" title="<?php echo $thisArticle->title;?>::<?php echo $thisArticle->introtext;?>">
				<?php echo $thisArticle->title;?>
				<?php if ($showDate) echo ' - ' . JHTML::_('date', $thisArticle->date, $dateFormat); ?>
				<?php if ($showCount)  
				{
					if ($thisArticle->match_count == 1)
					{
						echo ' (1 ' . JText::_('match') . ')'; 
					}
					else
					{
						echo ' (' . $thisArticle->match_count . ' '. JText::_('matches') . ')'; 
					}
				} ?>
				</span></a>		
			<?php endif; ?>

			<?php if (!($showTooltip) && ($titleLinkable)) : ?>
				<a href="<?php echo $thisArticle->route;?>" class="relateditems<?php echo $params->get('moduleclass_sfx');?>">
				<?php echo $thisArticle->title;?>
				<?php if ($showDate) echo ' - ' . JHTML::_('date', $thisArticle->date, $dateFormat); ?>
				<?php if ($showCount)  
				{
					if ($thisArticle->match_count == 1)
					{
						echo ' (1 ' . JText::_('match') . ')'; 
					}
					else
					{
						echo ' (' . $thisArticle->match_count . ' '. JText::_('matches') . ')'; 
					}
				} ?>
				</a>
			<?php endif;?>

			<?php if (($showTooltip) && !($titleLinkable)) : ?>
				<span class="relateditems<?php echo $params->get('moduleclass_sfx');?>">
				<span class="hasTip" title="<?php echo $thisArticle->title;?>::<?php echo $thisArticle->introtext;?>">
				<?php echo $thisArticle->title;?>
				<?php if ($showDate) echo ' - ' . JHTML::_('date', $thisArticle->date, $dateFormat); ?>
				<?php if ($showCount)  
				{
					if ($thisArticle->match_count == 1)
					{
						echo ' (1 ' . JText::_('match') . ')'; 
					}
					else
					{
						echo ' (' . $thisArticle->match_count . ' '. JText::_('matches') . ')'; 
					}
				} ?>
				</span></span>		
			<?php endif; ?>
			<?php if (!($showTooltip) && !($titleLinkable)) : ?>
				<span class="relateditems<?php echo $params->get('moduleclass_sfx');?>">
				<?php echo $thisArticle->title;?>
				<?php if ($showDate) echo ' - ' . JHTML::_('date', $thisArticle->date, $dateFormat); ?>
				<?php if ($showCount)  
				{
					if ($thisArticle->match_count == 1)
					{
						echo ' (1 ' . JText::_('match') . ')'; 
					}
					else
					{
						echo ' (' . $thisArticle->match_count . ' '. JText::_('matches') . ')'; 
					}
				} ?>
				</span>
			<?php endif;?>

			</li>
		<?php endforeach;?>
		</ul><br/></li>
	<?php endif; ?>
<?php endforeach;?>
</ul>