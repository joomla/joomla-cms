<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Statistics
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package Joomla
* @subpackage Statistics
*/
class HTML_statistics {
	function show( &$browsers, &$platforms, $tldomains, $bstats, $pstats, $dstats, $sorts, $option ) {
		$tab 	= mosGetParam( $_REQUEST, 'tab', 'tab1' );
		$width 	= 400;	// width of 100%
		
		$tabs 	= new mosTabs(1);
		?>	
		<form action="index2.php?option=com_statistics" method="post" name="adminForm">
		
		<div id="tablecell">				
			<?php
			$title = JText::_( 'Browsers' );
			$tabs->startPane("statsPane");
			$tabs->startTab( $title, "browsers-page" );
			?>
			
				<table class="adminlist">
				<tr>
					<th class="title">
						<?php echo ampReplace( $sorts['b_agent'] );?>
					</th>
					<th>&nbsp;</th>
					<th width="100" class="title">
						<?php echo ampReplace( $sorts['b_hits'] );?>
					</th>
					<th width="100" class="title">
						<?php echo JText::_( 'NUM' ); ?>
					</th>
				</tr>
				<?php
				$c = 1;
				if (is_array($browsers) && count($browsers) > 0) {
					$k = 0;
					foreach ($browsers as $b) {
						$f = $bstats->totalhits > 0 ? $b->hits / $bstats->totalhits : 0;
						$w = $width * $f;
					?>
					<tr class="row<?php echo $k;?>">
						<td width="200">
							<?php echo $b->agent; ?>
						</td>
						<td  width="<?php echo $width+10;?>">
							<div>
								<img src="images/blank.png" class="bar_<?php echo $c; ?>" height="6" width="<?php echo $w; ?>" />
							</div>
						</td>
						<td>
							<?php printf( "%.2f%%", $f * 100 );?>
						</td>
						<td>
							<?php echo $b->hits;?>
						</td>
					</tr>
					<?php
					$c = $c % 5 + 1;
					$k = 1 - $k;
					}
				}
				?>
				<tr>
					<th colspan="4">&nbsp;</th>
				</tr>
				</table>
			
			<?php
			$title = JText::_( 'OS Stats' );
			$tabs->endTab();
			$tabs->startTab( $title, "os-page" );
			?>
			
				<table class="adminlist">
				<tr>
					<th class="title">
						<?php echo ampReplace( $sorts['o_agent'] );?>
					</th>
					<th>&nbsp;</th>
					<th width="100" class="title">
						<?php echo ampReplace( $sorts['o_hits'] );?>
					</th>
					<th width="100" class="title">
						<?php echo JText::_( 'NUM' ); ?>
					</th>
				</tr>
				<?php
				$c = 1;
				if (is_array($platforms) && count($platforms) > 0) {
					$k = 0;
					foreach ($platforms as $p) {
						$f = $pstats->totalhits > 0 ? $p->hits / $pstats->totalhits : 0;
						$w = $width * $f;
						?>
						<tr class="row<?php echo $k;?>">
							<td width="200">
								&nbsp;<?php echo $p->agent; ?>&nbsp;
							</td>
							<td  width="<?php echo $width+10;?>">
								<div>
									<img src="images/blank.png" class="bar_<?php echo $c; ?>" height="6" width="<?php echo $w; ?>" />
								</div>
							</td>
							<td>
								<?php printf( "%.2f%%", $f * 100 );?>
							</td>
							<td>
								<?php echo $p->hits;?>
							</td>
						</tr>
						<?php
						$c = $c % 5 + 1;
						$k = 1 - $k;
					}
				}
				?>
				<tr>
					<th colspan="4">&nbsp;</th>
				</tr>
				</table>
				
			<?php
			$title = JText::_( 'Domain Stats' );
			$tabs->endTab();
			$tabs->startTab( $title, "domain-page" );
			?>
			
				<table class="adminlist">
				<tr>
					<th class="title">
						<?php echo ampReplace( $sorts['d_agent'] );?>
					</th>
					<th>&nbsp;</th>
					<th width="100" class="title">
						<?php echo ampReplace( $sorts['d_hits'] );?>
					</th>
					<th width="100" class="title">
						<?php echo JText::_( 'NUM' ); ?>
					</th>
				</tr>
				<?php
				$c = 1;
				if (is_array($tldomains) && count($tldomains) > 0) {
					$k = 0;
					foreach ($tldomains as $b) {
						$f = $dstats->totalhits > 0 ? $b->hits / $dstats->totalhits : 0;
						$w = $width * $f;
						?>
						<tr class="row<?php echo $k;?>">
							<td width="200">
								<?php echo $b->agent; ?>
							</td>
							<td  width="<?php echo $width+10;?>">
								<div>
									<img src="images/blank.png" class="bar_<?php echo $c; ?>" height="6" width="<?php echo $w; ?>" />
								</div>
							</td>
							<td>
								<?php printf( "%.2f%%", $f * 100 );?>
							</td>
							<td>
								<?php echo $b->hits;?>
							</td>
						</tr>
						<?php
						$c = $c % 5 + 1;
						$k = 1 - $k;
					}
				}
				?>
				<tr>
					<th colspan="4">&nbsp;</th>
				</tr>
				</table>
			
			<?php
			$tabs->endTab();
			$tabs->endPane();
			?>
		</div>
		
		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="tab" value="<?php echo $tab;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="op" value="bod" />
		</form>
		<?php
	}

