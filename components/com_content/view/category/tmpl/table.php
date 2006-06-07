<?php
/**
 * @version $Id: content.php 2851 2006-03-20 21:45:20Z Jinx $
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

	// Get the sort list information
	$lists	= $this->_buildSortLists();
	$order	= null;

	$mParams->def('title',			1);
	$mParams->def('hits',			$app->getCfg('hits'));
	$mParams->def('author',			!$app->getCfg('hideAuthor'));
	$mParams->def('date',			!$app->getCfg('hideCreateDate'));
	$mParams->def('date_format',	JText::_('DATE_FORMAT_LC'));
	$mParams->def('navigation',		2);
	$mParams->def('display',		1);
	$mParams->def('display_num',	$app->getCfg('list_limit'));
	$mParams->def('other_cat',		1);
	$mParams->def('empty_cat',		0);
	$mParams->def('cat_items',		1);
	$mParams->def('cat_description',0);
	$mParams->def('back_button',	$app->getCfg('back_button'));
	$mParams->def('pageclass_sfx',	'');
	$mParams->def('headings',		1);
	$mParams->def('filter',			1);
	$mParams->def('filter_type',	'title');

	/*
	 * Set some defaults for $limit and $limitstart
	 */
	$limit		= JRequest::getVar('limit', $mParams->get('display_num'), '', 'int');
	$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');
	$total		= count( $items );

	$nOtherCats	= count($other_categories);
	$hasNewIcon	= (($access->canEdit || $access->canEditOwn) && $nOtherCats > 0);
	if( $nOtherCats <= 1)
	{
		$mParams->set('other_cat', false);
	}

	/*
	 * Create JPagination object for the content
	 */
	jimport('joomla.presentation.pagination');
	$pagination = new JPagination($total, $limitstart, $limit);
	
	if ($mParams->get('page_title'))
	{
	?>
		<div class="componentheading<?php echo $mParams->get( 'pageclass_sfx' ); ?>">
			<?php echo $category->name; ?>
		</div>
	<?php
	}
	?>
	<table width="100%" cellpadding="0" cellspacing="0" border="0" align="center" class="contentpane<?php echo $mParams->get( 'pageclass_sfx' ); ?>">
	<tr>
		<td width="60%" valign="top" class="contentdescription<?php echo $mParams->get( 'pageclass_sfx' ); ?>" colspan="2">
			<?php
	
	if ($category->image)
	{
	?>
			<img src="images/stories/<?php echo $category->image;?>" align="<?php echo $category->image_position;?>" hspace="6" alt="<?php echo $category->image;?>" />
	<?php
	}
	echo $category->description;
	?>
		</td>
	</tr>
	<tr>
		<td>
		<?php

	// Displays the Table of Items in Category View
	if ($total)
	{
		$this->buildItemTable($items, $pagination, $mParams, $lists, $access, $category->id, $category->sectionid, $order);
	}
	else
		if ($category->id)
		{
	?>
				<br />
				<?php echo JText::_( 'This Category is currently empty' ); ?>
				<br /><br />
				<?php
	
		}
	// New Content icon
	if ($hasNewIcon)
	{
		JContentHTMLHelper::newIcon($category, $mParams, $access);
	}
	?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<?php
	
	// Displays listing of Categories
	if ($mParams->get('other_cat'))
	{
	?>
			<ul>
			<?php
			foreach ($other_categories as $row)
			{
				if ($cid != $row->id)
				{
				?>
				<li>
					<?php
					if ($row->access <= $user->get('gid'))
					{
						$link = sefRelToAbs('index.php?option=com_content&amp;task=category&amp;sectionid='.$sid.'&amp;id='.$row->id.'&amp;Itemid='.$Itemid);
					?>
						<a href="<?php echo $link; ?>" class="category">
							<?php echo $row->name;?></a>
							<?php

						if ($mParams->get('cat_items'))
						{
						?>
						&nbsp;<i>( <?php echo $row->numitems ." ". JText::_( 'items' );?> )</i>
						<?php
						}

						// Writes Category Description
						if ($mParams->get('cat_description') && $row->description)
						{
						?>
						<br />
						<?php
							echo $row->description;
						}
					}
					else
					{
						echo $row->name;
					?>
						<a href="<?php echo sefRelToAbs( 'index.php?option=com_registration&amp;task=register' ); ?>">
							( <?php echo JText::_( 'Registered Users Only' ); ?> )</a>
					<?php
					}
					?>
					</li>
			<?php
				}
			}
		?>
			</ul>
		<?php
	}
	?>
		</td>
	</tr>
</table>

