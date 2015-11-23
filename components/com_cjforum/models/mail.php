<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumModelMail extends JModelLegacy 
{
	function __construct() 
	{
		parent::__construct ();
	}
	
	public function enqueueMail($message, $recipients, $template = 'none')
	{
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$site_name = $app->getCfg('sitename');
		$site_url = JUri::root();
		
		try 
		{
			$message->params = json_encode(array('template'=>$template, 'placeholders'=>array()));
			$message->created = JFactory::getDate()->toSql();
			
			if(!$db->insertObject('#__corejoomla_messages', $message))
			{
				return false;
			}
			
			$messageId = $db->insertid();
			
			if($messageId > 0)
			{
				$query = $db->getQuery(true)
					->insert('#__corejoomla_messagequeue')
					->columns('message_id, to_addr, params, created, html');
					
				foreach ($recipients as $user)
				{
					$userparams = json_encode(array('placeholders'=>array('{NAME}'=>$user->name)));
					$query->values($messageId.','.$db->q($user->email).','.$db->q($userparams).','.$db->q($message->created).', 1');
				}
				
				$db->setQuery($query);
				
				if($db->execute())
				{
					return true;
				}
			}
		}
		catch (Exception $e)
		{
			return false;
		}
		
		return false;
	}
}