<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Statistics
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
* @package		Joomla
* @subpackage	Statistics
*/
class HTML_statistics
{
	function showSearches( &$rows, $pageNav, &$lists, $task, $showResults )
	{
		global $mainframe;

		JCommonHTML::loadOverlib();
		?>
		<form action="index.php?option=com_statistics&amp;task=searches" method="post" name="adminForm">

		<table>
		<tr>
			<td align="left" width="100%">
				<?php echo JText::_( 'Filter' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
				<button onclick="this.form.submit();"><?php echo JText::_( 'Go' ); ?></button>
				<button onclick="getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'Reset' ); ?></button>
			</td>
			<td nowrap="nowrap">
				<span class="componentheading"><?php echo JText::_( 'Search Logging' ); ?> :
					<?php echo $mainframe->getCfg( 'enable_log_searches' ) ? '<b><font color="green">'. JText::_( 'Enabled' ) .'</font></b>' : '<b><font color="red">'. JText::_( 'Disabled' ) .'</font></b>' ?>
				</span>
			</td>
			<td align="right">
				<?php
				if ( !$showResults ) {
					echo mosToolTip('WARN_RESULTS');
				}
				?>
			</td>
			<td align="right">
				<?php
				if ( $showResults ) {
					?>
					<input name="search_results" type="button" class="button" value="<?php echo JText::_( 'Hide Search Results' ); ?>" onclick="submitbutton('searches');">
					<?php
				} else {
					?>
					<input name="search_results" type="button" class="button" value="<?php echo JText::_( 'Show Search Results' ); ?>" onclick="submitbutton('searchesresults');">
					<?php
				}
				?>
			</td>
		</tr>
		</table>

		<div id="tablecell">
			<table class="adminlist">
			<thead>
				<tr>
					<th width="10">
						<?php echo JText::_( 'NUM' ); ?>
					</th>
					<th class="title">
						<?php JCommonHTML::tableOrdering( 'Search Text', 'search_term', $lists, $task ); ?>
					</th>
					<th nowrap="nowrap" width="20%">
						<?php JCommonHTML::tableOrdering( 'Times Requested', 'hits', $lists, $task ); ?>
					</th>
					<?php
					if ( $showResults ) {
						?>
						<th nowrap="nowrap" width="20%">
							<?php echo JText::_( 'Results Returned' ); ?>
						</th>
						<?php
					}
					?>
				</tr>
			</thead>
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
					<?php
					if ( $showResults ) {
						?>
						<td align="center">
							<?php echo $row->returns; ?>
						</td>
						<?php
					}
					?>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			<tfoot>
				<td colspan="4">
					<?php echo $pageNav->getListFooter(); ?>
				</td>
			</tfoot>
			</table>
		</div>

		<input type="hidden" name="option" value="com_statistics" />
		<input type="hidden" name="task" value="<?php echo $task;?>" />
		<input type="hidden" name="op" value="set" />
		<input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
		<input type="hidden" name="filter_order_Dir" value="" />
		</form>
		<?php
	}
}
?>
