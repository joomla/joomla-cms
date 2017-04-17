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
JHTML::_('behavior.tooltip');
$row =& $this->rows;
$config = hikashop_config();
if($row->vote_enabled != 1)
	return;
$row->hikashop_vote_average_score = (float)hikashop_toFloat($row->hikashop_vote_average_score);
JRequest::setVar("rate_rounded",$row->hikashop_vote_average_score_rounded);
JRequest::setVar("nb_max_star",$row->hikashop_vote_nb_star);
$main_div_name = $row->main_div_name;
$hikashop_vote_user_id = hikashop_loadUser();
if(empty($hikashop_vote_user_id))
	$hikashop_vote_user_id = 0;
$select_id = "select_id_".$row->vote_ref_id;
if($main_div_name != '' ){
	$select_id .= "_".$main_div_name;
}else{
	$select_id .= "_hikashop_main_div_name";
}

if($config->get('enable_status_vote', 'vote') != 'both') {
	if(empty($main_div_name)){ ?>
	<input 	type="hidden" id="hikashop_vote_ref_id" value="<?php echo $row->vote_ref_id;?>"/>
<?php } ?>
	<input 	type="hidden" id="hikashop_vote_ok_<?php echo $row->vote_ref_id;?>" value="0"/>
	<input 	type="hidden" id="vote_type_<?php echo $row->vote_ref_id;?>" value="<?php echo $row->type_item; ?>"/>
	<input 	type="hidden" id="hikashop_vote_user_id_<?php echo $row->vote_ref_id;?>" value="<?php echo (int)$hikashop_vote_user_id;?>"/>

	<div class="hikashop_vote_stars">
		<input type="hidden" name="hikashop_vote_rating" data-type="<?php echo $row->type_item; ?>" data-max="<?php echo $row->hikashop_vote_nb_star; ?>" data-ref="<?php echo $row->vote_ref_id;?>" data-rate="<?php echo $row->hikashop_vote_average_score_rounded; ?>" id="<?php echo $select_id;?>" />
		<span class="hikashop_total_vote">(<?php echo JHTML::tooltip($row->hikashop_vote_average_score.'/'.$row->hikashop_vote_nb_star, JText::_('VOTE_AVERAGE'), '', ' '.$row->hikashop_vote_total_vote.' '); ?>) </span>
		<span id="hikashop_vote_status_<?php echo $row->vote_ref_id;?>" class="hikashop_vote_notification_mini"></span>
<?php
} else { ?>
	<div class="hikashop_vote_stars">
		<div class="ui-rating"><?php
			for($i = 1; $i <= $row->hikashop_vote_average_score_rounded; $i++) {
				echo '<span class="ui-rating-star ui-rating-full"></span>';
			}
			for($i = $row->hikashop_vote_average_score_rounded; $i < $row->hikashop_vote_nb_star; $i++) {
				echo '<span class="ui-rating-star ui-rating-empty"></span>';
			}
		?></div>
		<span class="hikashop_total_vote">(<?php echo JHTML::tooltip($row->hikashop_vote_average_score.'/'.$row->hikashop_vote_nb_star, JText::_('VOTE_AVERAGE'), '', ' '.$row->hikashop_vote_total_vote.' '); ?>) </span>
<?php
}
?>
		<input type="hidden" class="hikashop_vote_rating" data-rate="<?php echo $row->hikashop_vote_average_score_rounded; ?>" />
	</div>
