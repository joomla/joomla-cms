<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
$current_url = hikashop_currentURL();
$set = JRequest::getString('sort_comment','');
$config = JFactory::getConfig();
if(HIKASHOP_J30){
	$sef = $config->get('sef');
}else{
	$sef = $config->getValue('config.sef');
}

if(!empty($set)){
	if($sef){
		$current_url = preg_replace('/\/sort_comment-'.$set.'/','',$current_url);
	}else{
		$current_url = preg_replace('/&sort_comment='.$set.'/','',$current_url);
	}
}
$row = & $this->rows;
$elt = & $this->elts;
$pagination = & $this->pagination;
$no_comment = 1;

$hikashop_vote_con_req_list = $row->hikashop_vote_con_req_list;
$useful_rating = $row->useful_rating;
$comment_enabled = $row->comment_enabled;
$useful_style = $row->useful_style;
$show_comment_date = $row->show_comment_date;

if ($comment_enabled == 1) {
	$hikashop_vote_user_id = hikashop_loadUser();
	if (($hikashop_vote_con_req_list == 1 && $hikashop_vote_user_id != "") || $hikashop_vote_con_req_list == 0) {
		?>
		<div class="hikashop_listing_comment ui-corner-top"><?php echo JText::_('HIKASHOP_LISTING_COMMENT');?>
		<?php if($row->vote_comment_sort_frontend){ ?>
			<span style="float: right;" class="hikashop_sort_listing_comment">
				<?php
				if($sef)
					echo '<select name="sort_comment" onchange="var url=\''.$current_url.'\'+\'/sort_comment-\'+this.value;  document.location.href=\''.JRoute::_('\'+url+\'').'\'">';
				else
					echo '<select name="sort_comment" onchange="var url=\''.$current_url.'\'+\'&sort_comment=\'+this.value;  document.location.href=\''.JRoute::_('\'+url+\'').'\'">';
				?>
				<option <?php if($set == 'date')echo "selected"; ?> value="date"><?php echo JText::_('HIKASHOP_COMMENT_ORDER_DATE');?></option>
				<option <?php if($set == 'helpful')echo "selected"; ?> value="helpful"><?php echo JText::_('HIKASHOP_COMMENT_ORDER_HELPFUL');?></option>
				</select>
			</span>
		<?php } ?>
		</div>
		<?php
		for ($i = 1; $i <= count($elt); $i++) {
			if (!empty ($elt[$i]->vote_comment)) {
		?>
				<table class="ui-corner-all hika_comment_listing">
					<tr>
						<td class="hika_comment_listing_name">
							<?php
							if ($elt[$i]->vote_pseudo == '0') {
							?>
								<span class="hika_vote_listing_username"><?php echo $elt[$i]->username; ?> </span>
							<?php
							} else {
							?>
								<span class="hika_vote_listing_username" ><?php echo $elt[$i]->vote_pseudo; ?></span>
							<?php
							}
							?>
						</td>
						<td class="hika_comment_listing_stars">
							<?php
								$nb_star_vote = $elt[$i]->vote_rating;
								JRequest::setVar("nb_star",$nb_star_vote);
								$nb_star_config = $row->vote_star_number;
								JRequest::setVar("nb_max_star",$nb_star_config);
								if($nb_star_vote != 0){
									for($k=0; $k < $nb_star_vote; $k++ ){
										?><span class="hika_comment_listing_full_stars" ></span><?php
									}
									$nb_star_empty = $nb_star_config - $nb_star_vote;
									if($nb_star_empty != 0){
										for($j=0; $j < $nb_star_empty; $j++ ){
											?><span class="hika_comment_listing_empty_stars" ></span><?php
										}
									}
								}
							?>
						</td>
						<td>
							<div class="hika_comment_listing_notification" id="<?php echo $elt[$i]->vote_id; ?>" >
								<?php
								if($elt[$i]->total_vote_useful != 0){
									if($elt[$i]->vote_useful == 0){
										$hika_useful[$i] = $elt[$i]->total_vote_useful / 2;
									}
									else if($elt[$i]->total_vote_useful == $elt[$i]->vote_useful){
										$hika_useful[$i] = $elt[$i]->vote_useful;
									}
									else if($elt[$i]->total_vote_useful == -$elt[$i]->vote_useful){
										$hika_useful[$i] = 0;
									}
									else{
										$hika_useful[$i] = ($elt[$i]->total_vote_useful + $elt[$i]->vote_useful)/2;
									}
									$hika_useless[$i] = $elt[$i]->total_vote_useful - $hika_useful[$i];
									if($useful_style == "helpful"){
										echo JText::sprintf('HIKA_FIND_IT_HELPFUL',$hika_useful[$i],$elt[$i]->total_vote_useful);
									}
								}
								else{
									$hika_useless[$i] = 0;
									$hika_useful[$i]  = 0;
									if($useful_style == "helpful"){
										if ($useful_rating == 1) {
											echo JText::_('HIKASHOP_NO_USEFUL');
										}
									}
								}
								?>
							</div>
						</td>
						<?php
						if ($useful_rating == 1) {
							if($row->hide == 0 && $elt[$i]->already_vote == 0 && $elt[$i]->vote_user_id != $hikashop_vote_user_id && $elt[$i]->vote_user_id != hikashop_getIP()){
						?>
								<?php if($useful_style == "thumbs"){?>
									<td class="hika_comment_listing_useful_p ui-corner-all">
										<?php echo $hika_useful[$i];?>
									</td>
								<?php
								}
								?>
								<td class="hika_comment_listing_useful" title="Useful" onclick="hikashop_vote_useful(<?php echo $elt[$i]->vote_id;?>,1);"></td>
								<?php if($useful_style == "thumbs"){?>
									<td class="hika_comment_listing_useful_p ui-corner-all">
										<?php echo $hika_useless[$i];?>
									</td>
								<?php
								}
								?>
								<td class="hika_comment_listing_useless" title="Useless" onclick="hikashop_vote_useful(<?php echo $elt[$i]->vote_id;?>,2);"></td>
						<?php
							}
							else{
								if($useful_style == "thumbs"){
						?>
									<td class="hika_comment_listing_useful_p ui-corner-all">
										<?php echo $hika_useful[$i];?>
									</td>
									<td class="hika_comment_listing_useful locked"></td>
									<td class="hika_comment_listing_useless_p ui-corner-all">
										<?php echo $hika_useless[$i];?>
									</td>
									<td class="hika_comment_listing_useless locked"></td>
						<?php
								}
								else{
						?>
									<td class="hika_comment_listing_useful_p hide"></td>
									<td class="hika_comment_listing_useful locked hide"></td>
									<td class="hika_comment_listing_useless_p hide"></td>
									<td class="hika_comment_listing_useless locked hide"></td>
						<?php
								}
							}
						}
						?>
					</tr>
					<?php if($show_comment_date){ ?>
					<tr>
						<td colspan="8">
						<?php
							$class = hikashop_get('class.vote');
							$vote = $class->get($elt[$i]->vote_id);
							echo hikashop_getDate($vote->vote_date);
						?>
						</td>
					</tr>
					<?php } ?>
					<tr>
						<td colspan="8">
							<div id="<?php echo $i; ?>" class="hika_comment_listing_content"><?php echo $elt[$i]->vote_comment; ?></div>
						</td>
					</tr>
					<tr>
						<td colspan="8" class="hika_comment_listing_bottom">
							<?php
								if (!empty ($elt[$i]->purchased)) {
							?>
								<span class="hikashop_vote_listing_useful_bought"><?php echo JText::_('HIKASHOP_VOTE_BOUGHT_COMMENT'); ?></span>
							<?php
								}else echo "<br/>";
							?>
						</td>
					</tr>
				</table>
		<?php
			$no_comment = 0;
			}
		}
		$later = '';
		if($no_comment == 1){
			?>
				<table class="ui-corner-all hika_comment_listing">
					<tr>
						<td>
						</td>
					</tr>
					<tr>
						<td class="hika_comment_listing_empty">
							<?php echo JText::_('HIKASHOP_NO_COMMENT_YET'); ?>
						</td>
					</tr>
					<tr>
						<td>
						</td>
					</tr>
				</table>
			<?php
		}
		else{
			$this->pagination->form = '; document.hikashop_comment_form';
			$later = '<div class="pagination">'.$this->pagination->getListFooter().$this->pagination->getResultsCounter().'</div>';
			echo $later;
		}
	}
}
?>
