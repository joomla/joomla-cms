<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_status
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_logged
 *
 * @since  1.5
 */
class ModStatusHelper
{

	//Get the number of frontend logged in users.
	public static function getOnlineCountFromDb($params)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->clear()
		->select('COUNT(session_id)')
		->from('#__session')
		->where('guest = 0 AND client_id = 0');

		$db->setQuery($query);
		return  (int) $db->loadResult();

	}
	
	//Get the number of backend logged in users.
	public static function getAdminsOnlineCountFromDb($params)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->clear()
		->select('COUNT(session_id)')
		->from('#__session')
		->where('guest = 0 AND client_id = 1');

		$db->setQuery($query);
		return  (int) $db->loadResult();
	}
	//Get the number of frontend logged in users.
	public static function getOnlineCountFromRedis($params)
	{
		/// Get the number of frontend logged in users.
		$ds = JFactory::getDso();				
		// Calculate number of  users
		$result	     = 0;
		
		try
		{			
			//$sessions=$ds->keys('sess-*');
			$result = $ds->smembers( 'utenti' );
		}
		catch (RuntimeException $e)
		{
			// Don't worry be happy
			$result = array();
		}
		return count($result);
	}

	// Get the number of back-end logged in users.
	public static function getAdminsOnlineCountFromRedis($params)
	{		
		$ds = JFactory::getDso();			
		// Calculate number of backend users
		$backend_users	     = 0;
		
		try
		{			

			$result = $ds->smembers( 'utenti' );
		}
		catch (RuntimeException $e)
		{
			// Don't worry no member in set
			$result = array();
		}
			
		// Get the datastore connection object and verify its connected.
		foreach ($result as $elm)
		{
		 
			try
			{
				$data = $ds->get('user-'.$elm);		
			}
			catch (RuntimeException $e)
			{
			 
				$backend_users = 0;
			}
			$data = json_decode($data);
			$results[]=$data;
	 
			foreach ($results as $k => $result)
			{
			  if((int)$results[$k]->client_id == 1 )
			  {			
 
				$backend_users++;
				}
			}		
		}				
		return $backend_users;
	}
	
	// Get the number of frontend logged in users.
	public static function getAdminsOnlineCount($params)
	{
 			$cfg=JFactory::getConfig();
			$handler = $cfg->get('session_handler', 'none');
			$results=null;
			switch ($handler)
		  {
		  	case 'database':
	   		case 'none':
					$results=ModStatusHelper::getAdminsOnlineCountFromDb($params);
					break;	
				case 'redis':
	   		  //  
	   		  $results=ModStatusHelper::getAdminsOnlineCountFromRedis($params);
	   			break;
	   			
	   		default:		   		  			
	   			break;			
			 
			}
			return $results;
	}		
// Get the number of frontend logged in users.
	public static function getOnlineCount($params)
	{
 			$cfg=JFactory::getConfig();
			$handler = $cfg->get('session_handler', 'none');
			$results=null;
			switch ($handler)
		  {
		  	case 'database':
	   		case 'none':
					$results=ModStatusHelper::getOnlineCountFromDb($params);
					break;	
				case 'redis':
	   		  //  
	   		  $results=ModStatusHelper::getOnlineCountFromRedis($params);
	   			break;
	   			
	   		default:		   		  			
	   			break;			
			 
			}
			return $results;
	}		
	
}