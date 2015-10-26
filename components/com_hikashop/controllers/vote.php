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
class VoteController extends hikashopController {
	var $modify_views = array();
	var $add = array();
	var $modify = array();
	var $delete = array();

	function __construct($config = array(),$skip=false){
		parent::__construct($config,$skip);
		if(!$skip){
			$this->registerDefaultTask('save');
		}
		$this->display[] = 'save';
	}

	function save() {
		$voteClass = hikashop_get('class.vote');

		if(!count($_POST)){
			$app = JFactory::getApplication();
			$app->redirect(preg_replace('#ctrl=vote&task=save&[0-9a-z=]+#','',preg_replace('#/vote/save/[0-9a-z-]+#','',hikashop_currentURL())),'', 'message', true);
		}

		$element = new stdClass();
		$element->hikashop_vote_type = JRequest::getVar('hikashop_vote_type', 0, 'default', 'string', 0);
		$element->vote_ref_id = JRequest::getVar('hikashop_vote_ref_id', 0, 'default', 'int');
		if(empty($element->vote_ref_id))
			$element->vote_ref_id = JRequest::getVar('hikashop_vote_product_id', 0, 'default', 'int');
		$element->user_id = JRequest::getVar('hikashop_vote_user_id', 0, 'default', 'int');
		$element->pseudo_comment = JRequest::getVar('pseudo_comment', 0, 'default', 'string', 0);
		$element->email_comment = JRequest::getVar('email_comment', 0, 'default', 'string', 0);
		$element->vote_type = JRequest::getVar('vote_type', 0, 'default', 'string', 0);
		$element->vote = JRequest::getVar('hikashop_vote', 0, 'default', 'int');
		$element->comment = JRequest::getVar('hikashop_vote_comment','','','string',JREQUEST_ALLOWRAW); // JRequest::getVar('hikashop_vote_comment', 0, 'default', 'string', 0);
		$element->comment = urldecode($element->comment);
		if(!empty($element->comment) || !empty($element->vote) || !empty($element->email_comment) || !empty($element->pseudo_comment) || $element->hikashop_vote_type == 'useful')
			$voteClass->save($element);
		else
			echo '0';
		exit;
	}
}
