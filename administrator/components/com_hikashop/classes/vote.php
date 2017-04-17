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
class hikashopVoteClass extends hikashopClass {
	var $tables = array('vote');
	var $pkeys = array('vote_id');
	var $toggle = array('vote_published'=>'vote_id');
	var $votePublished = array('vote_published'=>'vote_id');
	var $paginationStart = 0;
	var $paginationLimit = 50;

	function save(&$element, $forceBackend = false) {
		$app = Jfactory::getApplication();
		if($app->isAdmin() || $forceBackend) {
			return $this->saveBackend($element);
		}
		return $this->saveFrontend($element);
	}

	function saveBackend(&$element) {
		$app = JFactory::getApplication();
		$db	= JFactory::getDBO();
		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$currentElement = new stdClass();

		if(!empty($element->vote_id) && empty($element->vote_type)) {
			$db->setQuery('SELECT vote_type, vote_ref_id FROM '.hikashop_table('vote').' WHERE vote_id = '.$element->vote_id);
			$db_vote = $db->loadObject();
			$element->vote_type = $db_vote->vote_type;
			if(empty($element->vote_ref_id))
				$element->vote_ref_id = $db_vote->vote_ref_id;

			$vote_type = $db_vote->vote_type;
		} else {
			$vote_type = @$element->vote_type;
		}

		if($element->vote_id == 0) {
			if(empty($element->vote_ref_id)) {
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('VOTE_ENTER_ITEM_ID'), 'message');
				return false;
			}

			if($element->vote_type == 'product') {
				$db->setQuery('SELECT product_id FROM '.hikashop_table(''.$element->vote_type.'').' WHERE product_id = '.$element->vote_ref_id.' AND product_parent_id = 0');
				$currentElement = $db->loadResult();
			} else {
				$do = true;
				$dispatcher->trigger('onBeforeVoteCreate', array( &$element, &$do, &$currentElement ) );
				if(!$do){
					return false;
				}
			}
			if(!$currentElement) {
				$app = JFactory::getApplication();
				$app->enqueueMessage(JText::_('WRONG_ITEM_ID'), 'message');
				return false;
			}
		} else {
			$do = true;
			$dispatcher->trigger('onBeforeVoteUpdate', array( &$element, &$do, &$currentElement ) );
			if(!$do){
				return false;
			}
		}

		$vote_id = $element->vote_id;
		$new_published = $element->vote_published;
		$new_rating = isset($element->vote_rating)?$element->vote_rating:'0';
		if($vote_id == 0) {
			$vote_ref_id = $element->vote_ref_id;
		} else {
			$results = $this->get($vote_id);
			$old_rating = $results->vote_rating;
			$vote_ref_id = $results->vote_ref_id;
			$old_published = $results->vote_published;
		}

