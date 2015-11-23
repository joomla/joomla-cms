<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

require_once JPATH_ROOT.'/components/com_cjforum/helpers/constants.php';
require_once JPATH_ROOT.'/components/com_cjlib/framework.php';

CJLib::import('corejoomla.framework.core');
JFactory::getLanguage()->load('com_cjforum', JPATH_ROOT);

// Add logger
$date = JFactory::getDate()->format('Y.m.d');
JLog::addLogger(array('text_file' => 'com_cjforum'.'.'.$date.'.log.php'), JLog::ALL, 'com_cjforum');

class CjForumApiDummy 
{
	private static $_users = array();
	private static $_errors = array();
	private static $_enable_logging = false;
	
	/**
	 * Sets the debug logging enabled or disabled 
	 * 
	 * @param booleab $state sets the state of logging
	 */
	public static function set_logging($state = true)
	{
		CjForumApi::$_enable_logging = $state;
	}
	
	/**
	 * Gets the user profile/profiles of a given id or ids.
	 * 
	 * @param mixed $identifier id/ids of the user(s)
	 * @param boolean $force_reload tells if the profiles should be loaded forcibly or not
	 * 
	 * @return mixed single or array of user profile associative array.
	 */
	public static function get_user_profile($identifier, $force_reload = false)
	{
		if(is_array($identifier))
		{
			$return = array();
			self::load_users($identifier, $force_reload);
			
			foreach ($identifier as $id)
			{
				if(!empty(self::$_users[$id]))
				{
					$return[$id] = self::$_users[$id];
				}
			}

			return $return;
		} 
		elseif(is_numeric($identifier))
		{
			self::load_users(array($identifier), $force_reload);
			
			if(!empty(self::$_users[$identifier]))
			{
				return self::$_users[$identifier];
			}
		}
		
		return false;
	}
	
	/**
	 * Function to get avatar of a single user or multiple users.
	 * 
	 * @param int $identifier user id or array of ids of whom the avatar(s) is/are being retrieved.
	 * @param int $size size of the avatar
	 * 
	 * @return mixed <br/> 
	 * 	- avatar image if <code>identifier</code> is numeric, <br/>
	 *  - associative array of avatars with userid as index of the array elements if <code>identifier</code> is array,<br/> 
	 *  - default avatar image otherwise.
	 */
	public static function get_user_avatar_image($identifiers, $size = 48, $force_reload = false)
	{
		$size = ($size > 224 ? 256 : ($size > 160 ? 192 : ( $size > 128 ? 160 : ( $size > 96 ? 128 : ( $size > 64 ? 96 : ( $size > 48 ? 64 : ( $size > 32 ? 48 : ( $size > 23 ? 32 : 16 ) ) ) ) ) ) ) );
		$default_avatar = CF_MEDIA_URI.'images/'.$size.'-nophoto.jpg';
		$avatar_loc = CF_AVATAR_BASE_URI.'size-'.$size.'/';
		
		if(is_numeric($identifiers))
		{
			$profile = self::get_user_profile($identifiers, $force_reload);
			
			if($profile && !empty($profile['avatar']))
			{
				return $avatar_loc.$profile['avatar'];
			}
		} 
		elseif (is_array($identifiers))
		{
			$return = array();
			$profiles = self::get_user_profile($identifiers, $force_reload);
			
			if(!empty($profiles))
			{
				foreach ($profiles as $userid=>$profile)
				{
					if(!empty($profile['avatar']))
					{
						$return[$userid] = $avatar_loc.$profile['avatar'];
					}
					else
					{
						$return[$userid] = $default_avatar;
					}
				}
			} 
			else 
			{
				foreach ($identifiers as $userid)
				{
					if($userid)
					{
						$return[$userid] = $default_avatar;
					}
				}
			}
			
			return $return;
		}
		
		return $default_avatar;
	}
	
