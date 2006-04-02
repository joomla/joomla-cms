<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Polls
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
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
* @subpackage Polls
*/
class poll_html {


	function showResults( &$poll, &$votes, $first_vote, $last_vote, &$lists, &$params, &$menu ) {
		global $mainframe;

		// view parameters
		$paramPageTitle	= $params->get( 'page_title',	1 );
		$paramCSS		= $params->get( 'pageclass_sfx' );
		//$paramBackBtn	= $params->get( 'back_button', 	$mainframe->getCfg( 'back_button' ) );
		$paramHeader	= $params->get( 'header', 		$menu->name );
	
		$mainframe->SetPageTitle($poll->title);

		$breadcrumbs =& $mainframe->getPathWay();
		$breadcrumbs->setItemName(1, 'Polls');
		$breadcrumbs->addItem($poll->title, '');
?>
		<script type = "text/javascript">
		<!--
			var link = document.createElement('link');
			link.setAttribute('href', 'components/com_poll/poll_bars.css');
			link.setAttribute('rel', 'stylesheet');
			link.setAttribute('type', 'text/css');
			var head = document.getElementsByTagName('head').item(0);
			head.appendChild(link);
		//-->
		</script>
		<form action="index.php" method="post" name="poll" id="poll">
		<?php
		if ($paramPageTitle) {
			?>
			<div class="componentheading<?php echo $paramCSS; ?>">
				<?php echo $paramHeader; ?>
			</div>
			<?php
		}
		?>
		<div class="contentpane<?php echo $paramCSS; ?>">
			<label for="id">
				<?php echo JText::_('Select Poll'); ?>
				<?php echo $lists['polls']; ?>
			</label>
		</div>

		<div class="contentpane<?php echo $paramCSS; ?>">
<?php
		if (count( $votes ))
		{
			poll_html::graphit( $votes, $poll->title, $first_vote, $last_vote );
		}
?>
		</div>
		</form>
		<?php
	}


	function graphit( &$data_arr, $graphtitle, $first_vote, $last_vote ) {
		/*
		* Intialise Variables
		*/
		$polls_graphwidth 	= 200;
		$polls_barheight 	= 4;
		$polls_maxcolors 	= 5;
		$polls_barcolor 	= 0;		
		$tabcnt 			= 0;
		$colorx 			= 0;
		$maxval 			= 0;

		$maxval		= $data_arr[0]->hits;
		$sumval		= $data_arr[0]->voters;
		$nonZero	= ($maxval > 0 && $sumval > 0);

		$nOptions = count( $data_arr );
		?>
		<br />
		<table class="pollstableborder" cellspacing="0" cellpadding="0" border="0">
			<thead>
				<tr>
					<th colspan="3" class="sectiontableheader">
						<img src="components/com_poll/images/poll.png" align="middle" border="0" width="12" height="14" alt="" />
						<?php echo $graphtitle; ?>
					</th>
				</tr>
			</thead>
			<tbody>
		<?php
		$k = 0;
		for ($i = 0; $i < $nOptions; $i++) {
			$text = trim( $data_arr[$i]->text );
			$hits = $data_arr[$i]->hits;
			if ($nonZero) {
				$width		= ceil( $hits * $polls_graphwidth / $maxval );
				$percent	= round( 100 * $hits / $sumval, 1 );
			} else {
				$width = 0;
				$percent = 0;
			}
			?>
				<tr class="sectiontableentry<?php echo $k; ?>">
					<td width="100%" colspan="3">
						<?php echo stripslashes($text); ?>
					</td>
				</tr>
				<tr class="sectiontableentry<?php echo $k; ?>">
					<td align="right" width="25">
						<strong><?php echo $hits; ?></strong>&nbsp;
					</td>
	
					<td width="30" >
						<?php echo $percent; ?>%
					</td>

					<?php
					$tdclass='';
					if ($polls_barcolor==0) {
						if ($colorx < $polls_maxcolors) {
							$colorx = ++$colorx;
						} else {
							$colorx = 1;
						}
						$tdclass = "polls_color_".$colorx;
					} else {
						$tdclass = "polls_color_".$polls_barcolor;
					}
					?>
					<td width="300" >
						<div class="<?php echo $tdclass; ?>" style="height:<?php echo $polls_barheight; ?>px;width:<?php echo $percent; ?>%"></div>
					</td>
				</tr>
			<?php
			$k = 1 - $k;
		}
		?>
			</tbody>
		</table>

		<br />
		<table cellspacing="0" cellpadding="0" border="0">
			<tbody>
				<tr>
					<td class="smalldark">
						<?php echo JText::_( 'Number of Voters' ); ?>
					</td>
					<td class="smalldark">
						&nbsp;:&nbsp;
						<?php echo $sumval; ?>
					</td>
				</tr>
				<tr>
					<td class="smalldark">
						<?php echo JText::_( 'First Vote' ); ?>
					</td>
					<td class="smalldark">
						&nbsp;:&nbsp;
						<?php echo $first_vote; ?>
					</td>
				</tr>
				<tr>
					<td class="smalldark">
						<?php echo JText::_( 'Last Vote' ); ?>
					</td>
					<td class="smalldark">
						&nbsp;:&nbsp;
						<?php echo $last_vote; ?>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}
}
?>