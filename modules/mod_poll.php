<?php
/**
* @version $Id$
* @package Joomla
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

if (!defined( '_JOS_POLL_MODULE' )) {
	/** ensure that functions are declared only once */
	define( '_JOS_POLL_MODULE', 1 );

	function show_poll_vote_form( $Itemid, $moduleclass_sfx ) {
		global $database;

		$Itemid = intval( mosGetParam( $_REQUEST, 'Itemid', 0 ) );

		$query = "SELECT p.id, p.title"
		. "\n FROM #__polls AS p, #__poll_menu AS pm"
		. "\n WHERE ( pm.menuid = $Itemid OR pm.menuid = 0 )"
		. "\n AND p.id = pm.pollid"
		. "\n AND p.published = 1";

		$database->setQuery( $query );
		$polls = $database->loadObjectList();

		if($database->getErrorNum()) {
			echo "MB ".$database->stderr(true);
			return;
		}

		foreach ($polls as $poll) {
			if ($poll->id && $poll->title) {

				$query = "SELECT id, text"
				. "\n FROM #__poll_data"
				. "\n WHERE pollid = $poll->id"
				. "\n AND text <> ''"
				. "\n ORDER BY id";
				$database->setQuery($query);
				
				if(!($options = $database->loadObjectList())) {
					echo "MD ".$database->stderr(true);
					return;
				}
				
				poll_vote_form_html( $poll, $options, $Itemid, $moduleclass_sfx );
			}
		}
	}

	function poll_vote_form_html( &$poll, &$options, $Itemid, $moduleclass_sfx ) {
		;
		
		$tabclass_arr = array( 'sectiontableentry2', 'sectiontableentry1' );
		$tabcnt = 0;
		?>
		<form name="form2" method="post" action="<?php echo sefRelToAbs("index.php?option=com_poll&amp;Itemid=$Itemid"); ?>">
		
		<table width="95%" border="0" cellspacing="0" cellpadding="1" align="center" class="poll<?php echo $moduleclass_sfx; ?>">
		<tr>
			<td>
				<b><?php echo $poll->title; ?></b>
			</td>
		</tr>
		<tr>
			<td align="center">
				<table class="pollstableborder<?php echo $moduleclass_sfx; ?>" cellspacing="0" cellpadding="0" border="0">
				<?php
				for ($i=0, $n=count( $options ); $i < $n; $i++) { ?>
							<tr>
								<td class="<?php echo $tabclass_arr[$tabcnt]; ?><?php echo $moduleclass_sfx; ?>" valign="top">
									<input type="radio" name="voteid" id="voteid<?php echo $options[$i]->id;?>" value="<?php echo $options[$i]->id;?>" alt="<?php echo $options[$i]->id;?>" />
								</td>
								<td class="<?php echo $tabclass_arr[$tabcnt]; ?><?php echo $moduleclass_sfx; ?>" valign="top">
									<label for="voteid<?php echo $options[$i]->id;?>">
										<?php echo $options[$i]->text; ?>
									</label>
								</td>
							</tr>
					<?php
					if ($tabcnt == 1){
						$tabcnt = 0;
					} else {
						$tabcnt++;
					}
				}
				?>
				</table>
			</td>
		</tr>
		<tr>
			<td>
				<div align="center">
					<input type="submit" name="task_button" class="button" value="<?php echo JText::_('Vote'); ?>" />
					&nbsp;
					<input type="button" name="option" class="button" value="<?php echo JText::_('Results'); ?>" onclick="document.location.href='<?php echo sefRelToAbs("index.php?option=com_poll&amp;task=results&amp;id=$poll->id"); ?>';" />
				</div>
			</td>
		</tr>
		</table>

		<input type="hidden" name="id" value="<?php echo $poll->id;?>" />
		<input type="hidden" name="task" value="vote" />
		</form>
		<?php
	}
}

$moduleclass_sfx = $params->get( 'moduleclass_sfx' );
show_poll_vote_form( $Itemid, $moduleclass_sfx );
?>