	/**
	 * Gets the user profile url of one or more user ids. 
	 *  - If <code>identifier</code> is numeric, a single profile url is returned, 
	 *  - if <code>identifier</code> is an array of integers, respective associative array of user profiles is returned with userid as index of the array elements,
	 *  - false otherwise. 
	 * @param mixed $identifiers numeric or array of numeric user ids
	 * @param string $username if <code>path_only</code> is set as false, this option tells if the link value should be user original name or username.
	 * @param boolean $path_only if set to true, uri of the profile is returned, otherwise html link of the user profile is returned.
	 * 
	 * @return mixed user profile or array of user profiles based on the arguments passed. 
	 */
	public static function get_user_profile_url($identifiers, $path_only = false, $attribs = null, $xhtml = true, $ssl = null)
	{
		require_once JPATH_ROOT.'/components/com_cjforum/router.php';
		$profiles = self::get_user_profile($identifiers);
		
		if(CjForumApi::$_enable_logging)
		{
			JLog::add('Get Profile Urls - Profiles Loaded: '.count($profiles), JLog::DEBUG, 'com_cjforum');
		}
		
		if(!empty($profiles))
		{
			if(is_numeric($identifiers))
			{
				if($path_only)
				{
					return JRoute::_(CjForumHelperRoute::getProfileRoute($profiles['id']), $xhtml, $ssl);
				} 
				else 
				{
					return JHtml::link(CjForumHelperRoute::getProfileRoute($profiles['id']), CJFunctions::escape($profiles['author']), $attribs);
				}
			} 
			elseif(is_array($identifiers)) 
			{
				if(!empty($profiles))
				{
					$return = array();
					
					if(in_array(0, $identifiers))
					{
						if(null == $attribs) $attribs = array();
						$attribs['onclick'] = 'return false';
						
						$return[0] = $path_only ? '#' : JText::_('COM_CJFORUM_GUEST');
					}
					
					foreach ($profiles as $profile)
					{
						if($path_only)
						{
							$return[$profile['id']] = JRoute::_(CjForumHelperRoute::getProfileRoute($profiles['id']));
						} 
						else 
						{
							$return[$profile['id']] = JHtml::link(CjForumHelperRoute::getProfileRoute($profiles['id']), CJFunctions::escape($profile[$username]), $attribs);
						}
					}
					
					return $return;
				}
			}
		}
		
		return $path_only ? '#' : JText::_('COM_CJFORUM_GUEST');
	}
	
	/**
	 * Gets the user avatar linked with user profile.
	 * 
	 * @param mixed $userids single id of the user or array of user ids
	 * @param int $size height of the avatar
	 * @param string $username what name to display username or name?
	 * @param array $attribs An associative array of attributes to add to the link
	 */
	public static function get_user_avatar($userids, $size = 48, $username = 'name', array $attribs = array(), array $image_attribs = array())
	{
		if(!array_key_exists('height', $image_attribs))
		{
			$image_attribs['height'] = $size;
		}
		
		if(!is_array($userids)) $userids = intval($userids);
		
		if(is_numeric($userids))
		{
			$profile = self::get_user_profile($userids);
			$avatar_loc = self::get_user_avatar_image($userids, $size);

			$attribs['class'] = empty($attribs['class']) ? 'tooltip-hover' : $attribs['class'].' tooltip-hover';
			$attribs['title'] = empty($attribs['title']) ? $profile[$username] : $attribs['title'];

			$avatar_image = '<img src="'.$avatar_loc.'" alt="'.$attribs['title'].'" '.JArrayHelper::toString($image_attribs).'/>';
			$profile_url = self::get_user_profile_url($userids, $username, true);

			return JHtml::link($profile_url, $avatar_image, $attribs);
		} 
		elseif(is_array($userids) && !empty($userids))
		{
			$avatar_images = self::get_user_avatar_image($userids, $size);
			$profile_urls = self::get_user_profile_url($userids, $username, true);
			$profiles = self::get_user_profile($userids);
			$return = array();
			
			foreach ($userids as $userid)
			{
				if(!empty($avatar_images[$userid]) && !empty($profile_urls[$userid]))
				{
					$attribs['class'] = empty($attribs['class']) ? 'tooltip-hover' : $attribs['class'].' tooltip-hover';
					$attribs['title'] = CJFunctions::escape($profiles[$userid][$username]);

					$avatar_loc = self::get_user_avatar_image($userids, $size);
					$avatar_image = '<img src="'.$avatar_loc[$userid].'" alt="'.$attribs['title'].'" '.JArrayHelper::toString($image_attribs).'/>';

					$return[$userid] = JHtml::link($profile_urls[$userid], $avatar_image, $attribs);
				}
			}
			
			return $return;
		}
		
		return false;
	}
	
