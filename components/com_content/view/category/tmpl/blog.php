<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

$Itemid = JRequest::getVar('Itemid');

// Get the active menu item
$menus =& JMenu::getInstance();
$menu  = $menus->getItem($Itemid);

	// Menu item parameters
	if ($mParams->get('page_title', 1)) {
		$header = $mParams->def('header', $menu->name);
	} else {
		$header = '';
	}

	$columns = $mParams->def('columns', 2);
	if ($columns == 0) {
		$columns = 1;
	}

	$intro 					= $mParams->def('intro', 4);
	$leading 				= $mParams->def('leading', 1);
	$links 					= $mParams->def('link', 4);
	$usePagination 			= $mParams->def('pagination', 2);
	$showPaginationResults 	= $mParams->def('pagination_results', 1);
	$descrip 				= $mParams->def('description', 1);
	$descrip_image	 		= $mParams->def('description_image', 1);

	$mParams->def('pageclass_sfx', '');
	$mParams->set('intro_only', 1);

	$pageclass_sfx			= $mParams->get('pageclass_sfx');

	// Lets get the article data from the model
	$rows = & $this->get('Content');

	// Pagination support
	$total		= count($rows);
	$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');
	$limit		= $intro + $leading + $links;
	$i			= $limitstart;

	// Header output
	if ($header)
	{
	?>
		<div class="componentheading<?php echo $pageclass_sfx;?>">
			<?php echo $header;?>
		</div>
	<?php
	}

	// Do we have any items to display?
	if ($total) {
		$col_width = 100 / $columns; // width of each column
		$width = 'width="'.intval($col_width).'%"';
		?>
		<table class="blog<?php echo $pageclass_sfx;?>" cellpadding="0" cellspacing="0">
		<?php
		// Secrion/Category Description & Image
		if ($descrip || $descrip_image)
		{
		?>
			<tr>
				<td valign="top">
		<?php
			if ($descrip_image && $category->image)
			{
			?>
					<img src="images/stories/<?php echo $category->image;?>" align="<?php echo $category->image_position;?>" hspace="6" alt="" />
			<?php
			}
			if ($descrip && $category->description) {
				echo $category->description;
			}
			?>
					<br/>
					<br/>
				</td>
			</tr>
			<?php
		}

		// Leading story output
		if ($leading) 
		{
		?>
			<tr>
				<td valign="top">
		<?php
			for ($i = 0; $i < $leading; $i ++) {
				if ($i >= $total) {
					// stops loop if total number of items is less than the number set to display as leading
					break;
				}
				echo '<div>';
				$this->showItem($rows[$i], $access, true);
				echo '</div>';
			}
			?>
				</td>
			</tr>
		<?php
		}
		else
		{
			$i = 0;
		}

		// Newspaper style vertical layout
		if ($intro && ($i < $total))
		{
		?>
			<tr>
				<td valign="top">
					<table width="100%" cellpadding="0" cellspacing="0"><!-- intro -->
						<tr>
		<?php
			$divider = '';
			for ($c = 0; $c < $columns; $c ++)
			{
				if ($c > 0)
				{
					$divider = " column_seperator";
				}
				?>
							<td valign="top" <?php echo $width?> class="article_column <?php echo $divider;?>">
				<?php
				$remaining = $total;
				for ($r = 0; $r < $intro / $columns; $r ++)
				{
					if ($i <= $intro && ($i < $total))
					{
						$this->showItem($rows[$i], $access);
						$i ++;
					}
				}
				?>
							</td>
				<?php
			}
			?>
						</tr>
					</table><!-- /intro -->
			<?php
		}

		// Links output
		if ($links && ($i < $total))
		{
		?>
				<tr>
					<td valign="top">
						<div class="blog_more<?php echo $pageclass_sfx;?>">
							<?php $this->showLinks($rows, $links, $total, $i);?>
						</div>
					</td>
				</tr>
		<?php
		}

		// Pagination output
		if ($usePagination) {
			if (($usePagination == 2) && ($total <= $limit)) {
				// not visible when they is no 'other' pages to display
			} else {
				// get the total number of records
				$limitstart = $limitstart ? $limitstart : 0;
				jimport('joomla.presentation.pagination');
				$pagination = new JPagination($total, $limitstart, $limit);

				if ($option == 'com_frontpage') {
					$link = 'index.php?option=com_frontpage&amp;Itemid='.$Itemid;
				} else {
					$link = 'index.php?option=com_content&amp;task='.$task.'&amp;id='.$id.'&amp;Itemid='.$Itemid;
				}
				echo '<tr>';
				echo '<td valign="top" align="center">';
				echo $pagination->getPagesLinks($link);
				echo '<br /><br />';
				echo '</td>';
				echo '</tr>';

				if ($showPaginationResults) {
					echo '<tr>';
					echo '<td valign="top" align="center">';
					echo $pagination->getPagesCounter();
					echo '</td>';
					echo '</tr>';
				}
			}
		}

		echo '</table>';

	} else {
		// Generic blog empty display
		echo '<p>'.JText::_(EMPTY_BLOG).'</p>';
	}
?>