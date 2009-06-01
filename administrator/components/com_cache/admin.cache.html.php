<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Cache
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/**
 * HTML View class for the Cache component
 *
 * @static
 * @package 	Joomla
 * @subpackage	Cache
 * @since 		1.5
 */
class CacheView
{

	/**
	 * Displays the cache
	 *
	 * @param array An array of records
	 * @param string The URL option
	 */
	function displayCache(&$rows, &$client, &$page)
	{
		?>
		<form action="index.php" method="post" name="adminForm">
		<table class="adminlist" cellspacing="1">
			<thead>
			<tr>
				<th class="title" width="10">
					<?php echo JText::_('Num'); ?>
				</th>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($rows);?>);" />
				</th>
				<th class="title" nowrap="nowrap">
					<?php echo JText::_('Cache Group'); ?>
				</th>
				<th width="5%" align="center" nowrap="nowrap">
					<?php echo JText::_('Number of Files'); ?>
				</th>
				<th width="10%" align="center">
					<?php echo JText::_('Size'); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="6">
				<?php echo $page->getListFooter(); ?>
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php
			$rc = 0;
			for ($i = 0, $n = count($rows); $i < $n; $i ++) {
				$row = & $rows[$i];
				?>
				<tr class="<?php echo "row$rc"; ?>" >
					<td>
						<?php echo $page->getRowOffset($i); ?>
					</td>
					<td>
						<input type="checkbox" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->group; ?>" onclick="isChecked(this.checked);" />
					</td>
					<td>
						<span class="bold">
							<?php echo $row->group; ?>
						</span>
					</td>
					<td align="center">
						<?php echo $row->count; ?>
					</td>
					<td align="center">
						<?php echo $row->size ?>
					</td>
				</tr>
				<?php
				$rc = 1 - $rc;
			}
			?>
			</tbody>
		</table>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="option" value="com_cache" />
		<input type="hidden" name="client" value="<?php echo $client->id;?>" />
		<?php echo JHtml::_('form.token'); ?>
		</form>
		<?php
	}
	function showPurgeExecute(){
		?>
       <form action="index.php" method="post" name="adminForm">
        <table class="adminlist" cellspacing="1">
        <thead>
			<tr>
				<th align="left" style="text-align: left;">
					<?php echo JText::_('Purge expired items'); ?>
				</th>
			</tr>
        </thead>
        			<tr>
				<td align="left">
				<?php echo JText::_('Click on the Purge expired icon in the toolbar to delete all expired cache files. Note: Cache files that are still current will not be deleted.'); ?> <br />
                <span style="font-weight: bold"><?php echo JText::_('WARNING: This can be resource intensive on sites with large number of items!'); ?></span>
				</td>
			</tr>
        </table>
        <input type="hidden" name="task" value="" />
		<input type="hidden" name="option" value="com_cache" />
        <?php echo JHtml::_('form.token'); ?>
        </form>
         <?php
	}
	function purgeSuccess(){
		?>
        <form action="index.php" method="post" name="adminForm">
        <table class="adminlist" cellspacing="1">
        <thead>
			<tr>
				<th align="left" style="text-align: left;">
					<?php echo JText::_('Success!'); ?>
				</th>
			</tr>
        </thead>
        			<tr>
				<td align="left">
					<?php echo JText::_('Expired items have been purged'); ?>
				</td>
			</tr>
        </table>
        <input type="hidden" name="task" value="" />
		<input type="hidden" name="option" value="com_cache" />
        <?php echo JHtml::_('form.token'); ?>
        </form>
		 <?php
	}
}
