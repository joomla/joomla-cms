<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_categories
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
foreach ($list as $item) :
	
	//TODO This should be done differently, but can't find appropriate method in the core. Definitely needs refactoring.
	$catclass = '';
	foreach($catpath as $catpathitem){
		$catpathitempiece = explode(':',$catpathitem);
		if($item->id == $catpathitempiece[0])$catclass = 'active';
		if($item->id == $catid)$catclass .= ' current';
	}
?>
	<li<?php echo ($catclass!=''?' class="'.$catclass.'"':''); ?>> <?php $levelup = $item->level - $startLevel - 1; ?>
  <h<?php echo $params->get('item_heading') + $levelup; ?>>
		<a href="<?php echo JRoute::_(ContentHelperRoute::getCategoryRoute($item->id)); ?>">
		<?php echo $item->title;?><?php if($params->get('numitems')): ?>
			(<?php echo $item->numitems; ?>)
		<?php endif; ?></a>
   </h<?php echo $params->get('item_heading') + $levelup; ?>>

		<?php
		if ($params->get('show_description', 0))
		{
			echo JHtml::_('content.prepare', $item->description, $item->getParams(), 'mod_articles_categories.content');
		}
		if ($params->get('show_children', 0) && (($params->get('maxlevel', 0) == 0) || ($params->get('maxlevel') >= ($item->level - $startLevel))) && count($item->getChildren()))
		{

			echo '<ul>';
			$temp = $list;
			$list = $item->getChildren();
			require JModuleHelper::getLayoutPath('mod_articles_categories', $params->get('layout', 'default').'_items');
			$list = $temp;
			echo '</ul>';
		}
		?>
 </li>
<?php endforeach; ?>
