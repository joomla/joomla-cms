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
class VoteViewvote extends HikaShopView {
	function display($tpl = null,$params=array()){
		$this->paramBase = HIKASHOP_COMPONENT.'.'.$this->getName();
		$this->params =& $params;
		$function = $this->getLayout();
		if(method_exists($this,$function)) $this->$function();
		parent::display($tpl);
	}

	function listing(){
		$doc = JFactory::getDocument();
		$doc->addScript(HIKASHOP_JS . 'vote.js');
		$class = hikashop_get('class.vote');
		$class->loadJS();
		$db = JFactory::getDBO();
		$config = hikashop_config();
		$type_item = JRequest::getCmd('ctrl');
		$ctrl_param = $this->params->get('main_ctrl','');
		if(!empty($ctrl_param))
			$type_item = $ctrl_param;
		$row = new stdClass();
		$elts = null;
		$hikashop_vote_con_req_list = $config->get('show_listing_comment',0);
		$comment_to_show = $config->get('number_comment_product');
		$useful_rating = $config->get('useful_rating',0);
		$useful_style = $config->get('vote_useful_style');
		$vote_comment_sort = $config->get('vote_comment_sort');
		$access_useful = $config->get('register_note_comment',0);
		$show_comment_date = $config->get('show_comment_date',0);
		$vote_comment_sort_frontend = $config->get('vote_comment_sort_frontend',0);
		$hikashop_vote_user_id = hikashop_loadUser();
		if(!empty($this->params))
			JRequest::setVar('productlayout',$this->params->get('productlayout','show_default'));
		else
			JRequest::setVar('productlayout','show_default');

		$hide = 1; //already voted !!
		if(($access_useful == 1 && !empty($hikashop_vote_user_id)) || $access_useful == 0){
			$hide = 0;
		}

		if($config->get('enable_status_vote',0)=='comment' || $config->get('enable_status_vote',0)=='two' || $config->get('enable_status_vote',0)=='both' ){
			$comment_enabled = 1;
		}
		else{
			$comment_enabled = 0;
		}
		if ($comment_enabled == 1){
			if(!empty($this->params)){
				$hikashop_vote_ref_id = $this->params->get('vote_ref_id');
				if(empty($hikashop_vote_ref_id))
					$hikashop_vote_ref_id = $this->params->get('product_id');
			}else{
				$hikashop_vote_ref_id = hikashop_getCID();
			}

			$i = 1;

			$app = JFactory::getApplication();
			$pageInfo = new stdClass();
			$pageInfo->filter = new stdClass();
			$pageInfo->filter->order = new stdClass();
			$pageInfo->limit = new stdClass();
			$pageInfo->elements = new stdClass();
			$pageInfo->limit->start = $app->getUserStateFromRequest( $this->paramBase.'.limitstart', 'limitstart', 0, 'int' );
			$app->setUserState($this->paramBase.'.list_limit',$comment_to_show);
			$oldValue = $app->getUserState($this->paramBase.'.list_limit');
			if(empty($oldValue)){
				$oldValue =$app->getCfg('list_limit');
			}
			$pageInfo->limit->value = $comment_to_show;
			$app->setUserState($this->paramBase.'.list_limit',$comment_to_show);
			if($oldValue!=$pageInfo->limit->value){
				$pageInfo->limit->start = 0;
				$app->setUserState($this->paramBase.'.limitstart',0);
			}
			if (($hikashop_vote_con_req_list == 1 && $hikashop_vote_user_id != "") || $hikashop_vote_con_req_list == 0) { // if log needed and user logged in or log not needed
				$where = ' WHERE vote_published = 1 AND vote_type = '.$db->quote($type_item).' AND vote_ref_id = ' . (int) $hikashop_vote_ref_id . ' AND vote_comment != \'\'';
				$order=' ORDER BY `vote_useful` DESC, `vote_date` ASC';
				if($vote_comment_sort == "date"){
					$order = ' ORDER BY `vote_date` ASC';
				}elseif($vote_comment_sort == "date_desc"){
					$order = ' ORDER BY `vote_date` DESC';
				}
				$sort_comments = JRequest::getString('sort_comment','');
				if($sort_comments == "date"){
					$order = ' ORDER BY `vote_date` ASC';
				}else if($sort_comments == "date_desc"){
					$order = ' ORDER BY `vote_date` DESC';
				}else if($sort_comments == "helpful"){
					$order=' ORDER BY `vote_useful` DESC, `vote_date` ASC';
				}
				$query = 'FROM `#__hikashop_vote` AS hika_vote LEFT JOIN `#__hikashop_user` AS hika_user ON hika_vote.vote_user_id=hika_user.user_id LEFT JOIN `#__users`AS users ON hika_user.user_cms_id=users.id '.$where.'';
				$db->setQuery('SELECT COUNT(*) '.$query);
				$total = $db->loadResult();
				if($total < $pageInfo->limit->start){
					$pageInfo->limit->start = 0;
				}
				$limit = ' LIMIT '.(int)$pageInfo->limit->start.','.(int)$pageInfo->limit->value.'';
				$db->setQuery('SELECT * '.$query.$order.$limit);
				$scores = $db->loadObjectList();
				$elts = array();

				foreach ($scores as $hikashop_vote) {
					$elts[$i] = clone($hikashop_vote);
					$elts[$i]->total_vote_useful = 0;	//know the total of useful vote for this post
					$query= 'SELECT count(vote_user_id) FROM '.hikashop_table('vote_user').' WHERE vote_user_id = '.(int)$elts[$i]->vote_id.'';
					$db->setQuery($query);
					$elts[$i]->total_vote_useful = $db->loadResult();

					$elts[$i]->already_vote = 0;	//know if the user already vote for this post
					if(empty($hikashop_vote_user_id))$hikashop_vote_user_id = hikashop_getIP();
					$query=	'SELECT vote_user_useful FROM '.hikashop_table('vote_user').' WHERE vote_user_id = '.(int)$elts[$i]->vote_id.' AND vote_user_user_id = '.$db->quote($hikashop_vote_user_id).'';
					$db->setQuery($query);
					$elts[$i]->already_vote = $db->loadResult();

					if (!empty ($hikashop_vote->vote_comment) && $type_item){
						$purchased = '';
						$query = 'SELECT order_id FROM '.hikashop_table('order').' WHERE order_user_id = '.$db->quote($hikashop_vote->vote_user_id).'';
						$db->setQuery($query);
						if(!HIKASHOP_J25){
							$order_ids = $db->loadResultArray();
						} else {
							$order_ids = $db->loadColumn();
						}
						if(!empty($order_ids)){
							$query = 'SELECT product_id FROM '.hikashop_table('product').' WHERE product_parent_id = '.(int)$hikashop_vote_ref_id.'';
							$db->setQuery($query);
							if(!HIKASHOP_J25){
								$product_ids = $db->loadResultArray();
							} else {
								$product_ids = $db->loadColumn();
							}
							if(empty($product_ids)){
								$product_ids =  array(0 => 0);	//if the article has no variants
							}
							$query = 'SELECT order_product_id FROM '.hikashop_table('order_product').' WHERE order_id IN ('.implode(',',$order_ids).') AND product_id = '.(int)$hikashop_vote_ref_id.' OR product_id IN ('.implode(',',$product_ids).')';
							$db->setQuery($query);
							$result = $db->loadResult();
							if(!empty($result))
								$purchased = 1;
						}
						$elts[$i]->purchased = $purchased;
					}
					if($elts[$i]->vote_useful >10){
						$row->top_ranked = $elts[$i]->vote_id;
					}
					$i++;
				}
				$pageInfo->elements->total = $total;
				jimport('joomla.html.pagination');
				$pagination = hikashop_get('helper.pagination', $pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);
				$pagination->hikaSuffix = '';
				$this->assignRef('pagination',$pagination);
				$this->assignRef('pageInfo',$pageInfo);
			}
			$row->comment_to_show = $i;
		}
		$row->hikashop_vote_con_req_list = $hikashop_vote_con_req_list;
		$row->useful_rating = $useful_rating;
		$row->comment_enabled = $comment_enabled;
		$row->show_comment_date = $show_comment_date;
		$row->vote_comment_sort_frontend = $vote_comment_sort_frontend;
		$row->vote_star_number	= $config->get('vote_star_number');
		$row->hide = $hide;
		$row->useful_style = $useful_style;
		$this->assignRef('rows', $row);
		$this->assignRef('elts', $elts);
	}
	function form(){
		$doc = JFactory::getDocument();
		$doc->addScript(HIKASHOP_JS . 'vote.js');
		$class = hikashop_get('class.vote');
		$class->loadJS();
		$type_item = $this->params->get('vote_type','');
		if(empty($type_item))
			$type_item = JRequest::getCmd('ctrl');
		$config = hikashop_config();
		$db = JFactory::getDBO();
		if(!empty($this->params)){
			$hikashop_vote_ref_id = $this->params->get('vote_ref_id');
			if(empty($hikashop_vote_ref_id))
				$hikashop_vote_ref_id = $this->params->get('product_id');
		}else{
			$hikashop_vote_ref_id = hikashop_getCID('product_id');
		}
		$access_vote = $config->get('access_vote');
		$hikashop_vote_nb_star = $config->get('vote_star_number');
		$email_comment = $config->get('email_comment',0);
		$number_comment_product = $config->get('number_comment_product' ,0);
		$query = 'SELECT count(vote_id) FROM `#__hikashop_vote` WHERE vote_ref_id = '.(int)$hikashop_vote_ref_id.' AND vote_type = '.$db->quote('product').'';
		$db->setQuery($query);
		$countcomment = $db->loadResult();
		$comment_enabled = 0;
		if(($config->get('enable_status_vote',0)=='comment' || $config->get('enable_status_vote',0)=='two' || $config->get('enable_status_vote',0)=='both' )){
			$comment_enabled = 1;
		}
		$vote_enabled = 0;
		if($config->get('enable_status_vote',0)=='vote' || $config->get('enable_status_vote',0)=='two' || $config->get('enable_status_vote',0)=='both' ){
			$vote_enabled = 1;
		}
		if($type_item == 'vendor'){
			$query = 'SELECT vendor_average_score, vendor_total_vote FROM ' . hikashop_table('hikamarket_vendor',false) . ' WHERE vendor_id = ' . (int) $hikashop_vote_ref_id;
			$db->setQuery($query);
			$hikashop_vote_score = $db->loadObject();
			$hikashop_vote_average_score = $hikashop_vote_score->vendor_average_score;
			$hikashop_vote_total_vote = $hikashop_vote_score->vendor_total_vote;
		}else{
			$query = 'SELECT product_average_score, product_total_vote FROM ' . hikashop_table('product') . ' WHERE product_id = ' . (int) $hikashop_vote_ref_id;
			$db->setQuery($query);
			$hikashop_vote_score = $db->loadObject();
			$hikashop_vote_average_score = $hikashop_vote_score->product_average_score;
			$hikashop_vote_total_vote = $hikashop_vote_score->product_total_vote;
		}

		$hikashop_vote_average_score_rounded = round($hikashop_vote_average_score, 0);
		$purchased = 0;
		$vote_if_bought=0;
		if($config->get('access_vote',0)=='buyed'){
			$vote_if_bought = 1;
			$hikashop_vote_user_id = hikashop_loadUser();
		}
		if($vote_if_bought == 1 && !empty($hikashop_vote_user_id) && $type_item == 'product'){
			$query = 'SELECT order_id FROM '.hikashop_table('order').' WHERE order_user_id = '.$db->quote($hikashop_vote_user_id).'';
			$db->setQuery($query);
			if(!HIKASHOP_J25){
				$order_ids = $db->loadResultArray();
			} else {
				$order_ids = $db->loadColumn();
			}
			if(!empty($order_ids)){
				$query = 'SELECT product_id FROM '.hikashop_table('product').' WHERE product_parent_id = '.(int)$hikashop_vote_ref_id.'';
				$db->setQuery($query);
				if(!HIKASHOP_J25){
					$product_ids = $db->loadResultArray();
				} else {
					$product_ids = $db->loadColumn();
				}
				if(empty($product_ids)){
					$product_ids =  array(0 => 0);	//if the article has no variants
				}
				$query = 'SELECT order_product_id FROM '.hikashop_table('order_product').' WHERE order_id IN ('.implode(',',$order_ids).') AND product_id = '.(int)$hikashop_vote_ref_id.' OR product_id IN ('.implode(',',$product_ids).')';
				$db->setQuery($query);
				$result = $db->loadObjectList();
				if(!empty($result))
					$purchased = 1;
			}
		} // else if vote if bought and type_item == vendor >> check if user has bought an item of the vendor
		$row = new stdClass;
		$row->vote_ref_id = $hikashop_vote_ref_id;
		$row->hikashop_vote_average_score_rounded = $hikashop_vote_average_score_rounded;
		$row->type_item = $type_item;
		$row->hikashop_vote_nb_star = $hikashop_vote_nb_star;
		$row->email_comment = $email_comment;
		$row->comment_enabled = $comment_enabled;
		$row->vote_enabled = $vote_enabled;
		$row->hikashop_vote_average_score = $hikashop_vote_average_score;
		$row->hikashop_vote_total_vote = $hikashop_vote_total_vote;
		$row->access_vote = $access_vote;
		$row->purchased = $purchased;
		$this->assignRef('row', $row);
	}
	function mini(){
		if(!HIKASHOP_PHP5) {
			$doc =& JFactory::getDocument();
		} else {
			$doc = JFactory::getDocument();
		}
		$type_item = JRequest::getCmd('ctrl');
		$class = hikashop_get('class.vote');
		$class->loadJS();
		$doc->addScript(HIKASHOP_JS.'vote.js');
		$config = hikashop_config();
		$db = JFactory::getDBO();
		$hikashop_vote_nb_star = $config->get('vote_star_number');
		if($config->get('enable_status_vote',0)=='vote' || $config->get('enable_status_vote',0)=='two' || $config->get('enable_status_vote',0)=='both' ){
			$vote_enabled = 1;
		}else{
			$vote_enabled = 0;
		}
		$row = new stdClass();
		$row->vote_enabled = $vote_enabled;
		if($vote_enabled == 1){
			if(!empty($this->params)){
				$main_div_name = $this->params->get('main_div_name');
				$hikashop_vote_ref_id = $this->params->get('vote_ref_id');
				if(empty($hikashop_vote_ref_id))
					$hikashop_vote_ref_id = $this->params->get('product_id');
				$listing_true = $this->params->get('listing_product');
				$type_item = $this->params->get('vote_type');
			}
			$hikashop_vote_user_id = hikashop_loadUser();
			if($type_item == 'vendor'){
				$query = 'SELECT vendor_average_score, vendor_total_vote FROM '.hikashop_table('hikamarket_vendor',false).' WHERE vendor_id = '.(int)$hikashop_vote_ref_id;
				$db->setQuery($query);
				$scores = $db->loadObject();
				$hikashop_vote_average_score = $scores->vendor_average_score;
				$hikashop_vote_total_vote = $scores->vendor_total_vote;
			}else{
				$query = 'SELECT product_average_score, product_total_vote FROM '.hikashop_table('product').' WHERE product_id = '.(int)$hikashop_vote_ref_id;
				$db->setQuery($query);
				$scores = $db->loadObject();
				$hikashop_vote_average_score = $scores->product_average_score;
				$hikashop_vote_total_vote = $scores->product_total_vote;
			}

			$hikashop_vote_average_score_rounded = round($hikashop_vote_average_score, 0);
			$row->vote_ref_id = $hikashop_vote_ref_id;
			$row->main_div_name = $main_div_name ;
			$row->listing_true = $listing_true;
			$row->hikashop_vote_average_score_rounded = $hikashop_vote_average_score_rounded;
			JRequest::setVar("rate_rounded",$hikashop_vote_average_score_rounded);
			$row->hikashop_vote_average_score = $hikashop_vote_average_score;
			$row->hikashop_vote_total_vote = $hikashop_vote_total_vote;
			$row->hikashop_vote_nb_star = $hikashop_vote_nb_star;
			$row->type_item = $type_item;
		}
		$this->assignRef('rows', $row);
	}
}
