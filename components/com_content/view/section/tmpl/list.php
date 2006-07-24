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

$Itemid    	= JRequest::getVar('Itemid');
		
// Get the paramaters of the active menu item
$menu   =& JMenu::getInstance();
$mParams =& $menu->getParams($Itemid);

	// Get some data from the model
	$categories	= & $this->get( 'Categories' );

	$mParams->def('pageclass_sfx', 		'');
	$mParams->def('other_cat_section', 	1);
	$mParams->def('empty_cat_section', 	0);
	$mParams->def('other_cat', 			1);
	$mParams->def('empty_cat', 			0);
	$mParams->def('cat_items', 			1);
	$mParams->def('cat_description', 	1);
	$mParams->def('back_button', 		$app->getCfg('back_button'));
	$mParams->def('pageclass_sfx', 		'');

	$total = count($categories);
	if ($total == 0)
	{
		$mParams->set('other_cat_section', false);
	}

	if ($mParams->get('page_title')) {
	?>
	<div class="componentheading<?php echo $mParams->get( 'pageclass_sfx' ); ?>">
		<?php echo $section->name; ?>
	</div>
<?php
	}
?>
<table width="100%" cellpadding="0" cellspacing="0" border="0" align="center" class="contentpane<?php echo $mParams->get( 'pageclass_sfx' ); ?>">
	<tr>
		<td width="60%" valign="top" class="contentdescription<?php echo $mParams->get( 'pageclass_sfx' ); ?>" colspan="2">
			<?php
			if ($section->image)
			{
			?>
				<img src="images/stories/<?php echo $section->image;?>" align="<?php echo $section->image_position;?>" hspace="6" alt="<?php echo $section->image;?>" />
			<?php
			}
			echo $section->description;
			?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
		<?php
		// Displays listing of Categories
			if ($mParams->get('other_cat_section'))
			{
			?>
			<ul>
				<?php
				foreach ($categories as $row)
				{
				?>
				<li>
					<?php
					if ($row->access <= $user->get('gid')) {
							$link = sefRelToAbs('index.php?option=com_content&amp;task=category&amp;sectionid='.$section->id.'&amp;id='.$row->id.'&amp;Itemid='.$Itemid);
							?>
						<a href="<?php echo $link; ?>" class="category">
							<?php echo $row->name;?></a>
							<?php
							if ($mParams->get('cat_items')) {
									?>
								&nbsp;<i>( <?php echo $row->numitems ." ". JText::_( 'items' );?> )</i>
								<?php
							}

								// Writes Category Description
								if ($mParams->get('cat_description') && $row->description) {
									?>
								<br />
								<?php
								echo $row->description;
								}
						} else {
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
				?>
			</ul>
			<?php
			}
			?>
		</td>
	</tr>
</table>
