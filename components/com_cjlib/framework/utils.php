<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjlib
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

class CjLibUtils
{
	/**
	 * word-sensitive substring function with html tags awareness
	 *
	 * @param string text The text to cut
	 * @param int len The maximum length of the cut string
	 * @param array Array of tags to exclude
	 *
	 * @return string The modified html content
	 */
	public static function substrws( $text, $len=180, $tags=array()) 
	{
		if(function_exists('mb_strlen'))
		{
			if( (mb_strlen($text, 'UTF-8') > $len) ) 
			{
				$whitespaceposition = mb_strpos($text, ' ', $len, 'UTF-8')-1;
				if( $whitespaceposition > 0 ) 
				{
					$chars = count_chars(mb_substr($text, 0, $whitespaceposition + 1, 'UTF-8'), 1);
					if (!empty($chars[ord('<')]) && $chars[ord('<')] > $chars[ord('>')])
					{
						$whitespaceposition = mb_strpos($text, '>', $whitespaceposition, 'UTF-8') - 1;
					}
						
					$text = mb_substr($text, 0, $whitespaceposition + 1, 'UTF-8');
				}
					
				// close unclosed html tags
				if( preg_match_all("|<([a-zA-Z]+)|",$text,$aBuffer) ) 
				{
					if( !empty($aBuffer[1]) ) 
					{
						preg_match_all("|</([a-zA-Z]+)>|",$text,$aBuffer2);
						if( count($aBuffer[1]) != count($aBuffer2[1]) ) 
						{
							foreach( $aBuffer[1] as $index => $tag ) 
							{
								if( empty($aBuffer2[1][$index]) || $aBuffer2[1][$index] != $tag)
								{
									$text .= '</'.$tag.'>';
								}
							}
						}
					}
				}
			}
		} 
		else 
		{
			if( (strlen($text) > $len) ) 
			{
				$whitespaceposition = strpos($text, ' ', $len)-1;
				if( $whitespaceposition > 0 ) {
						
					$chars = count_chars(substr($text, 0, $whitespaceposition + 1), 1);
					if ($chars[ord('<')] > $chars[ord('>')])
					{
						$whitespaceposition = strpos($text, '>', $whitespaceposition) - 1;
					}
						
					$text = substr($text, 0, $whitespaceposition + 1);
				}
					
				// close unclosed html tags
				if( preg_match_all("|<([a-zA-Z]+)|",$text,$aBuffer) ) 
				{
					if( !empty($aBuffer[1]) ) 
					{
						preg_match_all("|</([a-zA-Z]+)>|",$text,$aBuffer2);
						if( count($aBuffer[1]) != count($aBuffer2[1]) ) 
						{
							foreach( $aBuffer[1] as $index => $tag ) 
							{
								if( empty($aBuffer2[1][$index]) || $aBuffer2[1][$index] != $tag)
								{
									$text .= '</'.$tag.'>';
								}
							}
						}
					}
				}
			}
		}
	
		return preg_replace('#<p[^>]*>(\s|&nbsp;?)*</p>#', '', $text);;
	}

	/**
	 * Convert special characters to HTML entities with UTF-8 encoding.
	 * 
	 * @param string $var content to be escaped
	 */
	public static function escape($var)
	{
		return htmlspecialchars($var, ENT_COMPAT, 'UTF-8');
	}
	
	/**
	 * Returns unicode alias string from the <code>title</code> passed as an argument. If the Joomla version is less than 1.6, the function will gracefully degrades and outputs normal alias.
	 *
	 * @param string $title
	 */
	public static function getUrlSafeString($title){

		if (JFactory::getConfig()->get('unicodeslugs') == 1) {
		
			return JFilterOutput::stringURLUnicodeSlug($title);
		} else {
		
			return JFilterOutput::stringURLSafe($title);
		}
	}

	/**
	 * Gets the ip address of the user from request
	 *
	 * @return string ip address
	 */
	public static function getUserIpAddress() {

		$ip = '';

		if( !empty($_SERVER['HTTP_X_FORWARDED_FOR']) AND strlen($_SERVER['HTTP_X_FORWARDED_FOR'])>6 ){
				
			$ip = strip_tags($_SERVER['HTTP_X_FORWARDED_FOR']);
		}elseif( !empty($_SERVER['HTTP_CLIENT_IP']) AND strlen($_SERVER['HTTP_CLIENT_IP'])>6 ){
				
			$ip = strip_tags($_SERVER['HTTP_CLIENT_IP']);
		}elseif(!empty($_SERVER['REMOTE_ADDR']) AND strlen($_SERVER['REMOTE_ADDR'])>6){
				
			$ip = strip_tags($_SERVER['REMOTE_ADDR']);
		}
// 		$ip = explode(',', $ip);
// 		$ip = $ip[0];
		return trim($ip);
	}

