<?php
/**
 * @version $Id: contact.php 3690 2006-05-27 04:59:14Z eddieajau $
 * @package Joomla
 * @subpackage Contact
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

jimport('joomla.presentation.pagination');
$page = new JPagination($contactCount, $limitstart, $limit);

// Set some defaults against system variables
$mParams->def('header', 			JComponentHelper::getMenuName());
$mParams->def('headings', 			1);
$mParams->def('position', 			1);
$mParams->def('email', 				1);
$mParams->def('telephone', 			1);
$mParams->def('fax', 				1);
$mParams->def('page_title',			1);
$mParams->def('back_button', 		$app->getCfg('back_button'));
$mParams->def('description_text', 	JText::_('The Contact list for this Website.'));
$mParams->def('image_align', 		'right');

// pagination parameters
$mParams->def('display_num', 		$limit);


	// table ordering
	if ( $filter_order_Dir == 'DESC' ) {
		$lists['order_Dir'] = 'ASC';
	} else {
		$lists['order_Dir'] = 'DESC';
	}
	$lists['order'] = $filter_order;
	$selected = '';

	global $Itemid, $hide_js;

	$pageclass_sfx = $mParams->get( 'pageclass_sfx' );
	$hasHeadBlock = ($currentCategory->image || $currentCategory->description);

	if ( $mParams->get( 'page_title' ) ) {
	?>
	<div class="componentheading<?php echo $pageclass_sfx; ?>">
	<?php
		if ($currentCategory->name)
		{
			echo $mParams->get('header').' - '.$currentCategory->name;
		}
		else
		{
			echo $mParams->get('header');
		}
	?>
	</div>
	<?php
	}
	?>
	<div class="contentpane<?php echo $pageclass_sfx; ?>">
	<?php
	if ($hasHeadBlock) {
		?>
		<div class="contentdescription<?php echo $pageclass_sfx; ?>">
			<?php
			// show image
			$customImage = $mParams->get('image');
			if ($customImage != -1 && $customImage != '')
			{
			?>
			<img src="images/stories/<?php echo $customImage; ?>" align="<?php echo $mParams->get('image_align'); ?>" hspace="6" alt="<?php echo JText::_( 'Contacts' ); ?>" />
			<?php
			}
			else if ($currentCategory->image)
			{
			?>
			<img src="images/stories/<?php echo $currentCategory->image; ?>" align="<?php echo $currentCategory->image_position; ?>" hspace="6" alt="<?php echo JText::_( 'Contacts' ); ?>" />
			<?php
			}
			echo $mParams->get('description_text', $currentCategory->description);
			?>
		</div>
		<?php
	}
	?>
				<?php
				$nContacts = count( $contacts );
				if ($nContacts) {
				?>
	<script language="javascript" type="text/javascript">
	function tableOrdering( order, dir, task ) {
		var form = document.adminForm;

		form.filter_order.value 	= order;
		form.filter_order_Dir.value	= dir;
		document.adminForm.submit( task );
	}
	</script>

	<form action="index.php" method="post" name="adminForm">

	<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
		<thead>
			<tr>
				<td align="right" colspan="6">
					<?php
					if ($mParams->get('display')) {
						echo JText::_('Display Num') .'&nbsp;';
						$link = "index.php?option=com_contact&amp;catid=$categoryId&amp;Itemid=$Itemid";
						echo $page->getLimitBox($link);
					}
					?>
				</td>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td align="center" colspan="6" class="sectiontablefooter<?php echo $pageclass_sfx; ?>">
					<?php
					$link = "index.php?option=com_contact&amp;catid=$categoryId&amp;Itemid=$Itemid";
					echo $page->writePagesLinks($link);
					?>
				</td>
			</tr>
			<tr>
				<td colspan="6" align="right">
					<?php echo $page->writePagesCounter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php
		if ($mParams->get( 'headings' )) {
			?>
			<tr>
				<td width="5" class="sectiontableheader<?php echo $pageclass_sfx; ?>">
					<?php echo JText::_('Num'); ?>
				</td>
				<td height="20" class="sectiontableheader<?php echo $pageclass_sfx; ?>">
					<?php mosCommonHTML::tableOrdering( 'Name', 'cd.name', $lists ); ?>
				</td>
				<?php
				if ( $mParams->get( 'position' ) ) {
					?>
					<td height="20" class="sectiontableheader<?php echo $pageclass_sfx; ?>">
						<?php mosCommonHTML::tableOrdering( 'Position', 'cd.con_position', $lists ); ?>
					</td>
					<?php
				}
				?>
				<?php
				if ( $mParams->get( 'email' ) ) {
					?>
					<td height="20" width="20%" class="sectiontableheader<?php echo $pageclass_sfx; ?>">
						<?php echo JText::_( 'Email' ); ?>
					</td>
					<?php
				}
				?>
				<?php
				if ( $mParams->get( 'telephone' ) ) {
					?>
					<td height="20" width="15%" class="sectiontableheader<?php echo $pageclass_sfx; ?>">
						<?php echo JText::_( 'Phone' ); ?>
					</td>
					<?php
				}
				?>
				<?php
				if ( $mParams->get( 'fax' ) ) {
					?>
					<td height="20" width="15%" class="sectiontableheader<?php echo $pageclass_sfx; ?>">
						<?php echo JText::_( 'Fax' ); ?>
					</td>
					<?php
				}
				?>
			</tr>
			<?php
		}

		$k = 0;
		for ($i = 0; $i < $nContacts; $i++) {
			$row = &$contacts[$i];
			$link = 'index.php?option=com_contact&amp;view=contact&amp;contact_id='. $row->id .'&amp;Itemid='. $Itemid;
			?>
			<tr>
				<td align="center" width="5">
					<?php echo $i+1; ?>
				</td>
				<td height="20" class="sectiontableentry<?php echo $k; ?>">
					<a href="<?php echo sefRelToAbs( $link ); ?>" class="category<?php echo $pageclass_sfx; ?>">
						<?php echo $row->name; ?></a>
				</td>
				<?php
				if ( $mParams->get( 'position' ) ) {
					?>
					<td class="sectiontableentry<?php echo $k; ?>">
						<?php echo $row->con_position; ?>
					</td>
					<?php
				}
				?>
				<?php
				if ( $mParams->get( 'email' ) ) {
					if ( $row->email_to ) {
						$row->email_to = mosHTML::emailCloaking( $row->email_to, 1 );
					}
					?>
					<td width="20%" class="sectiontableentry<?php echo $k; ?>">
						<?php echo $row->email_to; ?>
					</td>
					<?php
				}
				?>
				<?php
				if ( $mParams->get( 'telephone' ) ) {
					?>
					<td width="15%" class="sectiontableentry<?php echo $k; ?>">
						<?php echo $row->telephone; ?>
					</td>
					<?php
				}
				?>
				<?php
				if ( $mParams->get( 'fax' ) ) {
					?>
					<td width="15%" class="sectiontableentry<?php echo $k; ?>">
						<?php echo $row->fax; ?>
					</td>
					<?php
				}
				?>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		</tbody>
		</table>

		<input type="hidden" name="option" value="com_contact" />
		<input type="hidden" name="catid" value="<?php echo $categoryId;?>" />
		<input type="hidden" name="Itemid" value="<?php echo $Itemid;?>" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		</form>
		<?php
				}
				?>
		<div>
				<?php
				// Displays listing of Categories
				if ( ( $mParams->get( 'type' ) == 'category' ) && $mParams->get( 'other_cat' ) ) {
					JContactView::showCategories( $mParams, $categories, $categoryId );
				} else if ( ( $mParams->get( 'type' ) == 'section' ) && $mParams->get( 'other_cat_section' ) ) {
					JContactView::showCategories( $mParams, $categories, $categoryId );
				}
				?>
		</div>
	</div>
