<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Languages
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
* @package Joomla
* @subpackage Languages
*/
class HTML_languages {

	function showLanguages( &$rows, &$pageNav, $option, $client ) {
		global $my;

		?>
		<form action="index2.php" method="post" name="adminForm">

		<table class="adminlist">
		<tr>
			<th width="20">
			<?php echo JText::_( 'Num' ); ?>
			</th>
			<th width="30">
			&nbsp;
			</th>
			<th width="25%" class="title">
			<?php echo JText::_( 'Language' ); ?>
			</th>
			<th width="5%">
			<?php echo JText::_( 'Published' ); ?>
			</th>
			<th width="10%">
			<?php echo JText::_( 'Version' ); ?>
			</th>
			<th width="10%">
			<?php echo JText::_( 'Date' ); ?>
			</th>
			<th width="20%">
			<?php echo JText::_( 'Author' ); ?>
			</th>
			<th width="25%">
			<?php echo JText::_( 'Author Email' ); ?>
			</th>
		</tr>
		<?php
		$k = 0;
		for ($i=0, $n=count( $rows ); $i < $n; $i++) {
			$row = &$rows[$i];
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td width="20"><?php echo $pageNav->rowNumber( $i ); ?></td>
				<td width="20">
				<input type="radio" id="cb<?php echo $i;?>" name="cid[]" value="<?php echo $row->language; ?>" onClick="isChecked(this.checked);" />
				</td>
				<td width="25%">
				<?php echo $row->name;?>
				</td>
				<td width="5%" align="center">
				<?php
				if ($row->published == 1) {	 ?>
					<img src="images/tick.png" alt="<?php echo JText::_( 'Published' ); ?>"/>
					<?php
				} else {
					?>
					&nbsp;
				<?php
				}
				?>
				</td>
				<td align=center>
				<?php echo $row->version; ?>
				</td>
				<td align=center>
				<?php echo $row->creationdate; ?>
				</td>
				<td align=center>
				<?php echo $row->author; ?>
				</td>
				<td align=center>
				<?php echo $row->authorEmail; ?>
				</td>
			</tr>
		<?php
		}
		?>
		</table>
		<?php echo $pageNav->getListFooter(); ?>

		<input type="hidden" name="option" value="<?php echo $option;?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="hidemainmenu" value="0" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="client" value="<?php echo $client;?>" />
		</form>
		<?php
	}
}
?>