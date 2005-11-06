<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Polls
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
* @subpackage Polls
*/
class poll_html {


	function showResults( &$poll, &$votes, $first_vote, $last_vote, $pollist, $params ) {
		global $mosConfig_live_site, $_LANG;
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
		if ( $params->get( 'page_title' ) ) {
			?>
			<div class="componentheading<?php echo $params->get( 'pageclass_sfx' ); ?>">
			<?php echo $params->get( 'header' ); ?>
			</div>
			<?php
		}
		?>
		<table width="100%" class="contentpane<?php echo $params->get( 'pageclass_sfx' ); ?>">
		<tr>
			<td align="center">
				<table class="contentpane<?php echo $params->get( 'pageclass_sfx' ); ?>">
				<tr>
					<td >
					<?php echo $_LANG->_('Select Poll'); ?>&nbsp;
					</td>
					<td >
					<?php echo $pollist; ?>
					</td>
				</tr>
				</table>

				<table cellpadding="0" cellspacing="0" border="0" class="contentpane<?php echo $params->get( 'pageclass_sfx' ); ?>">
				<?php
				if ($votes) {
					$j=0;
					$data_arr["text"]=null;
					$data_arr["hits"]=null;
					foreach ($votes as $vote) {
						$data_arr["text"][$j]=trim($vote->text);
						$data_arr["hits"][$j]=$vote->hits;
						$j++;
					}
					?>
					<tr>
						<td>
						<?php
						poll_html::graphit( $data_arr, $poll->title, $first_vote, $last_vote );
						?>
						</td>
					</tr>
					<?php
				} else {
					?>
					<tr>
						<td valign="bottom">
						<?php echo $_LANG->_( 'There are no results for this poll.' ); ?>
						</td>
					</tr>
					<?php
				}
				?>
				</table>
			</td>
		</tr>
		</table>
		<?php
		// displays back button
		mosHTML::BackButton ( $params );
		?>
		</form>
		<?php
	}


	function graphit( $data_arr, $graphtitle, $first_vote, $last_vote ) {
		global $mosConfig_live_site, $polls_maxcolors, $tabclass;
    	global $_LANG;
    
		global $polls_barheight, $polls_graphwidth, $polls_barcolor;

		$tabclass_arr = explode( ",", $tabclass );
		$tabcnt = 0;
		$colorx = 0;
		$maxval = 0;

		array_multisort( $data_arr["hits"], SORT_NUMERIC,SORT_DESC, $data_arr["text"] );

		foreach($data_arr["hits"] as $hits) {
			if ($maxval < $hits) {
				$maxval = $hits;
			}
		}
		$sumval = array_sum( $data_arr["hits"] );
		?>
		<br />
		<table class='pollstableborder' cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td colspan="2" class="sectiontableheader">
			<img src="<?php echo $mosConfig_live_site; ?>/components/com_poll/images/poll.png" align="middle" border="0" width="12" height="14" alt="" />
			<?php echo $graphtitle; ?>
			</td>
		</tr>
		<?php
		for ($i=0, $n=count($data_arr["text"]); $i < $n; $i++) {
			$text = &$data_arr["text"][$i];
			$hits = &$data_arr["hits"][$i];
			if ($maxval > 0 && $sumval > 0) {
				$width = ceil( $hits*$polls_graphwidth/$maxval );
				$percent = round( 100*$hits/$sumval, 1 );
			} else {
				$width = 0;
				$percent = 0;
			}
			?>
			<tr class="<?php echo $tabclass_arr[$tabcnt]; ?>">
				<td width='100%' colspan='2'>
				<?php echo $text; ?>
				</td>
			</tr>
			<tr class="<?php echo $tabclass_arr[$tabcnt]; ?>">
				<td>
					<table cellspacing="0" cellpadding="0" border="0" width="100%">
					<tr class='<?php echo $tabclass_arr[$tabcnt]; ?>'>
						<td align="right" width="25">
						<b>
						<?php echo $hits; ?>
						</b>
						</td>
						<td  width="2">&nbsp;

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
						<div >
						&nbsp;
						<img src='<?php echo $mosConfig_live_site; ?>/components/com_poll/images/blank.png' class='<?php echo $tdclass; ?>' height='<?php echo $polls_barheight; ?>' width='<?php echo $width; ?>' alt="" />
						</div>
						</td>
					</tr>
					</table>
				</td>
			</tr>
			<?php
			$tabcnt = 1 - $tabcnt;
		}
		?>
		</table>

		<br />
		<table cellspacing="0" cellpadding="0" border="0">
		<tr>
			<td class='smalldark'>
			<?php echo $_LANG->_( 'Number of Voters' ); ?>
			</td>
			<td class='smalldark'>
			&nbsp;:&nbsp;
			<?php echo $sumval; ?>
			</td>
		</tr>
		<tr>
			<td class='smalldark'>
			<?php echo $_LANG->_( 'First Vote' ); ?>
			</td>
			<td class='smalldark'>
			&nbsp;:&nbsp;
			<?php echo $first_vote; ?>
			</td>
		</tr>
		<tr>
			<td class='smalldark'>
			<?php echo $_LANG->_( 'Last Vote' ); ?>
			</td>
			<td class='smalldark'>
			&nbsp;:&nbsp;
			<?php echo $last_vote; ?>
			</td>
		</tr>
		</table>
		<?php
	}
}
?>