		$status = parent::save($element);
		if($status && ($element->vote_id != 0 || ($element->vote_id == 0 && (int)$new_rating != 0))){
			if($vote_type == 'product') {
				$typeClass = hikashop_get('class.product');
				$results = $typeClass->get($vote_ref_id);
				$average_score = $results->product_average_score;
				$total_vote = $results->product_total_vote;
			} else {
				if(!isset($currentElement->average_score) || !isset($currentElement->total_vote)) {
					return false;
				}
				$average_score = $currentElement->average_score;
				$total_vote = $currentElement->total_vote;
			}

			if($vote_id == '0'){ //new vote (only backend vote)
				if($new_published == 1){
					$average_score = (($average_score * $total_vote)+$new_rating)/($total_vote + 1);
					$total_vote = ($total_vote + 1);
				}
			}else if($old_published == '0'){ //Published - Unpublished
				if($new_published == 1 && $old_rating != 0){ //on publie
					if($new_rating == 0){$new_rating = $old_rating;}
					$average_score = (($average_score * $total_vote)+$new_rating)/($total_vote + 1);
					$total_vote = ($total_vote + 1);
				}
			}else{ // Save
				if($new_rating != '0' || $new_rating != ''){
					if($old_published == 1){
						if($new_published == 0){
							if($old_rating != 0){ //update average & total - 1
								if($total_vote - 1 == 0){
									$average_score = 0; $total_vote = 0;
								}else{
									$average_score = (($average_score * $total_vote)-$old_rating)/($total_vote - 1);
									$total_vote = ($total_vote - 1);
								}
							}
						}else{
							if($old_rating != 0 && $new_rating == 0){ //update average & total - 1
								if($total_vote - 1 == 0){
									$average_score = 0; $total_vote = 0;
								}else{
									$average_score = (($average_score * $total_vote)-$old_rating)/($total_vote - 1);
									$total_vote = ($total_vote - 1);
								}
							}else if($old_rating != 0 && $new_rating != 0){ //update average
								$average_score = (($average_score * $total_vote)-$old_rating)/($total_vote - 1);
								$average_score = (($average_score * ($total_vote - 1))+$new_rating)/$total_vote;
							}else if($old_rating == 0 && $new_rating != 0){ //update average & total + 1
								$average_score = (($average_score * $total_vote)+$new_rating)/($total_vote + 1);
								$total_vote = ($total_vote + 1);
							}
						}
					}else{
						if($new_published == 1 && $new_rating != 0){ //update average & total +1
							$average_score = (($average_score * $total_vote)+$new_rating)/($total_vote + 1);
							$total_vote = ($total_vote + 1);
						}
					}
				}
			}

			$element->average_score = $average_score;
			$element->total_vote = $total_vote;

			$type = new stdClass();
			if($vote_type == 'product'){
				$type->product_id = (int)$vote_ref_id;
				$type->product_average_score = strip_tags($average_score);
				$type->product_total_vote = strip_tags($total_vote);
				$typeClass->save($type,true);
			}

			$dispatcher->trigger('onAfterVoteUpdate', array( &$element ) );
		}
		return $status;
	}

	function saveFrontend(&$element) {
		$db = JFactory::getDBO();
		$config = hikashop_config();
		$user_ip = hikashop_getIP();
		$date = time();

		if(empty($element->user_id) || (int)$element->user_id == 0)
			$element->user_id = $user_ip;

		if(empty($element->vote_type))
			$element->vote_type = 'product';

		JPluginHelper::importPlugin( 'hikashop' );
		$dispatcher = JDispatcher::getInstance();
		$do = true;
		$currentElement = new stdClass();
		$dispatcher->trigger('onBeforeVoteCreate', array( &$element, &$do, &$currentElement ) );
		if(!$do){
			return false;
		}

		$vElement = new stdClass();
		$vElement->vote_ref_id = (int)$element->vote_ref_id;
		$vElement->vote_type = strip_tags($element->vote_type);
		$vElement->vote_user_id = strip_tags($element->user_id);
		$vElement->vote_pseudo = strip_tags(@$element->pseudo_comment);
		$vElement->vote_ip = strip_tags($user_ip);
		$vElement->vote_email = strip_tags(@$element->email_comment);
		$vElement->vote_date = $date;

		$comment_by_person_by_product = $config->get('comment_by_person_by_product');
		$send_email = $config->get('email_each_comment');
		$vote_if_bought = ($config->get('access_vote', 0) == 'buyed');


		if($vote_if_bought == 1 && $vElement->vote_type == 'product') {
			$purchased = $this->hasBought($vElement->vote_ref_id,$element->user_id);
		}

		if($element->hikashop_vote_type == 'useful') {
			$useful = JRequest::getVar('value', 0, 'default', 'int');
			$vote_id = JRequest::getVar('hikashop_vote_id', 0, 'default', 'int');
			$element->user_id = JRequest::getVar('hikashop_vote_user_id', 0, 'default', 'int');
			if(empty($element->user_id))
				$element->user_id = $user_ip;

			$already_vote = 0;
			$useful_old	= 0;

			$query = 'SELECT vote_user_useful FROM '.hikashop_table('vote_user').' WHERE vote_user_id = '.(int)$vote_id.' AND vote_user_user_id = '.$db->quote($element->user_id).'';
			$db->setQuery($query);
			$already_vote = $db->loadResult();

			if($already_vote > 0) {
				echo '2';
				exit;
			}

			$voteClass = hikashop_get('class.vote');
			$results = $voteClass->get((int)$vote_id);
			$useful_old = $results->vote_useful;

			if($useful == 1) {
				 $useful_new = ($useful_old + 1);
			} else {
				$useful_new = ($useful_old - 1);
			}
			$vElement->vote_id = (int)$vote_id;
			$vElement->vote_useful = strip_tags($useful_new);

			$useful = new stdClass();
			$useful->vote_id = (int)$vote_id;
			$useful->vote_useful = (int)$useful_new;
			$updated = parent::save($useful);

			if($updated) {
				$dispatcher->trigger('onAfterVoteUpdate', array( &$element, $useful ) );

				$query = 'INSERT INTO '.hikashop_table('vote_user').' (vote_user_id,vote_user_user_id,vote_user_useful) VALUES ('.(int)$vote_id.','.$db->quote($element->user_id).',1)';
				$db->setQuery($query);
				$db->query();
				if( $db->getAffectedRows() > 0 ) {
					echo '1';
				}
			}
			exit;
		}

		if($vote_if_bought && !$purchased) {
			echo '3';
			exit;
		}

		if($vElement->vote_type == 'product'){
			$typeClass = hikashop_get('class.product');
			$results = $typeClass->get($vElement->vote_ref_id);
			$hikashop_vote_average_score = $results->product_average_score;
			$hikashop_vote_total_score = $results->product_total_vote;
		} else {
			if(!isset($currentElement->average_score) || !isset($currentElement->total_vote)) {
				echo '4';
				exit;
			}
			$hikashop_vote_average_score = $currentElement->average_score;
			$hikashop_vote_total_score = $currentElement->total_vote;
		}

		$hikashop_vote_total_score_new	= ($hikashop_vote_total_score + 1);
		$hikashop_vote_average_score_new = ((($hikashop_vote_average_score * $hikashop_vote_total_score)+$element->vote)/($hikashop_vote_total_score_new));

		$vote_id = '';
		$vote_old =  '';

		$filters = array('vote_type = '.$db->quote($vElement->vote_type),'vote_ref_id = '.(int)$vElement->vote_ref_id,'vote_rating != 0');

		if(empty($element->user_id) || $element->user_id == $user_ip){
			$filters[] = 'vote_ip = '.$db->quote($user_ip);
			$filters[] = 'vote_user_id = \'\'';
		}else{
			$filters[] = 'vote_user_id = '.$db->quote($element->user_id);
		}

		$query = 'SELECT * FROM '.hikashop_table('vote').' WHERE '.implode(' AND ',$filters);
		$db->setQuery($query);
		$result = $db->loadObject();
		if(!empty($result)){
			$vote_id = $result->vote_id;
			$vote_old = $result->vote_rating;
			$published = $result->vote_published;
		}

		$nb_comment = $this->commentPassed($vElement->vote_type,$vElement->vote_ref_id,$element->user_id);

		$vote_mode = $config->get('enable_status_vote', 0);

		if($element->hikashop_vote_type == 'vote') {
			$vElement->vote_rating = strip_tags($element->vote);
			$vElement->vote_comment = '';

			if(!empty($vote_id)){
				$vElement->vote_id = $vote_id;
				if(!empty($hikashop_vote_total_score))
					$hikashop_vote_average_score_new = (((($hikashop_vote_average_score * $hikashop_vote_total_score) - $vote_old) + $element->vote) / $hikashop_vote_total_score);

				$updated = parent::save($vElement);
				if($updated && $published == 1) {
					if($vElement->vote_type == 'product') {
						$type = new stdClass();
						$type->product_id = (int)$vElement->vote_ref_id;
						$type->product_average_score = $hikashop_vote_average_score_new;
						$type->product_total_vote = (int)$hikashop_vote_total_score;

						$typeClass->save($type, true);
					}

					$element->average_score = $hikashop_vote_average_score_new;
					$element->total_vote = (int)$hikashop_vote_total_score;

					$dispatcher->trigger('onAfterVoteUpdate', array( &$element ) );
				}
				echo '1';
			} else {
				$inserted = parent::save($vElement);
				if($inserted){
					if($vElement->vote_type == 'product') {
						$type = new stdClass();
						$type->product_id = (int)$vElement->vote_ref_id;
						$type->product_average_score = $hikashop_vote_average_score_new;
						$type->product_total_vote = (int)$hikashop_vote_total_score_new;

						$typeClass->save($type, true);
					}

					$element->average_score = $hikashop_vote_average_score_new;
					$element->total_vote = (int)$hikashop_vote_total_score_new;

					$dispatcher->trigger('onAfterVoteUpdate', array( &$element ) );
				}
				echo '2';
			}
			exit;
		}

		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = JFilterInput::getInstance(null, null, 1, 1);
		$config = hikashop_config();
		$vElement->vote_published = $config->get('published_comment', 0);

		if($element->hikashop_vote_type == 'both') {
			$vElement->vote_rating = strip_tags($element->vote);
			$vElement->vote_comment = $safeHtmlFilter->clean($element->comment, 'string');
			if($nb_comment < $comment_by_person_by_product) {
				$inserted = parent::save($vElement);
				if($inserted) {
					if($vElement->vote_type == 'product' && $vElement->vote_published) {
						$type = new stdClass();
						$type->product_id = (int)$vElement->vote_ref_id;
						$type->product_average_score = $hikashop_vote_average_score_new;
						$type->product_total_vote = (int)$hikashop_vote_total_score_new;

						$typeClass->save($type,true);
					}

					$element->average_score = $hikashop_vote_average_score_new;
					$element->total_vote = (int)$hikashop_vote_total_score;

					$dispatcher->trigger('onAfterVoteUpdate', array( &$element ) );

					if(!empty($send_email)) {
						$vote_id = $db->insertid();
						$this->sendNotifComment($vote_id, strip_tags($element->comment),(int)$vElement->vote_ref_id,(int)$element->user_id, strip_tags($element->pseudo_comment), strip_tags($element->email_comment), $vElement->vote_type);
					}
					echo '1';
				} else {
					echo '0';
				}
			} else {
				echo '2';
			}
			exit;
		}

		if($element->hikashop_vote_type == 'comment') {


			$vElement->vote_rating = '0';
			$vElement->vote_comment = $safeHtmlFilter->clean($element->comment, 'string');
			if($nb_comment < $comment_by_person_by_product) {
				$inserted = parent::save($vElement);
				$vote_id = 0;
				if($inserted) {
					$dispatcher->trigger('onAfterVoteUpdate', array( &$element ) );

					if($send_email != '') {
						$vote_id = $db->insertid();
						$this->sendNotifComment($vote_id, strip_tags($element->comment),(int)$vElement->vote_ref_id,(int)$element->user_id, strip_tags($element->pseudo_comment), strip_tags($element->email_comment), $vElement->vote_type);
					}
					echo '1';
				} else {
					echo '0';
				}
			} else {
				echo '2';
			}
		}
		exit;
	}

	function delete(&$elements){
		$db = JFactory::getDBO();
		JArrayHelper::toInteger($elements);
		JPluginHelper::importPlugin('hikashop');
		$dispatcher = JDispatcher::getInstance();
		$do = true;
		$currentElements = array();
		$dispatcher->trigger('onBeforeVoteDelete', array(&$elements, &$do, &$currentElements) );
		if(!$do)
			return false;

		$db->setQuery('SELECT vote_id, vote_rating, vote_ref_id, vote_published, vote_type FROM '.hikashop_table('vote').' WHERE vote_id IN ('.implode(',',$elements).')');
		$results = $db->loadObjectList();

		foreach($results as $result) {
			$vote_rating = $result->vote_rating;
			$vote_ref_id = $result->vote_ref_id;
			$vote_published = $result->vote_published;

			if($result->vote_type == 'product'){
				$productClass = hikashop_get('class.'.$result->vote_type);
				$resultVote = $productClass->get($vote_ref_id);
				$average_score = @$resultVote->product_average_score;
				$total_vote = @$resultVote->product_total_vote;
			} else if(isset($currentElements[(int)$result->vote_id])) {
				$element = $currentElements[(int)$result->vote_id];
				if(!isset($element->average_score) || !isset($element->total_vote)) {
					return false;
				}
				$average_score = $element->average_score;
				$total_vote = $element->total_vote;
			} else {
				$average_score = 0;
				$total_vote = 0;
				$element = null;
			}

			$status = parent::delete($result->vote_id);
			if($status && isset($element) && $element !== null) {
				$query = 'DELETE FROM '.hikashop_table('vote_user').' WHERE vote_user_id = '.(int)$result->vote_id.' ';
				$db->setQuery($query);
				$db->query();
				if($vote_published == 1 && $vote_rating != 0) {
					if($total_vote - 1 == 0) {
						$average_score = 0;
						$total_vote = 0;
					}else{
						$average_score = ((($average_score * $total_vote)-$vote_rating)/($total_vote - 1));
						$total_vote	= ($total_vote - 1);
					}

					$element->vote_id = (int)$result->vote_id;
					$element->vote_ref_id = (int)$result->vote_ref_id;
					$element->average_score = $average_score;
					$element->total_vote = $total_vote;

					if($result->vote_type == 'product'){
						$product = new stdClass();
						$product->product_id = (int)$vote_ref_id;
						$product->product_average_score = $average_score;
						$product->product_total_vote = (int)$total_vote;

						$productClass->save($product,true);
					}
				}

				$dispatcher->trigger('onAfterVoteDelete', array(&$element) );
			}
		}
		return true;
	}

	function saveForm(){
		$element = new stdClass();
		$element->vote_id = hikashop_getCID('vote_id');
		$formData = JRequest::getVar( 'data', array(), '', 'array' );
		jimport('joomla.filter.filterinput');
		$safeHtmlFilter = & JFilterInput::getInstance(null, null, 1, 1);
		foreach($formData['vote'] as $column => $value){
			hikashop_secureField($column);
			$element->$column = $safeHtmlFilter->clean($value);
			if($column!='vote_comment'){
				$element->$column = strip_tags($element->$column);
			}
		}
		$result = $this->save($element);
		return $result;
	}

	function loadJS() {
		static $done = false;
		if($done)
			return true;
		$done = true;

		$current_url = hikashop_currentURL();

		$baseUrl = hikashop_completelink('vote&task=save&'.hikashop_getFormToken().'=1');
		$ajaxUrl = hikashop_completelink('vote&task=save',true,true);
		if(strpos($baseUrl, '?') !== false)
			$baseUrl .= '&';
		else
			$baseUrl .= '?';

		$config = hikashop_config();
		$email_comment = $config->get('email_comment', 0);

		if($config->get('enable_status_vote', 0) == 'both')
			$vote_comment = 1;
		else
			$vote_comment = 0;

		$note_comment = $config->get('register_note_comment', 0);
		if($config->get('access_vote', 0) == 'buyed' || $config->get('access_vote', 0) == 'registered')
			$hikashop_vote_con_req = 1;
		else
			$hikashop_vote_con_req = 0;

		$js = '
function trim(myString){
	myString = myString.replace(/(^\s|&)+/g,\'\').replace(/\s+$/g,\'\').replace(/\\n/g,\'<br \/>\');
	return myString;
}

function hikashop_vote_useful(hikashop_vote_id,val){
	var hikashop_vote_user_id = "";
	if(document.getElementById("hikashop_vote_user_id")) hikashop_vote_user_id = document.getElementById("hikashop_vote_user_id").value;
	var hikashop_vote_note_comment 	= ' . $note_comment . ';
	if((hikashop_vote_note_comment == 1 && hikashop_vote_user_id != "") || hikashop_vote_note_comment == 0){
		data = "hikashop_vote_type=useful";
		data += "&value=" + encodeURIComponent(val);
		data += "&hikashop_vote_id=" + encodeURIComponent(hikashop_vote_id);
		data += "&hikashop_vote_user_id=" + encodeURIComponent(hikashop_vote_user_id);
		window.Oby.xRequest("'.$ajaxUrl.'", {mode: "POST", data: data}, function(xhr) {
			var el = document.getElementById(hikashop_vote_id);
			if(xhr.responseText == "1"){el.innerHTML = " ' . JText::_('THANK_FOR_VOTE', true) . '";}
			else if(xhr.responseText == "3"){el.innerHTML = " ' . JText::_('ALREADY_VOTE_USEFUL', true) . '";}
			else{el.innerHTML = " ' . JText::_('VOTE_ERROR', true) . '";}
		});
		setTimeout("document.location=\''.$current_url.'\'",2250);
	}
	else{
		document.getElementById(hikashop_vote_id).innerHTML = " ' . JText::_('ONLY_REGISTERED_CAN_VOTE', true) . '";
		setTimeout("document.getElementById(\'hikashop_vote_id\').innerHTML = \'\'",2250);
	}
}

function hikashop_send_vote(hikashop_vote, from){
	var re = new RegExp(\'id_(.*?)_hikashop\');
	var m = re.exec(from);
	if(m != null){
		var hikashop_vote_ref_id = "";
		for (i = 1; i < m.length; i++) {
			hikashop_vote_ref_id = hikashop_vote_ref_id + m[i] + "\n";
		}
	}else{
		var hikashop_vote_ref_id = document.getElementById("hikashop_vote_ref_id").value;
	}
	document.getElementById("hikashop_vote_ok_"+parseInt(hikashop_vote_ref_id)).value = "1";
	var hikashop_vote_vote_comment 	= ' . $vote_comment . ';
	var hikashop_vote_con_req		= ' . $hikashop_vote_con_req . ';
	var hikashop_vote_user_id 		= document.getElementById("hikashop_vote_user_id_"+parseInt(hikashop_vote_ref_id)).value;
	var vote_type					= document.getElementById("vote_type_"+parseInt(hikashop_vote_ref_id)).value;
	var div_vote_status				= "hikashop_vote_status_"+parseInt(hikashop_vote_ref_id);
	if((hikashop_vote_con_req == 1 && hikashop_vote_user_id != "") || hikashop_vote_con_req == 0){
		if(hikashop_vote_vote_comment == 1){//User must enter a comment to note a product
			if(from =="hikashop_vote_rating_id"){
				document.getElementById("hikashop_vote_status_form").innerHTML = " ' . JText::_('LET_COMMENT_TO_VALID_VOTE', true) . '";
				setTimeout("document.getElementById(\'hikashop_vote_status_form\').innerHTML = \'\'",2250);
			}else{
				var el = document.getElementById(div_vote_status);
				el.innerHTML = " ' . JText::_('LET_COMMENT_TO_VALID_VOTE', true) . '";
				setTimeout(function(){el.innerHTML = "";},2250);
			}
		}
		else{// Only vote - sending request to saveFrontend() function, and analysing the result, status(thanks, bought, error)
			if(from =="hikashop_vote_rating_id"){
				var el = document.getElementById("hikashop_vote_status_form");
			}else{
				var el = document.getElementById(div_vote_status);
			}
			data = "vote_type=" + encodeURIComponent(vote_type);
			data += "&hikashop_vote_type=vote";
			data += "&hikashop_vote=" + encodeURIComponent(hikashop_vote);
			data += "&hikashop_vote_user_id=" + encodeURIComponent(hikashop_vote_user_id);
			data += "&hikashop_vote_ref_id=" + encodeURIComponent(hikashop_vote_ref_id);
			window.Oby.xRequest("'.$ajaxUrl.'", {mode: "POST", data: data}, function(xhr) {
				if(xhr.responseText == "1"){
					el.innerHTML = " ' . JText::_('VOTE_UPDATED', true) . '";

					setTimeout(function(){el.innerHTML = "";},2250);
					resetVotes();

				}
				else if(xhr.responseText == "2"){el.innerHTML = " ' . JText::_('THANK_FOR_VOTE', true) . '"; }
				else if(xhr.responseText == "3"){el.innerHTML = " ' . JText::_('MUST_HAVE_BUY_TO_VOTE', true) . '";}
				else{el.innerHTML = " ' . JText::_('VOTE_ERROR', true) . '";}
			});
		}
	}
	else{ //The user must be registered to vote
		if(from =="hikashop_vote_rating_id"){
			document.getElementById("hikashop_vote_status_form").innerHTML = " ' . JText::_('ONLY_REGISTERED_CAN_VOTE', true) . '";
			setTimeout("document.getElementById(\'hikashop_vote_status_form\').innerHTML = \'\'",2250);
		}else{
			var el = document.getElementById(div_vote_status);
			el.innerHTML = " ' . JText::_('ONLY_REGISTERED_CAN_VOTE', true) . '";
			setTimeout(function(){el.innerHTML = "";},2250);
		}
	}
}

function hikashop_send_comment(){ //Action on submit comment
	var hikashop_vote_ref_id 		= document.getElementById("hikashop_vote_ref_id").value;
	var hikashop_vote_comment 		= encodeURIComponent(trim(document.getElementById("hikashop_vote_comment").value));
	var vote_type					= document.getElementById("vote_type_"+parseInt(hikashop_vote_ref_id)).value;
	var hikashop_vote_ok 			= document.getElementById("hikashop_vote_ok_"+parseInt(hikashop_vote_ref_id)).value;
	var hikashop_vote_vote_comment 	= ' . $vote_comment . ';
	var hikashop_vote_con_req		= ' . $hikashop_vote_con_req . ';
	var email_comment_bool 			= ' . $email_comment . ';
	var hikashop_vote_user_id 		= document.getElementById("hikashop_vote_user_id_"+parseInt(hikashop_vote_ref_id)).value;
	var pseudo_comment 				= document.getElementById("pseudo_comment").value;
	var email_comment				= document.getElementById("email_comment").value;
	var reg = new RegExp(\'^[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*@[a-z0-9]+([_|\.|-]{1}[a-z0-9]+)*[\.]{1}[a-z]{2,6}$\', \'i\'); // TEST EMAIL ADDRESS
	var verif_mail = reg.test(email_comment);

	if (hikashop_vote_user_id != ""){verif_mail = true;}
	if((hikashop_vote_con_req == 1 && hikashop_vote_user_id != "") || hikashop_vote_con_req == 0){ //if connection not required
		if(pseudo_comment == "" || (email_comment_bool == 1 && verif_mail == false)){ //if not connected
			if(pseudo_comment == ""){
				document.getElementById("hikashop_vote_status_form").innerHTML = "' . JText::_('PSEUDO_REQUIRED', true) . '";
				setTimeout("document.getElementById(\'hikashop_vote_status_form\').innerHTML = \'\'",2250);
			}else{
				document.getElementById("hikashop_vote_status_form").innerHTML = "' . JText::_('EMAIL_INVALID', true) . '";
				setTimeout("document.getElementById(\'hikashop_vote_status_form\').innerHTML = \'\'",2250);
			}
		}else{
			if(hikashop_vote_vote_comment == 1){ // Save comment & vote.
				var hikashop_vote = document.getElementById("hikashop_vote_rating_id").value;
				if(hikashop_vote_comment == "" || hikashop_vote_ok == 0){ // Just show a message
					document.getElementById("hikashop_vote_status_form").innerHTML = "' . JText::_('VOTE_AND_COMMENT_PLEASE', true) . '";
					setTimeout("document.getElementById(\'hikashop_vote_status_form\').innerHTML = \'\'",2250);
				}else{
					var data = window.Oby.getFormData("hikashop_comment_form");
					var regEx = /ctrl=(.*?)&/;
					data = data.replace(regEx,"");
					var regEx = /task=(.*?)&/;
					data = data.replace(regEx,"");
					var regEx = /limitstart=(.*?)&/;
					data = data.replace(regEx,"");
					data += "&hikashop_vote_type=both";
					regEx = /hikashop_vote_ref_id/;
					if(!regEx.test(data)){
						data += "&vote_type=" + encodeURIComponent(vote_type);
						data += "&email_comment=" + encodeURIComponent(email_comment);
						data += "&pseudo_comment=" + encodeURIComponent(pseudo_comment);
						data += "&hikashop_vote_user_id=" + encodeURIComponent(hikashop_vote_user_id);
						data += "&hikashop_vote_ref_id=" + encodeURIComponent(hikashop_vote_ref_id);
						data += "&hikashop_vote_comment=" + encodeURIComponent(hikashop_vote_comment);
					}
					data += "&hikashop_vote=" + encodeURIComponent(hikashop_vote);
					window.Oby.xRequest("'.$ajaxUrl.'", {mode: "POST", data: data}, function(xhr) {
						var el = document.getElementById("hikashop_vote_status_form");
						if(xhr.responseText == "1"){el.innerHTML = " ' . JText::_('THANKS_FOR_PARTICIPATION', true) . '";document.getElementById("hikashop_vote_comment").value="";}
						else if(xhr.responseText == "3"){el.innerHTML = " ' . JText::_('MUST_HAVE_BUY_TO_VOTE', true) . '";}
						else if(xhr.responseText == "2"){el.innerHTML = " ' . JText::_('REACH_LIMIT_OF_COMMENT', true) . '";}
						else{el.innerHTML = " ' . JText::_('VOTE_ERROR', true) . '";}
					});
					setTimeout("document.location=\''.$current_url.'\'",2250);
				}
			}else if(hikashop_vote_comment != ""){
				var data = window.Oby.getFormData("hikashop_comment_form");
				var regEx = /ctrl=(.*?)&/;
				data = data.replace(regEx,"");
				var regEx = /task=(.*?)&/;
				data = data.replace(regEx,"");
				var regEx = /limitstart=(.*?)&/;
				data = data.replace(regEx,"");
				data += "&hikashop_vote_type=comment";
				regEx = /hikashop_vote_ref_id/;
				if(!regEx.test(data)){
					data += "&vote_type=" + encodeURIComponent(vote_type);
					data += "&email_comment=" + encodeURIComponent(email_comment);
					data += "&pseudo_comment=" + encodeURIComponent(pseudo_comment);
					data += "&hikashop_vote_user_id=" + encodeURIComponent(hikashop_vote_user_id);
					data += "&hikashop_vote_ref_id=" + encodeURIComponent(hikashop_vote_ref_id);
					data += "&hikashop_vote_comment=" + encodeURIComponent(hikashop_vote_comment);
				}
				window.Oby.xRequest("'.$ajaxUrl.'", {mode: "POST", data: data}, function(xhr) {
					var el = document.getElementById("hikashop_vote_status_form");
					if(xhr.responseText == "1"){el.innerHTML = " ' . JText::_('THANKS_FOR_COMMENT', true) . '";document.getElementById("hikashop_vote_comment").value="";}
					else if(xhr.responseText == "3"){el.innerHTML = " ' . JText::_('MUST_HAVE_BUY_TO_VOTE', true) . '";}
					else if(xhr.responseText == "2"){el.innerHTML = " ' . JText::_('REACH_LIMIT_OF_COMMENT', true) . '";}
					else{el.innerHTML = " ' . JText::_('VOTE_ERROR', true) . '";}
				});
				setTimeout("document.location=\''.$current_url.'\'",2250);
			}else{
				document.getElementById("hikashop_vote_status_form").innerHTML = " ' . JText::_('PLEASE_COMMENT', true) . '";
				setTimeout("document.getElementById(\'hikashop_vote_status_form\').innerHTML = \'\'",2250);
			}
		}
	}else{
		document.getElementById("hikashop_vote_status_form").innerHTML = " ' . JText::_('ONLY_REGISTERED_CAN_COMMENT', true) . '";
		setTimeout("document.getElementById(\'hikashop_vote_status_form\').innerHTML = \'\'",2250);
	}
}
';
		if(!HIKASHOP_PHP5) {
			$doc =& JFactory::getDocument();
		} else {
			$doc = JFactory::getDocument();
		}
		$doc->addScriptDeclaration("\n<!--\n" . $js . "\n//-->\n");
		if(!HIKASHOP_J30)
			JHTML::_('behavior.mootools');
		else
			JHTML::_('behavior.framework');
	}

	function sendNotifComment($vote_id, $comment, $vote_ref_id, $user_id, $pseudo, $email, $vote_type){
		if($pseudo != '0'){
			$username = $pseudo;
			$email = $email;
			$config =& hikashop_config();
			$email_enabled = $config->get('email_comment');
			if($email_enabled == 0){
				$email = "Not required";
			}
		} else {
			$userClass = hikashop_get('class.user');
			$userInfos = $userClass->get($user_id);
			if(!empty($userInfos)){
				$username	= $userInfos->username;
				$email	= $userInfos->email;
			}
		}

		$result = new stdClass();
		$result->vote_id = $vote_id;
		$result->vote_type = $vote_type;
		$result->product_id = $vote_ref_id;
		$result->username_comment = $username;
		$result->email_comment = $email;
		$result->comment = $comment;

		$type = null;
		if($vote_type == 'product') {
			$productClass = hikashop_get('class.product');
			$type = $productClass->get($vote_ref_id);
		}

		$mailClass = hikashop_get('class.mail');
		$infos = new stdClass();
		$infos->type =& $type;
		$infos->result =& $result;
		$mail = $mailClass->get('new_comment',$infos);
		$mail->subject = JText::sprintf($mail->subject,HIKASHOP_LIVE);
		$config =& hikashop_config();

		$mail->dst_email = $config->get('email_each_comment');
		$mailClass->sendMail($mail);
		return ;
	}

	function get($id, $type = 'product', $content = 'main', $infos = array()){
		return parent::get($id);
	}

	function getList($id, $type = 'product', $content = 'main', $infos = array()){
		$votes = array();
		if((int)$id == 0)
			return $votes;

		$config = hikashop_config();
		$db = JFactory::getDBO();

		$select = 'SELECT a.*, b.*, c.*';
		$from = ' FROM '.hikashop_table('vote').' AS a';
		$leftJoin = ' LEFT JOIN '.hikashop_table('user').' AS b ON a.vote_user_id = b.user_id';
		$leftJoin .= ' LEFT JOIN '.hikashop_table('users',false).' AS c ON b.user_cms_id = c.id';
		$where = ' WHERE vote_type = '.$db->quote($type).' AND vote_ref_id = '.(int)$id;
		$where .= ' AND vote_published = 1';
		$sort = $config->get('vote_comment_sort');
		if($sort == 'date_desc'){
			$order = ' ORDER BY vote_date DESC';
		}elseif($sort == 'helpful'){
			$order = ' ORDER BY vote_useful ASC';
		}else{
			$order = ' ORDER BY vote_date ASC';
		}
		$limit = ' LIMIT '.$this->paginationStart.','.$this->paginationLimit;

		$query = $select.$from.$leftJoin.$where.$order.$limit;
		$db->setQuery($query);
		$votes = $db->loadObjectList('vote_id');

		$voteInfos = array('vote_id','vote_rating','vote_comment','vote_useful','vote_pseudo','vote_date');
		$userInfos = array('username');

		$allInfos = array_merge($voteInfos, $userInfos, $infos);

		$ids = array();
		foreach($votes as $k => $vote){
			$ids[] = $vote->vote_id;
			$userData = new stdClass();
			foreach($vote as $l => $data){
				if(!in_array($l,$allInfos)){
					unset($votes[$k]->$l);
				}
				if(in_array($l,$userInfos)){
					$userData->$l = $data;
					unset($votes[$k]->$l);
				}
			}

			if(isset($votes[$k]->vote_rating)){
				$votes[$k]->vote_value = $votes[$k]->vote_rating;
				unset($votes[$k]->vote_rating);
			}

			$votes[$k]->vote_username = '';
			if(!empty($userData->username)){
				$votes[$k]->vote_username = $userData->username;
			}elseif($vote->vote_pseudo != 0){
				$votes[$k]->vote_username = $vote->vote_pseudo;
			}
			unset($votes[$k]->vote_pseudo);
		}

		if($content == 'full' && !empty($ids)){
			$query = 'SELECT * FROM '.hikashop_table('vote').' WHERE vote_published = 1 AND vote_ref_id IN ('.implode(',',$ids).') AND vote_type LIKE '.$db->quote('criterion-%');
			$db->setQuery($query);
			$criterions = $db->loadObjectList('vote_id');

			$categoryIds = array();
			foreach($criterions as $k => $criterion){
				$categoryIds[] = str_replace('criterion-','',$criterion->vote_type);
			}

			$query = 'SELECT * FROM '.hikashop_table('category').' WHERE category_published = 1 AND category_id IN ('.implode(',',$categoryIds).')';
			$db->setQuery($query);
			$categories = $db->loadObjectList('category_id');

			$categoryInfos = array('category_name','category_description');
			$allInfos = array_merge($categoryInfos, $infos);
			foreach($categories as $k => $categorie){
				foreach($categorie as $l => $data){
					if(!in_array($l,$allInfos)){
						unset($categories[$k]->$l);
					}
				}
			}

			$criterionInfos = array('vote_id','vote_rating','vote_date');
			$allInfos = array_merge($criterionInfos, $infos);
			$categoryIds = array();
			foreach($criterions as $k => $criterion){
				$refId = $criterion->vote_ref_id;
				$categoryId = str_replace('criterion-','',$criterion->vote_type);
				$categoryIds[] = $categoryId;
				foreach($criterion as $l => $data){
					if(!in_array($l,$allInfos)){
						unset($criterion->$l);
					}
				}

				foreach($criterion as $l => $criterionInfo){
					$name = str_replace('vote','criterion',$l);
					$votes[$refId]->vote_criterions[$criterion->vote_id]->$name = $criterionInfo;
				}
				$votes[$refId]->vote_criterions[$criterion->vote_id]->criterion_name = $categories[$categoryId]->category_name;
				$votes[$refId]->vote_criterions[$criterion->vote_id]->criterion_description = $categories[$categoryId]->category_description;
			}
		}
		return $votes;
	}

	function hasBought($vote_ref_id, $user_id){
		$purchased = 0;
		$db = JFactory::getDBO();
		$query = 'SELECT order_id FROM '.hikashop_table('order').' WHERE order_user_id = '.$db->quote($user_id).'';
		$db->setQuery($query);
		if(!HIKASHOP_J25){
			$order_ids = $db->loadResultArray();
		} else {
			$order_ids = $db->loadColumn();
		}
		if(!empty($order_ids)) {
			$query = 'SELECT product_id FROM '.hikashop_table('product').' WHERE product_parent_id = '.(int)$vote_ref_id.'';
			$db->setQuery($query);
			if(!HIKASHOP_J25){
				$product_ids = $db->loadResultArray();
			} else {
				$product_ids = $db->loadColumn();
			}
			if(empty($product_ids)) {
				$product_ids =  array(0 => 0);
			}
			$query = 'SELECT order_product_id FROM '.hikashop_table('order_product').' WHERE order_id IN ('.implode(',',$order_ids).') AND product_id = '.(int)$vote_ref_id.' OR product_id IN ('.implode(',',$product_ids).')';
			$db->setQuery($query);
			$result = $db->loadObjectList();
			if(!empty($result))
				$purchased = 1;
		}
		return $purchased;
	}

	function commentPassed($vote_type, $vote_ref_id, $user_id){
		$nb_comment = 0;
		$db = JFactory::getDBO();
		$query = 'SELECT vote_comment FROM '.hikashop_table('vote').' WHERE vote_type = '.$db->quote($vote_type).' AND vote_ref_id = '.(int)$vote_ref_id.' AND vote_user_id = '.$db->quote($user_id).' AND vote_comment != \'\'';
		$db->setQuery($query);
		$results = $db->loadObjectList();
		foreach($results as $result) {
			$nb_comment++;
		}
	}

}