	/**
	 * Gets the formatted number in the format 10, 100, 1000, 10k, 20.1k etc
	 * 
	 * @param integer $num number to format
	 * @return string formatted number
	 */
	public static function formatNumber ($num)
	{
		$num = (int) $num;
		if ($num < 1000)
		{
			return $num;
		}
	
		if ($num < 10000)
		{
			return substr($num, 0, 1).','.substr($num, 1);
		}
	
		return round($num/1000, 1).'k';
	}
	

	/**
	 * Generate a random character string
	 *
	 * @param int $length length of the string to be generated
	 * @param string $chars characters to be considered, default alphanumeric characters.
	 *
	 * @return string randomly generated string
	 */
	public static function getRandomKey($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890'){
	
		// Length of character list
		$chars_length = (strlen($chars) - 1);
			
		// Start our string
		$string = $chars{rand(0, $chars_length)};
	
		// Generate random string
		for ($i = 1; $i < $length; $i = strlen($string))
		{
			// Grab a random character from our list
			$r = $chars{rand(0, $chars_length)};
	
			// Make sure the same two characters don't appear next to each other
			if ($r != $string{$i - 1}) $string .=  $r;
		}
	
		// Return the string
		return $string;
	}
	
	public static function getCurrentUrl()
	{
		$uri = JFactory::getURI();
		$absolute_url = $uri->toString();
		
		return JRoute::_($absolute_url);
	}
	
	public static function getCategoryOptions($extension, $acl = false)
	{
		$options = array();
		$published = array(0, 1);
		$jinput = JFactory::getApplication()->input;
		$oldCat = $jinput->get('id', 0);
		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('DISTINCT a.id AS value, a.title AS text, a.level, a.published, a.lft, a.language');
		
		$subQuery = $db->getQuery(true)
			->select('id,title,level,published,parent_id,extension,lft,rgt,language')
			->from('#__categories')
			->where('(extension = ' . $db->quote($extension) . ')')
			->where('published IN (' . implode(',', $published) . ')');
		
		// Filter language
		$languages = array(JFactory::getLanguage()->getTag(), '*');
		$subQuery->where('language IN (' . implode(',', $db->quote($languages)).')');
		
		$query
			->from('(' . $subQuery->__toString() . ') AS a')
			->join('LEFT', $db->quoteName('#__categories') . ' AS b ON a.lft > b.lft AND a.rgt < b.rgt')
			->order('a.lft ASC');
		
		// Get the options.
		$db->setQuery($query);
		
		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
		}
		
		// Pad the option text with spaces using depth level as a multiplier.
		for ($i = 0, $n = count($options); $i < $n; $i++)
		{
			if ($options[$i]->published == 1)
			{
				$options[$i]->text = str_repeat('- ', $options[$i]->level) . $options[$i]->text;
			}
			else
			{
				$options[$i]->text = str_repeat('- ', $options[$i]->level) . '[' . $options[$i]->text . ']';
			}
		}
		