	function pageImpressions( &$rows, $pageNav, &$lists, $task ) {
		global $my;
		?>
		<form action="index2.php?option=com_statistics&amp;task=pageimp" method="post" name="adminForm">
		
		<table class="adminform">
		<tr>
			<td align="left" width="100%">
				<?php echo JText::_( 'Filter' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
				<input type="button" value="<?php echo JText::_( 'Go' ); ?>" class="button" onclick="this.form.submit();" />
				<input type="button" value="<?php echo JText::_( 'Reset' ); ?>" class="button" onclick="getElementById('search').value='';this.form.submit();" />
			</td>
			<td nowrap="nowrap">
				<?php
				echo $lists['sectionid'];
				echo $lists['catid'];
				echo $lists['state'];
				?>
			</td>
		</tr>
		</table>
		
		<div id="tablecell">				
			<table class="adminlist">
			<tr>
				<th width="5">
					<?php echo JText::_( 'NUM' ); ?>
				</th>
				<th class="title">
					<?php mosCommonHTML :: tableOrdering( 'Title', 'c.title', $lists, $task ); ?>
				</th>
				<th width="80" align="center" nowrap="nowrap">
					<?php mosCommonHTML :: tableOrdering( 'Hits', 'c.hits', $lists, $task ); ?>
				</th>
				<th width="50" align="center" nowrap="nowrap">
					<?php mosCommonHTML :: tableOrdering( 'State', 'c.state', $lists, $task ); ?>
				</th>
				<th class="title" width="17%">
					<?php mosCommonHTML :: tableOrdering( 'Section', 'sec_title', $lists, $task ); ?>
				</th>
				<th class="title" width="17%">
					<?php mosCommonHTML :: tableOrdering( 'Category', 'cat_title', $lists, $task ); ?>
				</th>
				<th class="title" width="10%" nowrap="nowrap">
					<?php mosCommonHTML :: tableOrdering( 'Date', 'c.created', $lists, $task ); ?>
				</th>
			</tr>
			<?php
			$i = $pageNav->limitstart;
			$k = 0;
			foreach ($rows as $row) {
				$link = ampReplace( 'index2.php?option=com_content&sectionid=0&task=edit&hidemainmenu=1&id='. $row->id );
				
				// section handling
				if ($row->sectionid) {
					$row->sect_link = ampReplace( 'index2.php?option=com_sections&task=editA&hidemainmenu=1&id='. $row->sectionid );
					$title_sec		= JText::_( 'Edit Section' );
				} else {
					$row->sec_title = JText::_( 'Static Content' );
					$row->sect_link = ampReplace( 'index2.php?option=com_typedcontent' );
					$title_sec		= JText::_( 'View Static Content Manager' );
				}
				// category handling
				if ($row->catid) {
					$row->cat_link 	= ampReplace( 'index2.php?option=com_categories&task=editA&hidemainmenu=1&id='. $row->catid );
					$title_cat		= JText::_( 'Edit Category' );
				} else {
					$row->cat_title = JText::_( 'Static Content' );
					$row->cat_link = ampReplace( 'index2.php?option=com_typedcontent' );
					$title_cat		= JText::_( 'View Static Content Manager' );
				}
				
				$img			= ( $row->state	? 'tick.png' : 'publish_x.png' );
				$date 			= mosFormatDate( $row->created, JText::_( 'DATE_FORMAT_LC4' ) );
				?>
				<tr class="row<?php echo $k;?>">
					<td>
						<?php echo ++$i; ?>
					</td>
					<td>
						<?php
						if ( $row->checked_out && ( $row->checked_out != $my->id ) ) {
							echo $row->title;
						} else {
							?>
							<a href="<?php echo $link; ?>" title="<?php echo JText::_( 'Edit Content' ); ?>">
								<?php echo $row->title; ?></a>
							<?php
						}
						?>
					</td>
					<td align="center" style="font-weight: bold; font-size: larger;">						
						<?php echo $row->hits; ?>
					</td>
					<td align="center">
						<img src="images/<?php echo $img; ?>" width="12" height="12" border="0" />
					</td>
					<td>
						<a href="<?php echo $row->sect_link; ?>" title="<?php echo $title_sec; ?>">
							<?php echo $row->sec_title; ?></a>
					</td>
					<td>
						<a href="<?php echo $row->cat_link; ?>" title="<?php echo $title_cat; ?>">
							<?php echo $row->cat_title; ?></a>
					</td>
					<td>
						<?php echo $date; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</table>
			<?php echo $pageNav->getListFooter(); ?>
		</div>
		
	  	<input type="hidden" name="option" value="com_statistics" />
	  	<input type="hidden" name="task" value="pageimp" />
	  	<input type="hidden" name="op" value="pi" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="DESC" />
		</form>
		<?php
	}

	function showSearches( &$rows, $pageNav, &$lists, $option, $task ) {
		global $mainframe;

		?>
		<form action="index2.php?option=com_statistics&amp;task=searches" method="post" name="adminForm">
		
		<table class="adminform">
		<tr>
			<td align="left" width="100%">
				<?php echo JText::_( 'Filter' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
				<input type="button" value="<?php echo JText::_( 'Go' ); ?>" class="button" onclick="this.form.submit();" />
				<input type="button" value="<?php echo JText::_( 'Reset' ); ?>" class="button" onclick="getElementById('search').value='';this.form.submit();" />
			</td>
			<td nowrap="nowrap">
				<span class="componentheading"><?php echo JText::_( 'Search Logging' ); ?> :
					<?php echo $mainframe->getCfg( 'enable_log_searches' ) ? '<b><font color="green">'. JText::_( 'Enabled' ) .'</font></b>' : '<b><font color="red">'. JText::_( 'Disabled' ) .'</font></b>' ?>
				</span>
			</td>
		</tr>
		</table>
		
		<div id="tablecell">
			<table class="adminlist">
			<tr>
				<th width="10">
					<?php echo JText::_( 'NUM' ); ?>
				</th>
				<th class="title">
					<?php mosCommonHTML :: tableOrdering( 'Search Text', 'search_term', $lists, $task ); ?>
				</th>
				<th nowrap="nowrap" width="20%">
					<?php mosCommonHTML :: tableOrdering( 'Times Requested', 'hits', $lists, $task ); ?>
				</th>
				<th nowrap="nowrap" width="20%">
					<?php echo JText::_( 'Results Returned' ); ?>
				</th>
			</tr>
			<?php
			$k = 0;
			for ($i=0, $n = count($rows); $i < $n; $i++) {
				$row =& $rows[$i];
				?>
				<tr class="row<?php echo $k;?>">
					<td align="right">
						<?php echo $i+1+$pageNav->limitstart; ?>
					</td>
					<td>
						<?php echo $row->search_term;?>
					</td>
					<td align="center">
						<?php echo $row->hits; ?>
					</td>
					<td align="center">
						<?php echo $row->returns; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
		</table>
		<?php echo $pageNav->getListFooter(); ?>
	</div>
	
  	<input type="hidden" name="option" value="<?php echo $option;?>" />
  	<input type="hidden" name="task" value="<?php echo $task;?>" />
  	<input type="hidden" name="op" value="set" />
	<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="" />
	</form>
	<?php
	}
}
?>
