<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * HTML View class for the Content component
 *
 * @static
 * @package Joomla
 * @subpackage Content
 * @since 1.1
 */
class JContentViewHTML_category
{

	function show(& $model, & $access, & $lists, $order)
	{
		global $mainframe, $Itemid;

		$category = $model->getCategoryData();
		$other_categories = $model->getSiblingData();
		$items = $model->getContentData();
		$params = & $model->getMenuParams();

		/*
		 * Set some defaults for $limit and $limitstart
		 */
		$limit		= JRequest::getVar('limit', 0, '', 'int');
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');
		$total = count($items);
		$limit = $limit ? $limit : $params->get('display_num');
		if ($total <= $limit)
		{
			$limitstart = 0;
		}

		/*
		 * Create JPagination object for the content
		 */
		jimport('joomla.presentation.pagination');
		$pagination = new JPagination($total, $limitstart, $limit);

		/*
		 * Handle BreadCrumbs
		 */
		$breadcrumbs = & $mainframe->getPathWay();
		// Section
		$breadcrumbs->addItem($category->sectiontitle, sefRelToAbs('index.php?option=com_content&amp;task=section&amp;id='.$category->sectionid.'&amp;Itemid='.$Itemid));
		// Category
		$breadcrumbs->addItem($category->title, '');

		if ($params->get('page_title'))
		{
		?>
			<div class="componentheading<?php echo $params->get( 'pageclass_sfx' ); ?>">
				<?php echo $category->name; ?>
			</div>
		<?php

		}
		?>
		<table width="100%" cellpadding="0" cellspacing="0" border="0" align="center" class="contentpane<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<tr>
			<td width="60%" valign="top" class="contentdescription<?php echo $params->get( 'pageclass_sfx' ); ?>" colspan="2">
				<?php

		if ($category->image)
		{
			$link = 'images/stories/'.$category->image;
		?>
						<img src="<?php echo $link;?>" align="<?php echo $category->image_position;?>" hspace="6" alt="<?php echo $category->image;?>" />
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
		if (count($items))
		{
			JContentViewHTML_category::buildItemTable($items, $pagination, $params, $lists, $access, $category->id, $category->sectionid, $order);
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
		if (($access->canEdit || $access->canEditOwn) && count($other_categories) > 0)
		{
			$link = sefRelToAbs('index.php?option=com_content&amp;task=new&amp;sectionid='.$category->sectionid.'&amp;Itemid='.$Itemid);
		?>
					<a href="<?php echo $link; ?>">
						<img src="images/M_images/new.png" width="13" height="14" align="middle" border="0" alt="<?php echo JText::_( 'New' );?>" />
						&nbsp;<?php echo JText::_( 'New' );?>...</a>
					<br /><br />
					<?php

		}
		?>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<?php

		// Displays listing of Categories
		if (count($other_categories) > 0)
		{
			if ($params->get('other_cat'))
			{
				JContentViewHTML_category::buildCategories($other_categories, $params, $category->id, $category->sectionid);
			}
		}
		?>
			</td>
		</tr>
		</table>
		<?php

	}

	function buildCategories($categories, $params, $cid, $sid)
	{
		global $mainframe;

		$user = & $mainframe->getUser();
		$Itemid = JRequest::getVar('Itemid', 9999, '', 'int');

		if (count($categories) > 1)
		{
		?>
			<ul>
				<?php

			foreach ($categories as $row)
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

						if ($params->get('cat_items'))
						{
		?>
										&nbsp;<i>( <?php echo $row->numitems ." ". JText::_( 'items' );?> )</i>
										<?php

						}

						// Writes Category Description
						if ($params->get('cat_description') && $row->description)
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
	}

	function buildItemTable(& $items, & $pagination, & $params, & $lists, & $access, $cid, $sid, $order)
	{
		global $mainframe;

		$user = & $mainframe->getUser();
		$Itemid = JRequest::getVar('Itemid', 9999, '', 'int');

		$link = 'index.php?option=com_content&amp;task=category&amp;sectionid='.$sid.'&amp;id='.$cid.'&amp;Itemid='.$Itemid;
		?>
		<script language="javascript" type="text/javascript">
		function tableOrdering( order, dir, task ) {
			var form = document.adminForm;
		
			form.filter_order.value 	= order;
			form.filter_order_Dir.value	= dir;
			document.adminForm.submit( task );
		}
		</script>
		
		<form action="<?php echo sefRelToAbs($link); ?>" method="post" name="adminForm">
		
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<?php

		if ($params->get('filter') || $params->get('display'))
		{
		?>
			<tr>
				<td colspan="5">
					<table>
					<tr>
					<?php

			if ($params->get('filter'))
			{
		?>
						<td align="left" width="100%" nowrap="nowrap">
							<?php

				echo JText::_('Filter').'&nbsp;';
		?>
							<input type="text" name="filter" value="<?php echo $lists['filter'];?>" class="inputbox" onchange="document.adminForm.submit();" />
						</td>
					<?php

			}
			if ($params->get('display'))
			{
		?>
						<td align="right" width="100%" nowrap="nowrap">
							<?php

				$filter = '';
				if ($lists['filter'])
				{
					$filter = '&amp;filter='.$lists['filter'];
				}

				$link = 'index.php?option=com_content&amp;task=category&amp;sectionid='.$sid.'&amp;id='.$cid.'&amp;Itemid='.$Itemid.$filter;

				echo '&nbsp;&nbsp;&nbsp;'.JText::_('Display Num').'&nbsp;';
				echo $pagination->getLimitBox($link);
		?>
						</td>
						<?php

			}
		?>
					</tr>
					</table>
				</td>
			</tr>
			<?php

		}
		if ($params->get('headings'))
		{
		?>
			<tr>
				<td class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>" width="5">
					<?php echo JText::_('Num'); ?>
				</td>
				<?php

			if ($params->get('title'))
			{
		?>
					<td class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>" width="45%">
						<?php mosCommonHTML::tableOrdering( 'Item Title', 'a.title', $lists ); ?>
					</td>
					<?php

			}
			if ($params->get('date'))
			{
		?>
					<td class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>" width="25%">
						<?php mosCommonHTML::tableOrdering( 'Date', 'a.created', $lists ); ?>
					</td>
					<?php

			}
			if ($params->get('author'))
			{
		?>
					<td class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>"  width="20%">
						<?php mosCommonHTML::tableOrdering( 'Author', 'author', $lists ); ?>
					</td>
					<?php

			}
			if ($params->get('hits'))
			{
		?>
					<td align="center" class="sectiontableheader<?php echo $params->get( 'pageclass_sfx' ); ?>" width="5%" nowrap="nowrap">
						<?php mosCommonHTML::tableOrdering( 'Hits', 'a.hits', $lists ); ?>
					</td>
					<?php

			}
		?>
			</tr>
			<?php

		}

		$k = 0;
		$i = 0;
		foreach ($items as $row)
		{
			$row->created = mosFormatDate($row->created, $params->get('date_format'));
		?>
			<tr class="sectiontableentry<?php echo ($k+1) . $params->get( 'pageclass_sfx' ); ?>" >
				<td align="center">
					<?php echo $pagination->rowNumber( $i ); ?>
				</td>
				<?php

			if ($params->get('title'))
			{
				if ($row->access <= $user->get('gid'))
				{
					$link = sefRelToAbs('index.php?option=com_content&amp;task=view&amp;id='.$row->id.'&amp;Itemid='.$Itemid);
		?>
						<td>
							<a href="<?php echo $link; ?>">
								<?php echo $row->title; ?></a>
							<?php

					JContentViewHTMLHelper::editIcon($row, $params, $access);
		?>
						</td>
						<?php

				}
				else
				{
		?>
						<td>
							<?php

					echo $row->title.' : ';
					$link = sefRelToAbs('index.php?option=com_registration&amp;task=register');
		?>
							<a href="<?php echo $link; ?>">
								<?php echo JText::_( 'Register to read more...' ); ?></a>
						</td>
					<?php

				}
			}
			if ($params->get('date'))
			{
		?>
					<td>
						<?php echo $row->created; ?>
					</td>
					<?php

			}
			if ($params->get('author'))
			{
		?>
					<td >
						<?php echo $row->created_by_alias ? $row->created_by_alias : $row->author; ?>
					</td>
					<?php

			}
			if ($params->get('hits'))
			{
		?>
					<td align="center">
						<?php echo $row->hits ? $row->hits : '-'; ?>
					</td>
					<?php

			}
		?>
			</tr>
			<?php

			$k = 1 - $k;
			$i ++;
		}
		if ($params->get('navigation'))
		{
		?>
			<tr>
				<td colspan="5">&nbsp;</td>
			</tr>
			<tr>
				<td align="center" colspan="4" class="sectiontablefooter<?php echo $params->get( 'pageclass_sfx' ); ?>">
					<?php

			$filter = '';
			if ($lists['filter'])
			{
				$filter = '&amp;filter='.$lists['filter'];
			}

			$link = 'index.php?option=com_content&amp;task=category&amp;sectionid='.$sid.'&amp;id='.$cid.'&amp;Itemid='.$Itemid.$filter;
			echo $pagination->writePagesLinks($link);
		?>
				</td>
			</tr>
			<tr>
				<td colspan="5" align="right">
					<?php echo $pagination->writePagesCounter(); ?>
				</td>
			</tr>
			<?php

		}
		?>
		</table>
		
		<input type="hidden" name="id" value="<?php echo $cid; ?>" />
		<input type="hidden" name="sectionid" value="<?php echo $sid; ?>" />
		<input type="hidden" name="task" value="<?php echo $lists['task']; ?>" />
		<input type="hidden" name="option" value="com_content" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		</form>
		<?php
	}
}
?>