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
class JContentViewHTML_section
{

	function show(& $model)
	{
		global $mainframe;
		
		$section = $model->getSectionData();
		$categories = $model->getCategoriesData();
		$params = & $model->getMenuParams();

		/*
		 * Handle BreadCrumbs
		 */
		$breadcrumbs = & $mainframe->getPathWay();
		$breadcrumbs->addItem($section->title, '');

		if ($params->get('page_title')) {
		?>
			<div class="componentheading<?php echo $params->get( 'pageclass_sfx' ); ?>">
				<?php echo $section->name; ?>
			</div>
		<?php
		}
		?>
		<table width="100%" cellpadding="0" cellspacing="0" border="0" align="center" class="contentpane<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<tr>
			<td width="60%" valign="top" class="contentdescription<?php echo $params->get( 'pageclass_sfx' ); ?>" colspan="2">
				<?php
				if ($section->image) {
					$link = 'images/stories/'.$section->image;
					?>
					<img src="<?php echo $link;?>" align="<?php echo $section->image_position;?>" hspace="6" alt="<?php echo $section->image;?>" />
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
				if (count($categories) > 0) {
					if ($params->get('other_cat_section')) {
						JContentViewHTML_section::buildCategories($categories, $params, $section->id);
					}
				}
				?>
			</td>
		</tr>
		</table>
		<?php
	}
	
	function buildCategories( $categories, $params, $sid)
	{
		global $mainframe;
		
		$user		= & $mainframe->getUser();
		$Itemid	= JRequest::getVar( 'Itemid', 9999,'', 'int' );
				
		if ( count($categories) ) {
			?>
			<ul>
				<?php
				foreach ($categories as $row) {
					?>
					<li>
						<?php				
						if ($row->access <= $user->get('gid')) {
							$link = sefRelToAbs('index.php?option=com_content&amp;task=category&amp;sectionid='.$sid.'&amp;id='.$row->id.'&amp;Itemid='.$Itemid);
							?>
							<a href="<?php echo $link; ?>" class="category">
								<?php echo $row->name;?></a>
								<?php
								if ($params->get('cat_items')) {
									?>
									&nbsp;<i>( <?php echo $row->numitems ." ". JText::_( 'items' );?> )</i>
									<?php
								}
								
								// Writes Category Description
								if ($params->get('cat_description') && $row->description) {
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
	}
}
?>