	/**
	 * Prefetches user profiles to be used across the request life cycle
	 * 
	 * @param array $identifiers array of user ids to load
	 * @param boolean $force_reload indicates to load even if the user is already loaded
	 */
	public static function load_users(array $identifiers = array(), $force_reload = false)
	{
		$notfound = array();
		JArrayHelper::toInteger($identifiers);
		
		foreach ($identifiers as $userid)
		{
			if (!$force_reload && (!$userid || $userid != intval($userid))) 
			{
				unset($userid);
			} 
			elseif (empty(self::$_users[$userid]) && !in_array($userid, $notfound)) 
			{
				$notfound[] = $userid;
			}
		}
		
		if(!empty($notfound))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			
			$query
				->select('ju.id, u.avatar, u.about, u.hits, u.birthday, u.last_post_time, u.points, u.topics, u.replies, u.fans, u.thankyou')
				->select('ju.name as author, ju.email, ju.block, ju.registerDate, ju.lastvisitDate, ju.params')
				->select('rnk.id as rank_id, rnk.title as rank_title, rnk.rank_type, rnk.min_posts, rnk.rank_image, rnk.catid')
				->select("CASE WHEN u.handle is null OR trim(u.handle) = '' THEN ju.username ELSE u.handle END AS handle")
				->from('#__users ju')
				->join('left', '#__cjforum_users u on u.id = ju.id')
				->join('left', '#__cjforum_ranks rnk on rnk.id = u.rank')
				->where('ju.id in ('.implode(',', $notfound).')');
			
			$db->setQuery ( $query );
			$users = $db->loadAssocList();

			if(!empty($users))
			{
				foreach ($users as $user)
				{
					self::$_users[$user['id']] = $user;
				}
				
				if(CjForumApi::$_enable_logging)
				{
					JLog::add('Load Users - After Load - Successfully loaded: ', JLog::DEBUG, 'com_cjforum');
				}
				
				return;
			}
			
			if($db->getErrorNum())
			{
				JLog::add('Load Users - After Load - Somthing went wrong. DB Error: '.$db->getErrorMsg().$query, JLog::ERROR, 'com_cjforum');
			}
		}
	}
	
	/**
	 * Gets the fill url of the user avatar image.
	 * 
	 * @param string $avatar avatar image name
	 * @param int $size height of the image to load
	 */
	public static function resolve_avatar_location($avatar, $size)
	{
		$size = ($size > 255 ? 256 : ($size > 191 ? 192 : ( $size > 159 ? 160 : ( $size > 127 ? 128 : ( $size > 95 ? 96 : ( $size > 63 ? 64 : ( $size > 47 ? 48 : ( $size > 31 ? 32 : 16 ) ) ) ) ) ) ) );
		
		return !empty($avatar) ? CF_AVATAR_URI.'size-'.$size.'/'.$avatar : CF_MEDIA_URI.'images/'.$size.'-nophoto.jpg';
	}
	
	public static function award_points($rule_name, $user_id=0, $points=0, $reference=null, $description=null)
	{
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$points = intval($points);
		
		if(CjForumApi::$_enable_logging)
		{
			JLog::add('CjForumApi.award_points - Rule: '.$rule_name.'| UserID: '.$user_id, JLog::DEBUG, 'com_cjforum');
		}

		if(strlen($rule_name) < 3) return false;
		if(!$user_id && $user->guest) return false;
		
		$user_id = $user_id > 0 ? $user_id : $user->id;
		
		$query = $db->getQuery(true)
			->select('id, name, asset_name, description, points, published, auto_approve, access')
			->from('#__cjforum_points_rules')
			->where('name='.$db->q($rule_name));

		if($db->getErrorNum())
		{
			JLog::add('CjForumApi.award_points - DB Error: '.$db->getErrorMsg(), JLog::ERROR, 'com_cjforum');
		}
		
		$db->setQuery($query);
		$rule = $db->loadObject();
		
		if(!$rule || !$rule->id || ($rule->published != '1') || ($points == 0 && $rule->points == 0)) return false;
		if(!in_array($rule->access, JAccess::getAuthorisedViewLevels($user_id))) return false;
		
		if(!$points || $points == 0) $points = $rule->points;
		
		if($reference)
		{
			$query = $db->getQuery(true)
				->select('count(*)')
				->from('#__cjforum_points')
				->where('user_id = '.$user_id.' and rule_id='.$rule->id.' and ref_id='.$db->q($reference));
			
			$db->setQuery($query);
			$count = (int)$db->loadResult();

			if($db->getErrorNum())
			{
				JLog::add('CjForumApi.award_points - DB Error: '.$db->getErrorMsg(), JLog::ERROR, 'com_cjforum');
			}

			if($count > 0) return false;
		}
		
		$reference = !$reference ? 'null' : $db->quote($reference);
		$description = !$description ? 'null' : $db->quote(CJFunctions::clean_value($description, true));
		$createdate = JFactory::getDate()->toSql();
		$published = $rule->auto_approve == 1 ? 1 : 2;
		
		$query = $db->getQuery(true)
			->insert('#__cjforum_points')
			->columns('user_id, rule_id, points, ref_id, published, description, created_by, created')
			->values($user_id.','.$rule->id.','.$points.','.$reference.','.$published.','.$description.','.$user->id.','.$db->q($createdate));
		
		$db->setQuery($query);
		
		if(!$db->query())
		{
			CjForumApi::$_errors[] = 'Error: '.$db->getErrorMsg();

			if($db->getErrorNum())
			{
				JLog::add('CjForumApi.award_points - DB Error: '.$db->getErrorMsg(), JLog::ERROR, 'com_cjforum');
			}

			return false;
		}
		
		$query = $db->getQuery(true)
			->update('#__cjforum_users')
			->set('points = points '.($points > 0 ? '+'.$points : '-'.abs($points)))
			->where('id = '.$user_id);
		
		$db->setQuery($query);
		
		if(!$db->query())
		{
			CjForumApi::$_errors[] = 'Error: '.$db->getErrorMsg();
		}
		
		$params = JComponentHelper::getParams('com_cjforum');
		
		if($user_id && $user_id == $user->id && ($params->get('display_messages', 0) == 1))
		{
			JFactory::getApplication()->enqueueMessage(JText::sprintf('MSG_POINTS_ASSIGNED', $points));
		}
		
		return true;
	}
	
	/**
	 * Gets the user rank image or the formatted rank text based on the values passed.
	 * 
	 * @param string $profile the name of the rank profile
	 * @param string $image the filename of the rank image
	 * @param string $title rank title
	 * 
	 * @return string Image path to the rank or the formatted html text of the rank
	 */
	public static function get_rank_image($user_id, $profile='default')
	{
		$pofile = CjForumApi::get_user_profile($user_id);
		
		if(empty($pofile['rank_image'])) 
		{
			return '<div class="panel highlighted">'.CJFunctions::escape($pofile['rank_title']).'</div>';
		} 
		else 
		{
			return JHtml::image(CF_RANK_IMAGES_URI.$pofile['catid'].'/'.$pofile['rank_image'], $pofile['rank_title']);
		}
	}
	
	public static function check_messages($userId)
	{
		$count = 0;
		$db = JFactory::getDbo();
		
		$query = $db->getQuery(true)
			->select('count(*)')
			->from('#__cjforum_messages_map')
			->where('receiver_id = '.$userId.' and receiver_state = 0');
		
		$db->setQuery($query);
		
		try
		{
			$count = $db->loadResult();
		}
		catch (Exception $e)
		{
			JLog::add('CjForumApi.check_messages - DB Error: '.$db->getErrorMsg(), JLog::ERROR, 'com_cjforum');
		}
		
		return $count;
	}
	
	public static function get_activity_date($strdate)
	{
		if(empty($strdate) || $strdate == '0000-00-00 00:00:00')
		{
			return JText::_('LBL_NA');
		}
		
		jimport('joomla.utilities.date');
		$user = JFactory::getUser();
		
		// Given time
		$date = new JDate(JHtml::date($strdate, 'Y-m-d H:i:s'));
		$compareTo = new JDate(JHtml::date('now', 'Y-m-d H:i:s'));
		$diff = $compareTo->toUnix() - $date->toUnix();
		
		$diff = abs($diff);
		$dayDiff = floor($diff/86400);
		
		if($dayDiff == 0)
		{
			if($diff < 3600)
			{
				return JText::sprintf('COM_CJFORUM_DATE_FORMAT_MINUTES', floor($diff/60));
			}
			else
			{
				return JText::sprintf('COM_CJFORUM_DATE_FORMAT_HOURS', floor($diff/3600));
			}
		} else
		{
			return $date->format(JText::_('COM_CJFORUM_DATE_FORMAT_FULL_DATE', false, false));
		}
	}
}