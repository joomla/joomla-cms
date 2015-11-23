<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;
require_once JPATH_ROOT.'/components/com_cjforum/helpers/route.php';

class CjForumProfileApi 
{
	private static $_users = array();
	private $_enable_logging = false;
	
	public function __construct ($config = array())
	{
		if(isset($config['logging']))
		{
			$this->_enable_logging = true;
		}
		
		JFactory::getLanguage()->load('com_cjforum', JPATH_ROOT);
	}
	
	/**
	 * Gets the user profile/profiles of a given id or ids.
	 *
	 * @param mixed $identifier id/ids of the user(s)
	 * @param boolean $force_reload tells if the profiles should be loaded forcibly or not
	 *
	 * @return mixed single or array of user profile associative array.
	 */
	public function getUserProfile($identifier, $force_reload = false)
	{
		if(is_array($identifier))
		{
			$return = array();
			$this->load($identifier, $force_reload);
				
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
			$this->load(array($identifier), $force_reload);
				
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
	public function getUserAvatarImage($identifiers, $size = 48, $force_reload = false)
	{
		$size = ($size > 224 ? 256 : ($size > 160 ? 192 : ( $size > 128 ? 160 : ( $size > 96 ? 128 : ( $size > 64 ? 96 : ( $size > 48 ? 64 : ( $size > 32 ? 48 : ( $size > 23 ? 32 : 16 ) ) ) ) ) ) ) );
		$defaultAvatar = CF_MEDIA_URI.'images/'.$size.'-nophoto.jpg';
		$avatarLocation = CF_AVATAR_BASE_URI.'size-'.$size.'/';
	
		if(is_numeric($identifiers))
		{
			$profile = $this->getUserProfile($identifiers, $force_reload);
				
			if($profile && !empty($profile['avatar']))
			{
				return $avatarLocation.$profile['avatar'];
			}
		}
		elseif (is_array($identifiers))
		{
			$return = array();
			$profiles = $this->getUserProfile($identifiers, $force_reload);
				
			if(!empty($profiles))
			{
				foreach ($profiles as $userid=>$profile)
				{
					if(!empty($profile['avatar']))
					{
						$return[$userid] = $avatarLocation.$profile['avatar'];
					}
					else
					{
						$return[$userid] = $defaultAvatar;
					}
				}
			}
			else
			{
				foreach ($identifiers as $userid)
				{
					if($userid)
					{
						$return[$userid] = $defaultAvatar;
					}
				}
			}

			return $return;
		}

		return $defaultAvatar;
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
	public function getUserProfileLink($identifiers, $username = 'name', $path_only = false, $attribs = null, $xhtml = true, $ssl = null)
	{
		require_once JPATH_ROOT.'/components/com_cjforum/router.php';
		$profiles = $this->getUserProfile($identifiers);
	
		if($this->_enable_logging)
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
					return JHtml::link(CjForumHelperRoute::getProfileRoute($profiles['id']), htmlspecialchars($profiles['author'], ENT_COMPAT, 'UTF-8'), $attribs);
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
							$return[$profile['id']] = JHtml::link(CjForumHelperRoute::getProfileRoute($profiles['id']), htmlspecialchars($profile[$username], ENT_COMPAT, 'UTF-8'), $attribs);
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
	public function getUserAvatar($userids, $size = 48, $username = 'name', array $attribs = array(), array $image_attribs = array())
	{
		if(!array_key_exists('height', $image_attribs))
		{
			$image_attribs['height'] = $size;
		}
	
		if(!is_array($userids)) $userids = intval($userids);
	
		if(is_numeric($userids))
		{
			$profile = $this->getUserProfile($userids);
			$avatarLocation = $this->getUserAvatarImage($userids, $size);
	
			$attribs['class'] = empty($attribs['class']) ? 'tooltip-hover' : $attribs['class'].' tooltip-hover';
			$attribs['title'] = empty($attribs['title']) ? $profile[$username] : $attribs['title'];
	
			$avatar_image = '<img src="'.$avatarLocation.'" alt="'.$attribs['title'].'" '.JArrayHelper::toString($image_attribs).'/>';
			$profileUrl = $this->getUserProfileLink($userids, $username, true);
	
			return JHtml::link($profileUrl, $avatar_image, $attribs);
		}
		elseif(is_array($userids) && !empty($userids))
		{
			$avatar_images = $this->getUserAvatarImage($userids, $size);
			$profileUrls = $this->getUserProfileLink($userids, $username, true);
			$profiles = $this->getUserProfile($userids);
			$return = array();
				
			foreach ($userids as $userid)
			{
				if(!empty($avatar_images[$userid]) && !empty($profileUrls[$userid]))
				{
					$attribs['class'] = empty($attribs['class']) ? 'tooltip-hover' : $attribs['class'].' tooltip-hover';
					$attribs['title'] = htmlspecialchars($profiles[$userid][$username], ENT_COMPAT, 'UTF-8');
	
					$avatarLocation = $this->getUserAvatarImage($userids, $size);
					$avatar_image = '<img src="'.$avatarLocation[$userid].'" alt="'.$attribs['title'].'" '.JArrayHelper::toString($image_attribs).'/>';
	
					$return[$userid] = JHtml::link($profileUrls[$userid], $avatar_image, $attribs);
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
	public function load(array $identifiers = array(), $force_reload = false)
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
				->select('ju.id, u.avatar, u.about, u.hits, u.birthday, u.last_post_time, u.points, u.topics, u.replies, u.fans, u.thankyou, u.signature, u.banned')
				->select('u.last_access_date, u.current_access_date, ju.name, ju.username, ju.name as author, ju.email, ju.block, ju.registerDate, ju.lastvisitDate, ju.params')
				->select('rnk.id as rank_id, rnk.title as rank_title, rnk.rank_type, rnk.min_posts, rnk.rank_image, rnk.rank_class, rnk.catid')
				->select("CASE WHEN u.handle is null OR trim(u.handle) = '' THEN ju.username ELSE u.handle END AS handle")
				->select('u.gender, u.location, u.twitter, u.facebook, u.gplus, u.linkedin, u.flickr, u.bebo, u.skype')
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
	
				if($this->_enable_logging)
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
	public function resolveAvatarLocation($avatar, $size)
	{
		$size = ($size > 255 ? 256 : ($size > 191 ? 192 : ( $size > 159 ? 160 : ( $size > 127 ? 128 : ( $size > 95 ? 96 : ( $size > 63 ? 64 : ( $size > 47 ? 48 : ( $size > 31 ? 32 : 16 ) ) ) ) ) ) ) );
	
		return !empty($avatar) ? CF_AVATAR_BASE_URI.'size-'.$size.'/'.$avatar : CF_MEDIA_URI.'images/'.$size.'-nophoto.jpg';
	}
}