		// Get the current user object.
		$user = JFactory::getUser();
		if($acl)
		{
			foreach ($options as $i => $option)
			{
				if ($user->authorise($acl, $extension . '.category.' . $option->value) != true && $option->level != 0)
				{
					unset($options[$i]);
				}
			}
		}
		else 
		// For new items we want a list of categories you are allowed to create in.
		if ($oldCat == 0)
		{
			foreach ($options as $i => $option)
			{
				/* To take save or create in a category you need to have create rights for that category
				 * unless the item is already in that category.
				 * Unset the option if the user isn't authorised for it. In this field assets are always categories.
				 */
				if ($user->authorise('core.create', $extension . '.category.' . $option->value) != true && $option->level != 0)
				{
					unset($options[$i]);
				}
			}
		}
		// If you have an existing category id things are more complex.
		else
		{
			/* If you are only allowed to edit in this category but not edit.state, you should not get any
			 * option to change the category parent for a category or the category for a content item,
			 * but you should be able to save in that category.
			 */
			foreach ($options as $i => $option)
			{
				if ($user->authorise('core.edit.state', $extension . '.category.' . $oldCat) != true && !isset($oldParent))
				{
					if ($option->value != $oldCat)
					{
						unset($options[$i]);
					}
				}
		
				if ($user->authorise('core.edit.state', $extension . '.category.' . $oldCat) != true
						&& (isset($oldParent))
						&& $option->value != $oldParent)
				{
					unset($options[$i]);
				}
		
				// However, if you can edit.state you can also move this to another category for which you have
				// create permission and you should also still be able to save in the current category.
				if (($user->authorise('core.create', $extension . '.category.' . $option->value) != true)
						&& ($option->value != $oldCat && !isset($oldParent)))
				{
					{
						unset($options[$i]);
					}
				}
		
				if (($user->authorise('core.create', $extension . '.category.' . $option->value) != true)
						&& (isset($oldParent))
						&& $option->value != $oldParent)
				{
					{
						unset($options[$i]);
					}
				}
			}
		}
		
		return $options;
	}
	
	public static function getUserLocation($ip)
	{
		require_once CJLIB_PATH.'/lib/maxmind/ProviderInterface.php';
		require_once CJLIB_PATH.'/lib/maxmind/Database/Reader.php';
		require_once CJLIB_PATH.'/lib/maxmind/MaxMind/Db/Reader/Decoder.php';
		require_once CJLIB_PATH.'/lib/maxmind/MaxMind/Db/Reader/Util.php';
		require_once CJLIB_PATH.'/lib/maxmind/MaxMind/Db/Reader/Metadata.php';
		require_once CJLIB_PATH.'/lib/maxmind/Compat/JsonSerializable.php';
		require_once CJLIB_PATH.'/lib/maxmind/Record/AbstractRecord.php';
		require_once CJLIB_PATH.'/lib/maxmind/Record/AbstractPlaceRecord.php';
		require_once CJLIB_PATH.'/lib/maxmind/Record/Continent.php';
		require_once CJLIB_PATH.'/lib/maxmind/Record/Country.php';
		require_once CJLIB_PATH.'/lib/maxmind/Record/City.php';
		require_once CJLIB_PATH.'/lib/maxmind/Record/Location.php';
		require_once CJLIB_PATH.'/lib/maxmind/Record/Postal.php';
		require_once CJLIB_PATH.'/lib/maxmind/Record/Subdivision.php';
		require_once CJLIB_PATH.'/lib/maxmind/Record/MaxMind.php';
		require_once CJLIB_PATH.'/lib/maxmind/Record/RepresentedCountry.php';
		require_once CJLIB_PATH.'/lib/maxmind/Record/Traits.php';
		require_once CJLIB_PATH.'/lib/maxmind/Model/AbstractModel.php';
		require_once CJLIB_PATH.'/lib/maxmind/Model/Country.php';
		require_once CJLIB_PATH.'/lib/maxmind/Model/City.php';
		require_once CJLIB_PATH.'/lib/maxmind/MaxMind/Db/Reader.php';
		
		$info = array();
		$info['continent'] = 'Unknown';
		$info['country'] = "Unknown";
		$info['country_code'] = "XX";
		$info['country_code_3'] = "XXX";
		$info['city'] = "Unknown";
		$info['lattitude'] = '';
		$info['longitude'] = '';
		
		if(!file_exists(JPATH_ROOT.'/media/com_cjlib/geoip/GeoLite2-City.mmdb'))
		{
			return $info;
		}
		
		try 
		{
			$reader = new GeoIp2\Database\Reader(JPATH_ROOT.'/media/com_cjlib/geoip/GeoLite2-City.mmdb');
			$record = $reader->city($ip);
			
			if($record)
			{
				$info['country'] = $record->country->name;
				$info['country_code'] = $record->country->isoCode;
				$info['country_code_3'] = $record->country->isoCode;
				$info['city'] = $record->city->name;
				$info['lattitude'] = $record->location->latitude;
				$info['longitude'] = $record->location->longitude;
			}
		}
		catch (Exception $e)
		{
			$user = JFactory::getUser();
			if($user->authorise('core.admin', 'com_communitysurveys'))
			{
				JFactory::getApplication()->enqueueMessage($e->getMessage());
			}
		}
		
		return $info;
	}
}
